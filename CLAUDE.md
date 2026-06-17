# DGZ CRM Portal — CLAUDE.md

Laravel 13 / Filament v5 / PHP 8.4 multi-tenant CRM for DGZ Consulting clients.

---

## Architecture

- **Admin panel** → `/admin` — full access, manages all clients/sites/posts
- **Client panel** → `/area-cliente` — isolated per client, auth guard `client` or `client_user`
- **Multi-tenant chain**: `posts → site_id → sites.client_id` (complete isolation)
- **Astro sites** consume the CRM via public REST API at build time
- **Storage**: Cloudflare R2 (S3-compatible) for all media uploads

### Key Models
- `Client` — company accounts (one per customer). Has `HasMedia` (Spatie) for library uploads.
- `Site` — websites per client (slug used in API routes). Has `has_blog` and `has_portfolio` booleans.
- `Post` — blog articles. Has `HasMedia` with `cover` collection.
- `Category` — blog categories (has `site_id`)
- `PortfolioCategory` — art/portfolio categories (has `site_id`, `cover_image`, `sort_order`). Has `HasMedia`.
- `PortfolioItem` — individual portfolio images (has `portfolio_category_id`, `image_url`, `sort_order`). Has `HasMedia`.

### API Routes
```
GET /api/sites/{site:slug}/posts         → SitePostsController
GET /api/sites/{site:slug}/categories    → SiteCategoriesController
GET /api/sites/{site:slug}/portfolio     → SitePortfolioController
GET /api/client-media                    → ClientMediaController (auth required)
```

---

## Filament v5 Gotchas (CRITICAL — read before touching any Resource)

### Navigation group
```php
// WRONG — fails with "must be UnitEnum|string|null"
protected static ?string $navigationGroup = 'Contenido';

// CORRECT
public static function getNavigationGroup(): ?string { return 'Contenido'; }
```

### Navigation icon
```php
// Use string for custom Geist icons
protected static \BackedEnum|string|null $navigationIcon = 'geist-globe';
```

### Custom Geist SVG Icons
Registered in `AppServiceProvider::boot()` via `BladeUI\Icons\Factory`. SVGs in `resources/svg/geist/`.
Icons: globe, file-text, tag, credit-card, image, users, home. All 16x16 fill-based (Vercel style).

### Namespaces that changed vs v3/v4
| Component | v3/v4 | v5 |
|-----------|-------|-----|
| `Section` | `Filament\Forms\Components\Section` | `Filament\Schemas\Components\Section` |
| `Actions` | `Filament\Forms\Components\Actions` | `Filament\Schemas\Components\Actions` |
| `BulkAction` | `Filament\Tables\Actions\BulkAction` | `Filament\Actions\BulkAction` |
| Schema param | `Form $form` | `Schema $schema` |

### `$view` property in Pages/Widgets
```php
// WRONG — static in v5
protected static string $view = '...';

// CORRECT
protected string $view = '...';
```

### getPages() syntax
```php
// CORRECT — must use ::route()
'index' => ListPosts::route('/'),
'create' => CreatePost::route('/create'),
'edit' => EditPost::route('/{record}/edit'),
```

### Bulk actions go in `toolbarActions()`, not `bulkActions()`

---

## Design System — Geist/Vercel Inspired

### Typography
- **Body**: Geist font, `letter-spacing: -0.28px`
- **Headings (h1-h4)**: Urbanist font, `font-weight: 700`, `color: #1d212b`, `letter-spacing: -0.02em`
- **Sidebar labels**: Urbanist 600, active 700

### Colors
- **Primary**: Vercel blue `#0070f3` with full scale
- **Grays**: `Color::Zinc` (modern neutral)
- **Logo text**: `var(--logo-text-color, #1d212b)` / dark: `#ededed`

### Components
- Cards: `border: 1px solid #eaeaea`, no shadow, `border-radius: 8px`
- Buttons: 40px height, 6px radius, 200ms transitions
- Badges: pill style (`border-radius: 999px`)
- Inputs: 6px radius, `border-color: #eaeaea`, focus `#0070f3`
- Sidebar: 12px padding, 36px items, 8px gap, group separators

### Icons
- Sidebar: Custom Geist SVGs (fill-based, `stroke-width: 1px !important`)
- Sidebar icons registered as Blade icon set `geist-*`

### Brand Logo
- Component: `resources/views/components/brand-logo.blade.php`
- Uses SVG icon `public/icons/Icono-dgzconsulting-5kb-svg.svg` (1KB)
- Text in Urbanist 700, adapts light/dark via CSS variable
- Registered via `->brandLogo(view('components.brand-logo'))` in both panels

### Animations
- Page fade-in: 0.25s ease-out
- Loading bar: blue line on topbar
- Skeleton shimmer utility: `.skeleton` class
- Dashboard cards: staggered fadeInUp

### Reference doc
Full design tokens: `docs/design-system-reference.md`

---

## Media System

### Current State (IN PROGRESS)
- **Spatie Media Library** installed (`spatie/laravel-medialibrary` + `filament/spatie-laravel-media-library-plugin`)
- Config: `config/media-library.php` with `disk_name: r2`
- Models with `HasMedia` + `InteractsWithMedia`: Post, Client, PortfolioCategory, PortfolioItem
- Post cover: `SpatieMediaLibraryFileUpload` in PostForm (collection: `cover`, disk: `r2`)
- **Curator** still installed but nav hidden in client panel (`->registerNavigation(false)`)

### Media Library Page (custom)
- `app/Filament/Cliente/Pages/MediaLibrary.php` — custom page with upload zone + grid
- Shows all Spatie media belonging to the client (via Client, Post, Portfolio models)
- Upload via Livewire `WithFileUploads` → stores on Client model's `library` collection

### Media Picker Modal (IN PROGRESS)
- `app/Livewire/MediaPickerModal.php` — Livewire component
- `resources/views/livewire/media-picker-inline.blade.php` — Alpine.js modal with tabs
- API endpoint `GET /api/client-media` returns client's media as JSON
- Integrated in PostForm via Filament Action `browse_media` that opens modal
- **Known issue**: CSS grid not rendering correctly inside Filament modal (Alpine `x-show` conflicts with `display:grid`). Fix attempted with `x-if` template.

### TODO for next session
- [ ] Fix media picker modal grid layout (CSS conflict with Alpine/Filament modal)
- [ ] Make "Set featured image" button work (dispatch event → set cover_image URL)
- [ ] Test full flow: upload in Media Library → select in Post picker → save post
- [ ] Decide: keep Curator for admin or migrate admin to Spatie too
- [ ] Remove Curator package entirely once Spatie is fully working

---

## Portfolio System (Pablo Pinxit)

### Database
- `portfolio_categories` — site_id, name, slug, description, cover_image, sort_order
- `portfolio_items` — portfolio_category_id, title, description, image_url, sort_order
- `has_portfolio` boolean on sites table

### Resources
- Admin: `app/Filament/Resources/PortfolioCategories/` with RelationManager for items
- Client: `app/Filament/Cliente/Resources/PortfolioCategories/` (visible only if `has_portfolio`)
- Drag-to-reorder via `sort_order` in both categories and items

### API
- `GET /api/sites/{slug}/portfolio` — categories with nested items, ordered by sort_order

### Pablo Pinxit Data
- Client ID: 5, Site ID: 3, slug: `pablopinxit`
- 6 categories: Walls (38), Shutters (25), Dadapop (21), Ikons (88), Hybris (49), Mind Blowing Garden (27)
- 248 images uploaded to R2 via `php artisan portfolio:import-pablo`
- Cover images uploaded separately from original Astro project

### Astro Integration
- `/Users/mirkodgz/Projects/Pablo Pinxit/pablopinxit-sito/`
- `src/lib/crm.ts` — `getCrmPortfolio()` fetches from CRM API
- Pages consume API instead of filesystem scan
- Navigation receives categories as props from Layout
- GalleryGrid has spinner loading + fade-in per image
- Env: `CRM_URL`, `CRM_SITE_SLUG=pablopinxit`

---

## Blog / Posts System

### PostForm layout (WordPress-style, client panel)
- `->columns(3)` on root Schema
- Main content `Section` → `columnSpan(2)`: site, category, title, slug (hidden), description, content + word count
- Sidebar `Section` → `columnSpan(1)`: published, featured, pub_date, author, tags
- Cover image `Section` → `columnSpan(2)`: SpatieMediaLibraryFileUpload + "Elegir de biblioteca" button + cover_image URL field
- SEO section below (full width)

### Word count / reading time
- Placeholder reactivo debajo del RichEditor (200 wpm, debounce 1s)

### Post preview
- Button "Vista previa" in EditPost header
- Route `/preview/post/{id}` with blog-style template (Tailwind CDN)

---

## SEO

### Plugin: nomanur/filament-seo-pro
- `SeoSection::make()` in both PostForms
- `seo_meta` table (polymorphic via `HasSeo` trait) is single source of truth
- Manual SEO columns removed from posts table

---

## Categories

### Per site categories
**ModeloOctatrico** (Ana Orero, site_id: 49): Vibración y Sonido (11), Matemáticas y Geometría (4), Cosmología (3), Fundamentos (1)
**ConkretPeru** (Joel Carbajal, site_id: 50): Concreto Premezclado (8), Mortero (7), Shotcrete (2), Guías Técnicas (3)

---

## Sub-users / Editors (ClientUser) — OCULTO (plan futuro)

Feature completa pero desactivada. Ver `memory/project_editors_plan.md`.

---

## Client Panel Navigation
```
Dashboard
Mi Sitio
Mis Suscripciones
─────────────────
Contenido
  ├── Mis Posts (sort 1)
  ├── Categorías (sort 2)
  └── Media Library (sort 3) [custom page]
Portfolio (only if has_portfolio)
  └── Mi Portfolio
```

---

## Astro Integration

### Joel (conkret-peru): `/Users/mirkodgz/Projects/joel-peru/conkret-peru-sito`
### Ana (modelo-octatrico): `/Users/mirkodgz/Projects/AnaOrero/modelo-octatrico`
### Pablo (pablopinxit): `/Users/mirkodgz/Projects/Pablo Pinxit/pablopinxit-sito`

---

## Git Workflow
- **DO NOT push on every change** — commit locally, test in localhost, push only when Mirko confirms
- Production: `https://crm.dgzconsulting.com` (Laravel Cloud, auto-deploy on push to main)
- Each push triggers a deploy that costs money — batch changes

---

## Useful Commands
```bash
php artisan serve                    # Dev server on :8000
php artisan migrate --force
php artisan optimize:clear           # Clear all caches
php artisan view:clear               # Clear compiled views
php artisan icons:clear              # Clear blade icons cache
npm run build                        # Compile Filament themes
php artisan route:list | grep api
php artisan portfolio:import-pablo   # Import Pablo Pinxit images to R2
php artisan media:sync-curator       # Sync R2 images to Curator (deprecated)
```
