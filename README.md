# ExamQuest

A PHP/MySQL web application that helps university students find and study frequently repeated exam questions, built as a DBMS mini-project.

---

## What it does

Students select their **Syllabus → Branch → Semester**, browse the matching subjects, and view a ranked table of past exam questions sorted by how many times each has appeared in previous university papers. Questions are colour-coded by importance (High / Moderate / Low) based on their repeat frequency.

---

## Tech Stack

| Layer     | Technology                        |
|-----------|-----------------------------------|
| Backend   | PHP 8+ (procedural + OOP MySQLi)  |
| Database  | MySQL 5.7+                        |
| Frontend  | Vanilla HTML5, CSS3, JavaScript   |
| Styling   | Custom CSS with CSS custom properties (no framework) |
| Fonts     | Google Fonts — Inter              |
| Server    | Apache via XAMPP (local) / InfinityFree (production) |

---

## File Structure

```
ExamQuest/
├── db.php            — Database connection (credentials + charset)
├── index.php         — Landing page: Syllabus / Branch / Semester selector
├── subject.php       — Subject grid for the chosen filter combination
├── questions.php     — Paginated question table for a chosen subject
├── style.css         — All CSS (design tokens, layout, components, responsive)
├── database.sql      — Full DB schema + seed data (import via phpMyAdmin)
└── UI_References/    — Design mockups used during development
```

---

## Database Schema

```
syllabus ──< subject >── branch
                │
             questions
```

### Tables

**`syllabus`** — Stores the regulation/scheme year (e.g. "CUSAT B.Tech 2023 Scheme").

| Column          | Type         | Notes          |
|-----------------|--------------|----------------|
| syllabus_id     | INT PK AI    |                |
| regulation_year | VARCHAR(100) | NOT NULL       |

---

**`branch`** — Engineering departments (e.g. Computer Science and Engineering).

| Column      | Type         | Notes    |
|-------------|--------------|----------|
| branch_id   | INT PK AI    |          |
| branch_name | VARCHAR(100) | NOT NULL |

---

**`subject`** — Each subject belongs to one syllabus and one branch.

| Column       | Type         | Notes                                    |
|--------------|--------------|------------------------------------------|
| subject_id   | INT PK AI    |                                          |
| subject_name | VARCHAR(200) | NOT NULL                                 |
| semester     | VARCHAR(10)  | e.g. "3", "4"                            |
| subject_type | VARCHAR(50)  | DEFAULT 'Core Subject'                   |
| syllabus_id  | INT FK       | → syllabus(syllabus_id) ON DELETE CASCADE |
| branch_id    | INT FK       | → branch(branch_id) ON DELETE CASCADE    |

A `UNIQUE KEY` on `(subject_name, semester, syllabus_id, branch_id)` prevents duplicate subjects from being inserted.

---

**`questions`** — Exam questions linked to a subject.

| Column        | Type       | Notes                                    |
|---------------|------------|------------------------------------------|
| question_id   | INT PK AI  |                                          |
| subject_id    | INT FK     | → subject(subject_id) ON DELETE CASCADE  |
| question_text | MEDIUMTEXT | NOT NULL                                 |
| marks         | INT        | Mark value for this question             |
| frequency     | INT        | How many past papers it has appeared in  |

A `UNIQUE KEY` on `(subject_id, question_text(500))` prevents duplicate questions per subject. `ON DELETE CASCADE` on all foreign keys means deleting a subject automatically removes its questions.

---

## Local Setup (XAMPP)

1. **Install XAMPP** and start **Apache** and **MySQL**.
2. Open **phpMyAdmin** (`http://localhost/phpmyadmin`).
3. Create a new database named `examquest_db`.
4. Select the database, click **Import**, and choose `database.sql`.
5. Place the project folder inside `C:/xampp/htdocs/ExamQuest/`.
6. Visit `http://localhost/ExamQuest/` in your browser.

> **Credentials** are set in `db.php`. Default XAMPP values (root / no password) work out of the box.

---

## Deploying to InfinityFree

1. Create a free account at [infinityfree.net](https://infinityfree.net).
2. Create a hosting account and note the **MySQL host, username, password, and database name** from the hosting control panel.
3. Open `db.php` and update the four constants:
   ```php
   define('DB_HOST', 'your-mysql-host');
   define('DB_USER', 'your-username');
   define('DB_PASS', 'your-password');
   define('DB_NAME', 'your-database-name');
   ```
4. Log in to phpMyAdmin from the InfinityFree panel, select your database, and import `database.sql`. *(Do **not** add `CREATE DATABASE` or `USE` statements — the file is already written without them for this reason.)*
5. Upload all project files (except `.git/`, `UI_References/`, `Questions/`) via the InfinityFree File Manager or FTP.
6. Visit your site URL.

---


## Key Concepts Demonstrated

| Concept                  | Where used                                    |
|--------------------------|-----------------------------------------------|
| Relational DB design     | Four normalised tables with foreign keys      |
| Multi-table JOIN         | `subject.php` — LEFT JOIN to count questions  |
| Prepared statements      | All user-input queries (prevents SQL injection)|
| Pagination               | `questions.php` — LIMIT / OFFSET + smart page list |
| Client-side search (JS)  | `subject.php` — real-time subject filtering   |
| CSS custom properties    | `style.css` — design token system             |
| Responsive layout        | CSS Grid + three media query breakpoints      |
| XSS prevention           | `htmlspecialchars()` on every output          |

---

## Developed as part of DBMS Mini Project — CUSAT B.Tech CSE
## Abhidev B. Nath
## Abhijith K. S.
## Adinath M. C.
## Adith R. Lal
