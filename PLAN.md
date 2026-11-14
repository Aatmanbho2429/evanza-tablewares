# Evanza Tablewares — Website Development Plan

Ceramic mug manufacturer, Morbi, Gujarat (since 2022). Live site today: https://evanzatablewares.com/ (WordPress on Hostinger — we do **not** touch it).

Goal: an aesthetic, minimal website with 3D and motion. Five pages: **Home, About, Gallery, Collection, Contact**. **Collection is the most important page**: an enquiry catalogue that feels like a modern e‑commerce store (Myntra‑style filters and colour swatches).

---

## Decisions (agreed with the client)

| Topic | Decision |
|---|---|
| Real site | A custom **WordPress theme** in `wp/evanza/`. This is the source of truth and what goes to Hostinger. |
| Preview | `static/` is a **generated** HTML export of the running WordPress site, deployed to GitHub Pages. Never hand-edit `static/`. |
| Shopping | **Enquiry catalogue**, with no cart, prices or payments. Buyers build an **enquiry list** and send it via WhatsApp / email / contact form. |
| Catalogue PDFs | One **global** "Download catalogue" button and a **per‑category** button. No PDFs exist yet, so the links are static and point to one placeholder PDF. |
| Products | **Dummy data** for now (seeded from `wp/evanza/data/products.json`), with real photos from `images/` where they fit. |
| Local WordPress | **WordPress Playground CLI** (`@wp-playground/cli`, already a devDependency). No PHP/Docker on this machine. |
| GitHub | Public repo `evanza-tablewares` on account `Aatmanbho2429`. Pages URL: `https://aatmanbho2429.github.io/evanza-tablewares/`. The lead (not the coding agent) creates the repo and pushes. |

## Repository layout

```
Evanza Tablewares/
├── PLAN.md                     ← this file
├── package.json                ← npm scripts (dev, vendor, images, export, package)
├── images/                     ← original client photos (gitignored, read-only source)
├── wp/
│   ├── blueprint.json          ← Playground blueprint (activate theme, permalinks)
│   └── evanza/                 ← THE WordPress theme (classic PHP theme, no build step)
│       ├── style.css  functions.php  header.php  footer.php  index.php  404.php
│       ├── front-page.php  page-about.php  page-gallery.php  page-contact.php
│       ├── archive-evanza_product.php   ← Collection page (/collection/)
│       ├── single-evanza_product.php    ← Product page (/collection/<slug>/)
│       ├── inc/                 ← cpt.php, seed.php, products.php, enqueue.php, customizer.php, contact.php …
│       ├── data/products.json   ← dummy product seed
│       └── assets/ css/ js/ img/ vendor/ fonts/ catalogues/
├── scripts/                    ← optimize-images, vendor copy, mock images, export-static, package-theme
├── static/                     ← GENERATED export for GitHub Pages (+ .nojekyll)
└── .github/workflows/pages.yml ← deploys static/ to GitHub Pages
```

## Tech choices

- **Classic PHP theme, no bundler.** Plain modern CSS (custom properties, container queries, `clamp()`), vanilla ES modules, and an `<script type="importmap">` for `three`. Vendored libs are copied from `node_modules` into `assets/vendor/` by `npm run vendor` and committed, so the theme zip is self-contained for Hostinger.
- **3D:** Three.js. A **procedural ceramic mug** (LatheGeometry body with real wall thickness and rim, tube handle, `MeshPhysicalMaterial` with clearcoat glaze, RoomEnvironment lighting, soft contact shadow), built once as a reusable module `assets/js/mug3d.js`:
  `createMugViewer(canvas, { body, inner, handle, shape: 'classic'|'latte'|'heart', autoRotate, interactive })` → `{ setColors(), setShape(), dispose() }`.
  The same module powers the Home hero and the Product page, where it recolours live when a swatch is picked.
- **Motion:** GSAP + ScrollTrigger (scroll reveals, pinned sections, counters), FLIP for grid re-ordering on filter, Lenis for smooth scroll (optional, off on touch). Everything respects `prefers-reduced-motion`, and 3D falls back to a still image when WebGL is unavailable.
- **Product data path:** CPT `evanza_product` + taxonomy `evanza_category`. PHP helper `evanza_get_products()` returns the exact JSON shape below and prints it inline as `<script type="application/json" id="evanza-products">`, so the JS needs no API and the static export captures it automatically.
- **Design direction:** warm "porcelain" neutrals (off-white `#F5F1EA`-ish background, near-black ink), logo teal **`#0081A5`** as the single accent used sparingly, large elegant serif display type paired with a clean sans, generous whitespace, soft shadows, rounded corners, real photography. Logos: `Evanza-LOGO-1.png` (dark text, light backgrounds), `white-logo.png` (dark backgrounds), `cropped-white-1-1.png` (mark / favicon).

## Product data shape (`products.json` and `evanza_get_products()`)

```json
{
  "categories": [
    { "slug": "latte-mugs", "name": "Latte Mugs", "description": "…", "cover": "assets/img/…", "catalogue": "assets/catalogues/catalogue-placeholder.pdf" }
  ],
  "colorFamilies": [ { "slug": "teal", "name": "Teal", "hex": "#1f6f6a" } ],
  "catalogue": "assets/catalogues/catalogue-placeholder.pdf",
  "products": [
    {
      "slug": "aura-latte-mug",
      "name": "Aura Latte Mug",
      "category": "latte-mugs",
      "badges": ["new"],
      "featured": true,
      "shape": "latte",
      "specs": { "capacity": "350 ml", "material": "Stoneware", "finish": "Glossy", "microwaveSafe": true, "dishwasherSafe": true, "moq": "500 pcs" },
      "description": "…",
      "variants": [
        { "slug": "navy", "name": "Midnight Navy", "family": "navy", "hex": "#1d2346", "inner": "#1d2346", "handle": "#1d2346", "images": ["assets/img/products/016A1265-1600.webp"] }
      ]
    }
  ]
}
```

Dummy data: **~20 products across 6–8 categories, 2–6 colours each.** Suggested categories from the photos: Classic Mugs, Latte Mugs, Two‑Tone Mugs, Heart Handle Mugs, Marble & Gradient, Printed / Sublimation, Espresso & Small Cups, Gift Sets. Colour families: White, Black, Grey, Red, Pink, Orange, Yellow, Green, Teal, Blue, Navy, Purple, Brown, Multi.
Variants with a matching real photo use it. For the rest, `scripts/make-mock-images.mjs` generates a clean studio-style **SVG mug tinted with the variant colours** (body / inner / handle) and writes its path into `images[]`. Every variant always has at least one image.

---

## Phases

Each phase ends with a **git commit** (`Phase N: …`) and its **Verify** checks passing. Don't push. WordPress runs with `WP_DEBUG` on, and pages must render with zero PHP notices, warnings or JS console errors.

### Phase 0: Project setup & local WordPress
- `wp/evanza/` theme skeleton (`style.css` header, `functions.php`, `index.php`, `header.php`, `footer.php`).
- `wp/blueprint.json`: activate theme `evanza`, set permalinks `/%postname%/`, and set WP_DEBUG.
- npm script `dev`: `wp-playground-cli server --port=9400 --mount=./wp/evanza:/wordpress/wp-content/themes/evanza --blueprint=./wp/blueprint.json --login` (adjust flags as needed; document in README).
- Theme **activation routine** (`after_switch_theme`, idempotent): creates pages Home / About / Gallery / Contact, sets Home as static front page, and creates the primary + footer menus. It runs the same way on Playground and on Hostinger.
- **Verify:** `npm run dev` boots; `/`, `/about/`, `/gallery/`, `/contact/` return 200 with the theme active.

### Phase 1: Design system, layout shell, image pipeline
- `scripts/optimize-images.mjs` (use `sharp`): converts the needed photos from `images/` → `wp/evanza/assets/img/{products,factory,certificates,brand}/` as WebP at 800 and 1600 widths. Skip `eff1_5.gif` (58 MB) and the decorative `Shap-*`, `bheja-1`, `overlay_dark_480` files.
- `scripts/vendor.mjs` → copies three (module build + needed `examples/jsm` files), gsap + ScrollTrigger, and lenis into `assets/vendor/`.
- `assets/css/`: tokens (colour, type scale, spacing, radius, shadow, easing), reset, typography, buttons, form fields, chips, utility classes. Self-hosted or Google fonts.
- Header: logo, nav (Home, About, Gallery, Collection, Contact), enquiry-list icon with count badge, transparent over hero → solid on scroll, accessible mobile menu.
- Footer: logo, short brand line, quick links, the 4 MD contacts, email, address, "Download catalogue" link, ©.
- Global JS: reveal-on-scroll helper, reduced-motion guard, page-enter fade.
- **Verify:** every page shows the header/footer; the mobile menu opens and closes with the keyboard; the layout works at 375 px and 1440 px widths.

### Phase 2: Product data layer
- Register CPT `evanza_product` (`has_archive` + rewrite slug `collection`) and taxonomy `evanza_category`.
- `data/products.json` dummy data per the shape above, plus `scripts/make-mock-images.mjs`.
- `inc/seed.php`: on activation, if there are no products, imports `products.json` (posts, terms, specs and variants into post meta, `menu_order`).
- `inc/products.php`: `evanza_get_products()`, `evanza_get_product($id)`, and the inline-JSON printer. Asset paths resolve to full theme URLs.
- Placeholder `assets/catalogues/catalogue-placeholder.pdf` (a 1‑page "Catalogue coming soon" PDF); global and per‑category catalogue URLs come from the data and can be swapped later.
- **Verify:** WP admin lists ~20 products in 6–8 categories; `/collection/` source contains valid product JSON; the seed doesn't duplicate on re-activation.

### Phase 3: Collection page ⭐ (most important)
- Page header: title, short intro, **"Download full catalogue (PDF)"** button, result count.
- **Filters:** Category (checkboxes with counts), Colour (round swatch chips with counts; one product matches when **any** variant matches; multi-select). **Sort:** Featured, Newest, Name A–Z. Active-filter chips with ✕ and "Clear all". Counts update for the current selection.
- **URL sync:** `?category=latte-mugs,two-tone-mugs&color=teal,black&sort=name`. Shareable; back/forward works.
- Desktop: sticky left filter sidebar. Mobile: "Filter & Sort" bar that opens a bottom sheet with an "Apply (N)" button.
- **Product cards:** image (hover → second image / gentle zoom; subtle 3D tilt on pointer), name, category, capacity, badge, **colour dots** (hover/tap a dot swaps the card image to that colour, Myntra-style; "+N" when more than 5). When a colour filter is active, the card shows the matching variant first. The card links to `/collection/<slug>/?colour=<variant>`.
- **Per-category catalogue:** when exactly one category is selected, show a banner "Download <Category> catalogue (PDF)". Also give each category a card/row in a "Shop by category" strip at the top.
- Animations: staggered reveal on load; FLIP re-order on filter change; skeleton shimmer while images load. Empty state with "Clear filters".
- `<noscript>` fallback: a simple server-rendered product list.
- **Verify:** filter combos produce correct counts/results; reload with a query string restores the state; the mobile sheet works at 375 px; keyboard and screen-reader labels are present on all controls.

### Phase 4: Product page + enquiry list
- `/collection/<slug>/`: breadcrumb; image gallery (thumbnails, main image, hover zoom, swipe on mobile); **"View in 3D" toggle** using `mug3d.js` (drag to rotate, recolours with the selected swatch, uses the product `shape`).
- **Colour selector** ("Colour: Midnight Navy"): swatches swap the gallery images and 3D colours and update `?colour=` without reload.
- Specs table, description, badges.
- CTAs: **Enquire on WhatsApp** (`https://wa.me/<number>?text=` prefilled with product, colour and page URL), **Add to enquiry list** (with quantity), **Download <Category> catalogue (PDF)**.
- **Enquiry list:** localStorage; header badge; slide-in drawer listing product, colour swatch and editable quantity; "Send via WhatsApp" (builds one message), "Send via email" (mailto), "Go to contact form" (prefills the Contact message).
- Related products (same category) carousel.
- Customizer settings (`inc/customizer.php`): WhatsApp number (default `919898798986`), enquiry email (default `evanzatableware@gmail.com`), global catalogue URL.
- **Verify:** every product page renders; the swatch updates images, 3D and URL; the enquiry list persists across pages; the WhatsApp message text is correct.

### Phase 5: Home page
- **3D hero:** procedural mug, slow auto-rotate, pointer parallax, soft steam wisps, and a colour that cycles through 4–5 brand glazes (or the user clicks colour dots). Headline "Crafted for coffee lovers." with a short sub-line and CTAs "Explore collection" / "Download catalogue".
- Category marquee / "Shop by category" grid → links to `/collection/?category=…`.
- Featured products (cards reused from Collection).
- "Made in Morbi" story strip with a factory photo + animated stats (e.g. founded 2022, capacity, colours, countries; use clearly-placeholder numbers the client can edit).
- Interactive "Pick your glaze" colour section: click a colour family → shows matching products.
- Certifications strip (ISO 9001:2015, CE, QSA) and a contact CTA.
- **Verify:** the hero runs at 60 fps on a laptop; it pauses when offscreen; reduced-motion shows a still image; Lighthouse performance ≥ 80 on desktop.

### Phase 6: About, Gallery, Contact
- **About:** story (since 2022, Morbi), mission/values, a scroll-driven **process timeline** (Clay → Forming → Glazing → Firing → Quality check → Packing → Dispatch) using the factory WhatsApp photos, factory exterior image, certificates with lightbox, team / MD contacts.
- **Gallery:** filter tabs (All / Products / Factory / Certificates), masonry grid, reveal animation, accessible lightbox (keyboard, swipe, captions).
- **Contact:** form (name, company, email, phone, country, interested category, message, honeypot) → `admin-post.php` handler with nonce + sanitising + `wp_mail` to the enquiry email, with success/error states; prefilled from the enquiry list. Contact cards (4 MDs with tap-to-call + WhatsApp), email, address, embedded Google Map of the factory address.
- **Verify:** the form submits on WP (in Playground, check the handler returns success; real mail delivery is a Hostinger concern); the lightbox works by keyboard; pages work at 375 px.

### Phase 7: Static export → GitHub Pages
- `scripts/export-static.mjs` (`npm run export`): with the dev server running, crawl the site (all pages, the collection, every product URL, 404), save to `static/**/index.html`, copy the theme assets, rewrite `http://127.0.0.1:9400` (including the JSON-escaped `http:\/\/…` form) to the base path `/evanza-tablewares`, strip WP-only tags (wp-json links, RSD, emoji, admin bar), inject `window.EVANZA_STATIC = true`, write `static/.nojekyll` and `static/404.html`. Product query params like `?colour=` keep working.
- In static mode the contact form shows "Preview site: send via email/WhatsApp" and falls back to a prefilled mailto.
- `npm run preview:static`: a tiny local server that serves `static/` under `/evanza-tablewares/` for testing.
- `.github/workflows/pages.yml`: on push to `main`, upload `static/` as the Pages artifact and deploy.
- **Verify:** `preview:static` shows the whole site with working filters, swatches, 3D, gallery and navigation; there are zero 404s in the network tab.
- *(Lead: create the public repo, push, enable Pages with the workflow source.)*

### Phase 8: WordPress admin editing + Hostinger package
- Product edit screen meta boxes: **Specs** fields and a **Colour variants repeater** (name, colour family select, body/inner/handle colour pickers, images from the Media Library, drag to reorder). Category term meta: **Catalogue PDF** (media picker) and cover image. Customizer: global catalogue PDF.
- Images uploaded in WP admin flow into `evanza_get_products()` (attachment URLs), so client edits appear on the site with no code.
- `scripts/package-theme.mjs` (`npm run package`) → `dist/evanza-theme.zip`.
- `DEPLOY-HOSTINGER.md`: back up the current site, upload the theme zip, activate it (auto-creates pages and demo products), set the Customizer values, use an SMTP plugin for reliable mail, replace demo products, and switch DNS/live only when ready.
- **Verify:** in Playground, add a new product with 2 colours via admin; it appears on Collection with working swatches.

### Phase 9: Polish & QA
- Performance: lazy images with `srcset`/`sizes`, preload hero assets, three.js loaded only on pages that use it, fonts `display=swap`.
- SEO: titles/meta descriptions, Open Graph, `Product` + `Organization` JSON-LD, sitemap via WP core.
- Accessibility: focus styles, contrast, alt text, aria on filters/drawer/lightbox, skip link.
- Cross-browser check (Chrome, Safari, mobile Safari sizes). Final `npm run export`.

---

## Working agreement for the coding agent
- Work phase by phase, in order, and commit after each phase.
- Match this plan; if something in it is wrong or impossible, stop and report instead of improvising a different architecture.
- Don't push, create GitHub repos, or touch the live site.
- If a command is blocked by permissions, report it; don't work around it.
