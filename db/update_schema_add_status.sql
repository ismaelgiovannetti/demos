-- Add status column to users table
ALTER TABLE users 
ADD COLUMN status ENUM('active', 'archived') DEFAULT 'active' AFTER social_credit;
