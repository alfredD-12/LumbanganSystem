# Static Lumbangan landing page

Standalone, browser-rendered landing page based on `app/views/landing/landing.php` with the original visual design.

## Vercel settings
- GitHub repository: `alfredD-12/LumbanganSystem`
- Root Directory: **`vercel-landing`**
- Framework Preset: **Other**
- Build Command: blank
- Output Directory: blank
- Environment variables: none

## Features and limitations
- PHP, login, registration, face scanning, contact submission, database-driven announcements, and database-driven gallery updates are intentionally **not available**.
- Navigation, officials carousel, gallery carousel and lightbox work in the browser.
- Officials' names, project details and announcements require verification or manual updates before an official public launch.
- Original background/logo and gallery images are referenced from **commit-pinned raw GitHub links** in the public repository. To self-host the images, copy them into this folder and adjust paths in HTML/CSS.
- Never point Vercel at the repository root: it includes PHP code and database exports. This folder exposes only the static site.
- Existing PHP files were not modified.

## Local preview
Run `python -m http.server 8000` inside `vercel-landing`, then visit http://localhost:8000.
