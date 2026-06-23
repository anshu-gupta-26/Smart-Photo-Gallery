# 📸 Smart Photo Gallery Management System

A secure, full-featured photo gallery web application built with **PHP 8**, **MySQL**, and **Bootstrap 5** — featuring user authentication, album management, smart search, an admin control panel, and full light/dark mode support.

![PHP](https://img.shields.io/badge/PHP-8.1-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-MariaDB-4479A1?style=flat-square&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat-square&logo=bootstrap&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)

---

## 🚀 Live Demo

> Add your live demo link here (if hosted), or run locally using the steps below.

---

## ✨ Features

### 🔐 Authentication & Security
- Secure registration & login with password hashing (`bcrypt`)
- CSRF protection on all forms
- Login rate-limiting / temporary account lockout after 5 failed attempts
- Session timeout & session regeneration on login
- Google OAuth login integration
- Forgot password / email-based password reset (PHPMailer + SMTP)

### 🖼️ Photo & Album Management
- Drag-and-drop multiple photo upload with live preview
- File type & size validation (JPG, PNG, GIF, WEBP — max 5MB)
- Create, edit, and delete albums (public/private)
- Lightbox photo viewer
- Photo search by title
- Download and delete individual photos

### 👤 User Profile
- Editable profile with profile picture upload
- Change password functionality
- Personal stats (total photos, total albums)

### 🛠️ Admin Panel
- Centralized admin dashboard with system-wide stats
- Manage all users (promote/demote roles, delete accounts)
- Manage all photos and albums across the platform
- Role-based access control (User / Admin)

### 🎨 UI/UX
- Fully responsive Bootstrap 5 design
- **Light & Dark mode** with persistent preference (`localStorage`)
- Clean dashboard with icon-based stat cards
- Flash messages for user feedback

---

## 🧰 Tech Stack

| Layer | Technology |
|---|---|
| Frontend | HTML5, CSS3, JavaScript, Bootstrap 5, Bootstrap Icons |
| Backend | PHP 8+ (PDO for database access) |
| Database | MySQL / MariaDB |
| Mailing | PHPMailer (SMTP) |
| Auth | Google OAuth 2.0, Session-based Auth |
| Environment | XAMPP (Apache + MySQL + PHP) |

---

## 📂 Project Structure

```
photo_gallery/
├── admin/
│   ├── index.php           # Admin dashboard with stats
│   ├── users.php           # Manage users (role toggle, delete)
│   ├── photos.php          # Manage all photos
│   └── albums.php          # Manage all albums
├── albums/
│   ├── create.php
│   ├── edit.php
│   ├── delete.php
│   ├── index.php
│   └── view.php
├── auth/
│   ├── login.php
│   ├── register.php
│   ├── logout.php
│   ├── forgot.php           # Forgot password request
│   ├── reset-password.php   # Password reset form
│   ├── google-login.php     # Google OAuth entry point
│   └── google-callback.php  # Google OAuth callback handler
├── assets/
│   ├── css/style.css        # Custom styles + dark mode overrides
│   └── js/main.js           # Dark mode toggle, lightbox, form validation
├── config/
│   ├── constants.php        # Site-wide constants
│   └── database.php         # PDO database connection
├── includes/
│   ├── PHPMailer/           # Email sending library
│   ├── auth_check.php       # Session/auth guard
│   ├── functions.php        # Helper functions
│   ├── header.php
│   ├── footer.php
│   └── navbar.php
├── photos/
│   ├── index.php
│   ├── upload.php
│   ├── view.php
│   ├── search.php
│   └── delete.php
├── user/
│   ├── profile.php
│   ├── edit-profile.php
│   └── change-password.php
├── uploads/
│   ├── photos/              # Uploaded photo storage (gitignored)
│   └── profiles/            # Profile picture storage (gitignored)
├── dashboard.php             # User dashboard
├── index.php                 # Landing page
├── photo_gallery.sql         # Database schema
├── .env.example               # Environment variable template
└── .gitignore
```

---

## ⚙️ Installation & Setup

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) (or any PHP 8+ / MySQL environment)
- PHP 8.0 or higher
- MySQL / MariaDB

### Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/anshu-gupta-26/Smart-Photo-Gallery.git
   ```

2. **Move the project into your server's root**
   - Place the folder inside `C:\xampp\htdocs\` (or your server's web root)

3. **Create the database**
   - Open phpMyAdmin (`http://localhost/phpmyadmin`)
   - Create a new database named `photo_gallery`
   - Import `photo_gallery.sql`

4. **Configure environment variables**
   - Copy `.env.example` to `.env`
   - Fill in your own SMTP credentials and Google OAuth keys:
     ```env
     MAIL_HOST=smtp.gmail.com
     MAIL_PORT=587
     MAIL_USERNAME=your_email@gmail.com
     MAIL_PASSWORD=your_app_password
     MAIL_FROM_NAME=Smart Photo Gallery
     GOOGLE_CLIENT_ID=your_google_client_id
     GOOGLE_CLIENT_SECRET=your_google_client_secret
     GOOGLE_REDIRECT_URI=http://localhost/photo_gallery/auth/google-callback.php
     ```
   - ⚠️ **Never commit your real `.env` file.** It's already excluded via `.gitignore`.

5. **Configure database connection**
   - Edit `config/database.php` if your MySQL credentials differ from XAMPP defaults (default: host `localhost`, user `root`, no password)

6. **Start Apache & MySQL**
   - Launch them from the XAMPP Control Panel

7. **Run the app**
   ```
   http://localhost/photo_gallery/
   ```

---

## 🔑 Setting Up an Admin Account

1. Register a normal account through the app
2. Open phpMyAdmin → `photo_gallery` database → `users` table
3. Find your account row and change the `role` column value from `user` to `admin`
4. Log out and log back in — the **Admin** link will now appear in the navbar

---

## 🖥️ Screenshots

> Add screenshots of the landing page, dashboard, album view, and dark mode here.

| Light Mode | Dark Mode |
|---|---|
| _add screenshot_ | _add screenshot_ |

---

## 🛣️ Roadmap / Future Improvements

- [ ] Image compression & auto-resizing on upload
- [ ] Tagging system for photos
- [ ] Likes/comments on shared albums
- [ ] REST API for mobile integration
- [ ] Activity logs for admin auditing
- [ ] Pagination on admin user/photo management pages

---

## 🤝 Contributing

Contributions, issues, and feature requests are welcome. Feel free to fork the repo and submit a pull request.

---

## 📄 License

This project is licensed under the **MIT License** — feel free to use it for learning or as a base for your own projects.

---

## 👩‍💻 Author

**Anshu Gupta**
MCA Project — Smart Photo Gallery Management System

[GitHub](https://github.com/anshu-gupta-26) • Built with PHP, MySQL & Bootstrap