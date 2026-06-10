-- Production hardening migration for existing KIU Green Card System.
-- Run after database_migration_regulation_workflow.sql.

ALTER TABLE document_submissions
    ADD COLUMN IF NOT EXISTS payment_amount_ocr DECIMAL(12,2) NULL AFTER payment_amount,
    ADD COLUMN IF NOT EXISTS total_required_fee DECIMAL(12,2) NULL AFTER payment_date,
    ADD COLUMN IF NOT EXISTS payment_balance DECIMAL(12,2) NULL AFTER total_required_fee,
    ADD COLUMN IF NOT EXISTS bank_slip_hash CHAR(64) NULL AFTER bank_slip_path,
    ADD COLUMN IF NOT EXISTS bank_slip_ocr_status ENUM('not_processed','extracted','manual_review') NOT NULL DEFAULT 'not_processed' AFTER bank_slip_hash,
    ADD COLUMN IF NOT EXISTS manual_review_required BOOLEAN NOT NULL DEFAULT FALSE AFTER priority_level;

CREATE INDEX idx_document_submissions_reg_search
    ON document_submissions (registration_number);

CREATE INDEX idx_document_submissions_bank_slip_hash
    ON document_submissions (bank_slip_hash);

ALTER TABLE green_cards
    ADD COLUMN IF NOT EXISTS verification_token_hash CHAR(64) NULL AFTER qr_code_image_path;

CREATE UNIQUE INDEX uq_green_cards_verification_token_hash
    ON green_cards (verification_token_hash);

CREATE INDEX idx_green_cards_reg_lookup
    ON green_cards (registration_number, is_active);

ALTER TABLE document_uploads
    MODIFY document_type ENUM('admission_letter','s6_certificate','national_id','school_id','passport_photo','bank_slip','award_letter','other') NOT NULL,
    ADD COLUMN IF NOT EXISTS file_hash CHAR(64) NULL AFTER mime_type,
    ADD COLUMN IF NOT EXISTS classification_result JSON NULL AFTER file_hash,
    ADD COLUMN IF NOT EXISTS ownership_verification_status VARCHAR(50) NULL AFTER classification_result,
    ADD COLUMN IF NOT EXISTS manual_review_required BOOLEAN NOT NULL DEFAULT FALSE AFTER ownership_verification_status,
    ADD COLUMN IF NOT EXISTS ocr_extracted_text MEDIUMTEXT NULL AFTER manual_review_required;

CREATE INDEX idx_document_uploads_hash_type
    ON document_uploads (file_hash, document_type);

CREATE INDEX idx_document_uploads_review
    ON document_uploads (manual_review_required, uploaded_at);

