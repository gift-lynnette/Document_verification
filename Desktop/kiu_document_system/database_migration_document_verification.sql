-- Document verification migration
-- Adds analyzer fields for confidence and verification status.

ALTER TABLE document_uploads
    ADD COLUMN confidence_score INT NULL,
    ADD COLUMN verification_status VARCHAR(20) NULL;

-- Optional submission-level tracking if you want to store the final rollup later.
-- ALTER TABLE document_submissions
--     ADD COLUMN confidence_score INT NULL,
--     ADD COLUMN verification_status VARCHAR(20) NULL;
