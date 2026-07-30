# CapBay Vroom

CapBay Vroom is a full-stack vehicle sales system built with Laravel and
Livewire. It is intended to manage vehicle promotions and track a customer from
registration through test-drive, loan, and purchase milestones.

## Current progress

The database foundation is complete. The application currently includes:

- Users with administrator and sales-agent roles
- Vehicles with prices stored in sen and an active status
- Vehicle-specific promotions with discount, minimum down-payment, customer
  limit, validity period, and active status
- Customer registrations with optional promotions
- Registration status and timestamps for test-drive, loan, purchase, and
  cancellation milestones
- Price snapshots and calculated payment fields on registrations
- Seed data for users, a vehicle, and its promotion

The application workflows and user interface are not implemented yet.

## Database relationships

- A vehicle can have many promotions.
- A registration belongs to a vehicle.
- A registration may belong to a promotion.
- Deleting a vehicle is restricted when it has registrations.
- Deleting a promotion preserves its registrations and clears their promotion
  reference.

Monetary amounts are stored as integer sen to avoid floating-point rounding.
Promotion percentages are stored as basis points, where 100 basis points equal
1%.

## Tech stack

- PHP 8.3 or later
- Laravel 13
- Livewire 4
- MySQL
- Tailwind CSS 4
- Pest 5

## Local setup

### Prerequisites

- PHP 8.3 or later
- Composer
- Node.js and npm
- MySQL

### Installation

```bash
git clone https://github.com/hannanjamaludin/capbay-vroom.git
cd capbay-vroom
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Create a MySQL database, configure its credentials in `.env`, then run:

```bash
php artisan migrate:fresh --seed
npm run build
composer run dev
```

## Testing

```bash
php artisan test
```
