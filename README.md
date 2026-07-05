# 🔧 GarageFlow — API

Backend for **GarageFlow**, a vehicle service management system for garages — track service jobs from intake to delivery.

> 🚧 In active development. Front-end lives at [garageflow-frontend](https://github.com/mehedi-200/garageflow-frontend).

## Planned Features

- 🔐 Authentication with Laravel Sanctum (admin & mechanic roles)
- 👥 Customer management (CRUD + search)
- 🚗 Vehicle registry linked to customers
- 🛠️ Service jobs with enforced status workflow: `Pending → In Progress → Completed → Delivered`
- 🧾 Invoicing — labor cost, parts cost, totals, payment status
- 📊 Dashboard stats endpoint (customers, vehicles, jobs, revenue)

## Tech Stack

- PHP / Laravel
- Laravel Sanctum (API tokens)
- MySQL
- PHPUnit feature tests + GitHub Actions CI

## Database Design

```
users ─┐
       └─< service_jobs (mechanic)
customers ─< vehicles ─< service_jobs ─< service_items
                                       └─ invoices
```

## Git Workflow

`main` ← `develop` ← `feature/*` branches, merged via pull requests.

## Author

**Mehedi** — [@mehedi-200](https://github.com/mehedi-200)
