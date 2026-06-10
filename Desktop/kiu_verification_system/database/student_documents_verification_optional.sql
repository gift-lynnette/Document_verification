-- Optional compatibility migration for projects that store uploads in student_documents.
-- Use this only when your database has a student_documents table.

ALTER TABLE student_documents
    ADD COLUMN IF NOT EXISTS verification_status VARCHAR(20) NULL,
    ADD COLUMN IF NOT EXISTS confidence_score INT NULL,
    ADD COLUMN IF NOT EXISTS extracted_data JSON NULL,
    ADD COLUMN IF NOT EXISTS risk_flags TEXT NULL;
