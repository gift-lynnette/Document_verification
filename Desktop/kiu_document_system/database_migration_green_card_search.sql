-- ============================================================================
-- Green Card Search + Reporting Indexes
-- Run after database_migration_regulation_workflow.sql
-- ============================================================================

USE Greencard_system;

-- Green card core indexes
SET @idx_gc_issue := (
		SELECT COUNT(*) FROM information_schema.statistics
		WHERE table_schema = DATABASE()
			AND table_name = 'green_cards'
			AND index_name = 'idx_green_cards_issue_date'
);
SET @sql_gc_issue := IF(@idx_gc_issue = 0, 'CREATE INDEX idx_green_cards_issue_date ON green_cards (issue_date)', 'SELECT 1');
PREPARE stmt FROM @sql_gc_issue; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_gc_reg := (
		SELECT COUNT(*) FROM information_schema.statistics
		WHERE table_schema = DATABASE()
			AND table_name = 'green_cards'
			AND index_name = 'idx_green_cards_registration_number'
);
SET @sql_gc_reg := IF(@idx_gc_reg = 0, 'CREATE INDEX idx_green_cards_registration_number ON green_cards (registration_number)', 'SELECT 1');
PREPARE stmt FROM @sql_gc_reg; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Document upload indexes for verification reporting
SET @idx_du_sub_uploaded := (
		SELECT COUNT(*) FROM information_schema.statistics
		WHERE table_schema = DATABASE()
			AND table_name = 'document_uploads'
			AND index_name = 'idx_document_uploads_submission_uploaded'
);
SET @sql_du_sub_uploaded := IF(@idx_du_sub_uploaded = 0, 'CREATE INDEX idx_document_uploads_submission_uploaded ON document_uploads (submission_id, uploaded_at)', 'SELECT 1');
PREPARE stmt FROM @sql_du_sub_uploaded; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_du_sub_doc := (
		SELECT COUNT(*) FROM information_schema.statistics
		WHERE table_schema = DATABASE()
			AND table_name = 'document_uploads'
			AND index_name = 'idx_document_uploads_submission_doc_type'
);
SET @sql_du_sub_doc := IF(@idx_du_sub_doc = 0, 'CREATE INDEX idx_document_uploads_submission_doc_type ON document_uploads (submission_id, document_type)', 'SELECT 1');
PREPARE stmt FROM @sql_du_sub_doc; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_du_status := (
		SELECT COUNT(*) FROM information_schema.statistics
		WHERE table_schema = DATABASE()
			AND table_name = 'document_uploads'
			AND index_name = 'idx_document_uploads_verification_status'
);
SET @sql_du_status := IF(@idx_du_status = 0, 'CREATE INDEX idx_document_uploads_verification_status ON document_uploads (verification_status, confidence_score)', 'SELECT 1');
PREPARE stmt FROM @sql_du_status; EXECUTE stmt; DEALLOCATE PREPARE stmt;
