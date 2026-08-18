# Build Prompt: IN2120 Blog Application (HTML/CSS/JS + PHP + MySQL)

## Context
Build a full-stack **Blog Application** for a university web programming assignment (IN2120 — Web Programming, University of Moratuwa). The stack is fixed: **HTML, CSS, JavaScript** on the frontend, **PHP** (plain/procedural or OOP, no frameworks required) on the backend, and **MySQL** as the database. The app must run locally and also be deployable to a free shared-hosting provider such as InfinityFree or 000WebHost (so avoid anything that needs Composer/Node build steps at runtime, shell access, or non-standard PHP extensions — assume PHP 7.4–8.1 with mysqli or PDO available, no CLI access on the host).

## Goal
Produce a complete, working, submission-ready project with clean folder structure, working authentication, full blog CRUD with ownership-based authorization, a responsive UI, and a MySQL schema — ready to zip and host.

## Tech Stack Constraints
- Frontend: plain HTML, CSS, vanilla JavaScript (no React/Vue/build tools)
- Backend: PHP (mysqli or PDO — PDO with prepared statements preferred for security)
- Database: MySQL (InnoDB, utf8mb4)
- No frameworks (no Laravel, no Composer dependencies) — must run on free shared hosting with zero server config beyond uploading files and importing a SQL file
- Sessions handled via native PHP sessions (`session_start()`, `$_SESSION`)

## Folder Structure
Generate this structure:
```
/blog-app
  /config
    db.php                 # DB connection (mysqli or PDO)
  /includes
    auth.php               # helper: check if logged in, get current user, require-login guard
    functions.php          # shared helpers (e.g. sanitize input, format date)
  /assets
    /css
      style.css
    /js
      main.js               # e.g. delete confirmation, markdown preview
  /api  (or root-level PHP action scripts — your choice, but keep endpoints organized)
    register.php
    login.php
    logout.php
    create_post.php
    update_post.php
    delete_post.php
  index.php                 # home page — list all blogs
  post.php                  # single blog view (?id=)
  editor.php                # create/edit form (reused for both, ?id= present = edit mode)
  register.php               # registration page (form + handling, or split per above)
  login.php
  database.sql               # full schema + sample seed data
  README.md                  # setup instructions
```
(Adjust naming as needed, but keep it organized and consistent — don't dump everything in one file.)

## Database Schema (database.sql)
Create a `database.sql` file with:

```sql
CREATE DATABASE IF NOT EXISTS blog_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE blog_app;

CREATE TABLE user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,   -- store password_hash() output, never plain text
    role ENUM('user','admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE blogPost (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
) ENGINE=InnoDB;
```
Include a couple of sample seed rows (1-2 users, 2-3 posts) so the app isn't empty on first load.

## Feature Requirements

### 1. Authentication & Authorization
- **Register** (`register.php`): username, email, password fields. Hash password with `password_hash()`. Validate uniqueness of username/email. Show clear error messages.
- **Login** (`login.php`): verify with `password_verify()`, start session storing `user_id`, `username`.
- **Logout** (`logout.php`): destroy session, redirect to home.
- **Route protection:** any page/action that creates, edits, or deletes a post must check `$_SESSION['user_id']` is set — redirect to login otherwise.
- **Ownership check:** before allowing update/delete, the backend must verify `blogPost.user_id === $_SESSION['user_id']` (never trust a hidden form field alone — re-check server-side on every write). Return a 403-style error/message if not the owner.

### 2. Blog Management (CRUD)
- **Create:** `editor.php` (no `id` in URL) — title + content fields. A lightweight Markdown editor is acceptable (e.g. a `<textarea>` with a live-preview pane rendered via a small JS Markdown parser, or simply store raw Markdown and render it with a JS library like `marked.js` on the view page — pick one approach and implement it fully, don't half-do it).
- **Read:** `index.php` lists all posts (title, author, date, short excerpt), each linking to `post.php?id=X`.
- **Update:** `editor.php?id=X` — pre-fill form with existing post data (only if current user owns it).
- **Delete:** delete button/action on the post (only visible to the owner), with a JS confirm dialog before submitting.

### 3. Frontend Pages
- **Home (`index.php`):** list of blog cards — title, author, date, excerpt, "Read more" link. Show "Edit"/"Delete" only on posts the logged-in user owns.
- **Single post (`post.php`):** full content (rendered from Markdown if that approach is used), author name, created/updated date.
- **Editor (`editor.php`):** one form for both create and update.
- **Nav bar:** shows Login/Register when logged out; shows username + "New Post" + "Logout" when logged in.
- **Styling:** clean, modern CSS — responsive layout (flexbox/grid), mobile-friendly, consistent spacing/typography. Avoid a raw unstyled HTML look.

### 4. Security Basics (expected even though not explicitly listed)
- All SQL via prepared statements (PDO or mysqli) — no string-concatenated queries.
- Escape all output with `htmlspecialchars()` to prevent XSS.
- Passwords always hashed, never stored/compared in plain text.
- Validate/sanitize all form inputs server-side (not just client-side JS validation).

### 5. README.md
Include setup instructions: how to import `database.sql`, configure `config/db.php` with DB credentials, and run locally (e.g. via XAMPP/PHP built-in server), plus a short note on deploying to InfinityFree/000WebHost (upload via FTP, import SQL via phpMyAdmin, update `db.php` credentials).

## Deliverable
Generate the full project as described above — all files with complete, working code (not placeholders/pseudocode). After generating, summarize:
- How to run it locally
- How to import the database
- Any manual steps needed before hosting it (e.g. updating DB credentials in `config/db.php`)

## Out of Scope (don't add unless trivial)
- No need for password reset/email verification flows
- No need for comments, likes, tags, or categories unless you want to add them as a bonus — core requirement is just auth + CRUD + listing/viewing
