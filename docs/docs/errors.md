---
sidebar_position: 2
---

# Error handling

All API errors return JSON.

| Status | Meaning |
|--------|---------|
| 401 | Unauthenticated |
| 403 | Forbidden (role/policy) |
| 422 | Validation error (`errors` object) |
| 429 | Rate limited (login/register throttled) |
| 500 | Server error (details hidden in production) |

Validation example:

```json
{
  "message": "The email field is required.",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

Localized API messages are available in Laravel `lang/fa` and `lang/en`.
