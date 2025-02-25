-- Create events table if not exists
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    location VARCHAR(255),
    max_volunteers INT,
    current_volunteers INT DEFAULT 0,
    status ENUM('upcoming', 'ongoing', 'completed') DEFAULT 'upcoming',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create event_registrations table
CREATE TABLE IF NOT EXISTS event_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    volunteer_id INT NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('registered', 'confirmed', 'cancelled') DEFAULT 'registered',
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (volunteer_id) REFERENCES volunteers(id) ON DELETE CASCADE,
    UNIQUE KEY unique_registration (event_id, volunteer_id)
);

-- Add some sample events
INSERT IGNORE INTO events (name, description, event_date, location, max_volunteers) VALUES 
('Community Cleanup Drive', 'Help clean up local parks and streets', '2024-03-15', 'City Central Park', 50),
('Food Bank Support', 'Assist in sorting and packing food donations', '2024-03-22', 'Local Food Bank', 30),
('Charity Marathon', 'Volunteer for annual charity run event', '2024-04-05', 'City Stadium', 100);

-- Optional: Add a sample event registration if no registrations exist
INSERT IGNORE INTO event_registrations (event_id, volunteer_id, status) 
SELECT e.id, v.id, 'registered'
FROM events e, volunteers v
WHERE NOT EXISTS (
    SELECT 1 FROM event_registrations er 
    WHERE er.event_id = e.id AND er.volunteer_id = v.id
)
LIMIT 1;
