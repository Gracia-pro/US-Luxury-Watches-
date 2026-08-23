# Local backend

1. Copy this project into `C:\xampp\htdocs\watch`.
2. Start Apache and MySQL in XAMPP.
3. Open `http://localhost/watch/backend/setup.php`.
4. Create an admin username and password (8+ characters).
5. Delete `setup.php` after setup.
6. Admin dashboard: `http://localhost/watch/backend/login.php`.

The API is available under `/backend/api/`. Change database credentials in `config.php` if your XAMPP MySQL setup uses a password.

If schema import reports `Can't connect to MySQL server`, open XAMPP Control Panel and start MySQL before running setup.
