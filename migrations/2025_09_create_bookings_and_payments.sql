-- Creates bookings and payments tables and seeds initial data.
-- Seed inserts are idempotent via INSERT IGNORE or ON DUPLICATE KEY UPDATE.

CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    room_id INT NOT NULL,
    status VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_payments_booking FOREIGN KEY (booking_id) REFERENCES bookings(id)
);

-- Insert a sample booking if it does not already exist.
-- INSERT IGNORE ensures rerunning the migration will not insert duplicates or error.
INSERT IGNORE INTO bookings (id, user_id, room_id, status)
VALUES (1, 1, 101, 'confirmed');

-- Insert a matching payment while keeping amount up to date.
-- ON DUPLICATE KEY UPDATE makes this insert idempotent.
INSERT INTO payments (id, booking_id, amount)
VALUES (1, 1, 100.00)
ON DUPLICATE KEY UPDATE amount = VALUES(amount);
