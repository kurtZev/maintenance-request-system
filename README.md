# Maintenance Request System

A web system for submitting and tracking facility maintenance requests.
Built with PHP (MVC), MySQL, Bootstrap 5 and Bootstrap Icons.

## Run locally (XAMPP)

1. Copy the project to `C:\xampp\htdocs\maintenance-request-system`.
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. In phpMyAdmin, create a database named `maintenance_db` (utf8mb4) and import `database/database.sql`.
4. `config/db_connect.php` features automatic environment detection (XAMPP default: user `root`, empty password on `localhost`; and InfinityFree credentials online).
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

1. In the InfinityFree control panel, create a MySQL database. Note its **host** (e.g. `sql123.infinityfree.com`),
   **database name** (`if0_12345678_...`), **username** (`if0_12345678`) and the **hosting account password**.
   The XAMPP values (`localhost`, `root`, empty password) do not work there.
2. In XAMPP phpMyAdmin, open `maintenance_db`, then **Export → Quick → SQL**. Exporting from inside the
   database (not the server) keeps `CREATE DATABASE` out of the file. This copies your accounts too, so you can
   log in on the live site; `create_admin.php` cannot run on InfinityFree (no command line).
3. In InfinityFree phpMyAdmin, open the new database and **Import** that file.
4. Upload everything **inside** the project folder into `htdocs/`, including both hidden `.htaccess` files and the
   `storage/uploads/` folder. Skip `.git/`, `database/`, `README.md` and `QA_TEST_RESULTS.md`. Delete InfinityFree's
   default `index2.html`.
5. On the server, edit `htdocs/config/db_connect.php` with the four values from step 1. Keep the local copy
   unchanged so XAMPP keeps working.
6. Open your InfinityFree domain. Links adjust to the new address automatically.

If a page shows "Something went wrong", set `APP_DEBUG` to `true` in `htdocs/bootstrap.php`, reload to read the
real error, then set it back to `false`.
