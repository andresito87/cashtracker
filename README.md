<p align="center">
  <img src="public/logo.png" width="1027" alt="CashTracker Logo">
</p>

# CashTracker

CashTracker is a modern, premium financial management application built on top of Laravel. It allows users to track
incomes, expenses, and view real-time balance calculations under a secure, responsive, and internationalized layout.

---

## Technical Stack

* **Backend Framework:** Laravel 13 (PHP 8.5)
* **Database Engine:** PostgreSQL (Development / Production), SQLite In-Memory (Testing)
* **Testing Framework:** Pest PHP 4
* **Styling Framework:** TailwindCSS 4
* **Asset Bundler:** Vite

---

## Core Features & Optimizations

### 1. High-Performance Internationalization (i18n)

* **Single Roundtrip Switcher:** Changing languages uses a query parameter optimization (`?lang=`). The system detects
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
* **Database Unique Constraint Safe-Guard:** A `'unique:users'` email validator was added to `SignUpRequest` with
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

1. Install dependencies:

   ```bash
   composer install
   npm install
   ```

2. Build assets using Vite:

   ```bash
   npm run build
   ```

3. Start the development server using Composer:

   ```bash
   composer run dev
   ```

---

## Testing & Quality Assurance

### Test Suite Architecture (Pest 4)

CashTracker features a robust, professional test suite powered by **Pest PHP 4**.

* **Database Isolation:** All tests run in an isolated in-memory SQLite environment (`DB_CONNECTION=sqlite`,
  `DB_DATABASE=:memory:` configured in `phpunit.xml`), ensuring zero side-effects, ultra-fast RAM execution, and 100%
  safety for the local/production PostgreSQL database.
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
	* **Budget Authorization & Policies:** Owner CRUD operations, Policy authorization guards (`BudgetPolicy`), Admin
	  bypass permissions, guest and unverified user route restrictions.
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
