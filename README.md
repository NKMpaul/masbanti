# 🧺 Masbanti — Application de Gestion de Franchise de Pressing

Plateforme numérique complète pour la gestion d'une franchise de pressing.

## Stack Technologique

| Couche | Technologie |
|---|---|
| Backend / API | Laravel 12 (PHP 8.2) |
| Base de données | MySQL 8 |
| Back-office | React 18 + Vite + TypeScript |
| Site B2C | Next.js 14 |
| Application Mobile | React Native (Expo) |

## Architecture
```
masbanti/
├── backend/       ← API REST Laravel
├── backoffice/    ← Interface admin React
├── b2c/           ← Portail client Next.js
└── mobile/        ← Application mobile React Native
```

## Prérequis

- PHP 8.2+
- Composer
- Node.js 20+
- MySQL 8
- npm

## Installation

### 1. Cloner le repository
```bash
git clone https://github.com/NKMpaul/masbanti.git
cd masbanti
```

### 2. Backend Laravel
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed --class=RoleSeeder
php artisan serve
```

### 3. Backoffice React
```bash
cd backoffice
npm install
npm run dev
```

### 4. Site B2C Next.js
```bash
cd b2c
npm install
npm run dev
```

### 5. Application Mobile
```bash
cd mobile
npm install
npx expo start --web
```

## URLs

| Application | URL |
|---|---|
| Backoffice | http://localhost:5173 |
| Site B2C | http://localhost:3000 |
| API Laravel | http://localhost:8000 |

## Comptes de test

| Email | Mot de passe | Rôle |
|---|---|---|
| admin@masbanti.com | password123 | SUPER_ADMIN |
| alami@gmail.com | password123 | CLIENT |

## Modules

- ✅ Authentification & Rôles
- ✅ Gestion des Agences
- ✅ Gestion des Clients
- ✅ Gestion des Commandes
- ✅ Gestion des Employés & Pointage
- ✅ Facturation & Paiements
- ✅ Programme de Fidélisation
- ✅ Livraison & Collecte
- ✅ Notifications
- ✅ Dashboard & Reporting
- ✅ Tests Unitaires
- ✅ CI/CD GitLab

## Année universitaire 2025-2026zC