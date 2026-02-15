# TALL Stack Environment Setup Guide

**TALL** = **T**ailwind CSS + **A**lpine.js + **L**aravel + **L**ivewire

This guide covers setting up a complete TALL stack development environment on **Windows** (with WSL2 support).

---

## Prerequisites

### Windows Users
You can develop either:
1. **Natively on Windows** (using XAMPP/Laragon or native PHP)
2. **Using WSL2** (recommended for better performance and Linux compatibility)

This guide covers both paths.

---

## 1) Install Core Dependencies

### Option A: Windows Native Setup

#### 1.1) PHP 8.2+
Download and install from: https://windows.php.net/download/

**Recommended**: Use [Laragon](https://laragon.org/) or [XAMPP](https://www.apachefriends.org/) for an all-in-one solution.

Verify installation:
```powershell
php -v
# Should show PHP 8.2 or higher
```

#### 1.2) Composer
Download from: https://getcomposer.org/download/

Verify:
```powershell
composer --version
```

#### 1.3) Node.js & NPM
Download LTS version from: https://nodejs.org/

Verify:
```powershell
node -v
npm -v
```

#### 1.4) MySQL or PostgreSQL

**MySQL**:
- Download from: https://dev.mysql.com/downloads/installer/
- Or use XAMPP/Laragon's bundled MySQL

**PostgreSQL** (alternative):
- Download from: https://www.postgresql.org/download/windows/

#### 1.5) Git
Download from: https://git-scm.com/download/win

Verify:
```powershell
git --version
```

---

### Option B: WSL2 Setup (Recommended)

#### 1.1) Enable WSL2
```powershell
# Run as Administrator
wsl --install
```

Restart your computer, then install Ubuntu:
```powershell
wsl --install -d Ubuntu-22.04
```

#### 1.2) Inside WSL2 Ubuntu

Update packages:
```bash
sudo apt update && sudo apt upgrade -y
```

Install PHP 8.2+:
```bash
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install -y php8.2 php8.2-cli php8.2-fpm php8.2-mysql php8.2-xml php8.2-curl php8.2-mbstring php8.2-zip php8.2-gd php8.2-bcmath php8.2-intl
```

Verify:
```bash
php -v
```

Install Composer:
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

Install Node.js & NPM:
```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
node -v
npm -v
```

Install MySQL:
```bash
sudo apt install -y mysql-server
sudo service mysql start
sudo mysql_secure_installation
```

Install Git:
```bash
sudo apt install -y git
git --version
```

---

## 2) Laravel Project Setup

### 2.1) Create New Laravel Project

Using Composer:
```bash
composer create-project laravel/laravel us-app
cd us-app
```

Or using Laravel installer:
```bash
composer global require laravel/installer
laravel new us-app
cd us-app
```

### 2.2) Configure Environment

Copy `.env.example` to `.env`:
```bash
cp .env.example .env
```

Generate application key:
```bash
php artisan key:generate
```

### 2.3) Database Configuration

Edit `.env` file:

**For MySQL**:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=us_app
DB_USERNAME=root
DB_PASSWORD=your_password
```

**For PostgreSQL**:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=us_app
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

Create the database:
```bash
# MySQL
mysql -u root -p -e "CREATE DATABASE us_app;"

# PostgreSQL
psql -U postgres -c "CREATE DATABASE us_app;"
```

Run migrations:
```bash
php artisan migrate
```

---

## 3) Install TALL Stack Components

### 3.1) Install Livewire

```bash
composer require livewire/livewire
```

Publish Livewire assets (optional):
```bash
php artisan livewire:publish --config
```

### 3.2) Install Tailwind CSS

Install via NPM:
```bash
npm install -D tailwindcss postcss autoprefixer
npx tailwindcss init -p
```

Configure `tailwind.config.js`:
```javascript
/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}
```

Update `resources/css/app.css`:
```css
@tailwind base;
@tailwind components;
@tailwind utilities;
```

### 3.3) Install Alpine.js

Alpine.js is typically included via CDN in your layout, or you can install via NPM:

**Via CDN** (add to your layout file):
```html
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
```

**Via NPM**:
```bash
npm install alpinejs
```

Then in `resources/js/app.js`:
```javascript
import Alpine from 'alpinejs'
window.Alpine = Alpine
Alpine.start()
```

### 3.4) Install Laravel Jetstream (Optional but Recommended)

Jetstream provides authentication scaffolding with Livewire:

```bash
composer require laravel/jetstream
php artisan jetstream:install livewire
npm install
npm run build
php artisan migrate
```

---

## 4) Additional Dependencies

### 4.1) Redis (Optional - for caching/queues)

**Windows**: Download from https://github.com/microsoftarchive/redis/releases

**WSL2/Linux**:
```bash
sudo apt install -y redis-server
sudo service redis-server start
```

Update `.env`:
```env
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
```

### 4.2) Laravel Reverb (WebSocket Server)

For realtime features:
```bash
composer require laravel/reverb
php artisan reverb:install
```

Update `.env`:
```env
BROADCAST_DRIVER=reverb
```

---

## 5) Running the Application

### 5.1) Start Development Server

Terminal 1 - Laravel backend:
```bash
php artisan serve
# Runs on http://localhost:8000
```

Terminal 2 - Vite dev server (for assets):
```bash
npm run dev
```

Terminal 3 - Queue worker (if using queues):
```bash
php artisan queue:work
```

Terminal 4 - Reverb WebSocket server (if using realtime):
```bash
php artisan reverb:start
```

### 5.2) Access the Application

Open browser to: **http://localhost:8000**

---

## 6) Running Tests

Laravel uses PHPUnit for testing:

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test --filter=ExampleTest

# Run with coverage (requires Xdebug)
php artisan test --coverage
```

---

## 7) Common Commands Reference

```bash
# Create Livewire component
php artisan make:livewire ComponentName

# Create migration
php artisan make:migration create_table_name

# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Seed database
php artisan db:seed

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Generate IDE helper (for better autocomplete)
composer require --dev barryvdh/laravel-ide-helper
php artisan ide-helper:generate
```

---

## 8) Environment Variables Example

Here's a complete `.env` example (NO real secrets):

```env
APP_NAME="Us"
APP_ENV=local
APP_KEY=base64:GENERATED_KEY_HERE
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=us_app
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=reverb
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# AI Provider (Gemini example)
AI_PROVIDER=gemini
GEMINI_API_KEY=your_gemini_api_key_here

# File Storage
FILESYSTEM_DISK=local
# For production, use S3:
# AWS_ACCESS_KEY_ID=
# AWS_SECRET_ACCESS_KEY=
# AWS_DEFAULT_REGION=us-east-1
# AWS_BUCKET=
```

---

## 9) Troubleshooting

### Issue: "Class not found" errors
```bash
composer dump-autoload
php artisan clear-compiled
php artisan config:clear
```

### Issue: NPM build fails
```bash
rm -rf node_modules package-lock.json
npm install
npm run dev
```

### Issue: Permission errors (WSL2/Linux)
```bash
sudo chown -R $USER:$USER .
chmod -R 755 storage bootstrap/cache
```

### Issue: MySQL connection refused
```bash
# Check if MySQL is running
sudo service mysql status

# Start MySQL
sudo service mysql start
```

### Issue: Port 8000 already in use
```bash
# Use different port
php artisan serve --port=8001
```

### Issue: Livewire components not updating
```bash
php artisan livewire:discover
php artisan view:clear
```

### Issue: Tailwind styles not applying
```bash
# Make sure Vite is running
npm run dev

# Rebuild assets
npm run build
```

---

## 10) IDE Setup Recommendations

### VS Code Extensions
- **Laravel Extension Pack** (by Winnie Lin)
- **PHP Intelephense**
- **Tailwind CSS IntelliSense**
- **Alpine.js IntelliSense**
- **Livewire Goto**
- **Laravel Blade Snippets**

### PhpStorm Plugins
- **Laravel Idea** (paid, highly recommended)
- **Tailwind CSS**
- **Alpine.js Support**

---

## 11) Next Steps

1. ✅ Clone the repository
2. ✅ Run `composer install`
3. ✅ Run `npm install`
4. ✅ Copy `.env.example` to `.env`
5. ✅ Generate app key: `php artisan key:generate`
6. ✅ Create database and configure `.env`
7. ✅ Run migrations: `php artisan migrate`
8. ✅ Start servers: `php artisan serve` + `npm run dev`
9. ✅ Read `PROJECT_DOCUMENT.md` for feature specifications
10. ✅ Start building! 🚀

---

**Document Version**: 1.0  
**Last Updated**: 2026-02-16  
**Maintained by**: Us Development Team
