# DGZ CRM Portal — CLAUDE.md

Laravel 13 / Filament v5 / PHP 8.4 multi-tenant CRM for DGZ Consulting clients.

---

## Architecture

- **Admin panel** → `/admin` — full access, manages all clients/sites/posts
- **Client panel** → `/area-cliente` — isolated per client, auth guard `client` or `client_user`
- **Multi-tenant chain**: `posts → site_id → sites.client_id` (complete isolation)
- **Astro sites** consume the CRM via public REST API at build time

### Key Models
- `Client` — company accounts (one per customer)
- `Site` — websites per client (slug used in API routes)
- `Post` — blog articles (has `site_id`, `category_id`, `published`, `featured`, SEO fields)
- `Category` — blog categories (has `site_id`)
- `Tag` — many-to-many with posts

### API Routes
```
GET /api/sites/{site:slug}/posts         → SitePostsController
GET /api/sites/{site:slug}/categories    → SiteCategoriesController
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
// WRONG — string fails type check in some contexts
protected static ?string $navigationIcon = 'heroicon-o-tag';

// CORRECT — use BackedEnum
use Filament\Support\Icons\Heroicon;
protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedTag;
```

### Namespaces that changed vs v3/v4
| Component | v3/v4 | v5 |
|-----------|-------|-----|
| `Section` | `Filament\Forms\Components\Section` | `Filament\Schemas\Components\Section` |
| `BulkAction` | `Filament\Tables\Actions\BulkAction` | `Filament\Actions\BulkAction` |
| Schema param | `Form $form` | `Schema $schema` |

### getPages() syntax
```php
// WRONG — bare class strings
'index' => ListPosts::class,

// CORRECT — must use ::route()
'index' => ListPosts::route('/'),
'create' => CreatePost::route('/create'),
'view' => ViewPost::route('/{record}'),
'edit' => EditPost::route('/{record}/edit'),
```

### Bulk actions go in `toolbarActions()`, not `bulkActions()`
```php
->toolbarActions([
    BulkActionGroup::make([
        BulkAction::make('...')->...,
        DeleteBulkAction::make(),
    ]),
])
```

---

## Blog / Posts System

### PostForm layout (WordPress-style)
- `->columns(3)` on the root Schema
- Main content `Section` → `columnSpan(2)`: title, slug (hidden for client), cover_image + URL row, content, description
- Sidebar `Section` → `columnSpan(1)`: published, featured, pub_date, author, tags
- SEO section below (full width): `SeoSection::make()` from `nomanur/filament-seo-pro`

### Image row layout
```php
Grid::make(10) → [
    FileUpload  columnSpan(3)  // 30% — Imagen Portada
    TextInput   columnSpan(7)  // 70% — URL Imagen (Cloudinary/R2)
]
```

### RichEditor
- Filament v5 native `RichEditor` (no external package needed)
- `awcodes/filament-tiptap-editor` is Filament v3 only — do NOT install
- Min-height 300px via `->extraInputAttributes(['style' => 'min-height: 300px'])`

### Slug
- Auto-generated from title on create
- Hidden from client form (`->hiddenOn(['create', 'edit'])` on slug field)
- Visible to admin

---

## SEO

### Plugin: nomanur/filament-seo-pro
- Installed via composer
- Saves to separate `seo_meta` table via polymorphic `HasSeo` trait on `Post` model
- `SeoSection::make()` renders the full Yoast-style SEO panel in the form
- Registered in both panels:
  - Admin: `SeoPlugin::make()` (full features)
  - Client: `SeoPlugin::make()->enableManagementPage(false)->enableDashboardWidget(false)`

### ⚠️ Known conflict (unresolved)
`posts` table has manual SEO columns (`seo_title`, `seo_description`, `focus_keyword`, `canonical_url`, `og_title`, `og_description`, `og_image`, `robots`) added via migration `2026_06_16_230034`. The plugin uses `seo_meta` table instead. These manual columns are redundant. Decision pending: remove migration + columns, or keep as fallback.

---

## Categories

### Admin Resource
`app/Filament/Resources/Categories/CategoryResource.php`
- Shows all categories across all clients/sites
- Filter by site available

### Client Resource
`app/Filament/Cliente/Resources/Categories/CategoryResource.php`
- Filtered to only show categories belonging to authenticated client's sites
- `getEloquentQuery()` uses `whereHas('site', fn($q) => $q->where('client_id', $clientId))`

### Astro category pages
- Generated at build time via `getStaticPaths()` + `getCrmCategories()`
- Route: `/blog/categoria/[slug]`
- Includes Schema.org `CollectionPage` + `BreadcrumbList` for Google

---

## Client Panel Navigation
```
Contenido
  ├── Mis Posts (sort 1)
  ├── Categorías (sort 2)  [Client CategoryResource]
  └── Mis Imágenes (sort 2) [Curator]
```
- "SEO Management" page is hidden via `->enableManagementPage(false)`

---

## Posts Table Features
- Columns: site, client (admin only), title + description subtitle, category badge, pub_date, published icon, featured icon
- Filters: published/draft toggle, featured toggle, category select, has/no category ternary
- Bulk actions: "Asignar categoría" (bulk assign), Delete
- Default sort: `pub_date DESC`, 25 per page

---

## Astro Integration

### Joel (conkret-peru): `/Users/mirkodgz/Projects/joel-peru/conkret-peru-sito`
### Ana (modelo-octatrico): `/Users/mirkodgz/Projects/AnaOrero/modelo-octatrico`

Both have:
- `src/lib/crm.ts` — `getCrmPosts()`, `getCrmCategories()`, interfaces `CrmPost`, `CrmCategory`
- `src/pages/blog/[slug].astro` — HTML vs Markdown detection: `rawContent.trimStart().startsWith('<')`
- `src/pages/blog/categoria/[slug].astro` — category listing pages (NEW)

### NormalizedPost interface
Includes: `slug, title, description, content, tags, author, pubDate, cover_image, featured, category, category_slug`

---

## Pending / To Do (as of 2026-06-17)
- [ ] Resolve SEO column conflict (manual posts columns vs seo_meta table)
- [ ] Categorize existing 41 posts (all have `category_id = NULL`) — use bulk assign
- [ ] Client dashboard welcome page (currently empty)
- [ ] Post preview before publishing
- [ ] Word count / reading time in editor
- [ ] Auto sitemap trigger on publish (currently requires manual Astro redeploy)

---

## Useful Commands
```bash
php artisan migrate
php artisan filament:upgrade
php artisan config:cache
php artisan route:list | grep api
php artisan tinker
npm run build  # compile Filament themes
```
