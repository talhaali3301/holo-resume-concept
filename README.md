# Holo Resume

Concept · Sample projects · Designed & built by Talha Ali, [Robo Coders](https://robocoders.dev/)

A three-room portfolio for a web application developer. Visitors can walk a lobby, a projects hall, and a skills observatory, or read the same record as a standard page. The rooms are browser-rendered 3D. The writing, navigation, and project details do not depend on WebGL.

The projects and milestone dates shipped with this repository are **sample content**. They are not a record of a real client, employer, or measured outcome. Replace them in `content/portfolio.php` before you present them as your own work.

## Screens

| Screen | URL |
| --- | --- |
| Lobby | `/` |
| Projects hall | `/projects` |
| One project | `/projects/{slug}` |
| Skills observatory | `/skills` |
| One skill | `/skills/{slug}` |
| Standard reading view | add `?view=standard` on the projects or skills URL |

Project and skill panels are states of those screens, not extra pages. Unknown slugs return 404.

## Requirements

Verified on this machine:

- PHP 8.3.29 (the project requires `^8.3`)
- Composer 2
- Node.js 25.2.1 and npm 11 (Node 20 or newer is a reasonable minimum for the Vite 8 toolchain)
- The PHP `gd` extension, only if you want to regenerate `public/og.png`

No database is required. Session, cache, and queue are file / sync by default.

## Local setup

```bash
composer install
cp .env.example .env
php artisan key:generate
npm install
npm run build
php artisan serve
```

Open `http://127.0.0.1:8000`.

For frontend work with hot reload, run `npm run dev` in a second terminal and leave `php artisan serve` running. Stop the Vite process before you rely on `public/build`; while `public/hot` exists, Laravel loads the dev server instead of the production assets.

### Environment

Copy `.env.example`. The portfolio uses:

| Variable | Local value | Production |
| --- | --- | --- |
| `APP_NAME` | `Holo Resume` | same, unless you rename the product in content as well |
| `APP_KEY` | generated | required |
| `APP_ENV` | `local` | `production` |
| `APP_DEBUG` | `true` | `false` |
| `APP_URL` | `http://localhost:8000` | the public origin, including `https://` |
| `SESSION_DRIVER` | `file` | `file` is enough |
| `CACHE_STORE` | `file` | `file` is enough |
| `QUEUE_CONNECTION` | `sync` | `sync` is enough |

`DB_*` can stay at the sqlite defaults. This app does not migrate or query a database for the portfolio.

Do not put secrets in `content/portfolio.php`. Nothing in that file is sent except the public portfolio fields.

## Commands

```bash
npm run dev          # Vite dev server
npm run build        # production assets into public/build
npm test             # Vitest
npm run types:check  # vue-tsc
php artisan test     # PHPUnit
vendor/bin/pint --dirty   # PHP formatter, when the project is a git checkout
```

## Replace the sample content

Edit only `content/portfolio.php`. While `identity.sample` is true, the interface shows one “Sample content” status instead of a badge on every project and skill.

1. Set `identity.sample` to `false` when the headline, lede, and body are yours. Until then, the tests expect every project to stay marked `sample` and every milestone `period` to stay the exact string `Unverified`.
2. Set `identity.name` only to your real name. Leave it `null` if you do not want a personal name in the page or in structured data.
3. Add `contact.email` as a plain address, and `contact.links` as `https://` URLs. The contact panel shows a `mailto:` link and those URLs. There is no contact form.
4. Replace each project. Keep `sample => true` on anything you have not verified. Set `demo_url` and `repository_url` only when the URL is real; empty and non-https links are dropped.
5. Point `image` at a file under `public/images/...` (referenced as `/images/...`) or an `https://` URL. Missing local files are omitted and the panel shows a placeholder.
6. Connect projects to skills with skill slugs. Unknown slugs are ignored. A skill’s “used in” list is derived from those links.
7. Replace milestones. Do not invent employment dates. Use `Unverified` until the period is something you can stand behind, then set `sample` to `false`.

Slugs are lowercase words separated by hyphens. They become `/projects/{slug}` and `/skills/{slug}`.

After editing, run `php artisan test`. `PortfolioCatalogTest` checks that relationships resolve and that sample mode does not present invented history as fact.

## Architecture

Laravel renders three Inertia pages.

- `routes/web.php` maps `/`, `/projects`, `/projects/{slug}`, `/skills`, `/skills/{slug}`, `/sitemap.xml`, and `/robots.txt`.
- `content/portfolio.php` is the content source. `App\Portfolio\PortfolioCatalog` loads it and drops invalid links, missing images, unknown skill ids, duplicate slugs, and empty titles. Skill-to-skill lines are capped so the observatory stays readable.
- `App\Portfolio\PortfolioPresenter` shapes the props for each screen: shell, destinations, projects, skills, milestones, and document meta.
- `HandleInertiaRequests` shares the shell (brand, navigation, contact) with every page.
- `resources/views/app.blade.php` prints the title, description, canonical URL, Open Graph tags, and a noscript copy of the portfolio, then mounts Inertia. There is no Inertia SSR server. Crawlers that do not run JavaScript still receive the meta tags and the noscript record.

Vue pages live in `resources/js/Pages` (`Lobby.vue`, `Projects.vue`, `Skills.vue`) and use `PortfolioLayout` for the header, footer, skip link, and contact dialog. Project and skill detail, the mobile menu, and the standard list/grid are components on those pages.

The 3D layer is separate from the content:

- `SceneFrame.vue` detects WebGL, lazy-loads the scene, unmounts the canvas when it leaves the viewport, and shows a static fallback if WebGL is missing, the context is lost, or the scene fails to start.
- `LobbyScene.vue`, `HallScene.vue`, and `ObservatoryScene.vue` own the TresJS canvas (on-demand rendering, capped pixel ratio, no shadows).
- `LobbyWorld.vue`, `HallWorld.vue`, and `ObservatoryWorld.vue` compose lights, code-generated geometry, and pointer hits. Camera motion lives in `useCameraRig.ts` and stops when the pointer is idle or the visitor prefers reduced motion.
- Frame positions come from `layoutFrames` and skill positions from `layoutSkills`. Neither is hardcoded inside the page components.

Critical actions are HTML controls. Keyboard movement is never required. The contact action opens a dialog of configured links; it does not post a form.

## Deploy

Do not treat this section as a deployment that has already been run.

On a normal Laravel host or VPS:

1. Point the web root at `public/`. Do not expose the project root.
2. Install PHP 8.3+ with the usual Laravel extensions (`openssl`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`). `gd` is optional.
3. `composer install --no-dev --optimize-autoloader`
4. Install Node on the build machine, then `npm ci` and `npm run build`. `public/build` is gitignored, so the build has to happen wherever you deploy.
5. Create `.env` from `.env.example`, set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` to the public origin, and run `php artisan key:generate --force` once.
6. Keep `SESSION_DRIVER=file`, `CACHE_STORE=file`, and `QUEUE_CONNECTION=sync` unless you have a reason to add Redis or a database. Create no database for this portfolio.
7. `php artisan config:cache`, `php artisan route:cache`, and `php artisan view:cache`.
8. Make `storage/` and `bootstrap/cache/` writable by the PHP user.
9. Serve with PHP-FPM and Nginx or Apache. HTTPS should terminate at the web server. Reload PHP after `.env` changes and clear the config cache (`php artisan config:clear`) if you change it later.

`/sitemap.xml` and `/robots.txt` are application routes. Remove any static `public/robots.txt` if you add one; a static file would hide the route.

## What this build does not do

- Inertia server rendering. The first HTML includes meta tags, JSON-LD for the site, and a noscript portfolio. The interactive shell still needs JavaScript.
- A contact form. Links and `mailto:` only, and only when you configure them.
- A measured Lighthouse or Web Vitals score. The production build reports its own sizes: the initial app script is about 181 kB (62 kB gzip), and Three.js sits in a lazy scene chunk of about 852 kB (227 kB gzip).
- A named person, real clients, real dates, or real outcomes. Those stay empty or labeled sample until you edit `content/portfolio.php`.
