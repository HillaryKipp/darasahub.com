# DarasaHub — Kenya CBC Past Papers & National Exams Directory

A production-ready, SEO-first directory of Kenyan Competency-Based Curriculum (CBC) term assessments (PP1 to Grade 12) and national examinations (**KPSEA**, **KILEA**, **KJSEA**, **KCSE**) built with **Laravel 12**, **MySQL/MariaDB**, **Blade**, **Tailwind CSS v3**, **Alpine.js**, and **Filament v3** — engineered specifically for **DirectAdmin shared hosting** (no Redis, no queue workers, no guaranteed SSH).

---

## Key Architecture Highlights

- **Single Content-Readiness Gate (`App\Services\ReadinessService`)**: No page becomes indexable (`index, follow`) or enters `/sitemap.xml` unless it passes configurable readiness thresholds stored in `site_settings` (verified, published, permitted paper(s) + approved human-reviewed editorial intro). Unready pages serve `noindex, follow`; draft, archived, and demo papers return `404`.
- **Non-Negotiable Demo Isolation**: Every demo/factory paper carries `is_demo = true`. Model `saving` guards throw a `DomainException` if any demo record or non-`permitted` paper attempts to publish.
- **Curriculum Separation**: Term-based school exams (`/{grade}/{learning-area}/{term}/{exam-type}`) are separated from national exams (`/{exam}/{subject}-past-papers/{year}`), and `papers.curriculum` (`cbc` vs `legacy_844`) keeps legacy 8-4-4 KCSE papers clearly labelled apart from CBC papers.
- **Canonical & Slug-History 301 Redirects**: Global middleware enforces `https://darasahub.com` (in production), lowercase paths, no trailing slashes, and automatic `301` redirects from `slug_history` whenever a published slug changes.
- **Full-Page File Response Cache**: `spatie/laravel-responsecache` on the `file` store with model-observer invalidation, excluding `/admin`, `/search`, `/download/*`, authenticated users, and any page containing a CSRF token.
- **Role-Based Admin & Audit Log**: Filament v3 panel at `/admin` with server-side Laravel Policies for `admin`, `reviewer`, and `editor` roles, duplicate-intro similarity warnings, takedown moderation, and full `audit_logs`.

---

## Local Development Setup

### Prerequisites
- **PHP 8.4+** (or Laravel Herd on Windows; `composer.json` pins `platform.php: 8.4.28` to match HostAfrica production)
- **Composer 2.x**
- **Node.js 20+ & npm**
- **MariaDB 10.6+ / MySQL 8+**

### Quick Start
```bash
cp .env.example .env
composer install
php artisan key:generate
npm install && npm run build
```

Configure `DB_CONNECTION=mariadb`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in `.env`, then run:
```bash
php artisan migrate --seed
```

Create your first Filament admin user:
```bash
php artisan make:filament-user
```
*(Set `role = 'admin'` on the user record if needed via `php artisan tinker`: `App\Models\User::where('email','you@darasahub.com')->update(['role'=>'admin']);`.)*

---

## CSV / Excel Import Format

Imports are available via **Admin → Content & Papers → Import CSV / Excel** (`/admin/import-papers-page`) or CLI:
```bash
# Dry-run validation preview (writes zero papers)
php artisan papers:import docs/sample-papers-import.csv

# Commit validated rows as draft + unverified (NEVER auto-publishes)
php artisan papers:import docs/sample-papers-import.csv --commit
```

See [docs/sample-papers-import.csv](docs/sample-papers-import.csv) for a ready-to-use template.

| Column | Required | Allowed Values / Notes |
|---|---|---|
| `category` | Yes | `term` or `national` |
| `grade` | If `term` | Slug or name (e.g. `grade-5` or `Grade 5`) |
| `learning_area` | Yes | Slug or name (must exist in `grade_learning_area` for `term` papers) |
| `term` | If `term` | `term-1`, `term-2`, `term-3` (or `Term 1`..`Term 3`) |
| `exam_type` | If `term` | `opener-exams`, `mid-term-exams`, `end-term-exams` (or `Opener`, `Midterm`, `End Term`) |
| `national_exam` | If `national` | `kpsea`, `kilea`, `kjsea`, `kcse` |
| `year` | Yes | Integer (`1990` to current year + 1) |
| `title` | Optional | Auto-generated from curriculum fields if blank |
| `source` | Yes | Source attribution name |
| `source_url` | Optional | Valid `http(s)` URL |
| `file_url` | Yes | Valid `http(s)` URL (checked for duplicates) |
| `file_type` | Optional | `question_paper` (default) or `marking_scheme` |
| `curriculum` | Optional | `cbc` (default) or `legacy_844` |
| `paper_number` | Optional | e.g. `Paper 1`, `Paper 2` |
| `set_label` | Optional | e.g. `Set 1` |
| `description` | Optional | Revision context shown on the paper detail page |

---

## Essential Artisan Commands

| Command | Purpose |
|---|---|
| `php artisan sitemap:generate` | Generates chunked `/sitemap.xml` and `/sitemaps/*.xml` (ready pages only) |
| `php artisan seo:audit` | Lists failing readiness pages, thin intros, single-paper landings, and `noindex` published papers |
| `php artisan links:check --limit=50` | Checks paper URLs for broken HTTP statuses and HTTP 200 login walls |
| `php artisan papers:import {file} [--commit]` | Validates, previews, and optionally commits CSV/XLSX imports as drafts |
| `php artisan responsecache:clear` | Clears full-page file response cache |
| `php artisan test` | Runs the PHPUnit test suite (isolated on in-memory SQLite) |

---

## Documentation
- [Architecture & Stack Decisions](docs/DECISIONS.md)
- [DirectAdmin Shared-Hosting Deployment Guide](docs/DEPLOYMENT_DIRECTADMIN.md)
- [Post-Launch SEO & Scaling Checklist](docs/POST_LAUNCH_CHECKLIST.md)
