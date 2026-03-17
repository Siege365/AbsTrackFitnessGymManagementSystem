# AbsTrack Fitness Gym Management System

AbsTrack is a full gym operations platform built on Laravel 12. It centralizes customer management, training schedules, attendance, billing, inventory, reporting, staff administration, and system notifications in one dashboard.

The project uses a modular route structure and Blade-based UI with Vite-managed assets. Current implementation includes route groups for customers, sessions, payments and billing, inventory, reports and analytics, and staff management.

## Core Modules

- Dashboard KPI overview
- Customers: memberships and PT clients
- Sessions: training schedules and customer attendance
- Payments and Billing: membership/PT/product payments, history, receipts, refunds
- Inventory Supplies: products, stock transactions, inventory logs
- Reports and Analytics: revenue, attendance, and export
- Staff Management: staff accounts, trainers, activity logs
- Configuration: gym plans and categories
- Notifications: bell dropdown and full notifications page
- Account Settings: profile and password updates

## Main Route Map

These are the primary user-facing pages in the current system:

| Module                      | Path                                                  |
| --------------------------- | ----------------------------------------------------- |
| Dashboard                   | `/`                                                   |
| Memberships                 | `/customers/memberships`                              |
| PT Clients                  | `/customers/clients`                                  |
| Training Sessions           | `/sessions/training-sessions`                         |
| Customer Attendance         | `/sessions/customer-attendance`                       |
| Payment System (Membership) | `/payments-billing/payment-system/membership-payment` |
| Payment System (PT)         | `/payments-billing/payment-system/pt-payment`         |
| Payment System (Product)    | `/payments-billing/payment-system/product-payment`    |
| Payment History             | `/payments-billing/payment-history`                   |
| Inventory Products          | `/inventory/products`                                 |
| Inventory Logs              | `/inventory/inventory-logs`                           |
| Reports and Analytics       | `/reports-analytics`                                  |
| Staff Accounts              | `/staff-management/staff`                             |
| Trainers                    | `/staff-management/trainers`                          |
| Activity Logs               | `/staff-management/activity-logs`                     |
| Configuration               | `/configuration`                                      |
| Notifications Page          | `/notifications/page`                                 |
| Account Settings            | `/account/settings`                                   |

## Tech Stack

- Backend: Laravel 12, PHP 8.2+
- Frontend: Blade templates, jQuery-based interactions, custom CSS, MDI icons
- Build tool: Vite 7
- PDF support: `barryvdh/laravel-dompdf`
- Database: SQLite/MySQL (environment-configurable)
- Testing: PHPUnit 11

## Project Structure

- `app/Http/Controllers`: module controllers
- `app/Models`: domain models (Membership, Client, Payment, InventorySupply, Notification, etc.)
- `app/Services`: business services (RefundService, NotificationService)
- `app/Console/Commands`: custom Artisan commands
- `database/migrations`: schema history
- `resources/views`: Blade pages and partials
- `resources/js`: common and page-level JavaScript
- `resources/css`: global/theme/page styles
- `routes/web.php`: HTTP routes
- `routes/console.php`: scheduler and console routes

## Installation

### Prerequisites

- PHP 8.2+
- Composer
- Node.js and npm
- Database (SQLite or MySQL)

### Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
```

### Default Seeded Accounts

After `php artisan db:seed`, these accounts are available:

- Admin: `admin@abstrack.com` / `password`
- Manager: `manager@abstrack.com` / `password`

## Run the Application

### Full development stack (recommended)

```bash
composer run dev
```

This runs Laravel server, queue listener, log watcher, and Vite concurrently.

### Manual run (alternative)

```bash
php artisan serve
npm run dev
```

### Production build

```bash
npm run build
php artisan config:cache
php artisan route:cache
```

## Notifications and Scheduler

The project includes an automated notification generator:

- Command: `php artisan notifications:generate`
- Schedule: daily at `08:00` (configured in `routes/console.php`)
- Checks:
    - expiring memberships/clients (7-day window)
    - low-stock and out-of-stock inventory
    - cleanup of old notifications (30+ days)

For production, ensure Laravel scheduler is running every minute:

```bash
php artisan schedule:run
```

## Testing and Maintenance

```bash
composer run test
php artisan route:list
php artisan view:clear
php artisan config:clear
```

## Notes

- `/register` is inside authenticated routes and intended for admin/staff onboarding by logged-in authorized users.
- Payment history is currently served from one page route (`/payments-billing/payment-history`) with multiple transaction types.
- Notification bell and notifications page consume `/notifications/*` endpoints via AJAX.
