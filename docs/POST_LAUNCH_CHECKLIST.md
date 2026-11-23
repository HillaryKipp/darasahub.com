# Post-Launch SEO & Operations Checklist — DarasaHub

Use this checklist before and after launching `https://darasahub.com` so the site ranks sustainably without ever publishing thin pages.

---

## 1. Pre-Launch Verification
- [ ] **Demo Data Cleanup**: Confirm `SELECT COUNT(*) FROM papers WHERE is_demo = 1 AND publication_status = 'published'` returns `0`. Optionally delete all `is_demo = 1` records before public launch (`DELETE FROM papers WHERE is_demo = 1;`).
- [ ] **Verify KNEC National Exam Names**: Confirm official KNEC names and applicable grades for **KPSEA**, **KILEA**, **KJSEA**, and **KCSE** in `/admin/national-exams`.
- [ ] **Supply Official PP1 & PP2 Learning Areas**: Once you confirm the official KICD Pre-Primary learning areas, add them in `/admin/grade-learning-areas` with `verification_status = verified`.
- [ ] **Canonical & Redirect Checks**:
  - `http://darasahub.com` and `https://www.darasahub.com` → `301` to `https://darasahub.com`
  - `/Grade-5/` → `301` to `/grade-5`
  - `/klea` → `301` to `/kilea`
- [ ] **Verify `robots.txt` & `sitemap.xml`**:
  - `https://darasahub.com/robots.txt` allows `/`, disallows `/admin`, `/search`, `/download/`, `/_maintenance/`, and points to `https://darasahub.com/sitemap.xml`.
  - `https://darasahub.com/sitemap.xml` lists only ready URLs (no thin hubs, no `noindex` pages, no demo records).
- [ ] **Verify Maintenance Route Disabled**: Confirm `MAINTENANCE_ROUTE_ENABLED=false` in production `.env` and `POST /_maintenance/cache-clear` returns `404`.
- [ ] **Verify Legal & Takedown Flow**: Submit a test report on `/copyright-takedown`, confirm it appears in `/admin/takedown-requests`, and resolve/delete the test record.

---

## 2. Google Search Console & Indexing Setup
1. Add `darasahub.com` as a **Domain Property** in Google Search Console (DNS TXT record in DirectAdmin → DNS Management).
2. Submit `https://darasahub.com/sitemap.xml` under **Indexing → Sitemaps**.
3. Use **URL Inspection** on your first 3–5 fully populated vertical-slice landing pages to confirm:
   - Canonical matches the clean URL
   - `robots` meta is `index, follow`
   - Rich results parse cleanly (`BreadcrumbList`, `CollectionPage`, `LearningResource`, and `FAQPage` only where visible FAQs exist)
4. Monitor **Page Indexing** weekly to ensure `/search` and unready pages are NOT indexed.

---

## 3. How to Scale Content Without Creating Thin Pages (Vertical Slice Strategy)
Never try to publish all 14 grades × 9+ subjects × 3 terms × 3 exam types at once. Instead, launch in **vertical slices**:
1. Pick **1–2 high-demand grades** (e.g., Grade 5, Grade 8) or **1 national exam** (e.g., KCSE Mathematics / KPSEA Science).
2. Import papers via **CSV/Excel Import** (lands as `draft` + `unverified`).
3. Verify each paper's source, `permission_status = permitted`, working `file_url`, and write a helpful 20+ word revision description + link its marking scheme where available.
4. Write a **unique, page-specific editorial intro (80+ words)** in `/admin/landing-page-contents` explaining what topics that grade/subject/term exam covers, how learners should revise with it, and what years/sets are currently available. Check the **Duplicate-Similarity Warning** to ensure it is genuinely unique.
5. Approve the landing page content (human reviewer), publish the verified papers, and run `php artisan sitemap:generate` (or wait for the `02:30` cron).
6. Run `php artisan seo:audit` weekly to catch any single-paper landing pages or failing readiness items and prioritize filling those gaps before opening new grades.

---

## 4. Ongoing Maintenance & AdSense Readiness
- **Weekly**: Review the Filament **Readiness Dashboard** (`/admin`) for broken/login-walled links (`links:check`), open takedown requests, and pages with only 1 paper.
- **Takedown SLA**: Process copyright takedowns promptly by setting `permission_status = takedown`, `publication_status = archived`, and recording resolution notes.
- **AdSense Hygiene**: Keep pages fast, clutter-free, and learner-safe — avoid intrusive pop-ups, interstitials, or misleading download buttons. Only apply for AdSense after your initial vertical slices have consistent organic traffic and rich, unique editorial content.
