# SiteHRM — Construction HR Management System

> Built for African construction companies. Manage your site workforce efficiently.

**Current Deployment:** CRBC Uganda Ltd — Kayunga-Bbaale-Galiraya Road (87KM)  
**Contract:** MOWT/WORKS/2024-25/00115  
**Developer:** Eng. Bernard Nasinyama

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 12 |
| UI | Filament 4 + Livewire |
| Database | SQLite (dev) / MySQL (production) |
| Auth | Spatie Laravel Permission |
| Language | PHP 8.5 |
| Frontend | Tailwind CSS + Alpine.js |

---

## Quick Start

```bash
# Clone and install
git clone https://github.com/yourrepo/open-source-hrm
cd open-source-hrm
composer install

# Configure
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate --force
php artisan db:seed --class=RolePermissionSeeder

# Serve
php artisan serve --host=0.0.0.0 --port=8000
```

**New client setup:** Visit `http://localhost:8000/setup`

---

## User Roles

| Role | Access |
|------|--------|
| `super_admin` | Full system access |
| `admin` | HR + Finance (no system settings) |
| `hr_assistant` | HR portal only |
| `employee` | Self-service portal |

---

## Remote Access

```bash
# Start server
php artisan serve --host=0.0.0.0 --port=8000

# Start tunnel (new window)
./ngrok.exe http 8000
# or double-click START_REMOTE_ACCESS.bat
```

---

## Whitelabel Deployment

Edit `.env`:
```
APP_BRAND_NAME="Roko Construction HRM"
APP_COMPANY_NAME="Roko Construction Ltd"
APP_PROJECT_NAME="Kampala Northern Bypass"
APP_CONTRACT_NO="UNRA/2024/KNB-001"
APP_CURRENCY=UGX
```

Fresh install for new client:
```bash
php artisan db:seed --class=FreshInstallSeeder --force
php artisan db:seed --class=RolePermissionSeeder --force
```
