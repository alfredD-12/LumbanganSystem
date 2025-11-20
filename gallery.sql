CREATE TABLE IF NOT EXISTS gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image_path VARCHAR(500) NOT NULL,
    display_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default gallery items
INSERT INTO gallery (title, description, image_path, display_order) VALUES
('Community Event', 'Building stronger bonds', 'placeholder1.jpg', 1),
('Barangay Activities', 'Together we thrive', 'placeholder2.jpg', 2),
('Cultural Programs', 'Celebrating our heritage', 'placeholder3.jpg', 3),
('Youth Development', 'Empowering the next generation', 'placeholder4.jpg', 4),
('Community Projects', 'Progress through unity', 'placeholder5.jpg', 5),
('Sports & Recreation', 'Healthy body, healthy mind', 'placeholder6.jpg', 6);
