# Web Technology (U21CS501) – Assignment II

**Student:** S Makavishnu &nbsp;|&nbsp; **Roll No.:** 24CS108 &nbsp;|&nbsp; **Class:** III Year CSE, Semester V, Section A  


A student admission and records system built in three parts. All three share **one MySQL database**
and **one `uploads/` folder**, so a student registered in the HTML form (Q1) is stored by PHP (Q2)
and then shown by the Java Servlet portal (Q3).

```
 Q1  HTML + CSS + JS form  ──POST──►  Q2  PHP + MySQL CRUD  ──writes──►  MySQL + uploads/
                                                                              │
                                      Q3  Servlet + JDBC + JSP  ◄──reads──────┘
```

## Folder structure

```
webtech-assignment-2/
├── database/
│   └── schema.sql                  ← DB schema script (tables + sample data)
├── q1-registration-form/           ← Q1: HTML form, CSS, JavaScript validation
│   ├── index.html
│   ├── css/style.css
│   └── js/validation.js
├── q2-php-crud/                    ← Q2: PHP + MySQL CRUD and file upload
│   ├── index.php  add.php  view.php  edit.php  delete.php  delete_file.php
│   ├── includes/  (config, db, helpers, header, footer, form, gallery)
│   ├── css/style.css
│   └── uploads/                    ← uploaded images/documents are stored here
├── q3-servlet-portal/              ← Q3: Servlet, JDBC, JSP (Tomcat 10+)
│   ├── src/main/java/com/portal/   (util, model, dao, filter, servlet)
│   ├── src/main/webapp/            (JSP pages, css, WEB-INF/web.xml)
│   ├── build.sh   pom.xml
├── screenshots/                    ← browser output screenshots
└── docs/                           ← Word report with code explanation
```

## How to run

### 1. Database (once)
```bash
mysql -u root -p < database/schema.sql
```
Creates the database `student_portal` with tables `users`, `students`, `student_files` and sample rows.

### 2. Q1 + Q2 (PHP)
Needs PHP 7.4+ with the `pdo_mysql` and `fileinfo` extensions (both are on by default in XAMPP).

* **XAMPP:** copy this folder to `C:\xampp\htdocs\` and start Apache + MySQL, then open  
  `http://localhost/webtech-assignment-2/q1-registration-form/` (Q1) or `.../q2-php-crud/` (Q2).
* **Without XAMPP:** from the repo root run `php -S localhost:8000`, then open  
  `http://localhost:8000/q1-registration-form/`.

DB username/password are in `q2-php-crud/includes/config.php` (default `root` / empty).

### 3. Q3 (Servlet portal)
Needs JDK 17+, Tomcat 10.1+ and the MySQL Connector/J jar.

1. Open `q3-servlet-portal/src/main/webapp/WEB-INF/web.xml` and set `db.user`, `db.password` and
   **`upload.dir`** (absolute path of `q2-php-crud/uploads`).
2. Build the WAR:
   ```bash
   cd q3-servlet-portal
   ./build.sh /path/to/tomcat/lib/servlet-api.jar /path/to/mysql-connector-j-8.x.jar
   ```
   (Windows: run the same `javac`/`jar` commands from `build.sh`, or use the Maven `pom.xml` / an IDE.)
3. Copy `build/portal.war` to Tomcat's `webapps/` folder, start Tomcat, and open  
   `http://localhost:8080/portal/`.

**Login:** `admin` / `admin123` (or `faculty` / `student123`).

> **Tomcat 9 or older?** Replace `jakarta.servlet` with `javax.servlet` in every `.java` file and change the
> `web.xml` header to the Java EE 4.0 schema.

## What each question covers

| Requirement | Where |
|---|---|
| **Q1** HTML form (name, roll no., email, course, file upload) | `q1-registration-form/index.html` |
| **Q1** CSS layout, colours, responsive design, error styles | `css/style.css` |
| **Q1** JS validation: required, email format, password strength | `js/validation.js` |
| **Q2** Add / Edit / Delete / View with MySQL (PDO, prepared statements) | `q2-php-crud/*.php` |
| **Q2** File upload for profile image + ID proof + certificates | `includes/helpers.php` |
| **Q2** Metadata (path, type, date) stored in MySQL | table `student_files` |
| **Q2** Files shown on the view page | `view.php`, `includes/file_gallery.php` |
| **Q2** Size/type checks, no overwriting, error handling | `check_upload()`, `save_upload()` |
| **Q3** JDBC connection to MySQL, fetch students + file paths | `DBUtil.java`, `StudentDAO.java` |
| **Q3** Stream images/documents from the upload folder | `FileServlet.java` |
| **Q3** Dynamic profiles with `<img src="file?id=…">` | `students.jsp`, `profile.jsp` |
| **Q3** Loops + conditionals to list students and files | `students.jsp` |
| **Q3** Session handling – only logged-in users | `LoginServlet`, `AuthFilter`, `web.xml` |

## Security measures
* SQL injection: every query uses `PreparedStatement` / PDO prepared statements.
* XSS: all output is escaped (`e()` in PHP, `Html.escape()` in JSP).
* Uploads: real MIME type checked with `finfo`, size limits, random unique file names (no overwrite),
  `.htaccess` blocks script execution in `uploads/`.
* Passwords: bcrypt (`password_hash`) for students, SHA-256 + constant-time compare for portal users.
* Sessions: new session on login (prevents fixation), 30-minute timeout, HttpOnly cookie, filter on all private URLs.

## Test results
All of the following were executed against a real MySQL-compatible server (MariaDB 10.11), PHP 8.3 and Tomcat 10.1:

* Valid registration saved; photo + ID proof rows created with correct MIME type, size and timestamp.
* Rejected: file with an `.jpg` name but executable content, a 3 MB photo (limit 2 MB), missing files,
  weak password, duplicate roll number / e-mail.
* Edit replaced the old photo (old file removed from disk); extra certificates were added; deleting a file or a student removed the files from disk and the rows from MySQL (`ON DELETE CASCADE`).
* Servlet portal: anonymous requests to `/students`, `/profile`, `/file` redirect to login; wrong password and a SQL-injection string are rejected; streamed image/PDF bytes are identical to the uploaded originals.

## Screenshots

| | |
|---|---|
| ![Q1 empty](screenshots/01_q1_empty_form.png) **Q1 – form** | ![Q1 errors](screenshots/02_q1_required_errors.png) **Q1 – required errors** |
| ![Q1 format](screenshots/03_q1_format_errors.png) **Q1 – email / password / confirm errors** | ![Q1 valid](screenshots/04_q1_valid_strong_password.png) **Q1 – valid, strong password** |
| ![Q2 after register](screenshots/05_q2_view_after_registration.png) **Q2 – saved record + files** | ![Q2 list](screenshots/06_q2_student_list.png) **Q2 – student list** |
| ![Q2 upload error](screenshots/07_q2_server_side_upload_error.png) **Q2 – server-side upload error** | ![Q2 view](screenshots/08_q2_view_student_with_files.png) **Q2 – view with files** |
| ![Q2 edit](screenshots/09_q2_edit_student.png) **Q2 – edit** | ![Q2 delete](screenshots/10_q2_after_delete.png) **Q2 – after delete** |
| ![Q3 guard](screenshots/11_q3_redirect_to_login_when_not_logged_in.png) **Q3 – blocked when not logged in** | ![Q3 failed](screenshots/12_q3_login_failed.png) **Q3 – failed login** |
| ![Q3 list](screenshots/13_q3_student_list.png) **Q3 – student list** | ![Q3 profile](screenshots/14_q3_student_profile.png) **Q3 – profile with files** |
| ![DB](screenshots/15_mysql_tables.png) **MySQL tables** | |

## Conclusion
This assignment connected the whole web stack: a validated, responsive HTML/CSS/JavaScript form, a PHP + MySQL
back end with safe file uploads and full CRUD, and a Java Servlet/JDBC portal that reads the same data and streams
the stored files to logged-in users only. Doing both PHP and Servlets on one database showed me how the same ideas
(sessions, prepared statements, input validation, escaping output) appear in different technologies. The biggest
lessons were to never trust client-side checks alone and to keep file handling and database changes consistent,
so a failed upload never leaves half-saved data behind.
