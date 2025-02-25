-- Create causes table first
CREATE TABLE IF NOT EXISTS causes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    goal_amount DECIMAL(10, 2) NOT NULL,
    image_url VARCHAR(255),
    status ENUM('active', 'completed', 'paused') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Insert sample causes
INSERT INTO causes (title, description, goal_amount, status) VALUES
('Education Support', 'Helping underprivileged children receive quality education', 50000.00, 'active'),
('Elder Support', 'Ensuring dignity, health, and care for abandoned elderly', 30000.00, 'active'),
('Orphan Care', 'Providing a nurturing environment for orphaned children', 40000.00, 'active');

-- Add sub-categories table
CREATE TABLE IF NOT EXISTS cause_subcategories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cause_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    example_use TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cause_id) REFERENCES causes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Create donations table if it doesn't exist
CREATE TABLE IF NOT EXISTS donations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cause_id INT NOT NULL,
    donor_id INT,
    amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cause_id) REFERENCES causes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Add sub-category column to donations table
ALTER TABLE donations ADD COLUMN subcategory_id INT;
ALTER TABLE donations ADD CONSTRAINT fk_donation_subcategory FOREIGN KEY (subcategory_id) REFERENCES cause_subcategories(id) ON DELETE SET NULL;

-- Insert predefined sub-categories for Education Support
INSERT INTO cause_subcategories (cause_id, name, description, example_use) 
SELECT id, 'School Fees Sponsorship', 'Covers tuition fees for children', 'Paying for a child''s schooling for a year'
FROM causes WHERE title LIKE '%Education%' LIMIT 1;

INSERT INTO cause_subcategories (cause_id, name, description, example_use)
SELECT id, 'Books & Stationery', 'Provides books, notebooks, and supplies', 'Buying textbooks and study materials'
FROM causes WHERE title LIKE '%Education%' LIMIT 1;

INSERT INTO cause_subcategories (cause_id, name, description, example_use)
SELECT id, 'Scholarship Fund', 'Helps talented but financially weak students', 'Scholarships for top-performing students'
FROM causes WHERE title LIKE '%Education%' LIMIT 1;

INSERT INTO cause_subcategories (cause_id, name, description, example_use)
SELECT id, 'Digital Learning Access', 'Supports online education and technology', 'Purchasing laptops, tablets, or internet access'
FROM causes WHERE title LIKE '%Education%' LIMIT 1;

INSERT INTO cause_subcategories (cause_id, name, description, example_use)
SELECT id, 'School Infrastructure', 'Improves school facilities', 'Renovating classrooms, building libraries'
FROM causes WHERE title LIKE '%Education%' LIMIT 1;

-- Insert predefined sub-categories for Elder Support
INSERT INTO cause_subcategories (cause_id, name, description, example_use)
SELECT id, 'Medical Care Assistance', 'Covers medicines and health checkups', 'Regular health check-ups, buying medicines'
FROM causes WHERE title LIKE '%Elder%' LIMIT 1;

INSERT INTO cause_subcategories (cause_id, name, description, example_use)
SELECT id, 'Shelter & Living Expenses', 'Provides a safe home for elderly individuals', 'Monthly shelter costs, food, utilities'
FROM causes WHERE title LIKE '%Elder%' LIMIT 1;

INSERT INTO cause_subcategories (cause_id, name, description, example_use)
SELECT id, 'Food & Nutrition Support', 'Ensures nutritious meals for elderly', 'Providing daily meals, supplements, groceries'
FROM causes WHERE title LIKE '%Elder%' LIMIT 1;

INSERT INTO cause_subcategories (cause_id, name, description, example_use)
SELECT id, 'Recreational & Social Activities', 'Helps improve mental well-being', 'Organizing community gatherings, music therapy'
FROM causes WHERE title LIKE '%Elder%' LIMIT 1;

INSERT INTO cause_subcategories (cause_id, name, description, example_use)
SELECT id, 'Winter & Essential Supplies', 'Provides warm clothes, blankets, hygiene kits', 'Donating blankets, walking aids, eyeglasses'
FROM causes WHERE title LIKE '%Elder%' LIMIT 1;

-- Insert predefined sub-categories for Orphan Care
INSERT INTO cause_subcategories (cause_id, name, description, example_use)
SELECT id, 'Food & Nutrition', 'Ensures healthy meals for orphaned kids', 'Daily meals, baby formula, vitamin supplements'
FROM causes WHERE title LIKE '%Orphan%' LIMIT 1;

INSERT INTO cause_subcategories (cause_id, name, description, example_use)
SELECT id, 'Clothing & Basic Needs', 'Provides essential clothing and hygiene kits', 'Clothes, shoes, toothbrushes, soap'
FROM causes WHERE title LIKE '%Orphan%' LIMIT 1;

INSERT INTO cause_subcategories (cause_id, name, description, example_use)
SELECT id, 'Education & Schooling', 'Supports school fees and learning materials', 'Sponsoring school fees, buying books'
FROM causes WHERE title LIKE '%Orphan%' LIMIT 1;

INSERT INTO cause_subcategories (cause_id, name, description, example_use)
SELECT id, 'Mental & Emotional Support', 'Helps with counseling and therapy', 'Hiring social workers, organizing therapy sessions'
FROM causes WHERE title LIKE '%Orphan%' LIMIT 1;

INSERT INTO cause_subcategories (cause_id, name, description, example_use)
SELECT id, 'Recreation & Skill Development', 'Provides fun and learning activities', 'Sports, arts & crafts, coding classes'
FROM causes WHERE title LIKE '%Orphan%' LIMIT 1;
