# IN2120 Blog Application - Implementation Plan

Based on the project requirements from the prompt, the development of the blog application has been divided into 4 main parts to ensure a structured and iterative approach.

## Part 1: Project Setup & Database Schema

This phase focuses on laying the foundation of the application by setting up the folder structure, configuring the database, and creating the necessary tables.

**Tasks:**

- Initialize the required folder structure (`/config`, `/includes`, `/assets/css`, `/assets/js`, `/api`).
- Create `database.sql` with the `user` and `blogPost` tables, including proper constraints (e.g., `ON DELETE CASCADE`) and foreign keys.
- Insert sample seed data (1-2 users, 2-3 posts) into `database.sql` for initial testing.
- Create `config/db.php` to establish a secure database connection using PDO with error mode set to exceptions.

## Part 2: Authentication & Authorization System

This phase handles user identity, secure registration, logging in/out, and protecting routes against unauthorized access.

**Tasks:**

- Build frontend pages for registration (`register.php`) and login (`login.php`).
- Develop the backend logic for registration, ensuring `username` and `email` uniqueness, and hashing passwords using `password_hash()`.
- Develop the backend logic for login, validating credentials with `password_verify()` and initializing `$_SESSION`.
- Implement `logout.php` to securely destroy the session.
- Create `includes/auth.php` containing helper functions to check login status and restrict access to protected pages.

## Part 3: Blog Management (CRUD) Backend

This phase builds the core functionality of the blog, allowing authenticated users to create, read, update, and delete their own posts.

**Tasks:**

- Implement `includes/functions.php` for shared utilities (e.g., input sanitization, CSRF protection helpers, output escaping using `htmlspecialchars()`).
- Create `api/create_post.php` to handle new post submissions with PDO prepared statements.
- Create `api/update_post.php` to handle modifying existing posts.
- Create `api/delete_post.php` to handle post deletion.
- **Crucial Security Task:** Enforce server-side ownership checks on all update and delete endpoints to ensure `blogPost.user_id === $_SESSION['user_id']`.

## Part 4: Frontend Pages & UI Integration

This phase ties the backend to the user interface, implementing the visual components, markdown rendering, and responsive styling.

**Tasks:**

- Develop `index.php` (Home page) to fetch and display all blog posts (title, author, date, excerpt) with "Edit/Delete" buttons visible only to the respective post owners.
- Develop `post.php` (Single view) to display the full content of a blog post, converting markdown content to HTML.
- Develop `editor.php` to serve as a unified form for both creating and editing posts, optionally integrating a lightweight JS markdown previewer in `assets/js/main.js`.
- Write clean, modern, and responsive CSS in `assets/css/style.css` (using Flexbox/Grid) for the navigation bar, blog cards, and forms.
- Add JS confirmation dialogs for delete actions in `assets/js/main.js`.
- Write the `README.md` with instructions on local setup, database import, and deployment to free shared hosting.
