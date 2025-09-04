# StayFind

Minimal booking demo with manual payment proof.

## Installation
1. Copy project to web server (e.g. XAMPP `htdocs`).
2. Create MySQL database and import migration:
   ```
   mysql -u root -p stayfind_db < migrations/2025_09_create_bookings_and_payments.sql
   ```
3. Seed sample data:
   ```sql
   -- sample admin
   INSERT INTO users (id, email, password_hash, name, role) VALUES
   (1,'admin@example.com', PASSWORD('adminpass'),'Admin','admin')
   ON DUPLICATE KEY UPDATE email=email;
   -- sample user
   INSERT INTO users (id, email, password_hash, name, role) VALUES
   (2,'user@example.com', PASSWORD('userpass'),'User','user')
   ON DUPLICATE KEY UPDATE email=email;
   -- sample room
   INSERT INTO rooms (id,title,location,price,capacity,image) VALUES
   (1,'Sample Room','Manila',1000,2,'')
   ON DUPLICATE KEY UPDATE title=VALUES(title);
   ```
4. Adjust `includes/db.php` with your DB credentials.
5. Ensure `uploads/payments` is writable by the web server.

## Usage
- Register/login via `login.html` / `register.html`.
- Browse rooms on `dashboard.html`.
- Book a room and upload payment receipt.
- Admin checks `admin_bookings.php` to approve/decline payments.

## Tests
See `CHANGELOG.md` for list of changes.
