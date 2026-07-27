---
sidebar_position: 2
---

# Authentication

OstadBank uses Laravel Sanctum SPA authentication for the Next.js frontend.

## Flow

1. Call `GET /sanctum/csrf-cookie` (credentials included)
2. Read `XSRF-TOKEN` cookie
3. `POST /api/v1/auth/login` with JSON body and `X-XSRF-TOKEN` header
4. Session cookie authenticates subsequent `/api/v1/*` calls

## Register

```http
POST /api/v1/auth/register
Content-Type: application/json

{
  "name": "Student",
  "email": "student@example.com",
  "password": "Password!123",
  "password_confirmation": "Password!123",
  "locale": "fa"
}
```

## Login

```http
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "student@example.com",
  "password": "Password!123",
  "remember": true
}
```

## Roles

| Role | Access |
|------|--------|
| `student` | Submit/search/rankings/own experiences |
| `admin` | Moderation, taxonomy, settings, broadcast |
| `owner` | All admin + assign owner role |

Login and register are rate-limited to 10 requests per minute.
