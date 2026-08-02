<p style="text-align: center;">
  <img src="public/logo.png" width="1027" alt="CashTracker Logo">
</p>

# CashTracker

CashTracker is a modern, premium financial management application built on top of Laravel 13 and Inertia.js with
ReactJS. It enables users to create and manage budgets while tracking their associated categorized expenses, monitoring
real-time balance calculations and spending progress limits under a secure, responsive layout. It features an integrated
**AI Financial Assistant** powered by Laravel AI SDK and OpenRouter for natural language expense querying and
conversational expense creation with automated UI state synchronization.

---

## Technical Stack

* **Backend Framework:** Laravel 13 (PHP 8.5+)
* **Subscription & Payments Engine:** Laravel Cashier (`laravel/cashier` v16) with Stripe Checkout Integration
* **AI & Agent Engine:** Laravel AI SDK (`laravel/ai`) with OpenRouter Gateway
* **Frontend Architecture:** Inertia.js v3 with React 19, TypeScript & `@ai-sdk/react`
* **Database Engine:** SQLite (Local Development), PostgreSQL (Production Supported), SQLite In-Memory (Testing)
* **Testing Framework:** Pest PHP 4
* **Styling Framework:** TailwindCSS 4
* **Asset Bundler:** Vite 8

---

## Core Features & Optimizations

### 1. Budget & Categorized Expense Management Engine

* **Relational Budget-Expense Architecture:** Each budget contains multiple categorized expenses (`food`,
  `transportation`,
  `health`, `entertainment`, `subscriptions`, `beauty`, `clothing`, `home`, `education`, `pets`, `other`) with localized
  category labels, colors, and visual badges.
* **Real-Time Balance & Limit Guards:** Automatically computes total spent, remaining balance, and consumption
  percentage against budget limits. Prevents expense creation or updates from exceeding the available budget balance.
* **Inertia.js & React Modal System:** Expense creation and editing are handled via a client-side React modal
  ([ExpenseModal.tsx](resources/js/Components/ExpenseModal.tsx))
  with Zustand state management ([expense-modal-store.ts](resources/js/store/expense-modal-store.ts)). Budget editing
  also uses a React modal ([BudgetModal.tsx](resources/js/Components/BudgetModal.tsx)) with its own Zustand store
  ([budget-modal-store.ts](resources/js/store/budget-modal-store.ts)), providing a consistent UX across both entities.
* **Typed React Validation Error Component:** Field validation errors in React forms utilize a reusable
  `<InputError message={...} />` component ([InputError.tsx](resources/js/Components/InputError.tsx)), cleanly decoupled
  from server-side Blade components.
* **Reactive Session Flash Messages:** Automatic propagation of Laravel session status notifications (`status` and
  `status_type`) through Inertia shared props to render feedback banners on the UI after creating, updating, or deleting
  expenses.
* **Shallow Nesting Routing Architecture:** Adheres to Laravel REST best practices using Shallow Routing
  (`POST /budgets/{budget}/expenses` for creation within budget context, and shallow `PUT /expenses/{expense}` and
  `DELETE /expenses/{expense}` for member updates and deletions), avoiding redundant deep URL nesting.
* **Soft Deletes & Policy Guards:** Built-in soft deletion for expenses (`Expense`) and policy authorization guards
  (`ExpensePolicy`, `BudgetPolicy`) ensuring users can only access and modify their own budgets and expenses.

---

### 2. PRO Subscriptions & Multi-Currency Billing Engine (Laravel Cashier + Stripe)

* **Multi-Currency Dynamic Pricing (EUR / USD):** Dynamically selects the appropriate Stripe Price ID based on the
  authenticated user's selected currency (`$user->currency`), defaulting to EUR (39€/mo, 299€/yr) or USD ($39/mo, $
  299/yr) with localized price formatting across all views.
* **Laravel Cashier Integration:** Built using Laravel Cashier v16 to manage Stripe Checkout sessions, active
  subscriptions (`default`), grace period tracking (`onGracePeriod()`), plan swapping (`swap()`), cancellation
  (`cancel()`), and reactivation (`resume()`).
* **Interactive Pricing & Subscription Dashboard:** A modern React page component
  ([Manage.tsx](resources/js/Pages/Subscriptions/Manage.tsx) & [PricingTable.tsx](resources/js/Components/PricingTable.tsx))
  featuring real-time loading feedback, active plan indicators, grace period banners, and instant plan swapping.
* **Localized Payment Success & Cancellation Flow:** Custom, branded Blade views (`billing.success` and
  `billing.cancel`) with 100% i18n key translation support in both Spanish and English.

---

### Application Routes Reference

Below is the routing architecture for budgets, expenses, ticket scanning, and PRO subscriptions:

| HTTP Method | URI Path                           | Route Name                 | Controller & Action                     | Description                                         |
|:------------|:-----------------------------------|:---------------------------|:----------------------------------------|:----------------------------------------------------|
| `GET`       | `/`                                | `welcome`                  | `Closure`                               | Welcome / landing page                              |
| `GET`       | `/dashboard`                       | `dashboard`                | `BudgetController@index`                | User dashboard listing budgets                      |
| `GET`       | `/budgets`                         | `budgets.index`            | `BudgetController@index`                | List user budgets                                   |
| `POST`      | `/budgets`                         | `budgets.store`            | `BudgetController@store`                | Create a new budget                                 |
| `GET`       | `/budgets/create`                  | `budgets.create`           | `BudgetController@create`               | Show create budget form                             |
| `GET`       | `/budgets/{budget}`                | `budgets.show`             | `BudgetController@show`                 | View budget details & expense list                  |
| `PUT`       | `/budgets/{budget}`                | `budgets.update`           | `BudgetController@update`               | Update budget details                               |
| `DELETE`    | `/budgets/{budget}`                | `budgets.destroy`          | `BudgetController@destroy`              | Soft delete budget                                  |
| `GET`       | `/budgets/{budget}/edit`           | `budgets.edit`             | `BudgetController@edit`                 | Show edit budget form                               |
| `POST`      | `/budgets/{budget}/chat`           | `budgets.chat`             | `BudgetChatController@store`            | Stream AI agent chat for budget (`throttle:20,1`)   |
| `POST`      | `/budgets/{budget}/scan-ticket`    | `budgets.scan-ticket`      | `TicketScanController@store`            | OCR Ticket Scanner & Auto-Expense (`throttle:10,1`) |
| `POST`      | `/budgets/{budget}/expenses`       | `budgets.expenses.store`   | `ExpenseController@store`               | Create expense under budget                         |
| `PUT`       | `/expenses/{expense}`              | `expenses.update`          | `ExpenseController@update`              | Update expense (Shallow route)                      |
| `DELETE`    | `/expenses/{expense}`              | `expenses.destroy`         | `ExpenseController@destroy`             | Soft delete expense (Shallow route)                 |
| `GET`       | `/plans`                           | `plans`                    | `SubscriptionController@manage`         | PRO plans & subscription management page            |
| `POST`      | `/subscription-checkout/{plan}`    | `subscription.checkout`    | `SubscriptionController@checkout`       | Create Stripe checkout session for plan             |
| `GET`       | `/subscription`                    | `subscription.manage`      | `SubscriptionController@manage`         | User subscription management route                  |
| `POST`      | `/subscription/swap/{plan}`        | `subscription.swap`        | `SubscriptionController@swap`           | Swap active subscription plan (monthly / yearly)    |
| `POST`      | `/subscription/cancel`             | `subscription.cancel`      | `SubscriptionController@cancel`         | Cancel active subscription                          |
| `POST`      | `/subscription/resume`             | `subscription.resume`      | `SubscriptionController@resume`         | Resume canceled subscription on grace period        |
| `GET`       | `/billing`                         | `billing`                  | `SubscriptionController@billing`        | Stripe billing portal page                          |
| `GET`       | `/billing/success`                 | `billing.success`          | `SubscriptionController@success`        | Stripe checkout success confirmation page           |
| `GET`       | `/billing/cancel`                  | `billing.cancel`           | `SubscriptionController@cancelUrl`      | Stripe checkout cancellation page                   |
| `GET`       | `/auth/login`                      | `login`                    | `LoginController@index`                 | Show login form                                     |
| `POST`      | `/auth/login`                      | `login.store`              | `LoginController@store`                 | Authenticate user                                   |
| `POST`      | `/auth/logout`                     | `logout`                   | `LoginController@destroy`               | Logout user                                         |
| `GET`       | `/auth/register`                   | `register`                 | `RegisterController@index`              | Show registration form                              |
| `POST`      | `/auth/register`                   | `register.store`           | `RegisterController@store`              | Register new user                                   |
| `GET`       | `/email/verify`                    | `verification.notice`      | `Closure`                               | Email verification notice                           |
| `GET`       | `/verify-email/{id}/{hash}`        | `verification.verify`      | `RegisterController@verifyEmail`        | Verify email via signed URL                         |
| `POST`      | `/email/verification-notification` | `verification.send`        | `RegisterController@resendVerification` | Resend verification email                           |
| `GET`       | `/settings/profile`                | `settings.profile`         | `UpdateProfileController@edit`          | User profile settings page (Inertia React)          |
| `PUT`       | `/settings/profile`                | `settings.profile.update`  | `UpdateProfileController@update`        | Update user name and email                          |
| `GET`       | `/settings/password`               | `settings.password`        | `UpdatePasswordController@edit`         | Password change settings page (Inertia React)       |
| `PUT`       | `/settings/password`               | `settings.password.update` | `UpdatePasswordController@update`       | Update user password                                |
| `GET`       | `/admin`                           | `admin.dashboard`          | `Closure`                               | Admin dashboard                                     |

### 3. High-Performance Internationalization (i18n)

* **Single Round trip Switcher:** Changing languages uses a query parameter optimization (`?lang=`). The system detects
  the language, updates the session, and renders the translated page in a single HTTP request-response cycle (avoiding
  standard double redirection latency). There is no dedicated route; the `?lang=` query parameter is accepted by every
  GET request and is honored by the `SetLocale` middleware against the `config('app.available_locales')` whitelist.
* **Header Capsule Switcher:** A right-aligned capsule switcher (`ES | EN`) with active state highlight pills, rendered
  by the shared `resources/views/components/lang-switcher.blade.php` partial. It exposes one anchor per entry in
  `config('app.available_locales')`, so adding a language to the config is all that is required to surface it in every
  layout (`base`, `app`, `inertia`).
* **Session Locale Persistence on Logout:** The system backs up the user's selected locale key before invalidating the
  session during logout, rewriting it into the newly regenerated session so the login page maintains their preferred
  language.
* **Semantic Translation Keys:** Notifications and UI copy use clear, domain-specific translation keys (e.g.
  `email_verify_intro`, `email_verify_disclaimer`) across both `en` and `es` dictionaries instead of generic positional
  names.

### 4. Form Submission & Navigation Protection

* **Global Double-Submit Guard:** A DOM-level listener catches all form submissions, immediately disabling submit
  buttons (`button.disabled = true; pointer-events: none`) to prevent rapid double/triple clicks from queuing multiple
  database modifications or parallel login requests.
* **Loading Spinners:** Captures `data-loading-text` parameters on buttons (like Sign In, Register, and Logout) to
  dynamically replace their content with a rotating SVG spinner and a translated loading message during processing.
* **Global Header Navigation Guard:** Clicks on header links (`a` tags) flag the page as navigating
  (`data-navigating="true"`). Any concurrent navigation clicks are automatically blocked using `e.preventDefault()`,
  preventing multiple duplicate HTTP GET requests to the server (e.g. rapid clicking on the "Log In" or "Register"
  header links).

### 5. Professional UX & Styling

* **Text Selection Prevention (`select-none`):** Applied to the entire navigation `<header>` and auth `<main>` card
  wrapper. This prevents the browser from highlighting/selecting adjacent text nodes and links (turning them
  blue/underlined) when users click rapidly on buttons.
* **Field-Decoupled Authentication Errors:** Incorrect credentials errors are separated from field-specific formats
  (like invalid email patterns) and mapped to a general `'login'` key. They render centered directly above the submit
  button using a warning alert box.
* **Database Unique Constraint Safeguard:** A `'unique:users'` email validator was added to `SignUpRequest` with
  translation keys in both languages, preventing database `UniqueConstraintViolationException` crashes and returning a
  clean, localized form validation error if a duplicate email is entered.

### 6. Componentized Architecture & Hybrid Frontend

We abstract UI components cleanly across both Blade (server-side) and React (client-side):

* **Blade Server-Side Components:**
	* **`<x-input-error>` (`resources/views/components/input-error.blade.php`)**: Anonymous Blade component looping over
	  server-side form validation rules.
	* **`<x-alert>` (`resources/views/components/alert.blade.php`)**: Class-backed Blade component resolving status
	  styles and vector SVG icons.
* **React Client-Side Components (Inertia):**
	* **`<InputError />` (`resources/js/Components/InputError.tsx`)**: Reusable TypeScript React component rendering
	  field validation messages for Inertia form state.
	* **`<ExpenseModal />` & `<ExpenseForm />` (`resources/js/Components/ExpenseModal.tsx`)**: Client-side React modal
	  and form for creating and editing budget expenses with Zustand state synchronization.
	* **`<BudgetModal />` & `<BudgetForm />` (`resources/js/Components/BudgetModal.tsx`)**: Client-side React modal and
	  form for editing budget details (name, amount, type, description) with Zustand state synchronization.
	* **`<ConfirmDeleteModal />` (`resources/js/Components/ConfirmDeleteModal.tsx`)**: Reusable deletion modal for
	  confirming budget or expense removal.
	* **`<ProgressBar />` (`resources/js/Components/ProgressBar.tsx`)**: Visual progress bar displaying budget
	  consumption percentage with label.
	* **`<Toast />` & `<ToastContainer />` (`resources/js/Components/Toast.tsx`,
	  `resources/js/Components/ToastContainer.tsx`)**: Custom toast notification system for user feedback.
	* **`<FlashToastListener />` (`resources/js/Components/FlashToastListener.tsx`)**: Listens for Inertia flash props
	  and triggers toast notifications automatically.
	* **`<SettingsHeader />` (`resources/js/Components/settings/SettingsHeader.tsx`)**: Shared header component for
	  settings pages with tab navigation between profile and password sections.
	* **`<UpdateProfile />` (`resources/js/Pages/Settings/UpdateProfile.tsx`)**: Inertia React page for editing user
	  profile (name and email) with server-side validation and email re-verification on change.
	* **`<UpdatePassword />` (`resources/js/Pages/Settings/UpdatePassword.tsx`)**: Inertia React page for changing user
	  password with current password verification and confirmation matching.

### 7. AI Financial Assistant & Agentic Tools

* **Streaming Agent Architecture (`BudgetAssistant.php`)**: Built on top of Laravel AI SDK (`laravel/ai`) with
  OpenRouter integration (`openrouter/free` router or custom model options like Qwen 2.5 Coder, Ling 3.0 Flash). Streams
  responses via the Vercel AI Protocol (`usingVercelDataProtocol()`).
* **Structured Agent Tools (`Laravel\Ai\Contracts\Tool`)**:
	* **`SearchExpenses` (`app/Ai/Tools/SearchExpenses.php`)**: Allows natural language queries to search, filter by
	  name/category, and sort expenses (`sort_by`: `amount_desc`, `amount_asc`, `latest`, `oldest`) with a 30-item
	  prompt overflow guard and user currency symbol formatting.
	* **`AddExpense` (`app/Ai/Tools/AddExpense.php`)**: Enables conversational expense creation with strict validation
	  (positive amount required, category enum mapping, placeholder name rejection) and available budget balance limit
	  enforcement.
* **Security & Tenancy Safeguards**: Both AI tools validate budget ownership against the authenticated user
  (`Budget::where('user_id', auth()->id())`), preventing unauthorized data access or cross-user budget mutations.
* **Interactive Frontend Chat Component (`CashTrackerAgent.tsx`)**:
	* Real-time streaming UI using `@ai-sdk/react` (`useChat`).
	* Filters out internal Chain-of-Thought (CoT) tokens (`<think>`, `<|end|>`) and hides empty intermediate tool
	  execution bubbles while displaying a clean typing indicator.
	* Trigger-based Toast notifications using `react-hot-toast` + Inertia data reloads
	  (`router.reload({ only: ['budget', 'expenses'] })`) when an expense is successfully registered via
	  `[EXPENSE_CREATED]`.

---

### 8. User Settings & Profile Management

* **Inertia React Settings Pages:** Profile and password management are handled via dedicated React pages
  (`UpdateProfile.tsx`, `UpdatePassword.tsx`) rendered through Inertia, providing a seamless SPA experience without full
  page reloads.
* **Tabbed Navigation:** A shared `<SettingsHeader />` component provides tab-based navigation between profile and
  password sections, with prefetching enabled for instant tab switching.
* **Server-Side Validation:** Both forms use Laravel Form Requests (`UpdateProfileRequest`, `UpdatePasswordRequest`)
  for robust validation, with errors displayed inline below each field via the `<InputError />` component.
* **Email Re-Verification:** When a user changes their email address, the system automatically nullifies
  `email_verified_at` and sends a new verification notification, ensuring email ownership is always validated.
* **Password Security:** Password changes require current password verification, new password confirmation matching, and
  minimum length validation (8 characters). The form resets password fields on successful update.
* **Zustand State Management:** Budget editing uses a dedicated Zustand store (`budget-modal-store.ts`) following the
  same pattern as expense modals, ensuring consistent state management across the application.

---

## Getting Started

### Prerequisites

* PHP 8.5+
* Composer
* Node.js & NPM
* OpenRouter API Key (required for the AI Financial Assistant)

### Setup & Local Development

**Option A: Quick Automated Setup (Recommended)**

Run the single-command setup script which installs dependencies, creates `.env`, generates app keys, and runs database
migrations:

```bash
composer run setup
composer run dev
```

> Set `OPENROUTER_API_KEY` in `.env` to enable the AI Financial Assistant.

**Option B: Manual Setup**

1. Install dependencies & configure environment:

   ```bash
   composer install
   npm install
   cp .env.example .env
   php artisan key:generate
   ```

   Set your OpenRouter API key in `.env`:

   ```env
   OPENROUTER_API_KEY=your_openrouter_api_key_here
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

### 10. Inertia & Ziggy Route Generation

| Command                                                      | Options / Flags  | Description                                                                                                                                   |
|:-------------------------------------------------------------|:-----------------|:----------------------------------------------------------------------------------------------------------------------------------------------|
| `php artisan ziggy:generate --types=resources/js/ziggy.d.ts` | `--types=<path>` | Generates both the JavaScript route helper (`resources/js/ziggy.js`) and TypeScript declaration file (`resources/js/ziggy.d.ts`) for Inertia. |
| `php artisan ziggy:generate --types-only`                    | `--types-only`   | Generates/updates only the TypeScript declaration file (`resources/js/ziggy.d.ts`).                                                           |

```bash
# Generate Ziggy routes and TypeScript typings whenever you add or modify Laravel web routes:
php artisan ziggy:generate --types=resources/js/ziggy.d.ts
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
	* `actingAsVerifiedUser()`, `actingAsUnverifiedUser()`, `actingAsSubscribedUser()`
	* `validRegistrationPayload()`, `validBudgetPayload()`
* **Comprehensive Coverage:**
	* **Authentication & Guest Access:** Login, logout, session locale persistence, invalid credentials handling, guest
	  redirects.
	* **User Registration:** Signup flow, password uncompromised leak validation (`Password::uncompromised()`),
	  `Registered` event dispatching, and email verification notice redirects.
	* **Email Verification:** Unverified user middleware (`verified`), signed verification URLs (`verification.verify`),
	  notification resending, and localized email message rendering (`VerifyEmail`).
	* **Budget & Expense Management:** Full CRUD test coverage (`ExpenseCrudTest`), policy authorization
	  (`ExpensePolicy`, `BudgetPolicy`), soft deletion, payload validation, available budget balance limits, guest route
	  restrictions, and Inertia flash prop sharing.
	* **User Settings:** Profile update tests (`UpdateProfileTest`), password change tests (`UpdatePasswordTest`), Form
	  Request validation tests (`UpdateProfileRequestTest`, `UpdatePasswordRequestTest`), email re-verification on email
	  change, and current password verification for password updates.
	* **Internationalization (i18n):** Multi-language assertions (`en` and `es`) across forms, views, notifications, and
	  validation error messages.
	* **AI Tools & Agent:** Unit tests for `SearchExpenses`, `AddExpense` tools and `BudgetAssistant` agent
	  (`tests/Unit/Ai/Tools/`, `tests/Unit/Ai/Agents/`).

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
