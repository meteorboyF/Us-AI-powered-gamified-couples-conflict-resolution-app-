# Us - AI Powered Couples Communication App

A gamified couples counseling and communication platform built with the TALL stack (Tailwind CSS, Alpine.js, Laravel, Livewire).

## 🚀 Features

- **Couple Linking**: Invite code system for connecting partners
- **Gamified World**: Shared themed environment that evolves with relationship activities
- **Daily Mood Check-ins**: Track emotional states and needs
- **XP System**: Earn points for healthy communication habits
- **Missions**: Daily and weekly relationship-building activities
- **Private Vault**: Secure memory storage with dual-consent locking
- **AI Relationship Coach**: Safe, guardrailed support for conflict resolution
- **Gift/Date Suggestions**: Personalized recommendations based on preferences

## 🛠️ Tech Stack

- **Laravel 12.51.0** - Backend framework
- **Jetstream 5.4.0** - Authentication scaffolding
- **Livewire 3.7.10** - Reactive UI components
- **Tailwind CSS** - Utility-first CSS framework
- **MySQL** - Database
- **Node.js 24.13.1** - Frontend asset compilation
- **Vite** - Build tool

## 📋 Prerequisites

- PHP 8.2+
- MySQL 5.7+ or 8.0+
- Composer
- Node.js 18+ & npm
- Git

## 🔧 Installation

### 1. Clone the Repository

```bash
git clone https://github.com/meteorboyF/Us-AI-powered-gamified-couples-conflict-resolution-app-.git
cd us-app
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### 3. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Configuration

Edit `.env` file with your MySQL credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=us_app
DB_USERNAME=root
DB_PASSWORD=your_password
```

Create the database:

```bash
mysql -u root -p -e "CREATE DATABASE us_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 5. Run Migrations

```bash
php artisan migrate
```

### 6. Build Frontend Assets

```bash
# Development
npm run dev

# Production
npm run build
```

### 7. Start Development Server

```bash
php artisan serve
```

Visit: http://127.0.0.1:8000

## 📁 Project Structure

```
app/
├── Models/
│   ├── Couple.php          # Couple relationship model
│   ├── World.php           # Gamification world model
│   ├── XpEvent.php         # XP tracking model
│   └── MoodCheckin.php     # Daily mood check-in model
├── Services/
│   └── CoupleService.php   # Couple management logic
└── Livewire/
    ├── Dashboard/
    │   └── CoupleWorld.php # Main dashboard component
    └── Couple/
        └── CreateOrJoin.php # Couple linking component

database/
└── migrations/
    ├── *_create_couples_table.php
    ├── *_create_couple_user_table.php
    ├── *_create_worlds_table.php
    ├── *_create_xp_events_table.php
    └── *_create_mood_checkins_table.php
```

## 🗄️ Database Schema

### Core Tables

- **couples** - Couple relationships with invite codes
- **couple_user** - Pivot table linking users to couples
- **worlds** - Gamification state (theme, level, XP, ambience)
- **xp_events** - XP earning activity log
- **mood_checkins** - Daily mood tracking

### Jetstream Tables

- **users** - User authentication
- **teams** - Team management (adapted for couples)
- **personal_access_tokens** - API tokens

## 🎮 Core Concepts

### Couple Linking

Users can create or join a couple using an invite code:

```php
use App\Services\CoupleService;

$coupleService = new CoupleService();

// Create a couple
$couple = $coupleService->createCouple($user, ['theme' => 'garden']);

// Join using invite code
$couple = $coupleService->joinCouple($user, 'ABC12345');
```

### XP System

Earn XP through various activities:
- Daily mood check-ins
- Completing missions
- Conflict repair sessions
- Adding vault memories
- Positive chat interactions

### World Themes

- **Garden** - Nature-inspired environment
- **House** - Cozy home setting
- **Kitchen** - Culinary theme
- **Farm** - Rustic countryside

## 🔐 Security & Privacy

- All couple data is access-controlled
- Vault items support private/shared/dual-consent visibility
- AI conversations are strictly private
- Laravel policies enforce data access rules
- Rate limiting on sensitive endpoints

## 🧪 Testing

```bash
# Run tests
php artisan test

# Run with coverage
php artisan test --coverage
```

## 🩺 Dev Doctor (Quick Checks)

If local setup fails, run this short checklist first:

1. Start XAMPP and make sure both Apache and MySQL are running.
2. Verify database connection values in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=us_app
DB_USERNAME=root
DB_PASSWORD=
```

3. Run migrations and seeders:

```bash
php artisan migrate:fresh --seed
```

## Demo Mode

Seed rich deterministic demo data with one command:

```bash
php artisan us:seed-demo
```

If you want a fully fresh database first:

```bash
php artisan migrate:fresh --seed
php artisan us:seed-demo
```

### Demo Credentials

- `couplea1@demo.test` / `DemoPass123!`
- `couplea2@demo.test` / `DemoPass123!`
- `coupleb1@demo.test` / `DemoPass123!`
- `coupleb2@demo.test` / `DemoPass123!`

### Quick Testing Checklist

1. Log in as `couplea1@demo.test`, open Dashboard, verify Garden world level/items/placement.
2. Log in as `coupleb1@demo.test`, verify a different Space world and history.
3. Open `Missions` and inspect daily/weekly assignment and completion history.
4. Open `Repair` and confirm at least two completed sessions with acknowledged agreements.
5. Open `Vault` and verify shared/private/locked memories and comfort-flagged entries.
6. Open `AI Coach` and inspect vent + bridge histories and sent bridge suggestion.
7. Open `Gifts` and verify both partner wishlists and saved suggestion records.

## 📝 Git Setup

See [GIT_SETUP.md](GIT_SETUP.md) for detailed Git configuration instructions.

## 🚧 Development Roadmap

### Milestone 1: Project Setup & Auth ✅
- [x] Laravel TALL stack setup
- [x] Database schema
- [x] Couple linking system
- [x] Basic models and services

### Milestone 2: Core Loop (In Progress)
- [ ] World visualization
- [ ] XP engine
- [ ] Mood check-ins UI
- [ ] Mission system

### Milestone 3: Communication
- [ ] Couple chat
- [ ] Quick love buttons
- [ ] Reactions

### Milestone 4: Repair Flow
- [ ] Conflict repair wizard
- [ ] Agreement cards

### Milestone 5: Vault
- [ ] Media uploads
- [ ] Locking system
- [ ] Comfort playlist

### Milestone 6: AI Coach
- [ ] OpenAI integration
- [ ] Venting mode
- [ ] Bridge mode
- [ ] Guardrails

### Milestone 7: Polish
- [ ] UI theming
- [ ] Notifications
- [ ] Testing
- [ ] Documentation

## 🤝 Contributing

This is a private project. For questions or suggestions, contact the repository owner.

## 📄 License

Proprietary - All rights reserved

## 👨‍💻 Author

meteorboyF

---

**Note**: This is an MVP in active development. Features and documentation will be updated regularly.
