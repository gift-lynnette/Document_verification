from __future__ import annotations

import json
import re
from pathlib import Path
from typing import Any

from ocr import extract_text


ROOT = Path(__file__).resolve().parents[1]
REFERENCE_ROOT = ROOT / "reference_documents"
OUTPUT = Path(__file__).with_name("reference_standards.json")

TYPE_DIRS = {
    "admission_letter": "admission_letters",
    "certificate": "certificates",
    "bank_slip": "bank_slips",
    "award_letter": "bursary_letters",
    "former_school_id": "school_ids",
    "national_id_passport": "national_ids",
}

STOPWORDS = {
    "the", "and", "for", "with", "this", "that", "from", "your", "you", "are",
    "has", "have", "will", "shall", "to", "of", "in", "on", "or", "a", "an",
    "as", "by", "is", "be", "no", "not", "it", "at", "if", "within",
}


def normalize(text: str) -> str:
    return re.sub(r"\s+", " ", re.sub(r"[^a-z0-9\s]", " ", text.lower())).strip()


def tokens(text: str) -> list[str]:
    normalized = normalize(text)
    words = [word for word in normalized.split() if len(word) >= 3 and word not in STOPWORDS]
    return sorted(set(words))


def make_profile(document_type: str, path: Path) -> dict[str, Any]:
    text = path.read_text(encoding="utf-8", errors="ignore") if path.suffix.lower() == ".txt" else extract_text(str(path), document_type)
    token_list = tokens(text)
    return {
        "source": str(path.relative_to(ROOT)).replace("\\", "/"),
        "document_type": document_type,
        "text_length": len(text.strip()),
        "tokens": token_list[:500],
        "token_count": len(token_list),
        "preview": " ".join(text.split())[:300],
    }


def main() -> int:
    standards: dict[str, Any] = {
        "version": 1,
        "description": "Field-specific document standards generated from known authentic uploads.",
        "document_types": {},
    }

    for document_type, folder in TYPE_DIRS.items():
        directory = REFERENCE_ROOT / folder
        profiles = []
        if directory.is_dir():
            for path in sorted(directory.iterdir()):
                if path.is_file() and path.suffix.lower() in {".txt", ".pdf", ".jpg", ".jpeg", ".png"}:
                    profile = make_profile(document_type, path)
                    if profile["text_length"] > 0 or document_type == "passport_photo":
                        profiles.append(profile)
        standards["document_types"][document_type] = {"profiles": profiles}

    OUTPUT.write_text(json.dumps(standards, indent=2, ensure_ascii=False), encoding="utf-8")
    print(f"Wrote {OUTPUT.relative_to(ROOT)}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
