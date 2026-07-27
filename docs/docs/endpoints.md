---
sidebar_position: 3
---

# Endpoints

Base URL: `/api/v1`

## Public / authenticated student

| Method | Path | Description |
|--------|------|-------------|
| GET | `/university-categories` | University categories (`all=1` includes inactive) |
| GET | `/universities` | Universities (filter `university_category_id`) |
| GET | `/universities/{id}` | University detail with faculties/taxonomy/bots |
| GET | `/faculties` | Faculties (filter `university_id`) |
| GET | `/faculties/{id}` | Faculty detail |
| GET | `/degree-levels` | Degree levels CRUD-backed list |
| GET | `/fields` | Field → major → course tree (`faculty_id` / `university_id`) |
| GET | `/professors` | Professors (`q`, `university_id`, `course_id`, `faculty_id`) |
| GET | `/professors/{id}` | Professor profile + links + teaching assignments |
| GET | `/search?q=` | Search professors and courses |
| GET | `/rankings` | Professor ranking (min 3 reviews) |
| GET | `/rules` | Community rules text |
| GET | `/experiences` | Current user experiences (`all=1` for admins) |
| POST | `/experiences` | Submit experience (university → faculty → taxonomy → professor) |
| POST | `/experiences/{id}/attachments` | Upload notes/file (multipart) |
| DELETE | `/experiences/{id}/attachments/{attachment}` | Delete attachment |
| GET | `/experiences/{id}` | Show experience |
| PUT | `/experiences/{id}` | Resubmit rejected experience |
| DELETE | `/experiences/{id}` | Delete experience |

## Admin (`role:admin\|owner`)

| Method | Path | Description |
|--------|------|-------------|
| GET | `/admin/stats` | Dashboard counters + monthly series |
| GET | `/admin/moderation/pending` | Pending queue |
| POST | `/admin/moderation/{id}/approve` | Approve + publish to bot channel |
| POST | `/admin/moderation/{id}/reject` | Reject with optional reason |
| CRUD | `/admin/university-categories` | University category admin |
| CRUD | `/admin/universities` | Universities |
| CRUD | `/admin/faculties` | Faculties |
| CRUD | `/admin/degree-levels` | Degree levels |
| POST/PUT/DELETE | `/admin/fields\|majors\|courses` | Taxonomy under faculty |
| POST/PUT/DELETE | `/admin/professors` | Professors (+ bio, faculty sync) |
| POST/PUT/DELETE | `/admin/professors/{id}/links` | Academic links |
| POST/DELETE | `/admin/professors/{id}/assignments` | Multi-university teaching |
| GET/POST/PUT/DELETE | `/admin/bots` | Per-university Telegram/Bale bots |
| PUT | `/admin/bots/{id}/layout` | Bot UI Studio layout |
| PUT | `/admin/bots/{id}/settings` | Per-bot settings (force-join, …) |
| POST | `/admin/bots/{id}/texts` | Per-bot texts |
| POST/DELETE | `/admin/bots/{id}/channels` | Publish channels |
| POST/DELETE | `/admin/bots/{id}/required-channels` | Force-join channels |
| GET | `/admin/settings` | Global settings / legacy channels / texts |
| PUT | `/admin/settings` | Update setting key/value |
| GET | `/admin/users` | List users |
| POST | `/admin/users/{id}/role` | Assign role |
| POST | `/admin/users/{id}/toggle-active` | Activate/deactivate |
| POST | `/admin/broadcast` | Queue broadcast |
| POST | `/admin/direct-message` | Queue DM |

## Teaching / exam enums

Stored as stable English codes:

- Teaching rating: `excellent`, `good`, `average`, `poor`
- Exam: `easy`, `medium`, `hard`
- Teaching type: `in_person`, `online`, `hybrid`

Frontend and Laravel `lang/*` provide FA/EN labels.
