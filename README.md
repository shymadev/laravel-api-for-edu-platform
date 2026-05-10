# Tallksy API

REST API for the **Tallksy** English language learning platform. Built with **Laravel 12**, **PHP 8.4**, and a Dockerised service stack.

---

## Table of Contents

- [About the project](#about-the-project)
- [Tech stack](#tech-stack)
- [Getting started](#getting-started)
- [Environment variables](#environment-variables)
- [Running with Docker](#running-with-docker)
- [Database & seeding](#database--seeding)
- [Artisan commands reference](#artisan-commands-reference)
- [API overview](#api-overview)
- [Lesson content structure](#lesson-content-structure)
- [Content block reference](#content-block-reference)
- [Development workflow](#development-workflow)
- [Code quality & git hooks](#code-quality--git-hooks)

---

## About the project

Tallksy is a structured English language learning platform. The API exposes courses organised into topics and lessons, a phrase vocabulary catalog, user progress tracking, premium subscriptions via Stripe, and an admin/moderator content management surface.

**Core domain model:**

```
Course
  └── Topic
        └── Lesson  (content stored as a JSON array of typed blocks)

Phrase  (standalone vocabulary catalog; users can favourite and mark learned)
```

Users can:
- Browse and study courses by difficulty level (A1 → C2)
- Track per-lesson and per-course progress
- Favourite phrases from the phrasebook or from lesson phrase blocks
- Subscribe to a **premium** plan (Stripe Cashier) to unlock gated content
- Authenticate with email/password or Google OAuth

Moderators and admins can:
- Create, edit, publish, unpublish, and archive courses, topics, and lessons
- Manage the phrase catalog (with auto-generated TTS audio)
- Manage advertisements
- View application logs (admin only)

---

## Tech stack

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 12, PHP 8.4 |
| Auth | Laravel Sanctum (token), Google OAuth via Socialite |
| Subscriptions | Laravel Cashier (Stripe) |
| Queue | Redis |
| Storage | MinIO (S3-compatible) |
| TTS audio | Custom TTS microservice (`TTS_SERVICE_URL`) |
| Transcription | Espoke service (`ESPOKE_SERVICE_URL`) |
| Static analysis | Larastan / PHPStan level 5 |
| Code style | PHP CS Fixer (PSR-12 + custom rules) |
| PHPDoc enforcement | PHP_CodeSniffer — all classes and methods must have PHPDoc |
| Testing | Pest v3 / PHPUnit 11, SQLite in-memory |

---

## Getting started

### Prerequisites

- Docker + Docker Compose
- A running `app_network` Docker network shared with the nginx / frontend stack

```bash
docker network create app_network
```

### First-time setup

```bash
# 1. Copy env file and fill in required values (see Environment variables below)
cp .env.example .env

# 2. Install PHP dependencies
docker exec php-fpm composer install

# 3. Generate application key
docker exec php-fpm php artisan key:generate

# 4. Run migrations
docker exec php-fpm php artisan migrate

# 5. Seed the database
docker exec php-fpm php artisan db:seed
```

---

## Environment variables

Copy `.env.example` to `.env`. Key variables:

### Application

| Variable | Description |
|----------|-------------|
| `APP_KEY` | Generated with `php artisan key:generate` |
| `APP_URL` | Public base URL of the API |

### Database

| Variable | Description |
|----------|-------------|
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` / `DB_PORT` | MySQL host and port |
| `DB_DATABASE` | Database name |
| `DB_USERNAME` / `DB_PASSWORD` | Credentials |

### Redis

| Variable | Default | Description |
|----------|---------|-------------|
| `REDIS_HOST` | `redis` | Redis hostname |
| `REDIS_PORT` | `6379` | |
| `REDIS_DB` | `0` | Default DB |
| `REDIS_CACHE_DB` | `1` | Cache DB |
| `REDIS_SESSION_DB` | `2` | Session DB |
| `REDIS_QUEUE_DB` | `3` | Queue DB |

### Storage (MinIO)

| Variable | Default | Description |
|----------|---------|-------------|
| `MINIO_ENDPOINT` | `http://minio:9000` | S3 endpoint |
| `MINIO_ACCESS_KEY` | `minioadmin` | |
| `MINIO_SECRET_KEY` | `minioadmin` | |
| `MINIO_BUCKET` | `tallksy` | Bucket name |
| `MINIO_PUBLIC_URL` | `http://localhost:9000` | Public URL for file access |

### Stripe

| Variable | Description |
|----------|-------------|
| `STRIPE_KEY` | Publishable key |
| `STRIPE_SECRET` | Secret key |
| `STRIPE_WEBHOOK_SECRET` | Webhook signing secret |
| `STRIPE_PRODUCT_ID` | Product ID (e.g. `prod_UKVz1rx1TmbbB4`) |
| `STRIPE_PRICE_ID` | Default price ID (e.g. `price_1TLqxV0p1fKjr5cgo3E2HCXh`) |
| `STRIPE_WEBHOOK_FORWARD_TO` | Internal webhook URL for local queue worker |

### External services

| Variable | Default | Description |
|----------|---------|-------------|
| `TTS_SERVICE_URL` | `http://tts:8000` | Text-to-speech microservice |
| `TTS_TIMEOUT` | `120` | HTTP timeout (seconds) |
| `ESPOKE_SERVICE_URL` | `http://espoke:2700` | Phonetic transcription service |
| `GOOGLE_CLIENT_ID` | | Google OAuth client ID |
| `GOOGLE_CLIENT_SECRET` | | Google OAuth client secret |
| `GOOGLE_REDIRECT_URL` | | OAuth callback URL |

### Mail

| Variable | Description |
|----------|-------------|
| `MAIL_HOST` / `MAIL_PORT` | SMTP server |
| `MAIL_USERNAME` / `MAIL_PASSWORD` | SMTP credentials |
| `MAIL_FROM_ADDRESS` | From address |

---

## Running with Docker

The `docker-compose.yml` defines two services that attach to the shared `app_network`:

| Container | Purpose |
|-----------|---------|
| `php-fpm` | PHP-FPM process that serves the application via nginx (external) |
| `laravel-queue` | Redis queue worker — processes mail, TTS, and other jobs |

```bash
# Start both services (from the repo root, where docker-compose.yml lives)
docker compose up -d

# Tail logs
docker compose logs -f php-fpm
docker compose logs -f laravel-queue
```

> **Note:** nginx, MySQL, Redis, MinIO, and TTS containers are managed outside this `docker-compose.yml` (in the parent stack). Both containers here simply join `app_network` to reach them.

---

## Database & seeding

### Migrations

```bash
docker exec php-fpm php artisan migrate
docker exec php-fpm php artisan migrate:fresh          # drop + re-run
docker exec php-fpm php artisan migrate:fresh --seed   # drop + re-run + seed
```

### Seeder order

`DatabaseSeeder` runs seeders in dependency order:

| # | Seeder | What it creates |
|---|--------|----------------|
| 1 | `DifficultyLevelSeeder` | Difficulty levels A1–C2 |
| 2 | `RoleSeeder` | `user`, `moderator`, `admin` roles |
| 3 | `PhraseSeeder` | Full phrase vocabulary catalog with TTS audio |
| 4 | `CourseSeeder` | Courses, topics, and lessons from `database/seeders/data/courses/` JSON files |
| 5 | `UserSeeder` | 300+ users from `database/seeders/data/users/users.json` |
| 6 | `PremiumSubscriptionSeeder` | Fake premium subscriptions for users listed in `premium_subscriptions.json` |
| 7 | `UserProgressSeeder` | Lesson completion progress from `progress.json` |
| 8 | `CourseReviewSeeder` | Course reviews from `reviews.json` |
| 9 | `FavouritePhrasesSeeder` | Favourite phrases from `favourite_phrases.json` |

Run the full seed:

```bash
docker exec php-fpm php artisan db:seed
```

Run a single seeder:

```bash
docker exec php-fpm php artisan db:seed --class=CourseSeeder
```

### Seeder data files

All JSON seed data lives in `database/seeders/data/`:

```
data/
├── courses/
│   ├── a1/  a2/  b1/  b2/  c1/  c2/   # Course JSON files per level
├── users/
│   ├── users.json                       # User list
│   ├── premium_subscriptions.json       # Indices of users to give premium
│   ├── progress.json                    # Lesson completion records
│   ├── reviews.json                     # Course reviews
│   └── favourite_phrases.json           # Favourite phrase records
└── images/courses/                      # Course cover images
```

---

## Artisan commands reference

All commands run inside the `php-fpm` container:

```bash
docker exec php-fpm php artisan <command>
```

### `tts:regenerate` — Regenerate TTS audio

Regenerates Text-to-Speech audio for phrases, lesson audio blocks, and vocabulary game items. Checks TTS service health before starting.

```bash
# Regenerate all missing audio across all content types
docker exec php-fpm php artisan tts:regenerate

# Regenerate only phrase audio
docker exec php-fpm php artisan tts:regenerate --type=phrases

# Multiple types
docker exec php-fpm php artisan tts:regenerate --type=lesson-audio --type=lesson-games

# Force regenerate even when audio already exists (replaces old files)
docker exec php-fpm php artisan tts:regenerate --force

# Preview what would be regenerated without making any changes
docker exec php-fpm php artisan tts:regenerate --dry-run
```

**Available `--type` values:**

| Type | What it processes |
|------|------------------|
| `phrases` | `phrases.audio` column — phrase audio files |
| `lesson-audio` | Lesson content `audio` blocks with `type=tts` |
| `lesson-games` | Lesson `vocabulary-game` blocks, `listen-write` items with `type=tts` |

When `--type` is omitted all three types are processed. When `--force` is set, old audio files are deleted from storage before uploading replacements.

---

### `phrases:regenerate-transcriptions` — Regenerate phonetic transcriptions

Re-generates phonetic (IPA/Espoke) transcriptions for all phrases via the Espoke service.

```bash
docker exec php-fpm php artisan phrases:regenerate-transcriptions
```

---

### `stripe:sync-subscriptions` — Sync Stripe subscriptions

Fetches all active subscriptions from the Stripe API and upserts them into the local `subscriptions` and `subscription_items` tables. Matches users by their `stripe_id` (customer ID). Filters to `STRIPE_PRODUCT_ID` when set.

```bash
# Full sync
docker exec php-fpm php artisan stripe:sync-subscriptions

# Preview without writing
docker exec php-fpm php artisan stripe:sync-subscriptions --dry-run

# Custom page size (default 100)
docker exec php-fpm php artisan stripe:sync-subscriptions --limit=50
```

> Use this command after importing real users, after a Stripe data migration, or to reconcile subscriptions when webhooks were missed.

---

### Standard Laravel commands

```bash
# Run all tests (lint + phpcs + analyse + clear cache + Pest)
docker exec php-fpm composer test

# Run only unit tests
docker exec php-fpm composer unit-test

# Auto-fix code style (PHP CS Fixer)
docker exec php-fpm composer fix

# Check PHPDoc violations (PHP_CodeSniffer)
docker exec php-fpm composer phpcs

# Auto-fix PHPDoc formatting (PHP_CodeSniffer)
docker exec php-fpm composer phpcbf

# Static analysis (PHPStan level 5)
docker exec php-fpm composer analyse

# Clear all caches
docker exec php-fpm php artisan optimize:clear

# Open Tinker REPL
docker exec php-fpm php artisan tinker
```

---

## API overview

Base prefix: `/api`

### Public endpoints (no auth required)

| Method | Path | Description |
|--------|------|-------------|
| `POST` | `/api/auth/register` | Register with email + password |
| `POST` | `/api/auth/login` | Login (email or username) |
| `POST` | `/api/auth/forgot-password` | Send password reset email |
| `POST` | `/api/auth/reset-password` | Reset password with token |
| `GET` | `/api/courses` | List all published courses |
| `GET` | `/api/courses/{course}` | Course detail |
| `GET` | `/api/courses/{course}/topics` | Topics for a course |
| `GET` | `/api/topics/{topic}/lessons` | Lessons for a topic |
| `GET` | `/api/lessons/{lessonId}` | Lesson detail with full content |
| `GET` | `/api/phrases` | Phrase catalog (paginated, filterable) |
| `GET` | `/api/phrases-categories` | Phrase topic categories |
| `GET` | `/api/difficulty-levels` | Available difficulty levels |
| `GET` | `/api/courses/{courseId}/reviews` | Course reviews |
| `GET` | `/api/courses/{courseId}/rating` | Course average rating |
| `GET` | `/api/statistics/overall` | Platform statistics |
| `GET` | `/api/advertisements/active` | Active advertisements |

### Authenticated endpoints (`auth:sanctum`)

| Method | Path | Description |
|--------|------|-------------|
| `POST` | `/api/auth/logout` | Logout (revoke token) |
| `GET` | `/api/auth/user` | Current authenticated user |
| `GET/PUT` | `/api/profiles/{id}` | View / update profile |
| `POST` | `/api/profiles/{id}/avatar` | Upload profile avatar |
| `POST` | `/api/auth/change-password` | Change password |
| `GET` | `/api/favorite-phrases` | List favourited phrases |
| `POST` | `/api/favorite-phrases/toggle` | Add / remove favourite |
| `POST` | `/api/favorite-phrases/toggle-learned` | Mark / unmark as learned |
| `GET` | `/api/user/completed-lessons` | Completed lesson IDs |
| `POST` | `/api/user/completed-lessons` | Mark a lesson complete |
| `GET` | `/api/user/courses/progress` | Progress across all courses |
| `GET` | `/api/user/courses/{courseId}/progress` | Progress for one course |
| `POST` | `/api/user/courses/{courseId}/stop` | Stop (pause) a course |
| `POST` | `/api/user/courses/{courseId}/resume` | Resume a stopped course |
| `GET` | `/api/user/statistics` | Personal learning statistics |
| `POST` | `/api/courses/{courseId}/reviews` | Post a course review |
| `DELETE` | `/api/courses/{courseId}/reviews` | Delete own review |
| `POST` | `/api/subscription/setup-intent` | Create Stripe SetupIntent |
| `POST` | `/api/subscription/subscribe` | Subscribe to premium |
| `POST` | `/api/subscription/cancel` | Cancel subscription |
| `POST` | `/api/subscription/resume` | Resume cancelled subscription |
| `GET` | `/api/subscription/status` | Subscription status |

### Moderator + admin endpoints

| Method | Path | Description |
|--------|------|-------------|
| `POST/PUT/DELETE` | `/api/courses` | Create / update / delete courses |
| `PUT` | `/api/courses/{course}/publish` | Publish a course |
| `PUT` | `/api/courses/{course}/unpublish` | Unpublish a course |
| `PUT` | `/api/courses/{course}/archive` | Archive a course |
| `POST/PUT/DELETE` | `/api/topics` | Create / update / delete topics |
| `PUT` | `/api/topics/{topic}/publish` | Publish a topic |
| `POST/PUT/DELETE` | `/api/lessons` | Create / update / delete lessons |
| `PUT` | `/api/lessons/{lesson}/publish` | Publish a lesson |
| `POST/PUT/DELETE` | `/api/phrases` | Manage phrase catalog |
| `POST` | `/api/media/video` | Upload a video file |

### Admin-only endpoints

| Method | Path | Description |
|--------|------|-------------|
| `GET/POST/PUT/DELETE` | `/api/users` | User management |
| `PUT` | `/api/users/{user}/block` | Block a user |
| `PUT` | `/api/users/{user}/unblock` | Unblock a user |
| `POST/PUT/DELETE` | `/api/advertisements` | Manage advertisements |
| `GET` | `/api/logs` | Application log viewer |

### Google OAuth (web routes)

| Path | Description |
|------|-------------|
| `GET /auth/google` | Redirect to Google consent screen |
| `GET /auth/google/callback` | OAuth callback — issues Sanctum token |

---

## Lesson content structure

A lesson's `content` field is a JSON array of **ordered blocks**. Each block has a required `type` field and a numeric `order`.

```json
[
  { "type": "text",  "order": 1, "content": "<p>Hello world</p>" },
  { "type": "audio", "order": 2, "content": { "type": "tts", "text": "Hello", "audio_url": "..." } },
  { "type": "test",  "order": 3, "questions": [] }
]
```

### Audio block variants

An `audio` block's `content` object can be one of two sub-types.

**Stored file** (uploaded manually):

```json
{ "type": "stored_audio", "audio_url": "https://minio.example.com/tallksy/audio/file.wav" }
```

**TTS-generated** (auto-generated from text):

```json
{ "type": "tts", "text": "The quick brown fox.", "audio_url": "https://minio.example.com/tallksy/audio/..." }
```

When `audio_url` is empty and `type` is `tts`, the `ProcessParagraphsService` calls the TTS microservice automatically on lesson save. Use `php artisan tts:regenerate` to backfill missing audio.

---

## Content block reference

### `text`

Rich HTML content block.

```json
{
  "type": "text",
  "order": 1,
  "content": "<p>Lesson introduction text.</p>"
}
```

### `video`

Embeds a video by URL.

```json
{
  "type": "video",
  "order": 2,
  "url": "https://www.youtube.com/embed/abc123"
}
```

### `audio`

Audio playback — uploaded file or TTS-generated.

```json
{
  "type": "audio",
  "order": 3,
  "content": {
    "type": "tts",
    "text": "She sells seashells by the seashore.",
    "audio_url": "https://minio.example.com/tallksy/audio/..."
  }
}
```

### `phrases`

Displays a vocabulary list that users can add to their favourites.

```json
{
  "type": "phrases",
  "order": 4,
  "phrases": [
    { "text": "Good morning", "translation": "Доброе утро", "phrase_id": 42 }
  ]
}
```

`phrase_id` links to an existing `phrases` record. Omit it for inline phrases that are not yet in the catalog.

### `test`

Multiple-choice questions. Each question has one correct option.

```json
{
  "type": "test",
  "order": 5,
  "questions": [
    {
      "text": "Which word means 'happy'?",
      "options": ["sad", "joyful", "tired", "angry"],
      "correctOptions": [1]
    }
  ]
}
```

`correctOptions` is an array of **0-based** option indices.

### `fill-gaps`

Fill-in-the-blank exercise. Mark gaps in text with `{{gap}}` tokens and list the correct answers.

```json
{
  "type": "fill-gaps",
  "order": 6,
  "text": "I {{gap}} to school every day.",
  "gaps": ["go"]
}
```

### `matching`

Match left-column items with right-column items.

```json
{
  "type": "matching",
  "order": 7,
  "pairs": [
    { "left": "cat",  "right": "кошка" },
    { "left": "dog",  "right": "собака" }
  ],
  "shuffleRight": true
}
```

### `translation`

Sentence translation pairs. Users translate the left side and check against the right.

```json
{
  "type": "translation",
  "order": 8,
  "pairs": [
    { "source": "How are you?", "target": "Как дела?" }
  ]
}
```

### `categorization`

Drag items into the correct category bucket.

```json
{
  "type": "categorization",
  "order": 9,
  "categories": ["Fruits", "Vegetables"],
  "items": [
    { "text": "apple",   "category": "Fruits" },
    { "text": "carrot",  "category": "Vegetables" }
  ]
}
```

### `sentence-task`

Sentence construction tasks — arrange words into the correct order.

```json
{
  "type": "sentence-task",
  "order": 10,
  "tasks": [
    {
      "words": ["She", "likes", "coffee"],
      "answer": "She likes coffee."
    }
  ]
}
```

### `pre-listening`

Pre-listening warm-up before an audio lesson. Supports vocabulary preview and prediction questions.

```json
{
  "type": "pre-listening",
  "order": 11,
  "taskType": "prediction",
  "title": "Before you listen",
  "instructions": "What do you think the audio is about?",
  "vocabularyWords": [
    { "word": "commute", "definition": "travel to work regularly" }
  ],
  "questions": [
    { "text": "Where is the speaker going?" }
  ]
}
```

### `vocabulary-game`

Interactive vocabulary game. `gameType` controls the variant.

#### `listen-write` — listen and type

```json
{
  "type": "vocabulary-game",
  "order": 12,
  "gameType": "listen-write",
  "listenItems": [
    {
      "text": "neighbourhood",
      "type": "tts",
      "audio_url": "https://minio.example.com/tallksy/audio/..."
    }
  ]
}
```

When `audio_url` is empty and `type` is `tts`, audio is auto-generated on save. Use `php artisan tts:regenerate --type=lesson-games` to backfill.

#### `guess` — guess the word from image/clue

```json
{
  "type": "vocabulary-game",
  "order": 13,
  "gameType": "guess",
  "guessItems": [
    { "word": "bicycle", "clue": "A two-wheeled vehicle." }
  ]
}
```

#### `odd-one-out` — pick the word that doesn't fit

```json
{
  "type": "vocabulary-game",
  "order": 14,
  "gameType": "odd-one-out",
  "oddItems": [
    {
      "words": ["apple", "banana", "carrot", "grape"],
      "odd": "carrot"
    }
  ]
}
```

#### `mistake` — find the error in a sentence

```json
{
  "type": "vocabulary-game",
  "order": 15,
  "gameType": "mistake",
  "mistakeItems": [
    {
      "sentence": "She go to school every day.",
      "correction": "She goes to school every day."
    }
  ]
}
```

---

## Development workflow

```bash
# Fix code style (PHP CS Fixer)
docker exec php-fpm composer fix

# Fix PHPDoc formatting (PHP_CodeSniffer auto-fixer)
docker exec php-fpm composer phpcbf

# Static analysis
docker exec php-fpm composer analyse

# Run all checks + tests
docker exec php-fpm composer test

# Run a single test file
docker exec php-fpm php artisan test tests/Feature/SomeTest.php

# Run a test by name
docker exec php-fpm php artisan test --filter=test_user_can_login

# Clear all caches after config changes
docker exec php-fpm php artisan optimize:clear
```

OpenAPI documentation is served at `/api/documentation` (Swagger UI) when the app is running. The spec source is `resources/swagger/openapi.json` — update it when you add or change HTTP contracts.

---

## Code quality & git hooks

### Linting tools

Three tools enforce code quality:

| Tool | Config | Runs on |
|------|--------|---------|
| **PHP CS Fixer** | `.php-cs-fixer.dist.php` | All PHP files — PSR-12, import order, PHPDoc formatting (removes trailing dots, keeps `@return void`) |
| **PHPStan** | `phpstan.neon` | `app/` — static analysis at level 5 via Larastan |
| **PHP_CodeSniffer** | `phpcs.xml` | `app/` — PHPDoc presence: all classes and methods must have a docblock with typed `@param` and `@return` tags; summary must fit on one line; description text after tags is optional |

### PHPDoc rules (`phpcs.xml`)

- Every class must have a PHPDoc block.
- Every method must have a PHPDoc block.
- Every parameter must have a `@param Type $name` tag (description is **not** required).
- Every method must have a `@return Type` tag (description is **not** required).
- The summary line must be a single line.
- `@throws`, `@mixin`, and description text are allowed but never required.

### Git pre-commit hook

The hook at `hooks/pre-commit` runs automatically before every commit. It:

1. Skips entirely if no PHP files are staged.
2. Runs **PHP CS Fixer** across all files (fast — uses cache).
3. Runs **PHPStan** across the full project.
4. Runs **PHP_CodeSniffer** on staged `app/` files only — existing code without PHPDoc does not block commits; only new or modified files must comply.

If any check fails, the commit is aborted with a hint:

```
✗ Fix with: composer fix        # for CS Fixer
✗ Fix with: composer phpcbf     # for PHPCS auto-fixable issues
✗ PHPStan failed.               # review PHPStan output
```

### Installing the hook

The hook lives in `hooks/pre-commit` (tracked by git). Install it once per machine after cloning:

```bash
composer hooks:install
```

This copies `hooks/pre-commit` to `.git/hooks/pre-commit` and makes it executable. The hook uses the host `php` binary and `vendor/bin/` directly — no Docker required at commit time, but PHP 8.4 must be installed on the host.

> If you reinstall or update the hook script, re-run `composer hooks:install` to sync the copy in `.git/hooks/`.
