# Subscription Lifecycle Engine


A standalone Subscription Management API built with Laravel, implementing dynamic plan management, multi-currency support, and a stateful subscription lifecycle engine with automated scheduling.

---

## Architecture Decisions

### 1. Service Layer Pattern
All business logic lives in `App\Services\SubscriptionService`. Controllers are intentionally thin — they validate input, delegate to the service, and format responses. This keeps the domain logic testable in isolation and reusable (e.g., the same service can be called from a queue job or an Artisan command).

### 2. Enum-Driven Status
`App\Enums\SubscriptionStatus` is a PHP 8.1 backed enum. Using an enum instead of plain strings gives:
- IDE autocomplete and refactor safety
- A single source of truth for all valid states
- Readable `match()` expressions in the service

### 3. Repository Pattern
All business logic lives in `App\Repositories\SubscriptionRepository`. Controllers are intentionally thin — they validate input, delegate to the service, and format responses. This keeps the domain logic testable in isolation and reusable.

### 4. Price Storage in Cents
All monetary values are stored as **unsigned integers in the smallest currency unit** (cents / fils / piastres). This eliminates floating-point rounding bugs — a common source of billing bugs in production. The `Plan::getPriceAttribute()` accessor converts to a decimal for API responses.

### 5. Price Snapshot on Subscription
When a user subscribes, the plan's `price_cents` and `currency` are **copied onto the subscription row**. This means if an admin changes a plan's price later, existing subscribers are unaffected until their next renewal — matching real-world billing behavior.

### 6. Grace Period as a First-Class Column
`grace_period_ends_at` is a dedicated timestamp column rather than a calculated field. This makes it trivially queryable by the scheduler (`WHERE status = 'past_due' AND grace_period_ends_at <= NOW()`) with a proper index, avoiding full-table scans at scale.

### 7. Access Check Encapsulated on the Model
`Subscription::hasAccess()` is the single place that answers "should this user have access right now?" It accounts for all four statuses and the grace-period edge case. Any controller or middleware just calls this method.

### 8. Soft Deletes Everywhere
Both `plans` and `subscriptions` use `SoftDeletes`. Billing records must never be hard-deleted for audit, compliance, and dispute-resolution purposes.

### 9. Scheduler via Artisan Command
The lifecycle automation is an Artisan command (`subscriptions:process-lifecycle`) registered in `routes/console.php` to run daily at midnight. This is the standard Laravel approach — it's testable, manually invocable, and integrates cleanly with Laravel Forge / Envoyer.

---

## Subscription State Machine

```
                   ┌──────────────────────────────┐
                   │                              │
             ┌─────▼──────┐         ┌─────────────▼──────────┐
   subscribe │  TRIALING  │─trial   │         ACTIVE         │
   (w/trial) └─────┬──────┘  ends   └──────────────┬─────────┘
                   │         ──►          payment   │
                   │                     fails      │
                   │              ┌─────────────────▼──────────┐
                   │              │         PAST_DUE           │
                   │              │  (3-day grace period open) │
                   │              └──────┬─────────────────────┘
                   │                     │        │
                   │                  grace    payment
                   │                  period   succeeds
                   │                  ends       │
                   │                     │       └──► ACTIVE
                   │                     ▼
                   │              ┌──────────────┐
                   └─────────────►│   CANCELED   │
                   cancel         └──────────────┘
```

### State Transitions

| From       | To         | Trigger                                        |
|------------|------------|------------------------------------------------|
| —          | `trialing` | `subscribe()` on a plan with `trial_period_days > 0` |
| —          | `active`   | `subscribe()` on a plan with no trial          |
| `trialing` | `past_due` | Daily cron: trial expired, plan is paid        |
| `trialing` | `active`   | Daily cron: trial expired, plan is free        |
| `active`   | `past_due` | `recordFailedPayment()` — grace period opened  |
| `past_due` | `active`   | `recordSuccessfulPayment()` within grace period |
| `past_due` | `canceled` | Daily cron: grace period expired without payment |
| any        | `canceled` | `cancel()` called explicitly                   |

### Access Rules

| Status     | Has Access?                                      |
|------------|--------------------------------------------------|
| `trialing` | ✅ Yes, while `trial_ends_at` is in the future   |
| `active`   | ✅ Yes                                           |
| `past_due` | ✅ Yes, while `grace_period_ends_at` is in the future |
| `canceled` | ❌ No                                            |

---

## API Reference

### Authentication
All endpoints except `GET /api/plans` and `GET /api/plans/{id}` require a Sanctum Bearer token.

```
Authorization: Bearer <token>
```

### Plans

| Method   | Endpoint            | Description                     |
|----------|---------------------|---------------------------------|
| `GET`    | `/api/v1/plans`        | List plans (supports filters)   |
| `GET`    | `/api/v1/plans/{id}`   | Get single plan                 |
| `POST`   | `/api/v1/plans`        | Create plan *(auth)*            |
| `PUT`    | `/api/v1/plans/{id}`   | Update plan *(auth)*            |
| `DELETE` | `/api/v1/plans/{id}`   | Delete plan *(auth)*            |

**Query filters for `GET /api/plans`:**
- `active_only` (bool, default `true`)


**Create / Update Plan payload:**
```json
{
  "name": "Pro Monthly",
  "description": "Full-featured monthly plan",
  "tratrial_days": 7
}
```

### Subscriptions

| Method   | Endpoint                              | Description                        |
|----------|---------------------------------------|------------------------------------|
| `GET`    | `/api/v1/subscriptions`                  | List my subscriptions              |
| `POST`   | `/api/v1/subscriptions`                  | Subscribe to a plan                |
| `GET`    | `/api/v1/subscriptions/{id}`             | Get subscription detail            |
| `DELETE` | `/api/v1/subscriptions/{id}`             | Cancel subscription                |
| `GET`    | `/api/v1/subscriptions/{id}/access`      | Check if user currently has access |

**Subscribe payload:**
```json
{ "plan_id": 1 }
```

**Cancel options:**
```
DELETE /api/v1/subscriptions/{id}?immediately=true   # revoke now
DELETE /api/v1/subscriptions/{id}?immediately=false  # revoke at period end (default)
```

### Payments

| Method | Endpoint                                         | Description                     |
|--------|--------------------------------------------------|---------------------------------|
| `GET`  | `/api/v1/subscriptions/{id}/payments`               | List payments for subscription  |
| `POST` | `/api/v1/subscriptions/{id}/payments/succeed`       | Record a successful payment     |
| `POST` | `/api/v1/subscriptions/{id}/payments/fail`          | Record a failed payment         |

**Succeed payload:**
```json
{ "transaction_id": "TXN-ABC-12345" }
```

**Fail payload:**
```json
{ "failure_reason": "Insufficient funds" }
```

---

## Project Setup

### Requirements
- PHP 8.2+
- Composer
- MySQL 8.0+
- Laravel 12

### Installation

```bash
# 1. Clone the repository
git clone https://github.com/amanyzalat/Subscription.Engine.git
cd Subscription.Engine

# 2. Install dependencies
composer install

# 3. Copy and configure environment
cp .env.example .env
php artisan key:generate

# 4. Configure database in .env
DB_CONNECTION=mysql
DB_DATABASE=subscription_engine
DB_USERNAME=root
DB_PASSWORD=secret

# 5. Run migrations and seed plans
php artisan migrate --seed

# 6. Start the server
php artisan serve
```

### Cron Job (Production)
Add to your server's crontab:
```cron
* * * * * cd /var/www/your-app && php artisan schedule:run >> /dev/null 2>&1
```

The scheduler will run `subscriptions:process-lifecycle` every day at midnight.

You can also run the lifecycle command manually at any time:
```bash
php artisan subscriptions:process-lifecycle
```


---

## Postman Collection

Import `postman/Subscription_Engine.postman_collection.json` into Postman.

**Environment variables to set:**

| Variable   | Description                             |
|------------|-----------------------------------------|
| `base_url`  | Your local server URL (default: `http://localhost:8000`) |
| `token`     | Sanctum token from the Login response  |
| `billing_cycle_id`   | ID of a billing cycle to create a plan price for            |
| `currency_id`        | ID of a currency to create a plan price for            |
| `plan_id`   | ID of a plan to subscribe to            |
| `plan_price_id`   | ID of a plan price to subscribe to            |
| `sub_id`    | ID of a subscription to manage         |
| `payment_id`    | ID of a payment to manage         |

**Recommended walkthrough order:**
1. **Auth → Register** then **Auth → Login** (copy token into environment)
2. **Billing Cycles → Create Billing Cycle** — note the returned `id`
3. **Currencies → Create Currency** — note the returned `id`
4. **Plans → Create Plan (Monthly AED with Trial)** — note the returned `id`
5. **Plan Prices → Create Plan Price** — note the returned `id`
6. **Subscriptions → Subscribe to Plan** (subscription starts as `trialing`)
7. **Payments → Record Failed Payment** (status becomes `past_due`, grace period opens)
8. **Subscriptions → Check Access** (still `true` — within grace period)
9. **Payments → Record Successful Payment** (status returns to `active`)
7. **Subscriptions → Cancel Subscription**

---

## Directory Structure

```
app/
├── Console/Commands/
│   └── ProcessSubscriptionLifecycle.php  # Daily cron command
├── Enums/  
│   ├── PaymentStatus.php
│   ├── SubscriptionStatus.php            # PHP 8.1 backed enum
├── Http/
│   ├── Controllers/Api/
│   │   ├── AuthController.php
│   │   ├── BillingCycleController.php
│   │   ├── CurrencyController.php
│   │   ├── PlanController.php
│   │   ├── PlanPriceController.php
│   │   ├── SubscriptionController.php
│   │   └── PaymentController.php
│   ├── Requests/ 
│   │   ├── Auth/                        # Form requests (validation)
│   │   ├── BillingCycle/BillingCycleRequest.php
│   │   ├── Currency/CurrencyRequest.php
│   │   ├── Plan/PlanRequest.php
│   │   ├── PlanPrice/PlanPriceRequest.php
│   │   ├── Subscription/SubscriptionRequest.php
│   │   └── Payment/PaymentRequest.php
│   └── Resources/ 
│   │   ├── Auth/                        # API resources (response shaping)
│   │   ├── BillingCycle/BillingCycleResource.php
│   │   ├── Currency/CurrencyResource.php
│   │   ├── Plan/PlanResource.php
│   │   ├── PlanPrice/PlanPriceResource.php
│   │   ├── Subscription/SubscriptionResource.php
│   │   └── Payment/PaymentResource.php
├── Models/
│   ├── BillingCycle.php
│   ├── Currency.php
│   ├── Plan.php
│   ├── PlanPrice.php
│   ├── Subscription.php
│   └── SubscriptionPayment.php
├── Repositories/
│   ├── BillingCycle/BillingCycleRepository.php
│   ├── Currency/CurrencyRepository.php
│   ├── Plan/PlanRepository.php
│   ├── PlanPrice/PlanPriceRepository.php
│   ├── Subscription/SubscriptionRepository.php
│   └── User/UserRepository.php
├── Helpers/
│   └── SubscriptionHelper.php            # Helper functions
├── Policies/
│   └── SubscriptionPolicy.php
└── Services/
    └── SubscriptionService.php           # Core lifecycle logic

database/
├── factories/
├── migrations/
└── seeders/


routes/
├── api.php
└── console.php                           # Scheduler registration
```