-- Add status column to found_items table
USE campus_lost_found;

-- Add status column if it doesn't exist
ALTER TABLE found_items 
ADD COLUMN IF NOT EXISTS status ENUM('active', 'claimed', 'removed') DEFAULT 'active';

-- Update existing records to have 'active' status
UPDATE found_items SET status = 'active' WHERE status IS NULL;

-- Verify the column was added
DESCRIBE found_items;
