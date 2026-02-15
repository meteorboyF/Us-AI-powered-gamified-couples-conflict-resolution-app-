# Us 💕

**AI-Powered Gamified Couples Conflict Resolution Platform**

---

## What is Us?

**Us** is a cute, immersive couples platform that transforms daily communication into a cozy game experience. Build your shared world together, navigate conflicts with AI-powered support, and strengthen your relationship through playful daily habits and meaningful interactions.

Think of it as a **relationship companion** that makes emotional work feel like quality time together.

---

## ✨ Key Features

- 🏡 **Shared World Building** - Your relationship progress creates a beautiful, evolving game world
- 💬 **Realtime Chat** - Stay connected with messages, voice notes, and presence indicators
- 🤖 **AI Coach** - Get support during conflicts with Vent, Bridge, and Repair modes
- 🎯 **Daily Missions** - Small, meaningful actions that strengthen your bond
- 🔒 **Memory Vault** - A safe space for cherished moments and supportive memories
- 🎁 **Smart Gift Ideas** - AI-powered personalized gift suggestions

---

## 📚 Documentation

- **[PROJECT_DOCUMENT.md](./PROJECT_DOCUMENT.md)** - Complete product requirements, features, and architecture
- **[ENV_SETUP_TALL.md](./ENV_SETUP_TALL.md)** - Development environment setup guide (TALL stack)

---

## 🚀 Quick Start

### Prerequisites
- PHP 8.2+
- Composer
- Node.js & NPM
- MySQL or PostgreSQL
- Git

### Installation

```bash
# Clone the repository
git clone https://github.com/meteorboyF/Us-AI-powered-gamified-couples-conflict-resolution-app-.git
cd Us-AI-powered-gamified-couples-conflict-resolution-app-

# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Configure database in .env, then migrate
php artisan migrate

# Start development servers
php artisan serve          # Terminal 1 - Backend (http://localhost:8000)
npm run dev                # Terminal 2 - Frontend assets
```

For detailed setup instructions, see **[ENV_SETUP_TALL.md](./ENV_SETUP_TALL.md)**.

---

## 🛠️ Tech Stack

**TALL Stack**:
- **T**ailwind CSS - Utility-first styling
- **A**lpine.js - Lightweight JavaScript framework
- **L**aravel - PHP backend framework
- **L**ivewire - Reactive components

**Additional**:
- WebSockets (Laravel Reverb) - Realtime communication
- MySQL/PostgreSQL - Database
- Gemini/OpenAI - AI integration
- Redis - Caching & queues

---

## 📖 Project Structure

```
├── app/                    # Laravel application code
├── database/              # Migrations, seeders, factories
├── resources/
│   ├── views/            # Blade templates & Livewire components
│   ├── css/              # Tailwind CSS
│   └── js/               # Alpine.js & frontend logic
├── routes/               # Web, API routes
├── tests/                # Feature & unit tests
├── PROJECT_DOCUMENT.md   # Full PRD & specifications
├── ENV_SETUP_TALL.md     # Setup guide
└── README.md             # This file
```

---

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test
php artisan test --filter=CoupleTest
```

---

## 🤝 Contributing

This is a private project. For team members:

1. Create a feature branch from `main`
2. Follow the coding standards in the project
3. Write tests for new features
4. Submit a pull request for review

---

## 📝 Development Workflow

1. **Read** `PROJECT_DOCUMENT.md` to understand features and requirements
2. **Setup** your environment using `ENV_SETUP_TALL.md`
3. **Build** features following the acceptance criteria
4. **Test** thoroughly before committing
5. **Deploy** with confidence

---

## 🔐 Security & Privacy

- Couple data is strictly isolated
- Vault items have optional encryption
- AI interactions never auto-share without consent
- All sensitive data follows security best practices

See `PROJECT_DOCUMENT.md` section 5 for detailed non-functional requirements.

---

## 📞 Support

For questions or issues:
- Check `ENV_SETUP_TALL.md` troubleshooting section
- Review `PROJECT_DOCUMENT.md` for feature specifications
- Contact the development team

---

## 📄 License

Private project - All rights reserved.

---

**Built with ❤️ for couples who want to grow together**

---

**Version**: 1.0.0  
**Last Updated**: 2026-02-16  
**Status**: Foundation Ready - Ready for Development 🚀
