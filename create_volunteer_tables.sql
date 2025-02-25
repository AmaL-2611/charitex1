-- Modify volunteers table to add total_hours and skills columns
ALTER TABLE volunteers 
ADD COLUMN total_hours DECIMAL(10,2) DEFAULT 0,
ADD COLUMN skills TEXT;

-- Create volunteer_hours table to track individual hour logs
CREATE TABLE IF NOT EXISTS volunteer_hours (
    id INT AUTO_INCREMENT PRIMARY KEY,
    volunteer_id INT NOT NULL,
    event_id INT NOT NULL,
    hours_worked DECIMAL(10,2) NOT NULL,
    logged_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (volunteer_id) REFERENCES volunteers(id),
    FOREIGN KEY (event_id) REFERENCES events(id)
);

-- Optional: Add sample skills to existing volunteers
UPDATE volunteers 
SET skills = 'Community Outreach, Event Planning, Social Media Management'
WHERE skills IS NULL;
