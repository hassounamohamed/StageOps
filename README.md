# StageOps - Stage Management System

StageOps is a PHP + MySQL web application for internship stage management.
It provides two role-based experiences:

- Admin area: reviews demandes, assigns tasks, monitors notifications, and tracks platform activity.
- User area: submits internship demandes, follows assigned tasks, receives notifications, and manages profile data.

## Main Features

- Secure authentication with role-based redirection
- Internship demande lifecycle (pending, accepted, refused)
- Admin task assignment and user task completion workflow
- Notification center with unread/read state
- Profile management with optional image upload
- Modern responsive UI powered by Tailwind CSS (CDN)

## Tech Stack

- PHP 8+
- MySQL / MariaDB
- Tailwind CSS (CDN)

## Application Architecture

- includes/bootstrap.php: shared helpers, session start, escaping, redirects, CSRF utilities
- includes/auth.php: authentication guard
- includes/db.php: database connection
- includes/layout.php: shared top navigation and page layout
- admin/: admin-only pages
- user/: user-only pages

## Request Workflow

1. User registers and logs in.
2. User submits a demande with university, enterprise, dates, and CV file.
3. Admin reviews demandes and accepts or refuses each request.
4. Admin assigns tasks to users.
5. User marks tasks as done.
6. Notifications are generated across key events.

## Setup

1. Place the project under your web root, for example: c:/xampp/htdocs/project
2. Start Apache and MySQL in XAMPP.
3. Open: http://localhost/project/db_setup.php
4. Open: http://localhost/project/login.php

## Seed Accounts

- Admin: admin@stageops.local / admin123
- User: user@stageops.local / user123

## Security and Quality Notes

- Passwords are hashed with password_hash and verified with password_verify.
- Prepared statements are used for write operations and sensitive read paths.
- CSRF protection is enabled for POST forms.
- User-controlled output is escaped through the shared helper.
- Schema file is aligned with current application fields and includes migration-safe additions.

## Uploads

- Upload directory: uploads/ (created automatically when required)
- Allowed extensions: jpg, jpeg, png, gif, pdf

## Current Scope

- This project is focused on stage demand, tasks, notifications, and profile management.
- API endpoints, advanced reporting, and audit logging can be added in a future version.
