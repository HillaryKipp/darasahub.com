# Architecture & Technical Decisions — DarasaHub (Kenya CBC Past Papers & Exams Directory)

This document records all verified technical, domain, and architectural decisions made before and during the phased build of **DarasaHub** (`https://darasahub.com`).

---

## 1. Platform & Stack Decisions

| Decision | Choice | Rationale |
|---|---|---|
| **Production PHP Version** | PHP `8.4.28` (`^8.4`, `platform.php: "8.4.28"`) | Verified on HostAfrica DirectAdmin (`PHP 8.4.28`, `Apache/2`, `MariaDB 11.4.13-MariaDB-cll-lve`). Pinning `platform.php` in `composer.json` ensures local PHP 8.5.13 (Herd) locks packages strictly compatible with HostAfrica PHP 8.4.28. |
| **Framework** | Laravel `^12.0` | Latest stable Laravel release compatible with PHP 8.4 and Filament v3. |
| **Admin Panel** | Filament `^3.3` | Battle-tested Laravel 12 + PHP 8.4 admin panel with Livewire v3 and Tailwind v3. |
| **Frontend Stack** | Server-rendered Blade + Tailwind CSS `^3.4` (purged) + Alpine.js `^3.14` + Vite `^6.0` | Minimal JS for Core Web Vitals; Tailwind v3 aligns cleanly with Filament v3. All navigation and homepage accordions work without JS (`<details>`/`<summary>`). |
| **Sitemap** | `spatie/laravel-sitemap: ^8.0` | Native PHP 8.4 + Laravel 12 support; chunked sitemap index (max 5,000 URLs per file). |
| **Full-Page Response Cache** | `spatie/laravel-responsecache: ^8.0` | Native PHP 8.4 + Laravel 12 support using the `file` cache store (no Redis required). Excludes `/admin`, `/search`, downloads, and CSRF-bearing pages. |
| **CSV / Excel Import** | `spatie/simple-excel: ^3.7` (`openspout/openspout: ^5.0`) | Streaming reader keeps peak RAM flat (~10–20 MB) on shared hosting vs PhpSpreadsheet's high memory footprint. |
| **OG Image Generation** | PHP `ext-gd` (static file generated once on publish) | Standard on DirectAdmin; avoids Imagick dependency and zero per-request overhead. |
| **Cache / Queue / Session** | `CACHE_STORE=file`, `QUEUE_CONNECTION=database`, `SESSION_DRIVER=file` | Shared-hosting safe: no Redis, no persistent queue workers required. |
| **Local Development** | Laravel Herd (Windows) + live DirectAdmin MySQL (via Remote MySQL Access Host) | Provides PHP 8.4 + Composer locally so `composer install --no-dev`, migrations, asset builds, and tests run reliably even when the server lacks SSH. |

---

## 2. Canonical URL & Branding Policy

- **Site Name**: `DarasaHub`
- **Canonical Origin**: `https://darasahub.com` (HTTPS, non-www, lowercase paths, **no trailing slash** except root `/`).
- **Redirects**: Enforced via middleware with HTTP `301` redirects for wrong host (`www` -> non-`www`), uppercase paths, trailing slashes, and slug changes recorded in `slug_history`.

---

## 3. National Exams Configuration

All national exams are stored in `national_exams` (configurable in admin) and separated from term-based school exams:

| Short Code | Slug | Official Full Name | Grade / Level | Default Curriculum | Status |
|---|---|---|---|---|---|
| **KPSEA** | `kpsea` | Kenya Primary School Education Assessment | Grade 6 | `cbc` | Confirmed |
| **KJSEA** | `kjsea` | Kenya Junior School Education Assessment | Grade 9 | `cbc` | Confirmed |
| **KILEA** | `kilea` | Kenya Intermediate Level Education Assessment | Stage-Based (nullable grade) | `cbc` | Confirmed (note: spec referred to `KLEA`; `klea` -> `kilea` 301 redirect supported via `slug_history` if needed) |
| **KCSE** | `kcse` | Kenya Certificate of Secondary Education | Grade 12 (and Legacy Form 4) | `legacy_844` / `cbc` | Confirmed — papers explicitly carry `curriculum` (`cbc` or `legacy_844`) so legacy 8-4-4 and CBC papers are never mixed. |

---

## 4. Official CBC Learning Areas (User-Supplied Reference)

Per Section 4 (Content Integrity), only user-supplied official curriculum mappings are seeded as `verification_status = verified`:

- **Pre-Primary (`PP1`, `PP2`)**: Grades seeded; learning-area pivot mappings left **empty/unverified** until the official KICD Pre-Primary list is supplied.
- **Lower Primary (`Grade 1` – `Grade 3`) — 7 Learning Areas**:
  1. English
  2. Kiswahili / Kenya Sign Language (KSL)
  3. Indigenous Language
  4. Mathematics
  5. Environmental Activities
  6. Creative Activities
  7. Religious Education (CRE, IRE, or HRE)
- **Upper Primary (`Grade 4` – `Grade 6`) — 8 Learning Areas**:
  1. English
  2. Kiswahili / Kenya Sign Language (KSL)
  3. Mathematics
  4. Science and Technology
  5. Agriculture and Nutrition
  6. Social Studies
  7. Creative Arts
  8. Religious Education (CRE, IRE, or HRE)
- **Junior School (`Grade 7` – `Grade 9`) — 9 Core Learning Areas**:
  1. English
  2. Kiswahili / Kenya Sign Language (KSL)
  3. Mathematics
  4. Integrated Science
  5. Pre-Technical Studies
  6. Agriculture and Nutrition
  7. Social Studies
  8. Creative Arts and Sports
  9. Religious Education (CRE, IRE, or HRE)
- **Senior School (`Grade 10` – `Grade 12`) — Core & Pathway Electives** (tracked via `grade_learning_area.pathway`):
  - **Core (`core`)**: English, Kiswahili, Core Mathematics, Essential Mathematics, Community Service Learning (CSL), Physical Education
  - **STEM (`stem`)**: Biology, Chemistry, Physics, General Science, Agriculture, Computer Science, Aviation, Building Construction, Electrical Technology, Metal Technology, Power Mechanics, Wood Technology
  - **Social Sciences (`social_sciences`)**: History and Citizenship, Geography, Business Studies, Religious Education, Languages
  - **Arts and Sports Science (`arts_and_sports`)**: Fine Arts, Music & Dance, Theatre & Film, Sports & Recreation

---

## 5. Non-Negotiable Content Integrity & Readiness Decisions

1. **Demo Isolation**: Every factory/seeder demo paper sets `is_demo = true`. Public queries, sitemaps, search, and `ReadinessService` hard-exclude `is_demo = true` records so they can never be published or rendered publicly.
2. **Single Readiness Gate**: `App\Services\ReadinessService::evaluate($subject)` is the sole gatekeeper for `robots` meta, XML sitemap inclusion, Filament publish actions, public hub/homepage links, and the readiness dashboard. Per owner instruction, `readiness_min_landing_intro_words` and `readiness_min_hub_intro_words` default to `0` (optional editorial intro) so pages with verified, published, permitted non-demo papers are immediately live and browsable; thresholds remain configurable in `site_settings`.
3. **No Auto-Publishing**: CSV/Excel imports always stage a preview in `import_jobs` and commit records as `publication_status = draft`, `verification_status = unverified`.
4. **AI-Assisted Content Flag**: `landing_page_content.ai_assisted` requires human review (`reviewed_by`, `reviewed_at`, `status = approved`) before it satisfies readiness.
5. **No SSH Maintenance Route**: Disabled by default (`MAINTENANCE_ROUTE_ENABLED=false`), gated by a long expiring token + IP allowlist + fixed command allowlist, and every invocation writes to `audit_logs`.
