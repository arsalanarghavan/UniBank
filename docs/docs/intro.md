---
sidebar_position: 1
---

# OstadBank API

OstadBank exposes a versioned JSON API at `/api/v1`.

## Interactive OpenAPI

When the API is running, Scramble serves interactive docs at:

- [http://localhost:8000/docs/api](http://localhost:8000/docs/api)

## Authentication

The web app uses Laravel Sanctum SPA cookie authentication:

1. `GET /sanctum/csrf-cookie`
2. `POST /api/v1/auth/login` with credentials and CSRF headers
3. Subsequent requests include session cookies

Roles: `student`, `admin`, `owner`.

## Core endpoints

### Auth
- `POST /api/v1/auth/register`
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/logout`
- `GET /api/v1/auth/me`

### Student / public
- `GET /api/v1/university-categories`
- `GET /api/v1/universities`
- `GET /api/v1/faculties`
- `GET /api/v1/degree-levels`
- `GET /api/v1/fields`
- `GET /api/v1/professors`
- `GET /api/v1/search?q=`
- `GET /api/v1/rankings`
- `GET /api/v1/rules`
- `GET|POST /api/v1/experiences`
- `POST /api/v1/experiences/{id}/attachments`
- `GET|PUT|DELETE /api/v1/experiences/{id}`

### Admin domain
- CRUD university categories / universities / faculties / degree levels
- Faculty-scoped taxonomy + professor links & teaching assignments
- Per-university Telegram/Bale bots + UI Studio / force-join / texts

### Admin (`role:admin|owner`)
- `GET /api/v1/admin/stats`
- `GET /api/v1/admin/moderation/pending`
- `POST /api/v1/admin/moderation/{id}/approve`
- `POST /api/v1/admin/moderation/{id}/reject`
- Taxonomy CRUD under `/api/v1/admin/fields|majors|courses|professors`
- Settings/channels/users/broadcast endpoints under `/api/v1/admin/*`

### Telegram
- `POST /api/telegram/webhook`

## Ranking formula

Score = `0.6 * avg(overall_rating) + 0.4 * avg(teaching_score)`  
Teaching map: excellent=5, good=4, average=3, poor=1  
Minimum reviews: 3 approved experiences.
