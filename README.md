# MiniLedger — Admin API

> Distributed Offline-First POS & Inventory Management System  
> Built with Laravel 12 · PHP 8.1 · MySQL · Sanctum · Gemini AI

---

## Overview

MiniLedger is a distributed POS and inventory ecosystem designed for offline-first environments. It consists of two components:

- **Admin API** (this repo) — Laravel 12 RESTful backend for admins
- **POS Client** — Custom Native PHP terminal for cashiers (separate repo)

The system is built to operate on a local network with zero dependency on internet connectivity during sales operations, syncing data to the admin server when available.

---

## Features

- **Role-Based Access Control** — Admin and Cashier roles via Laravel Sanctum
- **Inventory Management** — Products, Categories, Suppliers with profit analysis and valuation reporting
- **Invoice System** — Sale and Return invoices with automatic stock adjustment and UUID idempotency
- **Fault-Tolerant Sync** — FIFO sync engine with ACID-compliant merging and Double-Sync prevention
- **Business Logic Enforcement** — Wholesale buy_price isolation, 25% minimum profit margin constraint
- **AI Integration** — Gemini AI API for automated vehicle-to-parts compatibility mapping
- **Notification System** — Actionable admin notifications on returns and sync events
- **Dynamic Search** — Unified search controller across all resources

---

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 12 |
| Language | PHP 8.1 |
| Auth | Laravel Sanctum |
| Database | MySQL |
| AI | Gemini AI API |
| API Style | RESTful JSON |

---

## Getting Started

### Requirements

- PHP >= 8.1
- Composer
- MySQL
- Laravel 12

### Installation
```bash
git clone https://github.com/yousefelnopi/miniledger-admin-api.git
cd miniledger-admin-api
composer install
cp .env.example .env
php artisan key:generate
```

Configure your `.env` file with your database and API credentials, then:
```bash
php artisan migrate --seed
php artisan serve
```

---

## API Collection

A full Postman collection is included in the `/postman` directory covering all endpoints:

- Auth (Login, Logout, Token Refresh)
- Categories, Products, Suppliers
- Inventory & Reporting
- Invoices (Sale & Return)
- Cashier Management
- AI Compatibility
- Dynamic Search
- Sync Endpoints

Import `postman/miniledger-api.postman_collection.json` into Postman to get started.

---

## Project Structure
```
app/
├── Http/Controllers/
│   ├── AuthController.php
│   ├── InvoiceController.php
│   ├── CompatibilityController.php
│   └── ...
├── Models/
├── Services/
└── Jobs/
```

---

## Related

- [MiniLedger POS Client](https://github.com/yousefelnopi) — Native PHP cashier terminal

---

## Author

**Yousef Ali Elnopi** — Backend Software Engineer  
[LinkedIn](https://linkedin.com/in/yousef-elnopi-backend) · [GitHub](https://github.com/yousefelnopi)
