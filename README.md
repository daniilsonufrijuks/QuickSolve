# QuickSolve

Small tools. Smarter business.

QuickSolve is a Laravel 12 and Vue 3 application for free business utilities, subscription plans, and one-time template downloads. The interface uses Inertia, so public pages are served by Laravel with crawlable titles, descriptions, and canonical URLs in the first HTML response. Interactive tools run in Vue. JSON endpoints live under `/api/v1` and use Sanctum's first-party session guard.

MySQL is the application database. The test suite uses SQLite in memory.

## Requirements

- PHP 8.2 or newer, with `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, and `gd` or `dom` for PDF export
- Composer
- Node.js 20 or newer
- MySQL 8

This workspace was built with PHP 8.2.12 and Laravel 12. Cashier 16, Sanctum 4, and DomPDF are already required in `composer.json`.

## MySQL

Create the database, then point `.env` at it:

```sql
CREATE DATABASE QuickSolve CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=QuickSolve
DB_USERNAME=root
DB_PASSWORD=
```

On XAMPP, start MySQL from the control panel first. Copy `.env.example` to `.env` if you do not already have one, then generate a key:

```bash
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
```

Seeding publishes the three tools and copies the sample downloads into `storage/app/private/templates`. Those files are the products for sale. Do not publish a template that does not have a private file.

The seeder does not create an administrator and does not contain a password. Create one yourself:

```bash
php artisan quicksolve:make-admin you@example.com --name="Your Name" --password="a-long-password"
```

If the email already belongs to an account, the command promotes that account and ignores `--password`.

## Local development

```bash
composer install
npm install
npm run dev
php artisan serve
```

The app is served by Laravel, usually at `http://localhost:8000`. Vite serves assets. `FRONTEND_URL` matches the Laravel origin because this is an Inertia app, not a separate SPA.

In another terminal, process queued mail:

```bash
php artisan queue:listen
```

Contact messages are stored immediately. The email itself waits for a worker. `MAIL_MAILER=log` writes messages to the log when you are developing.

The daily usage prune is scheduled in `routes/console.php`. Run it with:

```bash
php artisan schedule:work
```

## Authentication

Login, registration, password reset, and email-verification routes come from the Laravel Vue starter kit. Sessions are the primary login. Sanctum is installed for `/api/v1` so first-party requests from the same site send the session cookie and the CSRF token. Set `SANCTUM_STATEFUL_DOMAINS` to every origin that should be allowed to call the API with cookies.

Email verification routes exist. The `User` model does not implement `MustVerifyEmail` yet, which matches the starter kit. Add that interface when you want verification to be mandatory.

Authorization for downloads, invoices, and the admin area is enforced in Laravel policies and middleware. Hiding a link is not the control.

## Stripe test mode

Leave these empty until you are ready to click through Checkout:

```env
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
STRIPE_PRO_PRICE_ID=price_...
STRIPE_BUSINESS_PRICE_ID=price_...
CASHIER_CURRENCY=eur
```

Create two recurring EUR prices in the Stripe test dashboard: €9/month for Pro and €19/month for Business. Paste those price IDs into the environment. QuickSolve never trusts a price sent by the browser.

Forward webhooks while developing:

```bash
stripe listen --forward-to http://localhost:8000/stripe/webhook
```

Put the signing secret Stripe CLI prints into `STRIPE_WEBHOOK_SECRET`. Webhook requests are rejected when that secret is empty or the signature does not match. A return URL such as `?status=processing` does not activate a plan or a download. Cashier updates subscriptions from the webhook. One-time template purchases are marked paid only when `checkout.session.completed` is signed, the payment status is paid, and the amount and currency match the purchase row.

### Going live

1. Switch the Stripe keys and price IDs from test values to live values.
2. Create a live webhook endpoint for `https://your-domain/stripe/webhook` and set the live signing secret.
3. Confirm `APP_URL` is the public https origin and that `SANCTUM_STATEFUL_DOMAINS` lists that host.
4. Run migrations on the production database.
5. Create the administrator with `quicksolve:make-admin` on the server. Do not commit that password.
6. Run a queue worker and the scheduler as long-running processes.
7. Replace the privacy and terms pages with text your lawyer has reviewed before you charge customers.

## AI descriptions

```env
AI_PROVIDER=deepseek
AI_PROVIDER_API_KEY=
AI_PROVIDER_BASE_URL=https://api.deepseek.com
AI_PROVIDER_MODEL=deepseek-chat
```

Put a DeepSeek API key in `AI_PROVIDER_API_KEY`. The key stays on the server. The generator calls `POST {AI_PROVIDER_BASE_URL}/chat/completions` with the `deepseek-chat` model, which uses DeepSeek's OpenAI-compatible API. If the key is empty, the generator runs in demo mode and the response says so. The text is assembled from the form on the server. If a key is set and DeepSeek fails, the API returns an error and does not substitute a demo paragraph. Limits are enforced before the provider is called: guests have a daily limit, Free accounts have 5 generations a month, Pro has 100, and Business has 500. Long descriptions require Pro or Business.

## Checks

```bash
php artisan test
vendor/bin/pint --dirty
npm run build
```

Tests that talk to Stripe Checkout are mocked. Webhook tests sign a payload locally with `whsec_test`. They do not need a Stripe account. A live provider call is not part of the suite.

## SEO

The root Blade view prints the title, description, canonical URL, and Open Graph tags for each public page. The sitemap is `/sitemap.xml`. `robots.txt` is generated by Laravel and points at that sitemap. Tool and template pages include breadcrumbs, headings, and original explanatory copy. There are no invented ratings or review counts.

Full HTML for the Vue body requires the Inertia SSR server (`npm run build:ssr` and `php artisan inertia:start-ssr`). Without it, the first response still contains the metadata above, and the page content hydrates in the browser. That is the current default so the app runs under Apache/XAMPP without a second Node process.

## Deployment

Use a host that runs PHP 8.2+, MySQL 8, and a queue worker.

- Set `APP_ENV=production` and `APP_DEBUG=false`.
- Point the document root at `public`. Private downloads live in `storage/app/private` and must not be web-accessible.
- Run `php artisan migrate --force`, `php artisan config:cache`, `php artisan route:cache`, and `npm run build`.
- Link public storage only for template preview images: `php artisan storage:link`.
- Keep `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`, and `AI_PROVIDER_API_KEY` in the server environment, never in `VITE_` variables.
- Serve the app over HTTPS so the session cookie is secure.

## What is implemented

- Public pages: home, tools, tool detail, templates, template detail, pricing, about, contact, privacy, terms
- Profit margin calculator, product description generator, invoice generator with PDF export
- Database-backed catalog, private downloads, and purchase entitlements
- Sanctum session authentication, registration, login, password reset
- Stripe Checkout for Pro and Business, billing portal, cancellation at period end, and signed webhooks
- Customer dashboard for plan, purchases, downloads, and saved invoices
- Admin area for tools, categories, templates, purchases, subscriptions, and usage
- Pest tests for calculations, validation, limits, authorization, checkout wiring, and webhooks

## Still to do before a public launch

- Add live Stripe keys, prices, and a webhook endpoint
- Add an AI key if you want live descriptions
- Have counsel review the privacy and terms pages
- Turn on `MustVerifyEmail` if you want verification to block new accounts
- Run the Inertia SSR server if you need the tool body in the initial HTML
- Fill the e-commerce, converter, planner, and invoice-template categories when those products exist
- Decide a refund policy and map Stripe refunds back onto purchase status
