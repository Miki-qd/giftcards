# Gift Cards Management System

A secure, full-stack web application for managing gift cards and tracking operations, built with **Laravel 13**, **Inertia.js v3**, **Vue 3**, and **Tailwind CSS v4**.

## Key Features

- **Dashboard** — Real-time overview of system statistics (active/used cards, recent actions).
- **Gift Card CRUD** — Create, read, update, and delete gift cards with CSV export.
- **Activity Log** — Full audit trail of all operations with CSV export.
- **Authentication & Security**:
  - Registration gated by admin-generated **Invite Tokens**.
  - **Two-Factor Authentication (2FA)** via TOTP.
  - **Passkey / WebAuthn** support for passwordless login.
  - Automatic session timeout after 5 minutes of inactivity.

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 13 (PHP 8.3+) |
| Frontend | Vue 3 + TypeScript |
| Routing | Inertia.js v3 |
| Styling | Tailwind CSS v4 + shadcn-vue |
| Auth | Laravel Fortify + Passkeys |
| Database | SQLite (zero-configuration) |
| Build | Vite 8 |

## Prerequisites

| Requirement | Version |
|---|---|
| PHP | ≥ 8.3 |
| Composer | ≥ 2.x |
| Node.js | ≥ 18.x |
| npm | ≥ 9.x |

> No external database server (MySQL, PostgreSQL) is required. The application uses **SQLite** by default.

## How to Run the Project

Follow every step in order. Each command must complete successfully before proceeding.

### 1. Clone the Repository

```bash
git clone <repository-url>
cd giftcards
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Configure the Environment

```bash
cp .env.example .env
```

On Windows (Command Prompt):

```cmd
copy .env.example .env
```

> The default `.env` is pre-configured for SQLite. No manual edits are required.

### 4. Generate the Application Key

```bash
php artisan key:generate
```

### 5. Install Node Dependencies

```bash
npm install
```

### 6. Create the Database and Seed Data

```bash
php artisan migrate:fresh --seed
```

This creates all tables and seeds an administrator account.

### 7. Build Frontend Assets

```bash
npm run build
```

### 8. Start the Development Server

**Option A** — Single command (recommended):

```bash
composer dev
```

This starts the PHP server, queue worker, and Vite dev server concurrently.

**Option B** — Manual (two separate terminals):

```bash
# Terminal 1
php artisan serve

# Terminal 2
npm run dev
```

### 9. Access the Application

Open **http://giftcards.test** in your browser.

#### Default Admin Account

| Field | Value |
|---|---|
| Email | `admin@example.com` |
| Password | `12345678` |

Use this account to generate **Invite Tokens** (under Settings) for registering additional users.

## Quick Reference

| Command | Description |
|---|---|
| `composer dev` | Start all services (server + queue + Vite) |
| `php artisan serve` | Start PHP development server |
| `npm run dev` | Start Vite development server |
| `npm run build` | Build production frontend assets |
| `php artisan migrate:fresh --seed` | Reset database with seed data |
| `php artisan test` | Run the test suite |
