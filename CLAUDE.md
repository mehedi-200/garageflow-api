# GarageFlow API — Project Conventions

Vehicle service management backend. Laravel 13 + Sanctum + MySQL REST API.
Frontend repo: https://github.com/mehedi-200/garageflow-frontend

## Architecture (MANDATORY — follow for every feature)

```
Route → Middleware → Controller → FormRequest (validation) → Service (business logic) → Model
                                                     ↓
                                Resource (response filtering) → ApiResponse trait
```

## Rules

1. **NO business logic in controllers.** Controllers only receive the request, call a Service method, and return a response. All business logic lives in `app/Services/` (e.g. `app/Services/CustomerService.php`).

2. **NO inline validation in controllers.** Always create a FormRequest in `app/Http/Requests/` (e.g. `StoreCustomerRequest`, `UpdateCustomerRequest`) and type-hint it in the controller method.

3. **Always use API Resources** (`app/Http/Resources/`) to filter/shape every response. Never return models or arrays directly.

4. **All responses go through the `ApiResponse` trait** (`app/Traits/ApiResponse.php`). It has exactly two methods:
   - `sendSuccess($data, string $message = '...', int $code = 200)`
   - `sendError(string $message, int $code, $errors = null)`
   Controllers use the trait; never hand-build `response()->json()` elsewhere.

5. **Use middleware properly.** Protect routes with `auth:sanctum`; use route groups for shared middleware; role checks go in middleware, not controllers.

6. **Routes: always import controllers with `use` at the top** of the route file, then reference the class. Never inline fully-qualified class paths in the route definition.

   ```php
   use App\Http\Controllers\CustomerController;   // ✅ at top

   Route::apiResource('customers', CustomerController::class);
   ```
   This applies everywhere: any class path used in any file goes in a `use` statement at the top.

## Folder Structure

```
app/
├── Http/
│   ├── Controllers/    // thin controllers only
│   ├── Requests/       // FormRequest validation classes
│   ├── Resources/      // API Resources
│   └── Middleware/
├── Models/
├── Services/           // ALL business logic
└── Traits/
    └── ApiResponse.php // sendSuccess / sendError
```

## Domain

Customers → Vehicles → Service Jobs (status: Pending → In Progress → Completed → Delivered, transitions enforced in Service class) → Service Items + Invoices. Roles: admin, mechanic.

## Git

`main` ← `develop` ← `feature/*` branches, meaningful commit messages, PRs into develop.
