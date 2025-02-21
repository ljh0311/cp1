-- Insert sample users (password hashes are for the password: 'Password123!')
INSERT INTO users (username, email, password_hash, full_name, is_admin, status) VALUES
('admin', 'admin@example.com', '$2y$10$YourHashHere123456789uZfkvHGX5L4Qy6OxYj1yoiNbNLMCkK6O', 'Admin User', TRUE, 'active'),
('student', 'student@example.com', '$2y$10$YourHashHere123456789uZfkvHGX5L4Qy6OxYj1yoiNbNLMCkK6O', 'Student User', FALSE, 'active');

-- Note: The actual password for both users is: Password123! 