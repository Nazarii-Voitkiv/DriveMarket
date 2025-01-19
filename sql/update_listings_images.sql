ALTER TABLE listings 
MODIFY COLUMN images JSON DEFAULT '[]' NOT NULL;

-- Add index for faster searches
ALTER TABLE listings
ADD INDEX idx_user_id (user_id),
ADD INDEX idx_status (status);
