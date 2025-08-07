# Student Portal

A web-based student portal for managing programmes, modules, staff, and student interests. Built with PHP and MySQL.

## Features
- Student and staff login
- Programme and module management
- Admin dashboard
- Interested students tracking
- Secure authentication

## Requirements
- XAMPP (Apache, PHP, MySQL)
- Web browser

## Setup Instructions

1. **Move Project to XAMPP**
   - Copy the `student-portal` folder to your XAMPP `htdocs` directory (e.g., `C:/xampp/htdocs/`).

2. **Start XAMPP Services**
   - Open XAMPP Control Panel and start Apache and MySQL.

3. **Database Setup**
   - Open [phpMyAdmin](http://localhost/phpmyadmin) in your browser.
   - Create a new database (e.g., `student_portal`).
   - Import `example-data.sql` into the new database.

4. **Configure Database Connection**
   - Edit `config/database.php` and set your database name, username (default: `root`), and password (default: empty).

5. **Access the Application**
   - Visit [http://localhost/student-portal](http://localhost/student-portal) in your browser.

## File Structure
- `index.php` - Main entry point
- `admin/` - Admin dashboard and related features
- `assets/` - CSS and JavaScript files
- `config/` - Database and security configuration
- `includes/` - Common PHP includes (header, footer, functions)

## Notes
- Default XAMPP MySQL user is `root` with no password.
- Change default credentials and security settings before deploying to production.

## License
This project is for educational purposes.
