# Ilmora API — Postman Collection

Git-native Postman v3 collection and environment for manually testing the
Ilmora REST API, plus a runnable CLI suite (57 requests).

## Prerequisites

- The app running locally at `http://127.0.0.1:8000`.
- A migrated and seeded database.

```powershell
php artisan migrate --seed
php artisan serve
```

## Run

CLI:

```powershell
postman collection run "postman/collections/Ilmora API" -e "postman/environments/Ilmora Local.environment.yaml"
```

Postman app:

1. **Files → Open folder** and pick this repository, then switch to **Local
   View**.
2. Open the Collection Runner (right-click the collection → **Run collection**)
   and click **Run**. No environment is required — the collection ships
   built-in default variables (see below), so a fresh run works as-is.
3. (Optional) Select **Ilmora Local** in the top-right environment picker (and
   in the Runner) to override the defaults, e.g. to point at a different host
   or credentials.

## Variables

Defaults live in
`postman/collections/Ilmora API/.resources/definition.yaml`, so the collection
runs with **no environment selected**. The optional **Ilmora Local**
environment overrides them (e.g. to point at a different host).

| Variable          | Default / purpose                                    |
| ----------------- | ---------------------------------------------------- |
| `baseUrl`         | App origin (`http://127.0.0.1:8000`)                 |
| `apiUrl`          | `{{baseUrl}}/api`                                    |
| `adminEmail` / `adminPassword` | Seeded admin (`admin@ilmora.test` / `password`) |
| `testUserName` / `testUserPassword` | Account used by the register / login flows |
| `newPassword`     | New password used by the change-password flow        |
| `courseSlug`, `lessonSlug`, `certificateId`, `planSlug` | Sample record identifiers from the seeders |
| `courseId`, `lessonId`, `planId`, `userId` | Filled in automatically during a run     |
| `subscriptionId`, `paymentId` | Filled in automatically by the Subscriptions checkout flow |
| `resetToken`      | Optional — set manually to test the reset-password happy path |

`token`, `adminToken` and `testRunEmail` are collection variables set
automatically during a run.

## Subscription checkout flow

The `Subscriptions` folder (runs after the admin CRUD folders) and the
`Admin - Payments` folder exercise the paid-subscription flow end to end:

| Method | Path | Auth | Purpose | Success |
| ------ | ---- | ---- | ------- | ------- |
| `GET`  | `/api/plans/{slug}` | public | Plan detail | `200` |
| `POST` | `/api/subscriptions` | user | Create a pending order + payment | `201` |
| `GET`  | `/api/subscriptions` | user | The signed-in user's orders | `200` |
| `GET`  | `/api/subscriptions/{id}` | user | Order detail (404 if not the owner) | `200` |
| `GET`  | `/api/payments` | user | The signed-in user's payments | `200` |
| `GET`  | `/api/admin/payments` | admin | All payments + summary | `200` |
| `POST` | `/api/admin/payments/{id}/approve` | admin | Verify payment, activate subscription | `200` |
| `POST` | `/api/admin/payments/{id}/reject` | admin | Reject payment (`422` if not pending) | `200` |
| `GET`  | `/api/admin/subscriptions` | admin | All subscriptions + summary | `200` |

A subscription is only activated by the **admin** approval — never by the
frontend. Each run registers a fresh test user, so its order and subscription
are isolated from previous runs.

## Notes

- Run order matters. Auth captures the tokens; Admin folders use
  `adminToken`; Session (logout) runs last because it revokes tokens.
- `throttle:auth` limits register/login/forgot/reset/password to **5 per
  minute per email+IP**. A single full run stays within it; immediate
  re-runs may return `429`.
- `Reset Password (valid token)` returns `422` unless `resetToken` is set
  from the reset email (or the `password_reset_tokens` table).
- Each run registers a **unique** test user (`tester+<timestamp>@example.com`)
  and deletes the course/plan it creates, so the suite is repeatable.
