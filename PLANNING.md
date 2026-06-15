# DGZ CRM + Portal de Clientes — Planning

## Objetivo

Sustituir la "BD Clients" de Notion (26+ clientes, credenciales en texto plano) por un
CRM interno + portal de clientes, construido con Laravel + Filament, alojado en el
Hetzner que ya tiene DGZ Consulting.

- **Admin (DGZ)**: `crm.dgzconsulting.com` — vista global (solo Mirko/DGZ) de todos
  los clientes, sitios, suscripciones/pagos, notificaciones/recordatorios, y (fase 3)
  gestión de posts de blog.
- **Portal cliente**: `portal.dgzconsulting.com` — cada cliente entra con su login y
  ve SOLO lo suyo (servicios, facturas, fechas de pago, y posts de blog si su sitio
  tiene ese módulo activo). Aislamiento vía Filament Tenancy.

> Ambos son el MISMO proyecto Laravel + misma base de datos, desplegados juntos en
> Dokploy (servidor "Dokploy", 91.99.11.2). Son 2 paneles Filament distintos
> (`/admin` y portal), cada uno con su propio subdominio y login.
- **Marketing**: `dgzconsulting.com` — landing pública de DGZ Consulting, en Astro
  (estilo n8n.io), separada del CRM.

## Stack

- **Backend/Admin/Portal**: Laravel + Filament 3 (multi-panel: admin + area-cliente,
  tenancy = `Client`)
- **Base de datos**: MySQL o PostgreSQL (en el mismo Hetzner)
- **Emails transaccionales**: Resend (recordatorios de pago 30/14/7/0 días)
- **Hosting CRM**: Hetzner VPS existente
- **Marketing site**: Astro, deploy en Vercel (gratis)

## Modelo de datos (v1)

```
Client
 - name, email (login portal), logo

Site (1..N por Client)        ← reemplaza tabla "BD Clients"
 - name, domain, admin_url
 - cms_username, cms_password  → ENCRYPTED, visible solo en /admin (nunca en portal)
 - cms_type (WordPress / Astro / etc.)
 - hosting_provider (SiteGround, etc.)
 - has_blog: boolean           → activa módulo "Posts" en el portal

Subscription (1..N, ligado a Site)  ← reemplaza tabla "Pagos"
 - service_type (Hosting+Dominio, Dominio, Mantenimiento, etc.)
 - price, billing_cycle (Yearly/Monthly)
 - start_date, renewal_date
 - status (Pagado / Por vencer / Vencido / Fuera de servicio)
 - notes

Post (solo si Site.has_blog = true)
 - title, slug, description, content, cover_image, tags, pubDate, published
```

## Roadmap por fases

1. **Fase 1** — `Client`, `Site`, `Subscription` + panel `/admin`. Esto ya reemplaza
   Notion (incluye el vault encriptado de credenciales). Entregable: dejar de usar
   Notion para BD Clients.
2. **Fase 2** — Portal cliente (`/area-cliente`) con tenancy por `Client`: login,
   ver servicios/facturas/fechas de pago. Emails de recordatorio vía Resend.
3. **Fase 3** — Módulo `Post` (blog) por `Site`. Aquí se migran los posts desde:
   - **Strapi** (`cms.dgzconsulting.com`) → posts de `modelo-octatrico` (20 artículos)
   - **Markdown local** → posts de `conkret-peru-sito` (20 artículos)

   Cada sitio Astro cambia su `src/lib/strapi.ts` (o `content.config.ts`) por un
   cliente que llame a la API de este CRM (`GET /api/sites/{slug}/posts`).

## ⚠️ Strapi (`cms.dgzconsulting.com`) — NO BORRAR todavía

Strapi sigue siendo la fuente de los 20 posts de `modelo-octatrico` hasta que la
**Fase 3** esté lista y los posts estén migrados al nuevo módulo `Post`. Borrarlo
antes dejaría el blog de modelo-octatrico vacío (el build no rompe, pero
`getStrapiPosts()` devolvería `[]`).

Checklist antes de borrar Strapi de EasyPanel:
- [ ] Fase 3 completada (módulo `Post` funcionando en el CRM)
- [ ] Los 20 posts de Modelo Octátrico migrados a `Post` (con sus imágenes)
- [ ] `src/lib/strapi.ts` en `modelo-octatrico` reemplazado y build verificado
- [ ] Confirmar que ningún otro proyecto usa `cms.dgzconsulting.com`

## Seguridad

- Las credenciales `Site.cms_username` / `cms_password` van con `encrypted` cast de
  Laravel (Eloquent), accesibles solo desde el panel `/admin` (rol DGZ), nunca
  expuestas en `/area-cliente` ni en la API pública.
- Rotar progresivamente las contraseñas reutilizadas que estaban en la Notion
  (`N4DIEsabe2**`, `Passw0rd99@@`, etc.) — quedaron expuestas en un chat.

## Costos estimados (incremental)

| Pieza | Costo |
|---|---|
| Hetzner VPS | ya existente (~€4.50–8.50/mes si hace falta upgrade) |
| Laravel + Filament (core) | gratis — MIT/open source, sin suscripción |
| Plugins Filament (opcionales) | $49-99/año c/u, terceros vía filamentphp.com/plugins — no necesarios para el MVP |
| Resend | gratis hasta 3,000 emails/mes |
| Astro marketing (Vercel) | gratis (plan hobby) |
| Dominio dgzconsulting.com | ya existente |

## Próximos pasos / decisiones abiertas

- ¿Un `Client` puede tener varios usuarios de portal, o 1 login = 1 cliente?
- Taxonomía exacta de `Subscription.status` (¿cuántos estados, colores?)
- Definir roles dentro del panel `/admin` (solo tú, o asistentes con acceso limitado)

## GitHub

Repo aún no creado. Crear cuando arranque la Fase 1 (scaffold de Laravel), sugerido
bajo la misma org `DGZ-Consulting` (igual que `DGZ-Consulting/modelo-octatrico`),
ej. `DGZ-Consulting/crm-portal`. Te avisaré cuando lleguemos a ese punto.
