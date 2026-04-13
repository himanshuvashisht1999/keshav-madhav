-- Database changes for project
-- Date: 2026-04-13

-- Make account_number and ifsc_code nullable in bank_accounts table
ALTER TABLE bank_accounts MODIFY account_number VARCHAR(255) NULL;
ALTER TABLE bank_accounts MODIFY ifsc_code VARCHAR(255) NULL;

-- Make amount and period nullable in committees table
ALTER TABLE committees MODIFY amount DECIMAL(15,2) NULL;
ALTER TABLE committees MODIFY period VARCHAR(255) NULL;

-- Make percentage nullable in taxes table
ALTER TABLE taxes MODIFY percentage DECIMAL(5,2) NULL;

-- Make percentage nullable in interests table
ALTER TABLE interests MODIFY percentage DECIMAL(5,2) NULL;

-- Make percentage nullable in commissions table
ALTER TABLE commissions MODIFY percentage DECIMAL(5,2) NULL;


-- Add balance column to capital_masters table
ALTER TABLE capital_masters ADD COLUMN balance DECIMAL(15,2) DEFAULT 0 AFTER name;




