# Ilmora API — Postman Collection

Git-native Postman v3 collection and environment for manually testing the
Ilmora REST API, plus a runnable CLI suite (47 requests).

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

Or open `postman/collections/Ilmora API` in the Postman app and select the
**Ilmora Local** environment.

## Environment variables

| Variable          | Purpose                                              |
| ----------------- | ---------------------------------------------------- |
| `baseUrl`         | App origin (default `http://127.0.0.1:8000`)         |
| `apiUrl`          | `{{baseUrl}}/api`                                    |
| `adminEmail` / `adminPassword` | Seeded admin (`admin@ilmora.test` / `password`) |
| `testUserPassword` / `newPassword` | Password used by the register / change-password flows |
| `courseSlug`, `lessonSlug`, `certificateId`, `planSlug` | Sample record identifiers from the seeders |
| `courseId`, `lessonId`, `planId`, `userId` | Filled in automatically during a run     |
| `resetToken`      | Optional — set manually to test the reset-password happy path |

`token`, `adminToken` and `testRunEmail` are collection variables set
automatically during a run.

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
