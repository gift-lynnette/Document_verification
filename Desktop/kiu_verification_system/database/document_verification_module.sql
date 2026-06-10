-- Local Python document verification module migration.
-- Run this after database_migration_regulation_workflow.sql.

ALTER TABLE document_uploads
    ADD COLUMN IF NOT EXISTS verification_status VARCHAR(20) NULL,
    ADD COLUMN IF NOT EXISTS confidence_score INT NULL,
    ADD COLUMN IF NOT EXISTS extracted_data JSON NULL,
    ADD COLUMN IF NOT EXISTS risk_flags TEXT NULL,
    ADD COLUMN IF NOT EXISTS verification_document_type VARCHAR(50) NULL,
    ADD COLUMN IF NOT EXISTS verification_engine_version VARCHAR(30) NULL,
    ADD COLUMN IF NOT EXISTS verification_error TEXT NULL,
    ADD COLUMN IF NOT EXISTS verified_at TIMESTAMP NULL;

CREATE INDEX IF NOT EXISTS idx_document_uploads_verification_status
    ON document_uploads (verification_status, confidence_score);

-- Compatibility migration for installations that use student_documents.
-- Run manually only if that table exists in your database.
-- ALTER TABLE student_documents
--     ADD COLUMN verification_status VARCHAR(20) NULL,
--     ADD COLUMN confidence_score INT NULL,
--     ADD COLUMN extracted_data JSON NULL,
--     ADD COLUMN risk_flags TEXT NULL;
