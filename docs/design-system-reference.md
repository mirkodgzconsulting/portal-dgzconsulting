# DGZ Portal — Design System Reference

Guía de referencia para el rediseño premium del portal cliente `/area-cliente`.
Inspirado en Vercel, Resend, Linear, Stripe y Clerk.

---

## Filosofía de Diseño

- **Menos es más** — Color solo para acciones y estados. Todo lo demás en escala de grises.
- **Tipografía como diseño** — La fuente correcta transforma toda la percepción.
- **Espaciado generoso** — Mucho aire. Grid base de 8px.
- **Animaciones sutiles** — Micro-interacciones con Alpine.js transitions. LordIcon para highlights.
- **Consistencia** — Un solo set de iconos, un solo estilo de cards, un solo patrón de hover.

---

## Tipografía: Geist (Vercel)

Fuente open source, gratis, creada por Vercel. SIL Open Font License.

### Instalación
```bash
npm install geist
```

O vía CDN:
```html
<link href="https://fonts.googleapis.com/css2?family=Geist:wght@100..900&display=swap" rel="stylesheet">
```

También existe **Geist Mono** para código/monoespaciado.

### Valores tipográficos exactos (de Vercel)
| Elemento | Tamaño | Peso | Line Height | Letter Spacing |
|----------|--------|------|-------------|----------------|
| Body general | 16px | 400 | 24px | normal |
| Nav items (sidebar) | 14px | 400 | 24px | normal |
| Nav item activo | 14px | 500 | 20px | normal |
| Label botones | 14px | 500 | 20px | normal |
| Headings h1 | 24px | 600 | 32px | -0.02em |
| Headings h2 | 20px | 600 | 28px | -0.01em |
| Small/caption | 12px | 400 | 16px | normal |

### Integración Filament v5
```php
// En PanelProvider:
->font('Geist', provider: LocalFontProvider::class, url: asset('css/geist-font.css'))
```

O vía tema CSS:
```css
@theme {
    --font-sans: 'Geist', ui-sans-serif, system-ui, sans-serif;
    --font-mono: 'Geist Mono', ui-monospace, monospace;
}
```

---

## Paleta de Colores

### Light Mode (portal cliente — principal)
```css
--color-bg-base:        #ffffff;        /* Fondo principal */
--color-bg-100:         #fafafa;        /* Cards, inputs */
--color-bg-200:         #f5f5f5;        /* Hover states */
--color-bg-300:         #ebebeb;        /* Bordes activos */
--color-text-primary:   #171717;        /* Texto principal */
--color-text-secondary: #666666;        /* Texto muted */
--color-text-tertiary:  #999999;        /* Texto muy apagado */
--color-border:         #e5e5e5;        /* Bordes principales */
--color-blue:           #0070f3;        /* Acento (Vercel blue) */
```

### Dark Mode
```css
--color-bg-base:        #000000;
--color-bg-100:         #0a0a0a;        /* Cards, inputs, search */
--color-bg-200:         #111111;
--color-bg-300:         #1f1f1f;        /* Item activo sidebar */
--color-bg-400:         #292929;        /* Hover states */
--color-bg-500:         #2e2e2e;        /* Bordes de separación */
--color-text-primary:   #ededed;
--color-text-secondary: #a1a1a1;
--color-text-tertiary:  #454545;
--color-border:         #1f1f1f;
--color-border-subtle:  rgba(255,255,255,0.14);
--color-blue:           #0070f3;
```

### Acento DGZ (nuestro azul corporativo)
```
Primary: #0F65E6  (azul DGZ)
Puede cambiarse a #0070f3 (Vercel blue) para un look más moderno.
```

### Implementación Filament
```php
use Filament\Support\Colors\Color;

->colors([
    'primary' => Color::Blue,    // o custom hex
    'gray' => Color::Zinc,       // grises modernos tipo Vercel
])
```

---

## Sidebar — Medidas Exactas (Vercel)

```css
/* Sidebar wrapper */
width: 256px;
background: var(--bg-base);
padding: 0;

/* Contenedor de items */
padding-left: 8px;
padding-right: 8px;
gap: 1px;

/* Cada nav item */
display: flex;
align-items: center;
height: 36px;
padding-left: 2px;
border-radius: 6px;

/* Ícono wrapper */
width: 36px;
height: 36px;
display: grid;
place-items: center;

/* Texto item inactivo */
font-size: 14px;
font-weight: 400;
color: var(--text-secondary);

/* Item activo */
background: var(--bg-300);
color: var(--text-primary);
font-weight: 500;

/* Hover */
background: var(--bg-200);
color: var(--text-primary);
transition: all 150ms ease;
```

### En Filament (theme.css)
```css
/* Sidebar overrides */
.fi-sidebar {
    --fi-sidebar-width: 256px;
}

.fi-sidebar-item-button {
    height: 36px;
    border-radius: 6px;
    padding-left: 2px;
    font-size: 14px;
    transition: all 150ms ease;
}
```

---

## Iconos

### Sistema principal: Heroicons (ya integrado en Filament)
- Usar consistentemente **Outline** para nav, **Solid** solo para estados activos
- Tamaño 20px en sidebar, 16px en tablas

### Highlights: LordIcon (lordicon.com)
- Cuenta de Mirko: lordicon.com
- Usar solo en dashboard widgets, login page, estados vacíos (3-5 iconos máx)
- Trigger: `hover` o `loop` según contexto

### Integración LordIcon
```php
// En PanelProvider, render hook HEAD_END:
->renderHook(
    PanelsRenderHook::HEAD_END,
    fn () => '<script src="https://cdn.lordicon.com/lordicon.js"></script>'
)
```

```html
<!-- En Blade views -->
<lord-icon
    src="https://cdn.lordicon.com/abcdef.json"
    trigger="hover"
    colors="primary:#0F65E6"
    style="width:48px;height:48px">
</lord-icon>
```

### Alternativa: Lucide Icons
- Estilo casi idéntico a Geist Icons de Vercel
- lucide.dev para copiar SVGs a Blade
- `npm install lucide` o CDN

---

## Cards y Componentes

### Card style (Vercel/Resend pattern)
```css
background: var(--bg-100);
border: 1px solid var(--border);
border-radius: 8px;        /* 6px Vercel, 8px Resend */
padding: 24px;
box-shadow: none;           /* Sin sombra — solo border */
```

### Hover en cards
```css
transition: border-color 150ms ease;
&:hover {
    border-color: var(--bg-400);
}
```

### Badges (como "Pro", "Beta" en Vercel)
```css
display: inline-flex;
align-items: center;
font-size: 11px;
font-weight: 500;
padding: 2px 8px;
border-radius: 999px;
background: color-mix(in srgb, var(--color-blue) 15%, transparent);
color: var(--color-blue);
```

### Stats cards pattern
```html
<div class="border border-gray-200 rounded-lg p-6">
    <div class="flex items-center justify-between mb-4">
        <span class="text-sm text-gray-500">Posts publicados</span>
        <lord-icon src="..." trigger="hover" style="width:24px;height:24px"></lord-icon>
    </div>
    <div class="text-3xl font-semibold text-gray-900">24</div>
    <div class="mt-2 flex items-center text-sm text-green-600">
        <svg ...><!-- arrow up --></svg>
        +12% vs mes anterior
    </div>
</div>
```

---

## Charts / Data Visualization

### Plugin: leandrocfe/filament-apex-charts
```bash
composer require leandrocfe/filament-apex-charts
```
- Sparklines nativos en widgets Filament
- Line, bar, area, donut, radial
- Dark mode compatible
- Polling y deferred loading

### Ejemplo sparkline widget
```php
class PostsChartWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'postsChart';
    protected static ?int $sort = 2;

    protected function getOptions(): array
    {
        return [
            'chart' => ['type' => 'area', 'height' => 80, 'sparkline' => ['enabled' => true]],
            'series' => [['data' => [3, 5, 2, 8, 4, 7, 6]]],
            'colors' => ['#0F65E6'],
            'fill' => ['type' => 'gradient', 'gradient' => ['opacityFrom' => 0.5, 'opacityTo' => 0]],
        ];
    }
}
```

---

## Animaciones (Alpine.js)

### Fade + scale (dropdowns, modals)
```html
<div x-show="open"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 scale-95"
     x-transition:enter-end="opacity-100 scale-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100 scale-100"
     x-transition:leave-end="opacity-0 scale-95">
```

### Staggered list (cards apareciendo una por una)
```css
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-up {
    animation: fadeInUp 0.4s ease-out forwards;
    opacity: 0;
}
```
```blade
@foreach($items as $item)
    <div class="animate-fade-in-up" style="animation-delay: {{ $loop->index * 50 }}ms">
        ...
    </div>
@endforeach
```

### Hover lift (cards)
```css
.card-hover {
    transition: transform 150ms ease, box-shadow 150ms ease;
}
.card-hover:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
```

---

## Filament v5 — Render Hooks Clave

| Hook | Uso |
|------|-----|
| `HEAD_END` | Cargar Geist font, LordIcon CDN |
| `BODY_START` | Loading bar global |
| `AUTH_LOGIN_FORM_BEFORE` | Logo + texto bienvenida en login |
| `AUTH_LOGIN_FORM_AFTER` | Footer "Powered by" en login |
| `SIDEBAR_NAV_END` | Link de ayuda, badge de versión |
| `SIDEBAR_FOOTER` | Info del plan del cliente |
| `CONTENT_BEFORE` | Breadcrumbs, alertas |
| `TOPBAR_END` | Notificaciones, acciones rápidas |
| `USER_MENU_BEFORE` | Nombre del usuario (ya implementado) |

### Override de Blade components
```bash
php artisan vendor:publish --tag=filament-panels-views
```
Luego modificar templates específicos. **Preferir render hooks** sobre overrides completos.

---

## Login Page — Diseño Premium

### Approach: Split-screen
- **Izquierda**: fondo con gradiente/imagen + logo DGZ grande + testimonial/tagline
- **Derecha**: formulario limpio sobre fondo blanco

### Implementación
```php
// Render hooks en ClientPanelProvider:
->renderHook(PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, fn () => view('filament.cliente.login-header'))
->renderHook(PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, fn () => view('filament.cliente.login-footer'))
```

### CSS custom para login (en theme.css)
```css
.fi-auth-page {
    background: linear-gradient(135deg, #0F65E6 0%, #051c40 100%);
    /* o imagen de fondo */
}
```

---

## Orden de Implementación

### Fase 1 — Fundamentos (1 sesión)
1. Font swap a Geist
2. Paleta de colores (Zinc grays + acento DGZ)
3. Card styling base (bordes, radios, spacing)
4. Sidebar refinements (tamaños, transiciones)

### Fase 2 — Login Premium (1 sesión)
5. Login page branded con split-screen
6. LordIcon en login (animación de bienvenida)

### Fase 3 — Dashboard Wow (1-2 sesiones)
7. Stats cards con LordIcon + sparklines (ApexCharts)
8. Animaciones staggered en widgets
9. Micro-interacciones en hover states

### Fase 4 — Polish (1 sesión)
10. Empty states con ilustraciones
11. Loading skeletons
12. Transiciones entre páginas
13. Badges premium para estados

---

## Recursos Gratuitos

| Recurso | Link | Costo |
|---------|------|-------|
| Geist Font | vercel.com/font | Gratis (SIL License) |
| Geist Icons | geist-ui.dev/icons | Gratis |
| Lucide Icons | lucide.dev | Gratis |
| Heroicons | heroicons.com | Gratis |
| LordIcon | lordicon.com | Free tier + cuenta Mirko |
| ApexCharts | apexcharts.com | Gratis (MIT) |
| filament-apex-charts | github.com/leandrocfe | Gratis |
