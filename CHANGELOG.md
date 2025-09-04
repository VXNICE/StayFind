# Changelog

## Added
- `migrations/2025_09_create_bookings_and_payments.sql`
- Manual payment flow: `api/bookings_create.php`, `api/bookings_list.php`, `api/booking_payment_upload.php`, `api/booking_verify.php`
- Room creation endpoint `api/room_create.php`
- Admin booking management UI `admin_bookings.php` with `assets/js/admin_bookings.js`
- Dashboard JS moved to `assets/js/dashboard.js` with booking and payment modals
- Uploaded receipts folder guard `uploads/payments/.htaccess`
- Converted placeholder `.txt` files to PHP placeholders
- `README.md`, `CHANGELOG.md`

## Modified
- `dashboard.html` to load external script and include modals
