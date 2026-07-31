# CapBay Vroom

CapBay Vroom is a Laravel and Livewire vehicle-sales workflow application. Customers can register interest in a vehicle, while authenticated sales agents can search registrations, update financial details, and move customers through test-drive, purchase, or cancellation milestones.

## Tech stack

The current lock files and local application runtime use:

- PHP 8.5 (the Composer constraint supports PHP 8.3 and later)
- Laravel 13.23.0
- Livewire 4.3.3 with Blaze 1.x
- MySQL for the configured development database
- Bootstrap 5.3.8, loaded from jsDelivr, with project styles in `public/css/vroom.css`
- Pest 5.0.2

## Installation

Prerequisites: PHP 8.3 or later with the required Laravel extensions, Composer, and MySQL.

```bash
git clone https://github.com/hannanjamaludin/capbay-vroom.git
cd capbay-vroom
composer install
cp .env.example .env
php artisan key:generate
```

On Windows PowerShell, use `Copy-Item .env.example .env` instead of `cp` if needed.

## Database configuration

Create an empty database, then update `.env` with your local credentials:

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=capbay_vroom
DB_USERNAME=root
DB_PASSWORD=
```

## Migrations and seed data

Build the schema and load the standard demo data:

```bash
php artisan migrate:fresh --seed
```

For an existing database where data must be retained, use:

```bash
php artisan migrate
php artisan db:seed
```

The standard seeder creates the demo users, vehicle, promotion, and 11 ordered registrations: Customer A first, Customer B second, eight representative customers in positions 3–10, and Customer C in position 11. All are seeded with confirmed down payments except Customers B and C. The exact `down_payment_sen` fixtures are retained as defined in `RegistrationSeeder`. To add or refresh the optional 50,000-registration performance dataset, run:

```bash
php artisan db:seed --class=RegistrationScaleSeeder
```

The scale seeder is repeatable: it upserts rows by their unique email instead of creating duplicates.

## Run the application

Start the local Laravel development server:

```bash
php artisan serve
```

Customer registration is available at `/register`; the sales-agent sign-in is at `/agent/login`.

## Demo sales-agent credentials

Either seeded sales-agent account can access the agent area:

| Name | Email | Password |
| --- | --- | --- |
| Olivia Rodrigo | `olivia@mail.com` | `12345678` |
| Albert Einstein | `einstein@mail.com` | `12345678` |

## Testing

For the compact Laravel/Pest test output only:

```bash
php artisan test --compact
```

## Business rules and assumptions

- The seeded vehicle price is RM200,000.00.
- The seeded `Vroom First 10` promotion gives a 15% discount to the first 10 customers whose down payments are confirmed and are at least 10% of the vehicle price during the active promotion period.
- Promotion eligibility requires an active vehicle and promotion, a matching vehicle, registration within the promotion dates, a confirmed minimum 10% down payment, an unused customer email for that promotion, and remaining promotion capacity.
- Registering alone does not reserve a promotion place. An unpaid registration is recorded at full price and does not consume promotion capacity.
- A sales agent confirms the payment while updating the financial details. Confirmation is a one-time operation: afterward, the down-payment input and update action are disabled to prevent historical prices from changing unexpectedly.
- Once a paid customer receives the promotion, that place remains consumed even if the registration is later cancelled. Cancellation never reprices or creates a promotion place for another customer.
- Email and normalized phone number are unique per registration.
- A registration may progress from registered to test-drive scheduled/completed and purchased, or be cancelled according to the allowed status transitions.
- Vehicle prices and applied financial values are snapshotted on each registration so later catalogue or promotion changes do not rewrite historical figures.
- Loan amount is `max(0, final price - down payment)`; overpayment therefore produces a zero loan rather than a negative value.

### Integer-sen money representation

All monetary values are stored as unsigned integers in sen, where RM1.00 equals 100 sen. For example, RM200,000.00 is stored as `20_000_000`. This avoids binary floating-point rounding errors in financial calculations. User input is converted to sen before persistence and values are divided by 100 only for display. Promotion rates use basis points: 10,000 basis points equals 100%, so the seeded 15% discount is stored as `1500`.

### Customer B and Customer C decision

Customer B is the second registration and has a recorded RM20,000.00 down-payment amount, but the payment is not confirmed. B therefore receives no promotion and consumes no promotion place. “Decided not to buy this car” means B registered without paying and later cancelled; the cancellation itself does not affect promotion capacity.

Customer C is the 11th registration and also starts unpaid, so C initially has no promotion and remains at the RM200,000.00 full price. When a sales agent confirms C's seeded RM20,000.00 down payment through the financial-details UI, C becomes the tenth customer with a confirmed qualifying payment because Customer B never paid. The confirmation applies RM30,000.00 off, producing a final price of RM170,000.00 and a RM150,000.00 loan, then locks the financial field.

### 50,000-row performance approach

- `RegistrationScaleSeeder` generates rows in batches of 1,000 and uses a bulk upsert, avoiding one insert query per row and keeping memory bounded.
- The agent list selects only the columns it renders and eager-loads only each vehicle's `id` and `name`, preventing N+1 queries and oversized result payloads.
- Cursor pagination returns 12 rows at a time and orders by the indexed primary key, avoiding increasingly expensive offset scans on later pages.
- Indexes support status, registration date, vehicle/status, promotion/status, and exact unique email/phone lookups.
- Exact email, phone, and numeric-ID searches use index-friendly equality checks. Partial name/contact searches intentionally use `LIKE`.

## AI usage disclosure

OpenAI Codex was used as a development assistant for code exploration, implementation support, refactoring, tests, and documentation. One AI suggestion I rejected was to prioritize the first 10 registrations unconditionally, which would exclude everyone after them even when an earlier customer had never paid or was otherwise ineligible. I corrected that interpretation so the limit applies to the first 10 customers with confirmed qualifying payments: unpaid Customer B does not consume a place, and Customer C becomes the tenth paid eligible customer only when an agent confirms C's payment. A paid customer's later cancellation does not release a place or trigger another customer's price to change. AI-generated suggestions were reviewed against the requirements and application code, and the resulting behavior is covered by the project's Pest tests. No external AI service is called by the running application and no customer data is sent to an AI provider by this codebase.
