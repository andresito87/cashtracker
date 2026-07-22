<p style="text-align: center;">
  <img src="public/logo.png" width="1027" alt="CashTracker Logo">
</p>

# CashTracker

CashTracker is a modern, premium financial management application built on top of Laravel. It allows users to track
incomes, expenses, and view real-time balance calculations under a secure, responsive, and internationalized layout.

---

## Technical Stack

* **Backend Framework:** Laravel 13 (PHP 8.5+)
* **Database Engine:** SQLite (Local Development), PostgreSQL (Production Supported), SQLite In-Memory (Testing)
* **Testing Framework:** Pest PHP 4
* **Styling Framework:** TailwindCSS 4
* **Asset Bundler:** Vite

---

## Core Features & Optimizations

### 1. High-Performance Internationalization (i18n)

* **Single Round trip Switcher:** Changing languages uses a query parameter optimization (`?lang=`). The system detects
  the language, updates the session, and renders the translated page in a single HTTP request-response cycle (avoiding
  standard double redirection latency).
* **Header Capsule Switcher:** A right-aligned, sleek capsule switcher (`ES | EN`) with active state highlight pills and
  defined border boundaries. It acts as a single toggle link pointing to the alternative language to prevent redundant
  page reloads on the active state.
* **Session Locale Persistence on Logout:** The system backs up the user's selected locale key before invalidating the
  session during logout, rewriting it into the newly regenerated session so the login page maintains their preferred
  language.
* **Semantic Translation Keys:** Notifications and UI copy use clear, domain-specific translation keys (e.g.
  `email_verify_intro`, `email_verify_disclaimer`) across both `en` and `es` dictionaries instead of generic positional
  names.

### 2. Form Submission & Navigation Protection

* **Global Double-Submit Guard:** A DOM-level listener catches all form submissions, immediately disabling submit
  buttons (`button.disabled = true; pointer-events: none`) to prevent rapid double/triple clicks from queuing multiple
  database modifications or parallel login requests.
* **Loading Spinners:** Captures `data-loading-text` parameters on buttons (like Sign In, Register, and Logout) to
  dynamically replace their content with a rotating SVG spinner and a translated loading message during processing.
* **Global Header Navigation Guard:** Clicks on header links (`a` tags) flag the page as navigating
  (`data-navigating="true"`). Any concurrent navigation clicks are automatically blocked using `e.preventDefault()`,
  preventing multiple duplicate HTTP GET requests to the server (e.g. rapid clicking on the "Log In" or "Register"
  header links).

### 3. Professional UX & Styling

* **Text Selection Prevention (`select-none`):** Applied to the entire navigation `<header>` and auth `<main>` card
  wrapper. This prevents the browser from highlighting/selecting adjacent text nodes and links (turning them
  blue/underlined) when users click rapidly on buttons.
* **Field-Decoupled Authentication Errors:** Incorrect credentials errors are separated from field-specific formats
  (like invalid email patterns) and mapped to a general `'login'` key. They render centered directly above the submit
  button using a warning alert box.
* **Database Unique Constraint Safeguard:** A `'unique:users'` email validator was added to `SignUpRequest` with
  translation keys in both languages, preventing database `UniqueConstraintViolationException` crashes and returning a
  clean, localized form validation error if a duplicate email is entered.

### 4. Componentized Architecture

We abstracted our message displays into reusable Blade components:

* **`<x-input-error>` (`resources/views/components/input-error.blade.php`)**: An anonymous component that dynamically
  loops over multiple rules validation errors (`$errors->get('field')`) for an input, displaying them with uniform
  styling.
* **`<x-alert>` (`resources/views/components/alert.blade.php`)**: A class-backed component that dynamically resolves
  styles (`success` in green, `error` in red, `info` in blue) and vector SVG icons based on session values passed from
  controllers.

---

## Getting Started

### Prerequisites

* PHP 8.5+
* Composer
* Node.js & NPM

### Setup & Local Development

**Option A: Quick Automated Setup (Recommended)**

Run the single-command setup script which installs dependencies, creates `.env`, generates app keys, and runs database
migrations:

```bash
composer run setup
composer run dev
```

**Option B: Manual Setup**

1. Install dependencies & configure environment:

   ```bash
   composer install
   npm install
   cp .env.example .env
   php artisan key:generate
   ```

2. Run database migrations:

   ```bash
   touch database/database.sqlite
   php artisan migrate
   ```

3. Build frontend assets and start local dev server:

   ```bash
   npm run build
   composer run dev
   ```

---

## Artisan CLI Reference & Code Generation

CashTracker follows standard Laravel 13 code generation conventions. Below is a reference of essential `php artisan`
commands and flags for rapid development:

### 1. Models & Combined Scaffolding

| Command                                 | Shortcuts / Flags                                                  | Description                                                                                  |
|:----------------------------------------|:-------------------------------------------------------------------|:---------------------------------------------------------------------------------------------|
| `php artisan make:model <Name> -mcfs`   | `-m` (migration), `-c` (controller), `-f` (factory), `-s` (seeder) | Creates Model with its migration, controller, factory, and seeder.                           |
| `php artisan make:model <Name> -mcr`    | `-m` (migration), `-c` (controller), `-r` (resource)               | Creates Model with migration and resource controller (`index`, `show`, `create`, etc.).      |
| `php artisan make:model <Name> -a`      | `-a` / `--all`                                                     | Generates Model, Migration, Factory, Seeder, Policy, Resource Controller, and Form Requests. |
| `php artisan make:model <Name> --pivot` | `--pivot`                                                          | Creates a custom pivot model extending `Illuminate\Database\Eloquent\Relations\Pivot`.       |

### 2. Controllers

| Command                                                    | Flags                | Description                                                              |
|:-----------------------------------------------------------|:---------------------|:-------------------------------------------------------------------------|
| `php artisan make:controller <Name>Controller`             | *(none)*             | Generates a standard empty controller class.                             |
| `php artisan make:controller <Name>Controller --resource`  | `--resource` / `-r`  | Generates a controller with full CRUD resource methods.                  |
| `php artisan make:controller <Name>Controller --api`       | `--api`              | Generates an API resource controller (excludes `create` & `edit` views). |
| `php artisan make:controller <Name>Controller --invokable` | `--invokable` / `-i` | Generates a single-action controller with an `__invoke()` method.        |

### 3. Migrations, Factories & Seeders

| Command                                                                 | Options / Flags   | Description                                                 |
|:------------------------------------------------------------------------|:------------------|:------------------------------------------------------------|
| `php artisan make:migration create_<names>_table`                       | *(default)*       | Generates a new migration for table creation.               |
| `php artisan make:migration add_<col>_to_<table>_table --table=<table>` | `--table=<table>` | Generates a migration to alter an existing table structure. |
| `php artisan make:factory <Name>Factory --model=<Name>`                 | `--model=<Model>` | Creates a model factory bound to a specific model.          |
| `php artisan make:seeder <Name>Seeder`                                  | *(none)*          | Creates a new database seeder class.                        |

### 4. Testing (Pest 4)

| Command                                          | Flags           | Description                                            |
|:-------------------------------------------------|:----------------|:-------------------------------------------------------|
| `php artisan make:test <Name>Test --pest`        | `--pest`        | Generates a Pest feature test inside `tests/Feature/`. |
| `php artisan make:test <Name>Test --pest --unit` | `--pest --unit` | Generates a Pest unit test inside `tests/Unit/`.       |

### 5. Requests, Policies & Architecture Components

| Command                                               | Options           | Description                                                                              |
|:------------------------------------------------------|:------------------|:-----------------------------------------------------------------------------------------|
| `php artisan make:request <Name>Request`              | *(none)*          | Generates a Form Request class for input validation and authorization.                   |
| `php artisan make:policy <Name>Policy --model=<Name>` | `--model=<Model>` | Generates a Policy class mapped to an Eloquent model.                                    |
| `php artisan make:class <Name>`                       | *(none)*          | Generates a generic PHP class file in `app/`.                                            |
| `php artisan make:resource <Name>Resource`            | *(none)*          | Generates an Eloquent API Resource class for single JSON model transformation.           |
| `php artisan make:resource <Name>Collection`          | `--collection`    | Generates an Eloquent API Resource Collection class for transforming arrays/collections. |

### 6. Database Migrations & Management

| Command                            | Options / Flags   | Description                                                               |
|:-----------------------------------|:------------------|:--------------------------------------------------------------------------|
| `php artisan migrate`              | *(none)*          | Executes all pending database migrations.                                 |
| `php artisan migrate:fresh --seed` | `--seed`          | Drops all database tables and re-runs all migrations followed by seeders. |
| `php artisan migrate:rollback`     | `--step=N`        | Rolls back the last migration batch (or `N` steps back with `--step`).    |
| `php artisan migrate:reset`        | *(none)*          | Rolls back all application migrations.                                    |
| `php artisan migrate:status`       | *(none)*          | Displays the execution status (Ran / Pending) of each migration file.     |
| `php artisan db:seed`              | `--class=<Class>` | Runs database seeders (`DatabaseSeeder` by default or a specific class).  |

### 7. Cache, Configuration & Maintenance

| Command                      | Purpose              | Description                                                               |
|:-----------------------------|:---------------------|:--------------------------------------------------------------------------|
| `php artisan optimize:clear` | Flush All Caches     | Clears config, route, view, event, and application caches simultaneously. |
| `php artisan config:clear`   | Clear Config Cache   | Flushes the cached configuration file (`bootstrap/cache/config.php`).     |
| `php artisan route:clear`    | Clear Route Cache    | Flushes the cached routes file.                                           |
| `php artisan view:clear`     | Clear Compiled Views | Flushes compiled Blade template views.                                    |
| `php artisan cache:clear`    | Clear App Cache      | Flushes the application data cache store.                                 |
| `php artisan optimize`       | Production Cache     | Caches config, routes, and events for production speed.                   |
| `php artisan storage:link`   | Symlink Storage      | Creates the symbolic link from `public/storage` to `storage/app/public`.  |

### 8. Production Deployment & Server Maintenance

| Command                           | Purpose                 | Description                                                                                                                    |
|:----------------------------------|:------------------------|:-------------------------------------------------------------------------------------------------------------------------------|
| `composer dump-autoload -o`       | Optimize Autoloader     | Rebuilds Composer classmaps into a static optimized array (`-o` / `--optimize`) for maximum class lookup speed in production.  |
| `sudo service php8.5-fpm restart` | Reset PHP-FPM / OPcache | Restarts the PHP-FPM process pool to flush in-memory OPcache bytecode (replace `php8.5-fpm` with your server's exact version). |
| `sudo supervisorctl restart all`  | Restart Queue Workers   | Restarts all background workers (Queue / Horizon / Scheduler) managed by Supervisor to load modified code.                     |

```bash
# Complete production deployment & cache reset sequence (useful after code updates or FTP sync):
php artisan optimize:clear && php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear && composer dump-autoload -o && sudo service php8.5-fpm restart
sudo supervisorctl restart all
```

### 9. Helpful Inspection Commands

```bash
# List all registered routes (filter by path or method)
php artisan route:list --path=budgets --method=GET

# Show application configuration key
php artisan config:show app.name

# Execute interactive PHP shell in application context
php artisan tinker
```

---

## Testing & Quality Assurance

### Test Suite Architecture (Pest 4)

CashTracker features a robust, professional test suite powered by **Pest PHP 4**.

* **Database Isolation:** All tests run in an isolated in-memory SQLite environment (`DB_CONNECTION=sqlite`,
  `DB_DATABASE=:memory:` configured in `phpunit.xml`), ensuring zero side effects, ultra-fast RAM execution, and 100%
  safety for the local/production database.
* **Domain Test Helpers (`tests/Pest.php`):** Centralized helpers simplify test setup and keep code DRY:
	* `createVerifiedUser()`, `createUnverifiedUser()`, `createAdminUser()`
	* `actingAsVerifiedUser()`, `actingAsUnverifiedUser()`
	* `validRegistrationPayload()`, `validBudgetPayload()`
* **Comprehensive Coverage:**
	* **Authentication & Guest Access:** Login, logout, session locale persistence, invalid credentials handling, guest
	  redirects.
	* **User Registration:** Signup flow, password uncompromised leak validation (`Password::uncompromised()`),
	  `Registered` event dispatching, and email verification notice redirects.
	* **Email Verification:** Unverified user middleware (`verified`), signed verification URLs (`verification.verify`),
	  notification resending, and localized email message rendering (`VerifyEmail`).
	* **Budget Authorization & Policies:** Role-scoped index queries (Admins retrieve all budgets; regular users
	  retrieve only owned budgets), Policy authorization guards (`BudgetPolicy`), Admin ownership bypass, and
	  guest/unverified route restrictions.
	* **Internationalization (i18n):** Multi-language assertions (`en` and `es`) across forms, views, notifications, and
	  validation error messages.

### Running Tests

You can run tests using Composer scripts or direct CLI commands:

* **Full Test Suite:**
  ```bash
  composer run test
  ```
  *or using Artisan directly:*
  ```bash
  php artisan test
  ```

* **Compact Output Mode:**
  ```bash
  composer run test:compact
  ```
  *or:*
  ```bash
  php artisan test --compact
  ```

* **Filter Specific Tests:**
  ```bash
  composer run test:filter -- RegisterTest
  ```
  *or:*
  ```bash
  php artisan test --compact --filter=RegisterTest
  ```

* **Code Coverage:**
  ```bash
  composer run test:coverage
  ```

* **Code Style & Formatting (Pint):**
  ```bash
  composer run format
  ```
