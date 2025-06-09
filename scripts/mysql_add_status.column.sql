-- MySQL specific syntax for adding status column
USE campus_lost_found;

-- Check if column exists and add it if it doesn't
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'campus_lost_found' 
     AND TABLE_NAME = 'found_items' 
     AND COLUMN_NAME = 'status') = 0,
    'ALTER TABLE found_items ADD COLUMN status ENUM(''active'', ''claimed'', ''removed'') DEFAULT ''active''',
    'SELECT ''Column already exists'' as message'
));

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Update existing records to have 'active' status
UPDATE found_items SET status = 'active' WHERE status IS NULL OR status = '';

-- Show table structure
DESCRIBE found_items;
