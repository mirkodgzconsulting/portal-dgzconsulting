# DGZ CRM Portal — CLAUDE.md

Laravel 13 / Filament v5 / PHP 8.4 multi-tenant CRM for DGZ Consulting clients.

---

## Architecture

- **Admin panel** → `/admin` — full access, manages all clients/sites/posts
- **Client panel** → `/area-cliente` — isolated per client, auth guard `client` or `client_user`
- **Multi-tenant chain**: `posts → site_id → sites.client_id` (complete isolation)
- **Astro sites** consume the CRM via public REST API (SSR on Vercel, real-time)
- **Storage**: Cloudflare R2 (S3-compatible) for all media uploads

### Key Models
- `Client` — company accounts (one per customer). Has `HasMedia` (Spatie) for library uploads. Has `gender` field (`male`/`female`, default `male`).
- `Site` — websites per client (slug used in API routes). Has `has_blog` and `has_portfolio` booleans.
- `Post` — blog articles. Has `HasMedia` with `cover` collection (single file).
- `Category` — blog categories (has `site_id`). Slug auto-generated, not required.
- `PortfolioCategory` — art/portfolio categories (has `site_id`, `cover_image`, `sort_order`). Has `HasMedia`.
- `PortfolioItem` — individual portfolio images (has `portfolio_category_id`, `image_url`, `sort_order`). Has `HasMedia`.

### API Routes
```
GET /api/sites/{site:slug}/posts         → SitePostsController
GET /api/sites/{site:slug}/categories    → SiteCategoriesController
GET /api/sites/{site:slug}/portfolio     → SitePortfolioController
GET /api/client-media                    → ClientMediaController (auth required)
GET /api/subscriptions/upcoming?days=30  → SubscriptionsController (for n8n reminders)
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
// Use Phosphor Light icons (current standard)
protected static \BackedEnum|string|null $navigationIcon = 'phosphor-house-line-light';
```

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

### Hidden fields don't save by default
```php
// WRONG — hidden field won't persist on save
TextInput::make('cover_image')->hidden()

// CORRECT — must add dehydratedWhenHidden()
TextInput::make('cover_image')->hidden()->dehydratedWhenHidden()
```

### TextEntry size (infolist)
```php
// WRONG — class not found in v5
->size(TextEntry\TextEntrySize::Large)

// CORRECT
->size('lg')
```

---

## Design System — Geist/Vercel Inspired

### Typography
- **Body**: Geist font, `letter-spacing: -0.28px`
- **Headings (h1-h4)**: Urbanist font, `font-weight: 700`, `color: #1d212b`, `letter-spacing: -0.02em`
- **Sidebar labels**: Urbanist 600, active 700

### Colors
- **Primary**: DGZ brand blue `#0F65E6` with full scale (600 = brand color)
- **Grays**: `Color::Zinc` (modern neutral)
- **Logo text**: `var(--logo-text-color, #1d212b)` / dark: `#ededed`
- **Sidebar icons**: `#2a2a2c`
- **Sidebar separator (vertical)**: `#d7d7d7`

### Components
- Cards: `border: 1px solid #eaeaea`, no shadow, `border-radius: 8px`
- Buttons: 40px height, 6px radius, 200ms transitions, `background: #0F65E6`
- Badges: pill style (`border-radius: 999px`)
- Inputs: 6px radius, `border-color: #eaeaea`, focus `#0F65E6`
- Sidebar: 12px padding, 36px items, 8px gap, group separators, vertical border, white background
- Scrollbar: `scrollbar-width: thin`, `scrollbar-color: #808080 transparent`

### Icons
- **Sidebar**: Phosphor Light icons (`phosphor-*-light`) via `codeat3/blade-phosphor-icons`
- **Animated**: LordIcon JSON files in `public/icons/`, used via `<x-lord-icon>` Blade component
- **Legacy**: Geist SVGs still registered (`geist-*`) but replaced by Phosphor in client panel

### LordIcon Component
```blade
{{-- Basic usage --}}
<x-lord-icon icon="wired-outline-269-avatar-female-hover-jump" />

{{-- With options --}}
<x-lord-icon icon="my-icon" :size="72" trigger="loop" primary="#121331" secondary="#0F65E6" />
```
- JSON files go in `public/icons/`
- Preloaded in `<head>` via `ClientPanelProvider` render hook
- Defaults: size=48, trigger=hover, stroke=light, primary=#121331, secondary=#0F65E6

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

### Architecture
- **Spatie Media Library** (`spatie/laravel-medialibrary` + `filament/spatie-laravel-media-library-plugin`)
- Config: `config/media-library.php` with `disk_name: r2`
- Models with `HasMedia` + `InteractsWithMedia`: Post, Client, PortfolioCategory, PortfolioItem
- **Curator** removed completely — Spatie is the only media system

### Media Upload Flow (Post cover)
Two methods, both sync `cover_image` field automatically:
1. **Direct upload** via `SpatieMediaLibraryFileUpload` → Spatie saves to R2, `MediaHasBeenAddedEvent` listener syncs URL to `cover_image` field
2. **Media picker** via "Elegir de biblioteca" → Alpine.js modal fetches from `/api/client-media`, `$wire.set('data.cover_image', url)` sets the URL

### Auto-sync: Spatie → cover_image
`AppServiceProvider` listens to `MediaHasBeenAddedEvent`. When a media is added to Post's `cover` collection, the URL is copied to `posts.cover_image`. This ensures the API always has the cover URL regardless of upload method.

### Cover Preview (reactive)
- `ViewField` with `resources/views/filament/forms/components/cover-preview.blade.php`
- Alpine.js `$watch('$wire.data.cover_image', ...)` updates preview in real-time
- Shows existing Spatie media on edit as fallback
- X button to remove image

### Media Library Page (custom, client panel)
- `app/Filament/Cliente/Pages/MediaLibrary.php` — custom page with upload zone + grid
- Uses `fi-ta-ctn` class for consistent card styling
- **Filter tabs** by portfolio category (Walls, Shutters, etc.) + Posts + Library uploads
- **Pagination** with page numbers, per-page selector (24/48/96/All)
- **Navigation badge** with total media count
- Grid view with `rounded-xl shadow-sm border border-zinc-200` per image
- Shows all Spatie media belonging to the client (via Client, Post, Portfolio models)

### Media Picker Modal
- `resources/views/livewire/media-picker-inline.blade.php` — Alpine.js modal with tabs
- API endpoint `GET /api/client-media` returns client's media as JSON
- Integrated in PostForm via Filament Action `browse_media`
- Working: grid display, search, select, set featured image with preview

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
- `GET /api/sites/{slug}/portfolio` — categories with nested items, ordered by sort_order DESC (recientes primero)

### Pablo Pinxit Data
- Client ID: 5, Site ID: 3, slug: `pablopinxit`
- 6 categories: Walls (58), Shutters (25), Dadapop (21), Ikons (88), Hybris (49), Mind Blowing Garden (27)
- 268 total images on R2, 248 originals + 20 added 2026-06-18

### Astro Integration
- `/Users/mirkodgz/Projects/Pablo Pinxit/pablopinxit-sito/`
- `src/lib/crm.ts` — `getCrmPortfolio()` fetches from CRM API
- Env: `CRM_URL`, `CRM_SITE_SLUG=pablopinxit`

---

## Blog / Posts System

### PostForm layout (WordPress-style, client panel)
- `->columns(3)` on root Schema
- Main content `Section` → `columnSpan(2)`: site + category in `Grid::make(2)`, title, slug (hidden), description (hidden, dehydratedWhenHidden), content + word count
- Sidebar `Section` → `columnSpan(1)`: published, featured, pub_date, author (default: logged-in client name), tags
- Cover image `Section` → `columnSpan(2)`: reactive ViewField preview + SpatieMediaLibraryFileUpload + "Elegir de biblioteca" button + cover_image hidden field
- SEO section → `columnSpan(3)`: collapsed by default, title "SEO (opcional)"

### PostInfolist (View Post)
- `->columns(3)` layout: main content (2/3) + sidebar metadata (1/3)
- Main: title (lg bold), description, cover image (250px), content (HTML prose)
- Sidebar: site badge, category badge, status, featured, author, date, tags, slug (copyable), last edited (relative)

### Word count / reading time
- Placeholder reactivo debajo del RichEditor (200 wpm, debounce 1s)

### Post preview
- Button "Vista previa" in EditPost header
- Route `/preview/post/{id}` with blog-style template (Tailwind CDN)

### API (SitePostsController)
- Eager loads `category` and `seo` relationships
- `description` falls back to `seo->description` if post description is null
- All SEO fields read from `seo` relationship (polymorphic `seo_meta` table)
- `cover_image` uses `cover_image_url` accessor (Spatie first, then `cover_image` field)

---

## SEO

### Plugin: nomanur/filament-seo-pro
- `SeoSection::make()` in PostForm, collapsed by default under "SEO (opcional)"
- `seo_meta` table (polymorphic via `HasSeo` trait) is single source of truth
- `description` field on posts is nullable — SEO description used as fallback in API

---

## Categories

### Per site categories
**ModeloOctatrico** (Ana Orero, site_id: 49): Vibración y Sonido (11), Matemáticas y Geometría (4), Cosmología (4), Fundamentos (1)
**ConkretPeru** (Joel Carbajal, site_id: 50): Concreto Premezclado (8), Mortero (7), Shotcrete (2), Guías Técnicas (3)

---

## Subscriptions / Billing

### Model fields
- `site_id`, `service_type`, `price`, `currency` (EUR/USD/PEN/etc.), `billing_cycle` (monthly/quarterly/yearly/one_time)
- `payment_method` — stripe, paypal, transfer, cash (nullable)
- `payment_link` — URL de pago Stripe/PayPal (nullable, visible solo para stripe/paypal en form)
- `status` — pagado, por_vencer, vencido, fuera_de_servicio
- `start_date`, `renewal_date`, `notes`

### Client panel
- Lista de suscripciones con badge de método de pago y estado
- Vista detalle con botón "Pagar ahora →" si hay payment_link

### API for n8n reminders
- `GET /api/subscriptions/upcoming?days=30` — suscripciones que vencen en N días
- Devuelve: client name/email/phone, service, price, currency, payment_method, payment_link, renewal_date, days_until_renewal

### Future phases
- **Fase 2**: Laravel Cashier + Stripe para cobro automático recurrente (clientes selectos)
- **Fase 3**: n8n workflows para recordatorios via Resend (email) y Evolution API (WhatsApp)

---

## Phone Input
- Package: `ysfkaya/filament-phone-input` v4.2
- Used in admin Client form for `phone` field
- Default country: IT, separate dial code enabled
- Flag images in `public/vendor/filament-phone-input/`

---

## Admin Media Library
- `app/Filament/Pages/MediaLibrary.php` — custom page showing ALL media across all clients
- Filters: by client (dropdown), by type (Portfolio/Posts/Library)
- Detail panel shows: file info, client name, context (Post title / Portfolio category)
- Badge with total media count in sidebar

---

## Sub-users / Editors (ClientUser) — OCULTO (plan futuro)

Feature completa pero desactivada. Ver `memory/project_editors_plan.md`.

---

## Client Panel Navigation
```
Dashboard                    (phosphor-house-line-light)
Mi Sitio                     (phosphor-globe-light)
Mis Suscripciones            (phosphor-credit-card-light)
─────────────────
Contenido
  ├── Mis Posts (sort 1)     (phosphor-file-text-light)
  ├── Categorías (sort 2)   (phosphor-tag-light)
  └── Media Library (sort 3) (phosphor-image-light)
Portfolio (only if has_portfolio)
  └── Mi Portfolio           (phosphor-image-light)
```

---

## Dashboard Widget

### Welcome Widget
- LordIcon animated avatar: female (`wired-outline-269-avatar-female-hover-jump`) or male (`wired-outline-268-avatar-man-hover-jump`) based on `client.gender`
- Default/fallback: male avatar
- Stats: total posts, published, drafts, sites count
- CTA buttons: "+ Nuevo Post" (brand blue `#0F65E6`), "Ver todos los posts"

---

## Astro Integration

### Joel (conkret-peru): `/Users/mirkodgz/Projects/joel-peru/conkret-peru-sito`
### Ana (modelo-octatrico): `/Users/mirkodgz/Projects/AnaOrero/modelo-octatrico`
- SSR on Vercel (`output: 'server'`, `adapter: vercel()`)
- Posts appear in real-time (no rebuild needed)
- Env vars in Vercel: `CRM_URL`, `CRM_SITE_SLUG=modelo-octatrico`
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
```
