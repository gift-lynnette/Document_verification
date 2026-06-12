from __future__ import annotations

import argparse
import hashlib
import json
import struct
import sys
from pathlib import Path
from typing import Any

from ocr import extract_text
from validators import classify_document, validate_document


ENGINE_VERSION = "1.0.0"


IMAGE_EXTENSIONS = {".jpg", ".jpeg", ".png", ".bmp", ".tif", ".tiff"}


def load_config() -> dict[str, Any]:
    config_path = Path(__file__).with_name("config.json")
    return json.loads(config_path.read_text(encoding="utf-8"))


def clean_result(result: dict[str, Any]) -> dict[str, Any]:
    result.setdefault("document_type", "unknown")
    result.setdefault("status", "REJECTED")
    result.setdefault("confidence_score", 0)
    result.setdefault("extracted_fields", {})
    result.setdefault("missing_fields", [])
    result.setdefault("risk_flags", [])
    result.setdefault("engine_version", ENGINE_VERSION)
    return result


def verify_file(file_path: str, expected_type: str | None = None) -> dict[str, Any]:
    config = load_config()
    path = Path(file_path)

    if not path.exists() or not path.is_file():
        return clean_result({
            "document_type": expected_type or "unknown",
            "status": "REJECTED",
            "confidence_score": 0,
            "extracted_fields": {},
            "missing_fields": [],
            "risk_flags": ["invalid file"],
            "error": "File does not exist",
        })

    max_size = int(config.get("max_file_size_mb", 10)) * 1024 * 1024
    if path.stat().st_size <= 0:
        return clean_result({
            "document_type": expected_type or "unknown",
            "status": "REJECTED",
            "confidence_score": 0,
            "extracted_fields": {},
            "missing_fields": [],
            "risk_flags": ["empty file"],
        })

    if path.stat().st_size > max_size:
        return clean_result({
            "document_type": expected_type or "unknown",
            "status": "REJECTED",
            "confidence_score": 0,
            "extracted_fields": {},
            "missing_fields": [],
            "risk_flags": ["file exceeds verification size limit"],
        })

    if expected_type == "passport_photo":
        return verify_passport_photo(path)

    text = extract_text(str(path), expected_type)
    if path.suffix.lower() == ".pdf" and _looks_like_pdf_binary(text):
        return clean_result({
            "document_type": expected_type or "unknown",
            "status": "REVIEW",
            "confidence_score": 50,
            "extracted_fields": {},
            "missing_fields": [],
            "risk_flags": ["PDF text extraction unavailable; install Poppler/Tesseract for full automated verification"],
            "ocr_text_hash": hashlib.sha256(text.encode("utf-8", errors="ignore")).hexdigest() if text else None,
            "ocr_text_preview": text[:500],
        })
    document_type, classification_score = classify_document(text, config, expected_type)

    if document_type not in config["document_rules"]:
        return clean_result({
            "document_type": "unknown",
            "status": "REVIEW" if text.strip() else "REJECTED",
            "confidence_score": min(classification_score, 40),
            "extracted_fields": {},
            "missing_fields": [],
            "risk_flags": ["unable to classify document"] if text.strip() else ["OCR failure", "empty text"],
            "ocr_text_hash": hashlib.sha256(text.encode("utf-8", errors="ignore")).hexdigest() if text else None,
        })

    result = validate_document(text, document_type, config)
    result["document_type"] = document_type
    result["classification_score"] = classification_score
    result["ocr_text_hash"] = hashlib.sha256(text.encode("utf-8", errors="ignore")).hexdigest() if text else None
    result["ocr_text_preview"] = text[:500]
    return clean_result(result)


def verify_passport_photo(path: Path) -> dict[str, Any]:
    if path.suffix.lower() not in IMAGE_EXTENSIONS:
        return clean_result({
            "document_type": "passport_photo",
            "status": "REJECTED",
            "confidence_score": 0,
            "extracted_fields": {},
            "missing_fields": ["image_file"],
            "risk_flags": ["passport photo must be an image"],
        })

    dimensions = _read_image_dimensions(path)
    if dimensions is None:
        return clean_result({
            "document_type": "passport_photo",
            "status": "REJECTED",
            "confidence_score": 0,
            "extracted_fields": {},
            "missing_fields": ["readable_image"],
            "risk_flags": ["image could not be read"],
        })
    width, height = dimensions

    ratio = width / max(1, height)
    flags: list[str] = []
    if width < 240 or height < 240:
        flags.append("image resolution below 240x240")
    if ratio < 0.55 or ratio > 1.25:
        flags.append("image is not a portrait-style passport photo")

    return clean_result({
        "document_type": "passport_photo",
        "status": "APPROVED" if not flags else "REJECTED",
        "confidence_score": 90 if not flags else 35,
        "extracted_fields": {"width": width, "height": height, "aspect_ratio": round(ratio, 3)},
        "missing_fields": [],
        "risk_flags": flags,
    })


def _looks_like_pdf_binary(text: str) -> bool:
    head = text[:1200]
    return "%PDF-" in head and " obj" in head


def _read_image_dimensions(path: Path) -> tuple[int, int] | None:
    data = path.read_bytes()[:65536]

    if data.startswith(b"\x89PNG\r\n\x1a\n") and len(data) >= 24:
        return struct.unpack(">II", data[16:24])

    if data.startswith(b"\xff\xd8"):
        offset = 2
        while offset + 9 < len(data):
            if data[offset] != 0xFF:
                offset += 1
                continue
            marker = data[offset + 1]
            offset += 2
            if marker in {0xD8, 0xD9}:
                continue
            if offset + 2 > len(data):
                return None
            length = struct.unpack(">H", data[offset:offset + 2])[0]
            if length < 2 or offset + length > len(data):
                return None
            if marker in {0xC0, 0xC1, 0xC2, 0xC3, 0xC5, 0xC6, 0xC7, 0xC9, 0xCA, 0xCB, 0xCD, 0xCE, 0xCF}:
                if offset + 7 > len(data):
                    return None
                height = struct.unpack(">H", data[offset + 3:offset + 5])[0]
                width = struct.unpack(">H", data[offset + 5:offset + 7])[0]
                return width, height
            offset += length

    return None


def main() -> int:
    parser = argparse.ArgumentParser(description="Verify a student document and emit strict JSON.")
    parser.add_argument("file_path")
    parser.add_argument(
        "--expected-type",
        choices=[
            "admission_letter",
            "certificate",
            "national_id_passport",
            "former_school_id",
            "passport_photo",
            "award_letter",
            "bank_slip",
        ],
        default=None,
    )
    args = parser.parse_args()

    try:
        result = verify_file(args.file_path, args.expected_type)
    except Exception as exc:
        result = clean_result({
            "document_type": args.expected_type or "unknown",
            "status": "REJECTED",
            "confidence_score": 0,
            "extracted_fields": {},
            "missing_fields": [],
            "risk_flags": ["engine execution failure"],
            "error": str(exc),
        })

    sys.stdout.write(json.dumps(result, ensure_ascii=False, separators=(",", ":")))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
