# Adviso - Financial Literacy & Planning Platform

Adviso is a web-based platform designed to help individuals and businesses improve financial literacy, access expert tax solutions, and manage personalized financial plans.

## Features

- User Registration & Authentication
- Financial Literacy Hub
- Tax Solutions & Planning
- Personalized Dashboard
- Interactive Calculators
- Real-Time Data Display

## Technical Stack

- Frontend: HTML5, CSS3, JavaScript (Vanilla)
- Backend: PHP
- Database: MySQL (phpMyAdmin in XAMPP)

## Prerequisites

- XAMPP (Apache, MySQL, PHP)
- Web browser
- Text editor/IDE

## Installation

1. Clone or download this repository to your XAMPP's `htdocs` directory:
   ```
   C:\xampp\htdocs\adviso
   ```

2. Start XAMPP Control Panel and start Apache and MySQL services.

3. Open phpMyAdmin (http://localhost/phpmyadmin) and create a new database:
   - Click "New" in the left sidebar
   - Enter "adviso_db" as the database name
   - Click "Create"

4. Import the database structure:
   - Select the "adviso_db" database
   - Click the "Import" tab
   - Choose the `database.sql` file from this project
   - Click "Go" to import

5. Configure the database connection:
   - Open `includes/config.php`
   - Update the database credentials if needed:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'adviso_db');
     ```

6. Access the application:
   - Open your web browser
   - Navigate to: http://localhost/adviso

## Project Structure

```
adviso/
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── main.js
├── includes/
│   ├── config.php
│   ├── header.php
│   └── footer.php
├── index.php
├── login.php
├── register.php
├── dashboard.php
├── financial-literacy.php
├── tax-solutions.php
├── calculators.php
├── database.sql
└── README.md
```

## Usage

1. Register a new account or login with existing credentials
2. Access the Financial Literacy Hub for educational resources
3. Use the Tax Solutions section for tax planning
4. Track your financial goals in the Dashboard
5. Use the Calculators for various financial calculations

## Security Features

- Password hashing
- Prepared statements for database queries
- Session management
- Input validation and sanitization

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support, email support@adviso.com or create an issue in the repository. 