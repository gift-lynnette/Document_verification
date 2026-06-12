from __future__ import annotations

import os
import re
import shutil
import subprocess
import uuid
from pathlib import Path

from preprocess import preprocess_image_variants


IMAGE_EXTENSIONS = {".jpg", ".jpeg", ".png", ".bmp", ".tif", ".tiff"}
OCR_PSM_MODES = (3, 12, 11, 6)
_COMMON_OCR_KEYWORDS = (
    "admission",
    "certificate",
    "school",
    "student",
    "university",
    "bank",
    "receipt",
    "reference",
    "date",
    "name",
    "program",
    "course",
    "result",
    "exam",
    "academic",
    "campus",
    "uganda",
    "admitted",
)
_TYPE_OCR_HINTS = {
    "admission_letter": (
        "admission",
        "admitted",
        "offer",
        "program",
        "programme",
        "entry",
        "intake",
        "registrar",
    ),
    "certificate": (
        "certificate",
        "exam",
        "grade",
        "result",
        "education",
        "school",
        "university",
    ),
    "former_school_id": (
        "school",
        "student",
        "id",
        "card",
        "valid",
        "institution",
        "member",
        "year",
    ),
    "bank_slip": (
        "bank",
        "receipt",
        "reference",
        "transaction",
        "amount",
        "paid",
        "deposit",
        "cash",
        "teller",
    ),
    "award_letter": (
        "award",
        "bursary",
        "scholarship",
        "sponsorship",
        "financial",
        "aid",
    ),
    "national_id_passport": (
        "national",
        "passport",
        "identity",
        "citizenship",
        "dob",
    ),
}


def _find_executable(name: str, env_name: str, candidates: list[str]) -> str | None:
    configured = os.environ.get(env_name)
    if configured and Path(configured).is_file():
        return configured

    discovered = shutil.which(name)
    if discovered:
        return discovered

    for candidate in candidates:
        expanded = Path(os.path.expandvars(candidate))
        if expanded.is_file():
            return str(expanded)

    return None


def _run_command(command: list[str], timeout: int = 30) -> str:
    try:
        completed = subprocess.run(
            command,
            capture_output=True,
            text=True,
            encoding="utf-8",
            errors="ignore",
            timeout=timeout,
            check=False,
        )
    except Exception:
        return ""

    if completed.returncode != 0:
        return ""
    return (completed.stdout or "").strip()


def _ocr_with_tesseract(image_path: str, psm: int) -> str:
    tesseract = _find_executable("tesseract", "TESSERACT_PATH", [
        r"C:\Program Files\Tesseract-OCR\tesseract.exe",
        r"C:\Program Files (x86)\Tesseract-OCR\tesseract.exe",
    ])
    if not tesseract:
        return ""

    return _run_command([tesseract, image_path, "stdout", "--oem", "1", "--psm", str(psm)], timeout=35)


def _ocr_tesseract_digits_whitelist(image_path: str, psm: int) -> str:
    """Restricted character set OCR for handwritten amount rows (TOTAL / figures)."""
    tesseract = _find_executable("tesseract", "TESSERACT_PATH", [
        r"C:\Program Files\Tesseract-OCR\tesseract.exe",
        r"C:\Program Files (x86)\Tesseract-OCR\tesseract.exe",
    ])
    if not tesseract:
        return ""

    return _run_command(
        [
            tesseract,
            image_path,
            "stdout",
            "--oem",
            "1",
            "--psm",
            str(psm),
            "-c",
            "tessedit_char_whitelist=0123456789,/",
        ],
        timeout=35,
    )


def _digits_whitelist_ocr_candidates(image_path: str) -> list[str]:
    """Try several PSMs tuned for a single handwritten number line."""
    out: list[str] = []
    for psm in (7, 8, 6, 13, 11, 12):
        text = _ocr_tesseract_digits_whitelist(image_path, psm)
        if text:
            out.append(text)
    return out


def _score_candidate(text: str, expected_type: str | None = None) -> tuple[int, int, int, int, int]:
    cleaned = " ".join((text or "").split())
    if not cleaned:
        return (0, 0, 0, 0, 0)

    alpha_words = [word for word in cleaned.split() if word.isalpha() and len(word) >= 3]
    lower_cleaned = cleaned.lower()
    hints = list(_COMMON_OCR_KEYWORDS) + list(_TYPE_OCR_HINTS.get(expected_type, ()))
    keyword_hits = sum(1 for keyword in hints if keyword in lower_cleaned)
    distinct_words = len(set(word.lower() for word in alpha_words))
    structured_words = sum(1 for word in alpha_words if len(word) <= 14)
    reference_score = 0
    if expected_type:
        try:
            from validators.rules import reference_similarity_score  # type: ignore

            reference_score = reference_similarity_score(text, expected_type)
        except Exception:
            reference_score = 0

    if expected_type == "bank_slip":
        # Favor candidates where OCR captures the total row amount.
        has_total = 1 if re.search(r"\bt[o0]t[a4]l\b", cleaned, re.IGNORECASE) else 0
        has_large_amount = (
            1
            if re.search(
                r"\b\d{1,3}(?:,\d{3})+(?:\.\d{2})?\b|\b\d{6,9}\b", cleaned
            )
            else 0
        )
        keyword_hits += has_total * 10 + has_large_amount * 8
        lc = cleaned.lower()
        if "total" in lc and re.search(r"\d{5,}", cleaned):
            keyword_hits += 12

    return (reference_score, keyword_hits, structured_words, distinct_words, len(cleaned))


def _best_text_candidate(candidates: list[str], expected_type: str | None = None) -> str:
    best_text = ""
    best_score = (-1, -1, -1, -1, -1)
    for candidate in candidates:
        score = _score_candidate(candidate, expected_type)
        if score > best_score:
            best_score = score
            best_text = candidate
    return best_text


def _extract_pdf_text_candidates(path: str, expected_type: str | None = None) -> list[str]:
    candidates: list[str] = []

    pdftotext = _find_executable("pdftotext", "PDFTOTEXT_PATH", [
        str(Path(__file__).resolve().parents[1] / "tools" / "poppler" / "bin" / "pdftotext.exe"),
        r"%LOCALAPPDATA%\Microsoft\WinGet\Packages\oschwartz10612.Poppler_Microsoft.Winget.Source_8wekyb3d8bbwe\poppler-25.07.0\Library\bin\pdftotext.exe",
        r"C:\Program Files\Git\mingw64\bin\pdftotext.exe",
        r"C:\Program Files\poppler\Library\bin\pdftotext.exe",
        r"C:\poppler\Library\bin\pdftotext.exe",
    ])
    if pdftotext:
        text = _run_command([pdftotext, "-layout", path, "-"], timeout=35)
        if text and not _text_needs_image_ocr(text):
            candidates.append(text)

    try:
        from pypdf import PdfReader  # type: ignore

        reader = PdfReader(path)
        text = "\n".join((page.extract_text() or "") for page in reader.pages).strip()
        if text and not _text_needs_image_ocr(text):
            candidates.append(text)
    except Exception:
        pass

    scanned_candidates = _extract_scanned_pdf_text_candidates(path, expected_type)
    candidates.extend(scanned_candidates)
    return candidates


def _extract_image_text_candidates(path: str, expected_type: str | None = None) -> list[str]:
    prepared_paths = preprocess_image_variants(path, expected_type)
    if not prepared_paths:
        prepared_paths = [path]

    handwritten_paths: list[str] = []
    if expected_type == "bank_slip":
        try:
            from preprocess import preprocess_handwritten_digit_line_variants

            handwritten_paths = preprocess_handwritten_digit_line_variants(path)
        except Exception:
            handwritten_paths = []

    merged_paths = list(dict.fromkeys(prepared_paths + handwritten_paths))

    candidates: list[str] = []

    # Full-page binarization variants multiply cost; whitelist digits only on raw + ink-focused stacks.
    digit_whitelist_base_paths = (
        list(dict.fromkeys([path] + handwritten_paths))
        if expected_type == "bank_slip"
        else []
    )

    for prepared_path in merged_paths:
        for psm in OCR_PSM_MODES:
            text = _ocr_with_tesseract(prepared_path, psm)
            if text:
                candidates.append(text)

    if digit_whitelist_base_paths:
        for base_path in digit_whitelist_base_paths:
            for dz in _digits_whitelist_ocr_candidates(base_path):
                candidates.append(dz)

    return candidates


def _extract_image_text(path: str, expected_type: str | None = None) -> str:
    return _best_text_candidate(_extract_image_text_candidates(path, expected_type), expected_type)


def _text_needs_image_ocr(text: str) -> bool:
    cleaned = " ".join(text.split()).strip().lower()
    if not cleaned:
        return True
    if cleaned.startswith("%pdf-") and " obj" in cleaned[:2000]:
        return True
    if cleaned in {"camscanner", "scanned with camscanner"}:
        return True
    if len(cleaned) < 40 and "camscanner" in cleaned:
        return True
    return False


def _extract_scanned_pdf_text_candidates(path: str, expected_type: str | None = None, max_pages: int = 3) -> list[str]:
    pdftoppm = _find_executable("pdftoppm", "PDFTOPPM_PATH", [
        str(Path(__file__).resolve().parents[1] / "tools" / "poppler" / "bin" / "pdftoppm.exe"),
        r"%LOCALAPPDATA%\Microsoft\WinGet\Packages\oschwartz10612.Poppler_Microsoft.Winget.Source_8wekyb3d8bbwe\poppler-25.07.0\Library\bin\pdftoppm.exe",
        r"C:\Program Files\poppler\Library\bin\pdftoppm.exe",
        r"C:\poppler\Library\bin\pdftoppm.exe",
    ])
    if not pdftoppm:
        return []

    try:
        temp_root = Path(__file__).resolve().parents[1] / ".tmp_ocr"
        temp_root.mkdir(parents=True, exist_ok=True)
        temp_dir_path = temp_root / f"kiu_pdf_ocr_{uuid.uuid4().hex}"
        temp_dir_path.mkdir(parents=True, exist_ok=False)
        try:
            output_prefix = str(temp_dir_path / "page")
            converted = subprocess.run(
                [pdftoppm, "-r", "220", "-png", "-f", "1", "-l", str(max_pages), path, output_prefix],
                capture_output=True,
                text=True,
                encoding="utf-8",
                errors="ignore",
                timeout=60,
                check=False,
            )
            if converted.returncode != 0 and not list(temp_dir_path.glob("page-*.png")):
                return []

            candidates: list[str] = []
            page_images = sorted(temp_dir_path.glob("page-*.png"))
            for psm in OCR_PSM_MODES:
                page_texts: list[str] = []
                for image_path in page_images:
                    text = _ocr_with_tesseract(str(image_path), psm)
                    if text:
                        page_texts.append(text)
                candidate = "\n".join(page_texts).strip()
                if candidate:
                    candidates.append(candidate)
            return candidates
        finally:
            shutil.rmtree(temp_dir_path, ignore_errors=True)
    except Exception:
        return []


def extract_text_candidates(path: str, expected_type: str | None = None) -> list[str]:
    file_path = Path(path)
    suffix = file_path.suffix.lower()

    if suffix == ".pdf":
        return _extract_pdf_text_candidates(str(file_path), expected_type)

    if suffix in IMAGE_EXTENSIONS:
        return _extract_image_text_candidates(str(file_path), expected_type)

    try:
        raw = file_path.read_bytes()[:262144]
        text = raw.decode("utf-8", errors="ignore").strip()
        return [text] if text else []
    except Exception:
        return []


def extract_text(path: str, expected_type: str | None = None) -> str:
    text = _best_text_candidate(extract_text_candidates(path, expected_type), expected_type)

    # Tag very wide TOTAL-row screenshots so parsers do not hallucinate totals from handwritten digits alone.
    if expected_type == "bank_slip" and Path(path).suffix.lower() in IMAGE_EXTENSIONS:
        try:
            from PIL import Image

            with Image.open(path) as img:
                w, h = img.size
                if (w / max(1, h)) >= 2.5 and "DOCUMENT_SUBTYPE probable_total_row_only" not in text:
                    text = (text + "\nDOCUMENT_SUBTYPE probable_total_row_only").strip()
        except Exception:
            pass

    return text
