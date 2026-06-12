from __future__ import annotations

import json
import re
from functools import lru_cache
from pathlib import Path
from typing import Any


def normalize(text: str) -> str:
    return re.sub(r"\s+", " ", text.lower()).strip()


def _collapse_alnum(text: str) -> str:
    """Return lowercase string with only alphanumeric characters (for fuzzy matching)."""
    return re.sub(r"[^a-z0-9]", "", text.lower())


def _bank_slip_ocr_text_looks_like_app_ui(text: str) -> bool:
    """True when OCR is likely our dashboard/UI (feedback loop), not a real slip."""
    if not text or len(text.strip()) < 14:
        return False
    t = normalize(text)
    cues = (
        "payment information",
        "amount paid:",
        "slip ocr status",
        "amount paid:",
        "automated verification",
        "verification result",
        "confidence:",
        "expected: bank slip",
        "missing fields",
        "risk flags",
        "document verification module",
        "submit verification decision",
        "backward to dashboard",
        "bank slipapproved",
        "extracted:",
        "bank slip extract",
        "verification engine",
        "verification status",
        "payment amount",
    )
    if any(c in t for c in cues):
        return True
    tl = text.lower()
    meta = (
        r"bank_name\s*:",
        r"admission_number\s*\.",
        r"\| expected:",
        r"verification_status",
        r"confidence_score",
        r"extracted_fields",
        r"risk_flags",
        r"confidence\s*:\s*\d+\s*%",
        r"missing_fields",
        r"risk flags:",
    )
    return any(re.search(p, tl) for p in meta)


_BANK_SLIP_CHARGE_LINE_RE = re.compile(
    r"bank\s*(?:charge|charges|fee|fees)|charge(?:s|\s*$)|commission|stamp\s*duty|handling\s*f",
    re.IGNORECASE,
)


def _bank_slip_narrow_total_crop_marked(text: str) -> bool:
    """Synthetic hint from OCR when the image looks like only a TOTAL table row crop."""
    if not text:
        return False
    return bool(
        re.search(r"DOCUMENT_SUBTYPE\s+probable_total_row_only", text, re.IGNORECASE)
    )


def fuzzy_contains(text: str, keyword: str) -> bool:
    """Check whether keyword appears in text allowing for OCR noise like extra spaces/newlines/stamps.

    This compares collapsed alphanumeric forms as a cheap fuzzy match without extra deps.
    """
    if not text or not keyword:
        return False
    norm = normalize(text)
    if keyword.lower() in norm:
        return True
    return _collapse_alnum(keyword) in _collapse_alnum(text)


STOPWORDS = {
    "the", "and", "for", "with", "this", "that", "from", "your", "you", "are",
    "has", "have", "will", "shall", "to", "of", "in", "on", "or", "a", "an",
    "as", "by", "is", "be", "no", "not", "it", "at", "if", "within",
}


def reference_tokens(text: str) -> set[str]:
    normalized = re.sub(r"[^a-z0-9\s]", " ", text.lower())
    return {
        token
        for token in re.sub(r"\s+", " ", normalized).split()
        if len(token) >= 3 and token not in STOPWORDS
    }


@lru_cache(maxsize=1)
def load_reference_standards() -> dict[str, Any]:
    standards_path = Path(__file__).resolve().parents[1] / "reference_standards.json"
    if not standards_path.is_file():
        return {}
    try:
        return json.loads(standards_path.read_text(encoding="utf-8"))
    except Exception:
        return {}


def reference_similarity_score(text: str, document_type: str) -> int:
    standards = load_reference_standards()
    profiles = (
        standards.get("document_types", {})
        .get(document_type, {})
        .get("profiles", [])
    )
    if not profiles:
        return 0

    uploaded_tokens = reference_tokens(text)
    if not uploaded_tokens:
        return 0

    best = 0.0
    for profile in profiles:
        ref_tokens = set(profile.get("tokens", []))
        if not ref_tokens:
            continue
        shared = len(uploaded_tokens & ref_tokens)
        containment = shared / max(1, min(len(uploaded_tokens), len(ref_tokens)))
        jaccard = shared / max(1, len(uploaded_tokens | ref_tokens))
        score = (containment * 70) + (jaccard * 30)
        best = max(best, score)

    return int(round(max(0, min(100, best))))


def classify_document(text: str, config: dict[str, Any], expected_type: str | None = None) -> tuple[str, int]:
    normalized = normalize(text)
    if expected_type in config["document_rules"]:
        return expected_type, 85

    scores: dict[str, int] = {}
    for document_type, rule in config["document_rules"].items():
        score = 0
        for keyword in rule.get("keywords", []):
            if fuzzy_contains(text, keyword):
                score += 12
        scores[document_type] = min(score, 100)

    if not scores:
        return "unknown", 0

    document_type = max(scores, key=scores.get)
    return (document_type, scores[document_type]) if scores[document_type] > 0 else ("unknown", 0)


def extract_fields(text: str, document_type: str) -> dict[str, Any]:
    fields: dict[str, Any] = {}
    normalized = normalize(text)

    name_match = re.search(
        r"(?:student(?:'s|\s+s)?name|candidate\s+name|applicant\s+name)\s*[:\-]?\s*"
        r"([A-Z][A-Za-z'.-]+(?:\s+[A-Z][A-Za-z'.-]+){1,4})",
        text,
        re.IGNORECASE,
    )
    if name_match:
        fields["name"] = name_match.group(1).strip()

    institution_match = re.search(
        r"((?:Kampala International University|KIU|[A-Z][A-Za-z&.,' -]{2,80}(?:University|College|School|Institute|Board)))",
        text,
        re.IGNORECASE,
    )
    if institution_match:
        inst_raw = institution_match.group(1).strip()
        # Normalize whitespace introduced by OCR (newlines in stamps/signatures)
        inst_clean = re.sub(r"\s+", " ", inst_raw).strip()
        # If the capture is single letters separated by spaces (e.g. "K I U"), collapse to abbreviation
        if re.fullmatch(r"(?:[A-Za-z]\s+){1,}[A-Za-z]", inst_clean):
            fields["institution"] = inst_clean.replace(" ", "").upper()
        else:
            fields["institution"] = inst_clean
    else:
        # Attempt fuzzy detection for institution names that OCR split (stamps/signatures/newlines)
        keywords = ["Kampala International University", "KIU"]
        for kw in keywords:
            if fuzzy_contains(text, kw):
                fields["institution"] = kw
                break

    date_match = re.search(
        r"\b(\d{1,2}[\/\-.]\d{1,2}[\/\-.]\d{2,4}|\d{4}[\/\-.]\d{1,2}[\/\-.]\d{1,2}|(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*\s+\d{1,2},?\s+\d{4})\b",
        text,
        re.IGNORECASE,
    )
    if date_match:
        fields["date"] = date_match.group(1).strip()

    admission_match = re.search(
        r"(?:admission\s*(?:number|no\.?)|adm(?:ission)?\s*no\.?|student\s*no\.?|registration\s*(?:number|no\.?)?)\s*[:#\-]?\s*([A-Z0-9\/\-]{4,30})",
        text,
        re.IGNORECASE,
    )
    if admission_match:
        fields["admission_number"] = admission_match.group(1).strip()

    if document_type == "admission_letter":
        if re.search(r"\b(admitted|offer|accepted|congratulations?|following\s+your\s+application|pleased\s+to\s+inform|re\s*:\s*admission)\b", text, re.IGNORECASE):
            fields["admission_evidence"] = "present"
        if re.search(r"\b(stamp|signature|signed|registrar|admissions?\s+office|dean)\b", text, re.IGNORECASE):
            fields["official_marker"] = "present"

    program_match = re.search(
        r"(?:program(?:me)?|course)\s*[:\-]?\s*([A-Za-z][A-Za-z0-9&.,'()\/ -]{4,100})",
        text,
        re.IGNORECASE,
    )
    if program_match:
        fields["program"] = program_match.group(1).strip()

    if re.search(r"\b(certificate|diploma|transcript|examination|result)\b", text, re.IGNORECASE):
        fields["certificate_keyword"] = "present"

    if document_type == "national_id_passport":
        if re.search(r"\b(national\s+(?:id|identification)|passport|identity\s+card|citizenship|nin)\b", normalized, re.IGNORECASE):
            fields["identity_keyword"] = "present"
        dob_match = re.search(r"(?:date\s+of\s+birth|dob|born)\s*[:\-]?\s*(\d{1,2}[\/\-.]\d{1,2}[\/\-.]\d{2,4}|\d{4}[\/\-.]\d{1,2}[\/\-.]\d{1,2})", text, re.IGNORECASE)
        if dob_match:
            fields["date_of_birth"] = dob_match.group(1).strip()
        id_match = re.search(r"(?:nin|national\s*(?:id|identification)\s*(?:number|no\.?)?|passport\s*(?:number|no\.?)?)\s*[:#\-]?\s*([A-Z0-9\-\/]{5,40})", text, re.IGNORECASE)
        if id_match:
            fields["id_number"] = id_match.group(1).strip()

    if document_type == "former_school_id":
        if re.search(r"\b(school|student\s+(?:id|number|card)|registration|institution)\b", normalized, re.IGNORECASE):
            fields["school_keyword"] = "present"
        student_match = re.search(r"(?:student\s*(?:id|number|no\.?)|registration\s*(?:number|no\.?)?)\s*[:#\-]?\s*([A-Z0-9\-\/]{3,40})", text, re.IGNORECASE)
        if student_match:
            fields["student_number"] = student_match.group(1).strip()

    if document_type == "award_letter":
        if re.search(r"\b(bursary|scholarship|award|sponsorship|financial\s+aid)\b", text, re.IGNORECASE):
            fields["award_evidence"] = "present"
        if re.search(r"\b(stamp|signature|signed|registrar|dean|finance|sponsor)\b", text, re.IGNORECASE):
            fields["official_marker"] = "present"

    if document_type == "bank_slip":
        bank_match = re.search(
            r"^\s*bank\s*[:\-]\s*([A-Z][A-Za-z& .-]{2,80})\s*$",
            text,
            re.IGNORECASE | re.MULTILINE,
        ) or re.search(
            r"\b(Centenary|Stanbic|DFCU|Absa|Equity|PostBank|[A-Z][A-Za-z& -]{2,40}\s+Bank)\b",
            text,
            re.IGNORECASE,
        )
        if bank_match:
            fields["bank_name"] = bank_match.group(1).strip()

        ref_match = re.search(
            r"(?:ref(?:erence)?|receipt|transaction|txn)\s*(?:no\.?|number|#)?\s*[:#\-]?\s*([A-Z0-9\-\/]{5,40})",
            text,
            re.IGNORECASE,
        )
        if ref_match:
            fields["reference"] = ref_match.group(1).strip()

        amount = _extract_bank_slip_amount(text)
        if amount:
            fields["amount"] = amount

    if document_type == "bank_slip" and _bank_slip_ocr_text_looks_like_app_ui(text):
        fields.pop("bank_name", None)
        fields.pop("reference", None)
        fields.pop("amount", None)

    return fields


def _parse_amount_token(token: str) -> str | None:
    if not token:
        return None

    normalized = (
        token.replace("O", "0")
        .replace("o", "0")
        .replace("I", "1")
        .replace("l", "1")
        .replace("|", "1")
    )
    normalized = re.sub(r"[^0-9,./\s]", "", normalized).strip(" ,./")
    if not normalized:
        return None

    compact = re.sub(r"[\s,/]", "", normalized)
    if not compact:
        return None

    if re.fullmatch(r"\d+\.\d{1,2}", compact):
        amount = compact
    else:
        amount = re.sub(r"[^0-9]", "", compact)
    if not amount:
        return None
    try:
        if float(amount) <= 0:
            return None
    except Exception:
        return None
    return amount


def _extract_bank_slip_amount(text: str) -> str | None:
    if _bank_slip_ocr_text_looks_like_app_ui(text):
        return None

    if _bank_slip_narrow_total_crop_marked(text):
        # Tesseract reliably mis-reads handwritten figures on TOTAL-only crops; refuse to guess amounts.
        return None

    candidates: list[tuple[int, float, str]] = []
    lines = text.splitlines()
    amount_like_pattern = re.compile(r"[0-9OIl|][0-9OIl|,\s./]{2,}")

    def line_is_charge_row(line: str) -> bool:
        return bool(_BANK_SLIP_CHARGE_LINE_RE.search(line))

    for match in re.finditer(
        r"\bt[o0]t[a4]l\b[\s:=\-]{0,20}([0-9][0-9,\s./]{2,})",
        text,
        re.IGNORECASE,
    ):
        parsed = _parse_amount_token(match.group(1))
        if parsed:
            candidates.append((340, float(parsed), parsed))

    # Highest priority: amount on TOTAL line or immediate following lines.
    for idx, line in enumerate(lines):
        if re.search(r"\bt[o0]t[a4]l\b", line, re.IGNORECASE):
            context = [(0, line)]
            if idx + 1 < len(lines):
                context.append((1, lines[idx + 1]))
            if idx + 2 < len(lines):
                context.append((2, lines[idx + 2]))

            for _offset, sample in context:
                if line_is_charge_row(sample):
                    continue
                for match in amount_like_pattern.findall(sample):
                    parsed = _parse_amount_token(match)
                    if parsed:
                        candidates.append((300, float(parsed), parsed))

    # Next: labeled amounts (omit "cash" — often denomination column; OCR pairs it with charges).
    for match in re.finditer(
        r"\b(?:amount|paid|deposit|total|sum)\b\s*[:\-]?\s*(?:UGX|USh|SHS)?\s*([0-9][0-9,\s./]*)",
        text,
        re.IGNORECASE,
    ):
        span = match.span()
        line_start = text.rfind("\n", 0, span[0]) + 1
        line_end = text.find("\n", span[1])
        if line_end == -1:
            line_end = len(text)
        line_ctx = text[line_start:line_end]
        if line_is_charge_row(line_ctx):
            continue
        parsed = _parse_amount_token(match.group(1))
        if parsed:
            candidates.append((180, float(parsed), parsed))

    # Last resort: local-currency-prefixed values only.
    for match in re.finditer(
        r"(?:UGX|USh|SHS)\s*([0-9][0-9,\s./]*)",
        text,
        re.IGNORECASE,
    ):
        parsed = _parse_amount_token(match.group(1))
        if parsed and float(parsed) >= 1000:
            candidates.append((120, float(parsed), parsed))

    # Final fallback: generic comma-separated or grouped amounts.
    for match in re.finditer(r"\b([0-9]{1,3}(?:,[0-9]{3})+(?:\.[0-9]{1,2})?)\b", text):
        parsed = _parse_amount_token(match.group(1))
        if parsed:
            value = float(parsed)
            if 1000 <= value <= 50000000:
                candidates.append((80, value, parsed))

    # Plain-digit large totals OCR often emits without commas "1183900" (skip admission/reg lines).
    _adm_pat = re.compile(
        r"admiss|reg\.?\s*no|registration|student\s*(?:no|number)|nin\b", re.IGNORECASE
    )
    if re.search(r"\bt[o0]t[a4]l\b", text, re.IGNORECASE):
        for match in re.finditer(r"\b\d{6,9}\b", text):
            span = match.span()
            line_start = text.rfind("\n", 0, span[0]) + 1
            line_end = text.find("\n", span[1])
            if line_end == -1:
                line_end = len(text)
            line_ctx = text[line_start:line_end]
            if line_is_charge_row(line_ctx) or _adm_pat.search(line_ctx):
                continue
            parsed = match.group(0)
            try:
                v = float(parsed)
            except Exception:
                continue
            if 280_000 <= v <= 50_000_000:
                candidates.append((275, v, parsed))

    if not candidates:
        return None

    top_score = max(c[0] for c in candidates)
    tier = [c for c in candidates if c[0] == top_score]
    best_amt = max(c[1] for c in tier)
    for c in tier:
        if c[1] == best_amt:
            return c[2]
    return tier[0][2]


def detect_risk_flags(text: str, document_type: str, fields: dict[str, Any], rule: dict[str, Any]) -> list[str]:
    flags: list[str] = []
    normalized = normalize(text)

    keyword_hits = [kw for kw in rule.get("keywords", []) if fuzzy_contains(text, kw)]
    # Allow one critical hit for admission letters (tolerate stamps/signatures)
    if document_type == "admission_letter":
        min_required = 1
    else:
        min_required = max(1, min(2, len(rule.get("keywords", []))))

    if len(keyword_hits) < min_required:
        flags.append("missing critical keywords")

    if len(text.strip()) < 80:
        flags.append("very little readable text")

    if re.search(r"\b(sample|template|dummy|specimen|copy only)\b", normalized):
        flags.append("suspicious sample/template wording")

    repeated_digits = re.findall(r"(\d)\1{6,}", text)
    if repeated_digits:
        flags.append("suspicious repeated number pattern")

    if document_type == "admission_letter" and fields.get("institution") and fields.get("admission_evidence"):
        flags = [flag for flag in flags if flag != "missing critical keywords"]

    if document_type == "bank_slip" and "amount" in fields:
        try:
            if float(fields["amount"]) <= 0:
                flags.append("invalid payment amount")
        except Exception:
            flags.append("invalid payment amount")

    if document_type == "bank_slip" and _bank_slip_ocr_text_looks_like_app_ui(text):
        flags.append("ocr appears to be app ui screenshot not slip")

    if document_type == "bank_slip" and _bank_slip_narrow_total_crop_marked(text):
        flags.append(
            "upload full bank slip; narrow TOTAL-row crops are not reliable for amount extraction"
        )

    if document_type != "bank_slip" and re.search(r"\b(bank|deposit|teller|ugx|shillings|account|cash|paid\s*in|students\s+copy)\b", normalized):
        flags.append("appears to be a bank slip")

    if document_type != "certificate" and re.search(r"\b(uace|uce|uganda\s+advanced\s+certificate|examination\s+for\s+the\s+uganda|subsidiary\s+pass|principal\s+pass)\b", normalized):
        flags.append("appears to be an academic certificate")

    return flags


def validate_document(text: str, document_type: str, config: dict[str, Any]) -> dict[str, Any]:
    rule = config["document_rules"].get(document_type, {})
    fields = extract_fields(text, document_type)
    required_fields = rule.get("required_fields", [])
    missing_fields = [field for field in required_fields if not fields.get(field)]

    score = 10
    field_points = rule.get("field_points", {})
    for field, points in field_points.items():
        if fields.get(field):
            score += int(points)

    keyword_hits = [kw for kw in rule.get("keywords", []) if fuzzy_contains(text, kw)]
    score += min(10, len(keyword_hits) * 2)
    score = max(0, min(score, 100))

    risk_flags = detect_risk_flags(text, document_type, fields, rule)
    reference_score = reference_similarity_score(text, document_type)
    if document_type == "bank_slip" and _bank_slip_ocr_text_looks_like_app_ui(text):
        reference_score = min(reference_score, 25)
    if document_type == "bank_slip" and _bank_slip_narrow_total_crop_marked(text):
        reference_score = min(reference_score, 25)
    if reference_score >= 55:
        score = max(score, 82)
        risk_flags = [
            flag for flag in risk_flags
            if flag not in {"missing critical keywords", "very little readable text"}
            and not flag.startswith("appears to be ")
        ]
        missing_fields = []
    elif reference_score >= 38:
        score = max(score, 68)
        risk_flags = [flag for flag in risk_flags if flag != "very little readable text"]

    score -= len(risk_flags) * 8
    score -= len(missing_fields) * 6
    if any(flag.startswith("appears to be ") for flag in risk_flags) and reference_score < 38:
        score = min(score, 45)
    score = max(0, min(score, 100))

    thresholds = config.get("thresholds", {"approved": 75, "review": 50})
    if not text.strip():
        status = "REJECTED"
        risk_flags.append("empty OCR output")
    elif score >= int(thresholds["approved"]) and not missing_fields:
        status = "APPROVED"
    elif score >= int(thresholds["review"]):
        status = "REVIEW"
    else:
        status = "REJECTED"

    return {
        "status": status,
        "confidence_score": score,
        "extracted_fields": fields,
        "missing_fields": missing_fields,
        "risk_flags": list(dict.fromkeys(risk_flags)),
        "reference_similarity": reference_score,
    }
