# Local Document Verification Module Setup

This module runs fully on your machine. PHP uploads a document, calls `python/verify.py` with the saved file path, receives strict JSON, and stores the result in MySQL.

## 1. Install Local Tools

Install Python 3.10+ and Tesseract OCR.

Recommended Python packages:

```bash
pip install pytesseract pillow opencv-python pypdf
```

Optional but useful for PDF text:

```bash
pdftotext -v
```

On Windows, `pdftotext` comes from Poppler. Tesseract and Poppler must be available in your system `PATH`.

## 2. Configure PHP

The default command is `python`. If your system uses another executable, set an environment variable before starting Apache:

```bash
set KIU_PYTHON_BINARY=C:\Path\To\python.exe
```

The PHP constants are in `config/constants.php`:

```php
PYTHON_BINARY
DOCUMENT_VERIFIER_SCRIPT
DOCUMENT_VERIFIER_APPROVE_THRESHOLD
DOCUMENT_VERIFIER_REVIEW_THRESHOLD
```

## 3. Run Database Migration

For this existing KIU project, use:

```bash
mysql -u root -p Greencard_system < database/document_verification_module.sql
```

If your installation has a `student_documents` table instead of `document_uploads`, run:

```bash
mysql -u root -p Greencard_system < database/student_documents_verification_optional.sql
```

The student submission page also bootstraps missing `document_uploads` columns at runtime for local development.

## 4. Test Python Directly

```bash
python python/verify.py uploads/doc1.pdf --expected-type admission_letter
python python/verify.py uploads/slip.pdf --expected-type bank_slip
python python/verify.py uploads/certificate.pdf --expected-type certificate
```

The output must be JSON only.

## 5. Test Through PHP

Use the existing student flow:

```text
http://localhost/research/modules/student/submit_documents.php
```

When documents are uploaded, PHP stores the files, calls the Python verifier, and saves:

- `verification_status`
- `confidence_score`
- `extracted_data`
- `risk_flags`
- `verification_document_type`
- `verification_engine_version`
- `verified_at`

Admissions can view the automated result on:

```text
http://localhost/research/modules/admissions/verify_documents.php?id=SUBMISSION_ID
```

There is also a standalone JSON upload endpoint at `php/upload.php` for testing.

## Status Rules

- `APPROVED`: score is at least 75 and required fields are present.
- `REVIEW`: score is at least 50, or the PHP bridge could not run the engine cleanly.
- `REJECTED`: score is below 50, OCR is empty, or critical data is missing.

## Limitations

- OCR accuracy depends on scan quality, lighting, page angle, and file type.
- Fraud detection is heuristic-based and cannot prove authenticity.
- The module does not verify documents against real institutions or bank systems.
- Image-only PDFs require Tesseract/Poppler support to extract useful text.
