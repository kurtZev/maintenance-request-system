# Maintenance Request System

A web system for submitting and tracking facility maintenance requests.
Built with PHP (MVC), MySQL, Bootstrap 5 and Bootstrap Icons.

## Run locally (XAMPP)

1. Copy the project to `C:\xampp\htdocs\maintenance-request-system`.
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. In phpMyAdmin, create a database named `maintenance_db` (utf8mb4) and import `database/database.sql`.
4. Check the credentials in `config/db_connect.php` (XAMPP default: user `root`, empty password).
5. Open <http://localhost/maintenance-request-system/>.

`database/database.sql` contains the tables only. Create the first administrator
from the command line (MySQL must be running):

```
C:\xampp\php\php.exe database\create_admin.php
```

It asks for a name, email and password, applies the same rules as the website,
and stores only a `password_hash()` value. After that, add more accounts in
**Users** inside the admin area.

## Folder structure

| Folder / file | Purpose |
|---|---|
| `public/` | The only folder the browser can reach: `index.php` (entry point) and `assets/` |
| `routes/web.php` | Which URL runs which controller method, and who may open it |
| `app/controllers/` | Receive requests, validate input, call models, choose a view |
| `app/models/` | All database queries (PDO prepared statements) |
| `app/views/` | HTML only. `layouts/` holds the shared navbar, sidebar and footer |
| `app/core/` | Router, base Controller and helper functions |
| `app/middleware/` | Login, role and session checks |
| `config/db_connect.php` | Database credentials |
| `storage/uploads/` | Uploaded files (not reachable directly from the browser) |
| `database/database.sql` | Table structure |
| `bootstrap.php` | Loads settings, classes and the session for every request |
| `.htaccess` | Sends every request into `public/` and hides all other folders |

## Adding a page

1. Add a method to a controller in `app/controllers/` that ends with `$this->view('folder/name', [...])`.
2. Create the view in `app/views/folder/name.php` (HTML only, no queries).
3. Register the route in `routes/web.php` with its access level.
4. If it belongs in the sidebar, its link in `app/views/layouts/sidebar.php` appears automatically once the route exists.

## Deploy to InfinityFree

1. Create a MySQL database in the InfinityFree control panel and import `database/database.sql` with its phpMyAdmin.
2. Put the InfinityFree host, database name, username and password in `config/db_connect.php`.
3. Upload everything **inside** the project folder (including both `.htaccess` files) into `htdocs/`. Do not upload `database/` or `README.md`.
4. Open your InfinityFree domain. Links adjust to the new address automatically.
