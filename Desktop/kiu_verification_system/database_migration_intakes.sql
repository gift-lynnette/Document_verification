-- ============================================================================
-- Intake Management Migration
-- Adds intakes table and intake_id linkage for new-student workflow
-- ============================================================================

USE Greencard_system;

CREATE TABLE IF NOT EXISTS intakes (
    intake_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    intake_name VARCHAR(60) NOT NULL,
    intake_year YEAR NOT NULL,
    admission_period_start DATE NULL,
    admission_period_end DATE NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_intake_name_year (intake_name, intake_year),
    INDEX idx_intake_active (is_active, intake_year)
) ENGINE=InnoDB COMMENT='Admission intakes (January/April/August)';

-- Add intake_id to document_submissions if missing
SET @col_exists := (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'document_submissions'
      AND column_name = 'intake_id'
);
SET @sql_add_intake := IF(@col_exists = 0,
    'ALTER TABLE document_submissions ADD COLUMN intake_id BIGINT UNSIGNED NULL',
    'SELECT 1'
);
PREPARE stmt FROM @sql_add_intake; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Add intake_id to student_profiles if missing
SET @col_exists := (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'student_profiles'
      AND column_name = 'intake_id'
);
SET @sql_add_intake := IF(@col_exists = 0,
    'ALTER TABLE student_profiles ADD COLUMN intake_id BIGINT UNSIGNED NULL',
    'SELECT 1'
);
PREPARE stmt FROM @sql_add_intake; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Add year_of_study to student_profiles if missing
SET @col_exists := (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'student_profiles'
      AND column_name = 'year_of_study'
);
SET @sql_add_year := IF(@col_exists = 0,
    'ALTER TABLE student_profiles ADD COLUMN year_of_study TINYINT UNSIGNED NOT NULL DEFAULT 1',
    'SELECT 1'
);
PREPARE stmt FROM @sql_add_year; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Add year_of_study to document_submissions if missing
SET @col_exists := (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'document_submissions'
      AND column_name = 'year_of_study'
);
SET @sql_add_year := IF(@col_exists = 0,
    'ALTER TABLE document_submissions ADD COLUMN year_of_study TINYINT UNSIGNED NOT NULL DEFAULT 1',
    'SELECT 1'
);
PREPARE stmt FROM @sql_add_year; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Add foreign key for intake_id where possible
SET @fk_exists := (
    SELECT COUNT(*) FROM information_schema.key_column_usage
    WHERE table_schema = DATABASE()
      AND table_name = 'document_submissions'
      AND constraint_name = 'fk_doc_submission_intake'
);
SET @sql_add_fk := IF(@fk_exists = 0,
    'ALTER TABLE document_submissions ADD CONSTRAINT fk_doc_submission_intake FOREIGN KEY (intake_id) REFERENCES intakes(intake_id) ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT 1'
);
PREPARE stmt FROM @sql_add_fk; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fk_exists := (
    SELECT COUNT(*) FROM information_schema.key_column_usage
    WHERE table_schema = DATABASE()
      AND table_name = 'student_profiles'
      AND constraint_name = 'fk_student_profile_intake'
);
SET @sql_add_fk := IF(@fk_exists = 0,
    'ALTER TABLE student_profiles ADD CONSTRAINT fk_student_profile_intake FOREIGN KEY (intake_id) REFERENCES intakes(intake_id) ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT 1'
);
PREPARE stmt FROM @sql_add_fk; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Normalize existing semesters to semester_1 for new intake-only flow
UPDATE document_submissions SET intake_semester = 'semester_1' WHERE intake_semester <> 'semester_1';
UPDATE student_profiles SET intake_semester = 'semester_1' WHERE intake_semester <> 'semester_1';

-- Seed standard intakes if missing for current year
SET @current_year := YEAR(CURDATE());
INSERT IGNORE INTO intakes (intake_name, intake_year, admission_period_start, admission_period_end, is_active)
VALUES
    ('January Intake', @current_year, MAKEDATE(@current_year, 1), MAKEDATE(@current_year, 31), 1),
    ('April Intake', @current_year, MAKEDATE(@current_year, 91), MAKEDATE(@current_year, 120), 1),
    ('August Intake', @current_year, MAKEDATE(@current_year, 213), MAKEDATE(@current_year, 243), 1);
