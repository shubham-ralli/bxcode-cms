# BxCode CMS (Laravel Edition)

A robust, modular Content Management System built on Laravel 10. Designed for flexibility, performance, and ease of use.

## 🚀 Key Features

### 🎨 Design & Customization
-   **Theme System**: Fully customizable frontend themes located in `resources/views/themes`.
-   **Plugin Architecture**: Extend core functionality without touching the codebase (`app/Plugins`).
-   **Menu Manager**: Drag-and-drop menu builder with **Custom CSS Classes** and **Target Support** (New Tab vs Same Tab).

### 🖼️ Media Library
-   **Drag & Drop Upload**: Modern upload interface with progress tracking.
-   **Smart Management**: Filter by type (Image, Audio, Video, Document) or Date.
-   **Deep Linking**: Share specific media items via URL (e.g., `?item=123` auto-opens the preview modal).
-   **Metadata**: Auto-detects file sizes, MIME types, and supports custom Titles/Alt Text.

### 👥 User Management
-   **Role-Based Access**: Administrators, Editors, Authors, etc.
-   **Rich Profile Editing**: Integrated WYSIWYG editor for user bios.
-   **Smart Redirects**: Editing your own user redirects to the "My Profile" page.

### ⚡ Admin Experience
-   **Toast Notifications**: Non-intrusive success/error popups.
-   **Dashboard Widgets**: At-a-glance analytics (Total Users, Posts, Media).
-   **Responsive Design**: Mobile-friendly admin panel built with Tailwind CSS.

### 🔧 Technical
-   **SEO Ready**: Built-in meta tag management.
-   **Robust API**: Underlying service architecture for Posts, Media, and Users.

---

## 🛠️ Installation

### 1. Requirements
-   PHP 8.1+
-   Composer
-   MySQL 8.0+ or MariaDB

### 2. Setup
Clone the repository and install dependencies:

```bash
git clone https://github.com/your-repo/laravel-cms.git
cd laravel-cms
composer install
```

### 3. Environment
Configure your database credentials:

```bash
cp .env.example .env
nano .env
# Set DB_DATABASE, DB_USERNAME, DB_PASSWORD
```

### 4. Database Seeding
Run the installer to set up tables and create the default admin account:

```bash
php artisan migrate --seed
```

> **Default Admin:**
> - **Email:** `admin@example.com`
> - **Password:** `password`

### 5. Finalize
Link the storage directory for public image access:

```bash
php artisan storage:link
```

---

## 📖 Usage Guide

-   **Admin Panel**: `/lp-admin`
-   **Frontend**: `/` (Home)

### Managing Menus
Go to **Appearance > Menus**.
1.  Create a menu (e.g., "Main Menu").
2.  Add Pages, Posts, or Custom Links from the left sidebar.
3.  **Advanced**: Expand a menu item to add a **Custom CSS Class** (e.g., `btn-primary`) or change the **Link Target**.

### Managing Plugins
Go to **Plugins** in the sidebar.
-   Upload standard Laravel packages or custom BxCode plugins.
-   Toggle plugins On/Off instanty.

---

## 🤝 Contributing
Contributions are welcome! Please fork the repository and submit a Pull Request.

## 📄 License
This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
