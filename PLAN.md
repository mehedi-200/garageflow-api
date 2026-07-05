# GarageFlow — Development Plan

> Vehicle service management system. Laravel 13 API + React 19 SPA.
> Workflow: `main` ← `develop` ← `feature/*`. One feature = one branch = one PR.
> UI rules in [CLAUDE.md](CLAUDE.md) apply to every part below — no exceptions.

**Progress legend:** `[ ]` pending · `[x]` done

---

## Feature 0 — App Shell & Theme System — `feature/app-shell` (frontend)

### Part 0A — Theme engine
- [x] CSS variable design tokens (colors, surfaces, borders, text) in `index.css`
- [x] Three themes: `dark` (DEFAULT), `light`, `reading` via root class
- [x] `useTheme` hook + localStorage persistence
- [x] No hardcoded colors anywhere — tokens only

### Part 0B — Core component library (`src/components/`)
- [x] Button (variants: primary / secondary / danger / icon)
- [x] Input, Select, Textarea (React Hook Form compatible)
- [x] Card, Modal, EmptyState, Spinner, StatusChip
- [x] Table (desktop) / ListView cards (mobile) — one data-list component, two renders
- [x] **Pagination** — exact approved design: rounded bar, circular indigo refresh button, `Showing [N] entries` badge, `Show [size]` input, `‹Previous · pages (active = solid indigo square) · Next›`; compact app-style variant on mobile

### Part 0C — Desktop layout
- [x] Thin header: master search bar + notification bell + profile menu
- [x] Thin collapsible sidebar (icons-only when collapsed, state persisted)
- [x] Thin footer

### Part 0D — Mobile / tablet native-app layout
- [x] Bottom navigation bar (Dashboard, Customers, Vehicles, Jobs, More)
- [x] App-style top bar per page (title + contextual actions)
- [x] Full-screen card-based pages, 44px touch targets

### Part 0E — Routing skeleton
- [x] React Router routes for all pages (placeholder pages)
- [x] `<AppLayout>` switching chrome by breakpoint
- [x] `<ProtectedRoute>` wrapper (redirects to /login)

---

## Feature 1 — Authentication & Profile — `feature/authentication`

### Part 1A — API: auth endpoints
- [x] `users` table: add `role` (admin|mechanic), seeder for first admin
- [x] `POST /api/login`, `POST /api/logout` — `AuthController` → `LoginRequest` → `AuthService` → `UserResource` → `ApiResponse`
- [x] Role middleware (`admin` only routes)

### Part 1B — API: profile & mechanic accounts
- [x] `GET/PUT /api/profile` (`UpdateProfileRequest`, password change)
- [x] Admin CRUD for mechanic accounts (`StoreUserRequest`)

### Part 1C — Frontend: auth flow
- [x] Login page (app-style on mobile)
- [x] `authService.js` + `useAuth` hook, token storage, 401 auto-logout (interceptor exists)
- [x] ProtectedRoute wired to real auth state

### Part 1D — Frontend: profile & settings
- [x] Profile page (view/edit, password change)
- [x] Profile dropdown in header: profile / theme switcher / logout
- [x] Mechanics management page (admin only)

---

## Feature 2 — Customers — `feature/customer-management`

### Part 2A — API
- [ ] Migration + `Customer` model (soft deletes), factory
- [ ] `apiResource /api/customers` — thin controller → `CustomerService` → `CustomerResource`
- [ ] `StoreCustomerRequest` / `UpdateCustomerRequest`
- [ ] Search (`?q=` name/phone) + paginated index

### Part 2B — Frontend: list
- [ ] Customers page: Table on desktop / cards on mobile
- [ ] Search box + shared Pagination
- [ ] `customerService.js` + TanStack Query hooks (`useCustomers`)

### Part 2C — Frontend: create / edit / delete
- [ ] Add & edit forms (React Hook Form + shared Input components, modal on desktop / full page on mobile)
- [ ] Delete with confirm Modal (soft delete)

### Part 2D — Customer detail
- [ ] Detail page: info card + vehicles list + service history
- [ ] API: `GET /api/customers/{id}` returns vehicles + jobs (Resource nesting)

---

## Feature 3 — Vehicles — `feature/vehicle-management`

### Part 3A — API
- [ ] Migration + `Vehicle` model (`customer_id`, unique `registration_no`, brand, model, year, notes)
- [ ] `apiResource /api/vehicles` + `VehicleService` + `VehicleResource` + Form Requests
- [ ] Filter by customer, search by registration number, pagination

### Part 3B — Frontend: list & filter
- [ ] Vehicles page (Table/cards) + customer filter + search + Pagination
- [ ] `vehicleService.js` + `useVehicles` hooks

### Part 3C — Frontend: create / edit + detail
- [ ] Add/edit forms with customer selector
- [ ] Vehicle detail page: info + full job history

---

## Feature 4 — Service Jobs — `feature/service-jobs` ⭐ core

### Part 4A — API: jobs CRUD
- [ ] Migrations: `service_jobs` (vehicle_id, mechanic_id, service_type, status, description, expected_delivery) + `service_items` (job_id, name, cost)
- [ ] `ServiceJobController` (thin) → `ServiceJobService` → `ServiceJobResource`
- [ ] `StoreServiceJobRequest` (vehicle, mechanic, fixed service-type list)

### Part 4B — API: status state machine
- [ ] `PATCH /api/service-jobs/{id}/status` — transitions enforced in `ServiceJobService`: Pending → In Progress → Completed → Delivered only
- [ ] Invalid transition ⇒ `sendError(422)`
- [ ] Admin-only cancel; mechanics restricted to their own jobs (policy/middleware)

### Part 4C — API: service items
- [ ] Add/remove items while job In Progress (`StoreServiceItemRequest`)
- [ ] Items sum exposed on `ServiceJobResource`

### Part 4D — Frontend: jobs list
- [ ] Jobs page: status filter chips + mechanic/date filters + search + Pagination
- [ ] StatusChip colors per status (theme tokens)
- [ ] "My Jobs" view for mechanics

### Part 4E — Frontend: create job + detail
- [ ] Create form: vehicle select → owner auto-display, mechanic select, type, dates
- [ ] Job detail page: info, status advance button (only valid next step shown), service items add/remove

---

## Feature 5 — Invoices — `feature/invoice`

### Part 5A — API
- [ ] Migration: `invoices` (job_id, invoice_no, labor_cost, parts_cost, total, payment_status, paid_at)
- [ ] Auto-create on job → Completed (in `ServiceJobService`), `InvoiceService` computes totals server-side
- [ ] Invoice number generator `INV-YYYY-NNNN`
- [ ] `PATCH /api/invoices/{id}/pay` + paginated index + `InvoiceResource`

### Part 5B — Frontend
- [ ] Invoices page: list + payment status filter + Pagination
- [ ] Invoice detail: print-friendly layout (works in all 3 themes; print = clean light)
- [ ] Mark-as-paid action (admin)

---

## Feature 6 — Dashboard — `feature/dashboard`

### Part 6A — API
- [ ] `GET /api/dashboard` — totals: customers, vehicles, in-service, completed (month), revenue (month, paid invoices) — single `DashboardService`
- [ ] Recent jobs + jobs-by-status counts in same response

### Part 6B — Frontend
- [ ] Stat cards grid (responsive, theme-aware)
- [ ] Recent jobs list + status breakdown
- [ ] Dashboard is the post-login landing page

---

## Feature 7 — Master Search — `feature/dashboard` (same branch)

### Part 7A — API
- [ ] `GET /api/search?q=` → grouped results (customers, vehicles by reg no, jobs) via `SearchService`

### Part 7B — Frontend
- [ ] Header search bar: debounced dropdown with grouped results → navigate to detail
- [ ] Mobile: search icon in top bar → full-screen app-style search page

---

## Feature 8 — Notifications — `feature/dashboard` (same branch)

### Part 8A — API
- [ ] `notifications` table + endpoints: list (paginated), unread count, mark read
- [ ] Triggers in Services: job assigned (→ mechanic), status changed, invoice paid (→ admin)

### Part 8B — Frontend
- [ ] Bell + unread badge in header / app top bar
- [ ] Notification panel (dropdown desktop / full-screen sheet mobile), mark-as-read
- [ ] Poll with TanStack Query refetch interval

---

## Feature 9 — Quality & Release — `refactor/*` → `main`

### Part 9A — Tests (API)
- [ ] Auth: login, role access
- [ ] Status machine: every valid + invalid transition
- [ ] Invoice totals math
- [ ] Customer/vehicle CRUD + validation

### Part 9B — CI & data
- [ ] GitHub Actions: run tests on push/PR + README badges
- [ ] Seeders: 1 admin, 2 mechanics, ~15 customers, ~25 vehicles, ~40 jobs (all statuses), invoices

### Part 9C — Release
- [ ] README screenshots (desktop + mobile, all 3 themes)
- [ ] Final review, merge `develop` → `main`, tag `v1.0.0`

---

## Out of scope for v1 (planned v2 — open as GitHub issues)
Public registration · password-reset emails · customer portal · SMS/email to customers · CSV import/export · appointment calendar · parts inventory · online payments · partial payments · tax config · real-time websockets (Reverb) · charts library · E2E tests
