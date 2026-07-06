# 🔧 GarageFlow — API

![tests](https://github.com/mehedi-200/garageflow-api/actions/workflows/tests.yml/badge.svg)

Backend for **GarageFlow**, a vehicle service management system for garages — track service jobs from intake to delivery. Front-end lives at [garageflow-frontend](https://github.com/mehedi-200/garageflow-frontend).

## Features

- 🔐 **Authentication** — Laravel Sanctum tokens, admin & mechanic roles (`role` middleware)
- 👥 **Customers** — CRUD with search, pagination and soft deletes
- 🚗 **Vehicles** — registry linked to customers, unique registration numbers
- 🛠️ **Service jobs** — enforced status workflow `Pending → In Progress → Completed → Delivered`
  (no skipping, no going backwards — one small state machine in `ServiceJobService`), admin-only
  cancel, mechanics scoped to their own jobs, service items only while in progress
- 🧾 **Invoices** — auto-created when a job completes; `INV-YYYY-NNNN` numbering; labor + parts
  totals always computed server-side; mark-as-paid (double-pay blocked)
- 📊 **Dashboard** — totals, vehicles in service, monthly completed & revenue, jobs by status
- 🔍 **Master search** — grouped results across customers, vehicles and jobs
- 🔔 **Notifications** — job assigned → mechanic, status changed / invoice paid → admins

## Architecture

Every endpoint follows the same strict chain (see [CLAUDE.md](CLAUDE.md)):

```
Route → Middleware → Controller (thin) → FormRequest → Service (business logic) → Resource → ApiResponse
```

All responses share one envelope via the `ApiResponse` trait:

```json
{ "success": true, "message": "…", "data": { } }
```

## Tech Stack

PHP 8.4 · Laravel 13 · Sanctum · MySQL · PHPUnit (25 feature tests) · GitHub Actions CI

## Getting Started

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Set your MySQL credentials in `.env`, then:

```bash
php artisan migrate --seed
php artisan serve        # http://127.0.0.1:8000
```

The seeder creates demo data (15 customers, 25+ vehicles, 40 jobs, invoices) and these accounts:

| Role | Email | Password |
|---|---|---|
| Admin | `admin@garageflow.test` | `password` |
| Mechanic | `jakir@garageflow.test` | `password123` |
| Mechanic | `rafiq@garageflow.test` | `password123` |

## Tests

```bash
php artisan test
```

Feature tests cover authentication & roles, customer CRUD/search/soft-delete, every valid and
invalid status transition, item rules, invoice auto-creation, totals math and payment flow.

## Database

```
users ─┐
       └─< service_jobs (mechanic) ─< service_items
customers ─< vehicles ─< service_jobs ─ invoices (1:1)
notifications >─ users
```

## Author

**Mehedi** — [@mehedi-200](https://github.com/mehedi-200)
