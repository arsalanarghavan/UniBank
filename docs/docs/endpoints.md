---
sidebar_position: 3
---

# Endpoints

Base URL: `/api/v1`

## Public / authenticated student

| Method | Path | Description |
|--------|------|-------------|
| GET | `/fields` | Field → major → course tree |
| GET | `/professors` | Professor list (paginated, `q` search) |
| GET | `/search?q=` | Search professors and courses |
| GET | `/rankings` | Professor ranking (min 3 reviews) |
| GET | `/rules` | Community rules text |
| GET | `/experiences` | Current user experiences (`all=1` for admins) |
| POST | `/experiences` | Submit experience |
| GET | `/experiences/{id}` | Show experience |
| PUT | `/experiences/{id}` | Resubmit rejected experience |
| DELETE | `/experiences/{id}` | Delete experience |

## Admin (`role:admin\|owner`)

| Method | Path | Description |
|--------|------|-------------|
| GET | `/admin/stats` | Dashboard counters + monthly series |
| GET | `/admin/moderation/pending` | Pending queue |
| POST | `/admin/moderation/{id}/approve` | Approve + publish to Telegram |
| POST | `/admin/moderation/{id}/reject` | Reject with optional reason |
| POST/PUT/DELETE | `/admin/fields\|majors\|courses\|professors` | Taxonomy CRUD |
| GET | `/admin/settings` | Settings, channels, bot texts |
| PUT | `/admin/settings` | Update setting key/value |
| POST | `/admin/channels` | Add required channel |
| DELETE | `/admin/channels/{id}` | Remove channel |
| PUT | `/admin/bot-texts/{id}` | Update bot text |
| GET | `/admin/users` | List users |
| POST | `/admin/users/{id}/role` | Assign role |
| POST | `/admin/users/{id}/toggle-active` | Activate/deactivate |
| POST | `/admin/broadcast` | Queue Telegram broadcast |
| POST | `/admin/direct-message` | Queue Telegram DM |

## Teaching / exam enums

Stored as stable English codes:

- Teaching: `excellent`, `good`, `average`, `poor`
- Exam: `easy`, `medium`, `hard`

Frontend and Laravel `lang/*` provide FA/EN labels.
