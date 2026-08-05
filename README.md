<p style="text-align: center;">
  <img src="public/logo.png" width="1027" alt="CashTracker logo">
</p>

# CashTracker

CashTracker is a personal finance web application for creating budgets, tracking categorized expenses, and understanding
spending through an AI-assisted workflow. It combines a Laravel backend with a hybrid Blade and Inertia React frontend,
PostgreSQL-backed financial operations, and Stripe subscriptions for premium features.

## Highlights

- Create general or goal-based budgets in EUR or USD.
- Record, edit, categorize, and soft-delete expenses.
- Prevent overspending with transactional writes and pessimistic locking.
- Explore expenses and create new entries through a streaming AI assistant.
- Scan receipt images and convert their line items into expenses.
- Manage monthly or yearly subscriptions through Stripe Checkout and the Billing Portal.
- Use localized English and Spanish interfaces.
- Secure accounts with email verification, password recovery, authorization policies, and rate limiting.

## Screenshots

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; align-items: start;">
  <div>
    <strong>Landing Page</strong><br>
    <img src="docs/screenshots/landing.png" alt="CashTracker landing page" width="1254">
  </div>
  <div>
    <strong>Dashboard</strong><br>
    <img src="docs/screenshots/dashboard.png" alt="CashTracker dashboard" width="1254">
  </div>
  <div>
    <strong>Add Expense Modal</strong><br>
    <img src="docs/screenshots/add-modal.png" alt="Add expense modal" width="1254">
  </div>
  <div>
    <strong>AI Assistant</strong><br>
    <img src="docs/screenshots/ai-assistant.png" alt="CashTracker AI assistant" width="1254">
  </div>
  <div>
    <strong>Subscription Management</strong><br>
    <img src="docs/screenshots/subscription.png" alt="Subscription management" width="1254">
  </div>
  <div>
    <strong>Stripe Subscription Invoice</strong><br>
    <img src="docs/screenshots/invoice-stripe-subscription.png" alt="Stripe subscription invoice" width="1254">
  </div>
  <div>
    <strong>Settings</strong><br>
    <img src="docs/screenshots/settings.png" alt="Account settings" width="1254">
  </div>
  <div>
    <strong>Login</strong><br>
    <img src="docs/screenshots/login.png" alt="Login page" width="1254">
  </div>
  <div>
    <strong>Registration</strong><br>
    <img src="docs/screenshots/register.png" alt="Registration page" width="1254">
  </div>
</div>

## Main Features

### Budgets and expenses

- Budget and expense CRUD with per-user authorization.
- General and savings-goal budget types.
- Categorized expenses, balance calculations, and visual spending progress.
- Soft deletion for financial records.
- Transactional expense writes that protect against concurrent overspending.
- Dashboard and budget-detail views with responsive interactions and toast feedback.

### AI financial tools

Premium users can access:

- A streaming assistant scoped to an individual budget.
- Natural-language expense search, filtering, and ordering.
- Conversational expense creation with ownership and balance validation.
- Receipt image scanning that extracts a merchant, category, and line items before creating expenses atomically.

AI features require a configured provider API key. The default chat configuration uses OpenRouter and `openrouter/free`.

### Stripe subscriptions

- Monthly and yearly plans with EUR and USD price configuration.
- Stripe Checkout with promotion-code support.
- Plan changes, cancellation, resumption, and grace-period handling.
- Stripe Customer Billing Portal integration.
- Signed webhook verification that fails closed when no webhook secret is configured.

### Account and platform features

- Registration, login, logout, password reset, and email verification.
- Profile, password, currency, and language preferences.
- English and Spanish localization through the `?lang=` query parameter.
- Custom authorization, throttling, and error pages.
- Laravel health endpoint at `/up`.

## Technology Stack

| Area             | Technology                                   |
|------------------|----------------------------------------------|
| Backend          | PHP 8.5, Laravel 13                          |
| Frontend         | Inertia.js 3, React 19, TypeScript, Blade    |
| Styling          | Tailwind CSS 4                               |
| Database         | PostgreSQL                                   |
| AI               | Laravel AI SDK, OpenRouter, AI SDK for React |
| Billing          | Laravel Cashier 16, Stripe                   |
| State management | Zustand                                      |
| Testing          | Pest 4, PHPUnit 12                           |
| Build tooling    | Vite 8, npm                                  |

## Requirements

- PHP 8.5 with PDO and the PostgreSQL PDO driver
- Composer
- PostgreSQL
- Node.js 20.19+ or 22.12+
- npm
- An OpenRouter API key for AI features
- A Stripe account and Stripe CLI for local billing development

## Local Setup

1. Install backend and frontend dependencies:

   ```bash
   composer install
   npm install
   ```

2. Create the local environment file and application key:

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. Create a PostgreSQL database and configure `.env` before running migrations:

   ```env
   APP_URL=http://localhost:8000

   DB_CONNECTION=pgsql
   DB_URL=postgresql://USER:PASSWORD@127.0.0.1:5432/DATABASE_NAME
   ```

4. Configure the integrations you intend to use. Keep all real credentials in `.env`; never commit them.

5. Prepare the database:

   ```bash
   php artisan migrate
   ```

6. Start the application, queue listener, logs, and Vite development server:

   ```bash
   composer run dev
   ```

The application is available at `http://localhost:8000` by default.

> `composer run setup` automates dependency installation, key generation, migrations, and the frontend build. Configure
> `DB_URL` first because the value shipped in `.env.example` is only a placeholder.

## Environment Configuration

### AI

```env
OPENROUTER_API_KEY=
AI_CHAT_PROVIDER=openrouter
AI_CHAT_MODEL=openrouter/free
```

The OpenRouter key is required for the chat assistant and receipt scanning. Provider and model values shown above are
the chat defaults.

### Stripe

```env
STRIPE_KEY=
STRIPE_SECRET=
STRIPE_WEBHOOK_SECRET=

STRIPE_PRICE_EUR_MONTHLY=
STRIPE_PRICE_EUR_YEARLY=
STRIPE_PRICE_USD_MONTHLY=
STRIPE_PRICE_USD_YEARLY=

CASHIER_CURRENCY=EUR
CASHIER_CURRENCY_LOCALE=es_ES
```

Create the corresponding products and recurring prices in Stripe, then copy each Price ID into the matching variable.
`STRIPE_KEY`, `STRIPE_SECRET`, and `STRIPE_WEBHOOK_SECRET` are different credentials and are not interchangeable.

### Mail, queues, sessions, and cache

The default local environment stores queues, sessions, and cache data in PostgreSQL. `MAIL_MAILER=log` is sufficient for
development; configure a real mail transport when testing delivery outside the application log.

## Stripe Webhooks in Local Development

CashTracker receives Stripe events at `POST /stripe/webhook`. The endpoint requires a valid Stripe signature and returns
`403` when `STRIPE_WEBHOOK_SECRET` is missing or invalid.

1. Authenticate the Stripe CLI:

   ```bash
   stripe login
   ```

2. With CashTracker running on port 8000, forward Stripe events to the local webhook:

   ```bash
   stripe listen --forward-to http://localhost:8000/stripe/webhook
   ```

3. Stripe CLI will print a signing secret similar to:

   ```text
   Ready! Your webhook signing secret is whsec_...
   ```

4. Copy the complete `whsec_...` value into `.env` and clear cached configuration:

   ```env
   STRIPE_WEBHOOK_SECRET=whsec_...
   ```

   ```bash
   php artisan config:clear
   ```

5. Keep `stripe listen` running while testing Checkout or subscription changes. You can send a test event from another
   terminal:

   ```bash
   stripe trigger customer.subscription.created
   ```

The local CLI signing secret is separate from the webhook signing secret configured for a production endpoint in the
Stripe Dashboard.

## Key Routes

The table below highlights the application's main entry points. Most authenticated routes also require a verified email;
AI routes additionally require an active subscription.

| Area           | Method                       | Path                            | Purpose                                   |
|----------------|------------------------------|---------------------------------|-------------------------------------------|
| Public         | `GET`                        | `/`                             | Landing page                              |
| Authentication | `GET`, `POST`                | `/auth/login`                   | Sign in                                   |
| Authentication | `GET`, `POST`                | `/auth/register`                | Create an account                         |
| Authentication | `GET`, `POST`                | `/auth/forgot-password`         | Request a password reset                  |
| Dashboard      | `GET`                        | `/dashboard`                    | List the user's budgets                   |
| Budgets        | `GET`, `POST`                | `/budgets`                      | List and create budgets                   |
| Budgets        | `GET`, `PUT/PATCH`, `DELETE` | `/budgets/{budget}`             | View, update, or delete a budget          |
| Expenses       | `POST`                       | `/budgets/{budget}/expenses`    | Add an expense to a budget                |
| Expenses       | `PUT`, `DELETE`              | `/expenses/{expense}`           | Update or delete an expense               |
| AI             | `POST`                       | `/budgets/{budget}/chat`        | Stream a budget-scoped assistant response |
| AI             | `POST`                       | `/budgets/{budget}/scan-ticket` | Scan a receipt image and create expenses  |
| Subscriptions  | `GET`                        | `/plans`                        | View available plans                      |
| Subscriptions  | `GET`                        | `/subscription`                 | Manage the current subscription           |
| Subscriptions  | `POST`                       | `/subscription-checkout/{plan}` | Start Stripe Checkout                     |
| Billing        | `GET`                        | `/billing`                      | Open the Stripe Billing Portal            |
| Stripe         | `POST`                       | `/stripe/webhook`               | Receive signed Stripe webhook events      |
| Settings       | `GET`, `PUT`                 | `/settings/profile`             | View or update the user profile           |
| Settings       | `GET`, `PUT`                 | `/settings/password`            | View or update the password               |
| Health         | `GET`                        | `/up`                           | Laravel application health check          |

For the complete, authoritative route list, run:

```bash
php artisan route:list
```

## Development Commands

| Command                                        | Purpose                                                  |
|------------------------------------------------|----------------------------------------------------------|
| `composer run dev`                             | Run Laravel, the queue listener, Pail, and Vite together |
| `npm run dev`                                  | Run only the Vite development server                     |
| `npm run build`                                | Build production frontend assets                         |
| `php artisan migrate`                          | Run pending database migrations                          |
| `php artisan db:seed`                          | Load the available seed data                             |
| `composer run test:compact`                    | Run the default test suite with compact output           |
| `php artisan test --compact --filter=TestName` | Run a focused test                                       |
| `composer run test:coverage`                   | Generate coverage when Xdebug or PCOV is available       |
| `composer run format`                          | Format PHP code with Laravel Pint                        |

## Testing

Run the default Pest suite with:

```bash
composer run test:compact
```

The default suite uses SQLite in memory for fast isolation. PostgreSQL-specific integration tests live under
`tests/Feature/Postgres` and are excluded from the default `phpunit.xml` suite; run them against an isolated PostgreSQL
test database when validating database constraints, locking, or concurrency behavior.

Never point tests at a development or production database.

## Architecture Notes

- The frontend is intentionally hybrid: Blade handles several server-rendered flows while Inertia React powers
  dashboards, budget details, subscription management, and settings.
- AI and receipt-scanning endpoints are restricted to subscribed users and rate-limited.
- Expense creation uses database transactions and row locking to keep budget totals consistent.
- Stripe webhook requests are excluded from CSRF protection but always require a configured signing secret and valid
  signature.
- Database-backed queues should be running when testing asynchronous behavior; `composer run dev` starts the listener
  automatically.
