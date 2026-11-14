# Evanza Tablewares — Website Development Plan (v2: full redesign)

Ceramic mug manufacturer, Morbi, Gujarat (since 2022). Live site today: https://evanzatablewares.com/ (WordPress on Hostinger — we do **not** touch it).

Goal: a **brand-new, unique** website that is aesthetic, minimal, 3D and animated, with five pages: **Home, About, Gallery, Collection, Contact**. **Collection is the most important page**: an enquiry catalogue that feels like a modern e‑commerce store (category and colour filters; Myntra‑style colour options per product).

> **v2 change:** the client wants a total revamp. Nothing visual carries over from the current site: **no teal brand colour, no cream/porcelain + serif look** (the v1 design system is scrapped). It must not look like a typical template or "AI-made" site. Architecture, tooling and the data layer from v1 stay.

---

## Decisions (agreed with the client)

| Topic | Decision |
|---|---|
| Real site | A custom **WordPress theme** in `wp/evanza/`. This is the source of truth and what goes to Hostinger. |
| Preview | `static/` is a **generated** HTML export of the running WordPress site, deployed to GitHub Pages. Never hand-edit `static/`. |
| Shopping | **Enquiry catalogue**, with no cart, prices or payments. Buyers build an **enquiry list** and send it via WhatsApp / email / contact form. |
| Catalogue PDFs | One **global** and one **per‑category** "Download catalogue" button. No PDFs yet, so the links are static and point to `assets/catalogues/catalogue-placeholder.pdf`. |
| Products | **Dummy data** (`wp/evanza/data/products.json`: 20 products, 8 categories), with real photos from `images/` where they fit. |
| Local WordPress | **WordPress Playground CLI** (`npm run dev` → http://127.0.0.1:9400). No PHP/Docker on this machine. |
| GitHub | Public repo `evanza-tablewares` on `Aatmanbho2429` → `https://aatmanbho2429.github.io/evanza-tablewares/`. The lead creates the repo and pushes. |

---

## Design direction: "Colour rooms"

**The idea.** Evanza's product *is* colour: the same mug in a dozen glazes. So the site takes its colour from the glazes, not from a brand accent. Each section is a **flat, full‑bleed colour field ("room") holding one object**, a real-time 3D Evanza mug, with **huge, active typography**. It's calm and minimal, but unmistakably this brand.

**The signature interaction: the glaze pour.** When a visitor picks a colour (home hero, collection colour filter, product swatch), the new colour **floods the room as a circle expanding from the exact swatch they pressed** (clip-path / circle reveal, ~700 ms, ease-out), and the 3D mug recolours in the same beat. This is the one memorable moment; it recurs on Home, Collection and Product so the site feels like one system.

**Depth through type.** In the hero, the headline is split into a back layer and a front layer with the WebGL canvas between them, so the mug sits *inside* the words.

### Palette (named tokens; no cream, no terracotta, no teal accent)
| Token | Hex | Use |
|---|---|---|
| Chalk | `#EFEFEC` | Base background (cool off-white) |
| Graphite | `#161616` | Text, UI, dark rooms |
| Slate | `#5C5C58` | Secondary text (AA on Chalk) |
| Pistachio | `#C9D8A6` | Glaze room |
| Butter | `#F1DC8B` | Glaze room |
| Powder | `#BCD3E6` | Glaze room |
| Blush | `#EEC3C9` | Glaze room |
| Bottle | `#1F4A42` | Dark glaze room (light text) |
| Midnight | `#1E2340` | Dark glaze room (light text) |

On product and collection pages the room colour is derived from the selected **variant hex**: field = `color-mix(in oklch, <variant> 35%, Chalk)` for a tone-on-tone look. Dark variants (luminance check in JS) flip the room to `data-tone="dark"` with light text. The logo is shown **monochrome** (Graphite or white). Generate single-colour logo assets; don't use the teal logo.

### Content voice (client feedback, v2.1)
Evanza is a **ceramic mug manufacturer for every use**, not a coffee brand. Copy must speak to the whole market: everyday mugs, tea and coffee, gifting, cafés and hotels (HoReCa), corporate and branded or custom-printed mugs, retailers, distributors and exporters. The old tagline "Crafted for coffee lovers." is **retired**: don't use it in the hero, site tagline, footer or meta. Coffee can appear only where it's specific to a product (e.g. latte mugs). Tone: plain, confident, manufacturer-to-buyer, with facts over adjectives (made in Morbi, glazes, capacities, MOQ, custom printing, bulk orders).

### Typography
- **One family: Bricolage Grotesque** (variable: `opsz`, `wdth`, `wght`), via `@fontsource-variable/bricolage-grotesque`, self-hosted. Headlines condensed (`wdth` ~75), heavy, very large, tight leading, and treated as a design element. Body uses the text optical size at regular weight, max ~70ch.
- Sentence case everywhere. Plain, warm, direct copy in Evanza's own voice (see Content voice).

### Do not (anti-generic list)
Cream background + serif + terracotta/teal accent · Instrument Serif / Geist / Inter / Space Grotesk · tracked ALL-CAPS eyebrows · monospace "data" labels · middle‑dot meta strings · hairline-rule broadsheet layouts · identical rounded cards with uniform soft shadows · gradient washes / glassmorphism · italic or coloured emphasis on one headline word · arrows on every link · fade-and-slide on every section · hover effects on everything · numbered markers (except the genuinely sequential making process) · the "big number + small label" stat hero.

### Motion budget
- **One orchestrated moment per page:** Home = hero entrance + 3D scroll story · Collection = glaze pour on colour filter + FLIP re-order · Product = glaze pour on swatch change · About = sticky process sequence · Gallery = drag-able shelves with momentum.
- Everything else moves **only in response to the user** (filter, open, drag), to show what changed. Curves and durations follow the `animate` / `emil-design-eng` skills (fast ease-out for UI, no bounce on enter, interruptible).
- `prefers-reduced-motion`: the glaze pour becomes an instant colour change, 3D shows a still pose, and smooth scroll is off.

### Skills the coding agent must use
Installed in `~/.claude/skills`; load them with the Skill tool if available, otherwise read `~/.agents/skills/<name>/SKILL.md`.
- `design-taste-frontend`: overall taste and finish gate (run its checker if provided). PLAN.md wins on concept, palette, type and 3D.
- `animate`, `choreograph`: hero entrance, glaze pour, FLIP, scroll story.
- `emil-design-eng`, `apple-design`: component polish, press states, drawer and sheet behaviour.
- `gestures`: gallery shelves drag, mobile filter sheet, product image swipe.
- `animation-performance`: 3D and scroll performance.
- `mobile-native`: mobile pass (100vh, tap highlight, safe areas).
- Online reference, Anthropic's `frontend-design` skill: write the token system and ASCII wireframes **before** building, review them against the brief, and remove one element before finishing. Its don'ts are folded into the list above.

---

## Repository layout

```
Evanza Tablewares/
├── PLAN.md  DESIGN.md (tokens + wireframes, written in Phase 1)  README.md
├── package.json                ← npm scripts (dev, vendor, images, mock-images, export, package)
├── images/                     ← original client photos (gitignored, read-only source)
├── wp/
│   ├── blueprint.json
│   └── evanza/                 ← THE WordPress theme (classic PHP, no bundler)
│       ├── front-page.php page-about.php page-gallery.php page-contact.php
│       ├── archive-evanza_product.php   ← Collection (/collection/)
│       ├── single-evanza_product.php    ← Product (/collection/<slug>/)
│       ├── inc/  data/products.json
│       └── assets/ css/ js/ img/ vendor/ fonts/ catalogues/
├── scripts/                    ← optimize-images, vendor, make-mock-images, export-static, package-theme
├── static/                     ← GENERATED export for GitHub Pages
└── .github/workflows/pages.yml
```

## Tech choices
- **Classic PHP theme, no bundler.** Modern CSS (custom properties, `color-mix`, container queries, `clamp()`), vanilla ES modules, and an `importmap` for `three`. Vendored libs live in `assets/vendor/` (`npm run vendor`), committed.
- **3D: one procedural mug module, `assets/js/mug3d.js`.** LatheGeometry body with real wall thickness and rolled rim, tube handle (classic / latte cone / heart / belly / espresso shapes from product `shape`), `MeshPhysicalMaterial` glaze with clearcoat, RoomEnvironment + PMREM lighting, soft contact shadow, and a **material state uniform** for the Home story: `bisque` (matte raw clay, subtle noise) → `glazing` (glaze rises from the base via a noise threshold) → `fired` (brief warm kiln glow) → `glazed`.
  API: `createMugViewer(canvas, { colors:{body,inner,handle}, shape, autoRotate, interactive, state })` → `{ setColors(c, {animate}), setShape(), setState(name, progress), dispose() }`. Pause the render loop when offscreen or the tab is hidden; cap DPR at 2; fall back to a still image without WebGL.
- **Motion:** GSAP + ScrollTrigger (scroll story, pinning), FLIP for grid re-order, Lenis smooth scroll (desktop only).
- **Data path:** CPT `evanza_product` + taxonomy `evanza_category`. `evanza_get_products()` prints inline JSON `<script type="application/json" id="evanza-products">`, so the JS needs no API and the static export captures it.
- **Product images:** each variant's **card image is a transparent studio render** (SVG from `scripts/make-mock-images.mjs`, restyled for v2: transparent background, soft realistic cylinder shading, inner-colour rim, matching the 3D mug's look) shown on a tone-on-tone tile. Real photos (when present) are the hover image and fill the product gallery.

## Product data shape
Unchanged from v1 (`categories[]`, `colorFamilies[]`, `catalogue`, `products[]` with `slug, name, category, badges, featured, shape, specs{capacity, material, finish, microwaveSafe, dishwasherSafe, moq}, description, variants[{slug, name, family, hex, inner, handle, images[]}]`). Every variant has at least one image.

---

## Page designs (v2)

**Header (all pages).** A thin bar with the monochrome logo, nav (Collection first, then About, Gallery, Contact) and an "Enquiry (n)" text button. It takes the tone of the room beneath it (light or dark), hides on scroll down, and shows on scroll up. Mobile: full-screen menu in Graphite with large type.

**Home**
1. *Hero room:* the 3D mug nested inside a giant split headline, with 5–6 glaze dots. Tapping a dot triggers the glaze pour and recolours the mug. Drag to spin. CTAs: "See the collection", "Download catalogue". Entrance: the type parts and the mug rises and settles (the one orchestrated load moment).
2. *Clay to cup (scroll story, pinned):* the mug drops to raw `bisque`, then glaze rises up it, the kiln glow fires, and it ends glazed. Three short lines of copy about Morbi clay and the factory. This is the only scroll-driven 3D.
3. *Find your mug:* a grid of category tiles (replaced the big-type "Shop by category" list in Phase 10); each goes to `/collection/?category=…`.
4. *(Removed in Phase 10 at the client's request: the "New in" rail.)*
5. *Made in Morbi:* a full-bleed factory photo, a short paragraph and the certification marks (ISO 9001:2015, CE, QSA) as a plain line.
6. *Catalogue + contact:* a Butter room with a large "Get the catalogue" button and WhatsApp / email links.

**Collection ⭐**
- Page top: a big title, a short line, the result count, and a **"Download full catalogue (PDF)"** button.
- **Glaze bar:** a row of glaze drops (one per colour family, with counts), the primary colour filter. Picking one pours that colour into the page header and filters (multi-select; a product matches when any variant matches).
- **Sticky filter bar:** Category (popover multi-select with counts), Colour (popover, same state as the glaze bar), Sort (Featured, Newest, Name A–Z), active-filter chips with ✕ and "Clear all". Mobile: a "Filter & sort (n)" button opens a bottom sheet with "Show N products".
- **URL sync:** `?category=a,b&color=x,y&sort=name`. Shareable; back/forward works.
- When exactly one category is selected: a **"Download <Category> catalogue (PDF)"** strip.
- **Product tiles:** a flat tone-on-tone square (field mixed from the shown variant), the transparent render centred, then name, capacity and colour dots below. Hovering or tapping a dot swaps the render *and* the tile colour (Myntra-style); "+N" after 5. When a colour filter is active the tile shows the matching variant. Pointer hover reveals the real photo if one exists. No shadows, no uniform rounded-card look. Links go to `/collection/<slug>/?colour=<variant>`.
- FLIP re-order on filter change; an empty state that invites clearing filters; a `<noscript>` server-rendered list.

**Product**
- Left: a tone-on-tone room with the **live 3D mug (default view)**, drag to rotate, and photo thumbnails below (tap → photo replaces 3D; a "3D" thumb returns to it). Mobile: swipe between 3D and photos.
- Right: breadcrumb, name, short description, **"Colour: Midnight Navy"** with swatches. A swatch triggers the glaze pour of the room, recolours the 3D mug, swaps the photos and updates `?colour=` (no reload). Then specs (capacity, material, finish, microwave/dishwasher safe, MOQ), **Enquire on WhatsApp** (prefilled product + colour + URL), **Add to enquiry list** (quantity), and **Download <Category> catalogue**.
- Related products rail (same category).
- **Enquiry list:** localStorage; header count; slide-in drawer with product, colour dot and editable quantity; "Send on WhatsApp", "Send by email" (mailto) and "Use contact form" (prefills Contact).

**About:** a full-bleed factory exterior photo with a short statement. **Making process** (sequential, so numbered 1–5 is fine): Clay → Forming → Glazing → Firing → Packing & dispatch, as a sticky photo stack that swaps per step on scroll, using the factory photos. Then certificates (lightbox) and the four MDs with tap-to-call / WhatsApp.

**Gallery:** three **shelves** (Products, Factory, Certificates), each a horizontal drag-able row with momentum (native scroll-snap on touch). Tapping opens an accessible lightbox (keyboard, swipe, captions).

**Contact:** a Blush room with a giant "Let's talk." (or similar), the form (name, company, email, phone, country, category of interest, message, honeypot) prefilled from the enquiry list, the four MD contacts, email, address, and an embedded Google Map.

---

## Phases & status

Each phase ends with a **git commit** (`Phase N: …`) and its **Verify** checks. Don't push. WP_DEBUG is on: zero PHP notices/warnings and zero JS console errors.

### Phase 0: Project setup & local WordPress ✅ done
Theme skeleton, Playground blueprint, idempotent activation routine (pages, front page, menus), `npm run dev`.

> **Status (latest):** Phases 0–4 are done and reviewed (commits `dba9600`, `2242e40`, `cdefb10`). Next: Phases 5–6. Open review items: (d) at 1440 px the mug still hides the "s" of "Ceramic mugs" on the back headline line; (e) check the white glaze for speckle/shadow-acne artifacts on the inner rim at full resolution; (f) run the `design-taste-frontend` finish gate, which hasn't been run yet.
>
> **Earlier status:** Phases 1 (v2) and 2 are done and the client approved the look. Lead review fixes carried into the Phase 3 batch: (a) on mobile (≤ 480 px) the mug covers the bottom of the back headline line, and there's a large empty gap under the headline; (b) the "Midnight" dot recolours the mug bright cobalt, so rename that glaze "Cobalt" (the room can stay midnight); (c) replace the coffee-specific hero headline, site tagline and any copy per *Content voice*.

### Phase 1 (REDO for v2): Design system, shell, signature prototype
- Write **`DESIGN.md`**: the tokens above (colour, type scale, spacing, radii, motion curves and durations), type specimen notes, and **ASCII wireframes for all 5 pages + product page**, reviewed against the brief and the "Do not" list.
- Replace the v1 CSS tokens, fonts (remove Instrument Serif + Geist; add Bricolage Grotesque) and component styles; restyle header, footer, mobile menu and 404 to v2. Monochrome logo assets via `optimize-images`.
- Build `mug3d.js` (shapes, glaze material, lighting, shadow, `setColors` with animated transition, drag-to-rotate, offscreen pause, fallback) and the **Home hero prototype**: split-type depth layering + glaze dots + glaze pour + entrance.
- Keep the v1 image pipeline, vendor script and catalogue placeholder.
- **Verify:** the hero runs smoothly; the glaze pour originates from the pressed dot; reduced-motion works; layout works at 375 / 768 / 1440 px; no v1 tokens or fonts remain (`grep` for `#0081a5`, `#f5f1ea`, `Instrument`, `Geist`).
- **⏸ Checkpoint:** stop here for lead review (screenshots of hero, header/footer, DESIGN.md) before Phase 2 is committed further.

### Phase 2: Product data layer (finish)
- CPT `evanza_product` (`has_archive` + rewrite slug `collection`), taxonomy `evanza_category`, seed on activation from `products.json` (idempotent), `evanza_get_products()` + inline JSON, catalogue URLs.
- Restyle `make-mock-images.mjs` renders for v2 (transparent background, refined shading; see Tech choices).
- **Verify:** admin lists 20 products in 8 categories; `/collection/` source has valid JSON; re-activation doesn't duplicate.

### Phase 3: Collection page ⭐
Build everything under *Page designs → Collection*.
**Verify:** filter combos give correct results and counts; URL state restores on reload and back/forward; glaze pour + FLIP feel smooth; mobile sheet works at 375 px; all controls have keyboard and screen-reader labels.

### Phase 4: Product page + enquiry list
Build everything under *Page designs → Product*, plus Customizer settings: WhatsApp number (default `919898798986`), enquiry email (default `evanzatableware@gmail.com`), global catalogue URL.
**Verify:** every product page renders; a swatch updates the room, 3D, photos and URL; the enquiry list persists across pages; the WhatsApp text is correct.

### Phase 5: Home page (rest)
Sections 2–6 of *Page designs → Home*, including the `bisque → glazing → fired → glazed` scroll story.
**Verify:** 60 fps on a laptop; the 3D pauses offscreen; reduced-motion gives a static sequence; Lighthouse performance ≥ 80 on desktop.

### Phase 6: About, Gallery, Contact
Per *Page designs*. The contact form uses an `admin-post.php` handler with nonce + sanitising + `wp_mail`, with success/error states.
**Verify:** the form handler succeeds in Playground; the lightbox and shelves work by keyboard and touch; 375 px layouts hold up.

### Phase 7: Static export → GitHub Pages
`scripts/export-static.mjs` (`npm run export`): crawl all pages and product URLs from the running dev server → `static/`; rewrite `http://127.0.0.1:9400` (and JSON-escaped `http:\/\/…`) to `/evanza-tablewares`; strip WP-only tags; inject `window.EVANZA_STATIC = true` (the contact form then falls back to a prefilled mailto); write `.nojekyll` and `404.html`. Add `npm run preview:static` (serves `static/` under `/evanza-tablewares/`) and `.github/workflows/pages.yml` (deploy `static/` on push to `main`).
**Verify:** the preview works fully with zero 404s. *(Lead: create the repo, push, enable Pages.)*

### Phase 8: WordPress admin editing + Hostinger package
Product meta boxes (specs; colour-variant repeater with name, family, body/inner/handle colour pickers, Media Library images, reorder), category term meta (catalogue PDF, cover), Customizer global catalogue PDF. Data from admin flows through `evanza_get_products()`. `npm run package` → `dist/evanza-theme.zip`. Write `DEPLOY-HOSTINGER.md` (backup, upload, activate, Customizer, SMTP plugin, replace demo products, go live).
**Verify:** a product added via admin with 2 colours appears on Collection with working swatches.

### Phase 9: Polish & QA
`srcset`/lazy images, three.js only on pages that use it, preload hero assets, SEO (titles, meta, OG, `Product` + `Organization` JSON-LD), accessibility (focus, contrast, alt, aria, skip link), `design-taste-frontend` finish gate, cross-browser and mobile pass, final export.

### Phase 10: Client bug list (header, footer, hero, home)
Nine items from the client's review. Design rules from DESIGN.md still hold (no teal, no serif, Bricolage only, one orchestrated moment per page, reduced motion respected). Motion values follow `design-taste-frontend` `references/motion.md` (hover 150ms in / 120ms out, press scale .985, drawn underlines, no gliding pill, no tilt). Check every item at 1440×900 and 375×812 (also 390×844 and 320 for overflow).

**A. Header (items 1, 7, 9)**: `header.php`, `assets/css/layout.css`, `assets/js/lib/header.js`
1. *Menu icon.* Replace the "Menu"/"Close" text in `.menu-btn` with a 44×44 icon button: two 2px bars (CSS spans, `currentColor`) that morph into an X when `aria-expanded="true"` (bars translate to centre, then rotate ±45deg; ~300ms, `--ease-out`; reduced motion: instant swap). Keep a visually hidden label ("Open menu" / "Close menu", flipped in JS) and `aria-expanded`. JS must stop writing `textContent` on the toggle.
2. *Header look.* Turn the full-width bar into a floating bar: inset by `--s-3` from the viewport edges and top, radius `--r-m`, still filled with the room colour (`--room`, as today). At the very top of the page it reads flat in the room (no visible edge); once scrolled past ~40px add `data-scrolled` and give it a 1px hairline of `--line` plus a soft shadow (blur 24px, ink 10% max), and height eases from `--header-h` to ~3.5rem. Keep hide-on-scroll-down / show-on-scroll-up. Enquiry becomes a small pill button with the count in a round badge (badge pops scale .8→1 when the count changes). Content below the header (hero padding, `.page-head`, sticky bars using `data-header-hidden`) must still clear it.
3. *Nav links.* Desktop links: on hover the label rolls up and an identical copy rolls in from below (wrap label in two stacked spans or `data-text` + `::after`, `overflow:hidden`, translateY, 300ms `--ease-out`), and a 1px underline draws left→right 4px under the word (`scaleX` on a pseudo-element, origin left in / right out). Current page: a still 2px bar under the word. Fine pointers only for hover; focus-visible shows the ring. Reduced motion: no roll, underline appears without travel.
4. *Mobile menu.* Opening: the Graphite sheet wipes down from the top (`clip-path: inset(0 0 100% 0)` → `inset(0)`, ~450ms `--ease-drawer`; closing reverses faster ~250ms). Do **not** use a circle reveal (the circle is the glaze pour, which stays the only signature). Links keep the staggered rise but each word rises inside an overflow mask (like the hero chars), with a small index number ("01"…"05") in `--t-small` beside each, and a tap/hover state that shifts the word 0.5rem right with an arrow appearing. Foot links unchanged.

**B. Footer (item 9)**: `footer.php`, `layout.css`
Make it a statement footer, still Graphite: (1) a 6px stripe of the six hero glaze colours across the very top edge (flat equal segments); (2) "Talk to the factory." stays as the big line, now with an arrow that slides 3px on hover and a drawn underline; (3) the four columns as now, links with the same drawn-underline hover as the nav; (4) a "Back to top" button (smooth scroll via Lenis if present, instant under reduced motion); (5) a giant full-width "Evanza" set in Bricolage 800 at ~75% stretch, sized with `vw` so it spans the container exactly, `aria-hidden`, colour `color-mix(chalk 12%)`, sitting at the very bottom under the copyright row and allowed to be cropped by the footer's bottom edge by ~15% of its height. Keep the monochrome logo image in the bottom row. No scroll-triggered animation.

**C. Display type spacing (item 8)**: `tokens.css`, `base.css`, `home.css`, `layout.css`, plus any other heavy condensed heading
Add tokens `--track-hero`, `--track-display`, `--track-head` and replace the hard-coded negative values (-0.03em, -0.025em, -0.022em, -0.015em) with them. Start at hero `-0.005em`, display `0`, headings `0.005em`, and widen the stretch of the heaviest styles from 75% to ~80%; raise the hero line-height a little if lines touch. Judge from screenshots at 1440 and 375; the goal is that letters in "Ceramic mugs for every table." and the mobile menu words no longer touch. After this, re-check the hero mug framing (`place()` in `home.js` reads line height) and that the hero still fits one screen.

**D. Hero headline reveal (item 5)**: `home.js`, `home.css`
Likely cause to confirm first: the second line "for every table." exists twice (in `.hero__title` and `.hero__front`); the back copy rises at ~90ms and the front copy rises over it at ~350ms, so the line appears to be already there and then a second one comes up from below. Fix so only one copy of each line is ever visible (hide the back title's second line, or keep both copies on the same timing so they move as one). Also confirm no text shows before the split runs (the `js-ready` 2500ms fallback in `inc/enqueue.php`, and the font-swap moment). Then make the reveal smoother: per-character rise with a slight blur/opacity lift (`translateY(105%)` → 0, opacity .0→1 over the first 40%, 800ms `cubic-bezier(0.22,1,0.36,1)`), stagger ~18ms per char and ~80ms per line, animating `transform`/`opacity` only (no `filter` if it costs frames on mobile). Reduced motion: text shows at once.

**E. Glaze pour smoothness (item 2)**: `assets/js/lib/glaze-pour.js`, `home.css`, `home.js`
The pour animates `clip-path: circle()`, which runs on the main thread and stutters while three.js renders. Rebuild the layers as a circle element (`border-radius:50%`, sized to the needed diameter, positioned at the origin) grown with `transform: scale()` from ~0 to 1, which is compositor-only. Keep the lighter leading wave but make it subtle (mix 15% white, 30ms lead). Lengthen to ~800ms fill with `cubic-bezier(0.65,0,0.35,1)`-style ease so it doesn't snap at the start. Text/tone flip: crossfade `color` over 300ms at ~45% instead of a hard switch, and make the header's colour change with the same duration so it doesn't lag or jump. The mug's `setColors` should use the same duration and start with the pour. Rapid clicks must stay interruptible (current commit-then-restart behaviour). This file is shared with Collection and Product: verify both still work.

**F. Mug load animation (item 3)**: `assets/js/mug3d.js` (`enter()`/`updateIntro()`), `home.js`
Make the entrance a small "glazing" moment on brand: the mug drops in from above as bisque (raw clay, `uGlaze` 0), lands with a soft squash-settle (scale y .96 → 1 with `easeOutBack`), spins a fast 1.5 turns that decays into the normal auto-rotate (no visible speed jump), and the glaze washes over it during the spin (`uGlaze` 0→1 over ~900ms, starting ~250ms after landing). Total ≤ 1.8s; it runs once, in step with the headline reveal (mug lands as the back line finishes). Add an `enter({ glaze: true })` option so Product pages keep their current entrance. Reduced motion: mug appears glazed and still. The story canvas must be unaffected.

**G. Mobile mug crop (item 4)**: `home.js` `place()`, `home.css` (≤40rem block), possibly `setFraming` in `mug3d.js`
Reproduce at 375×812, 390×844 and 360×740: find what cuts the mug's base (canvas bounds vs framing `offsetY`, the intro overshoot, or the `.hero__bar` overlapping the canvas). Fix so the whole mug, including its shadow and the intro's settle, sits inside the canvas with ~4% margin at every phone size, and the hero still fits one screen.

**H. Replace "Shop by category" (item 6)**: `front-page.php`, `home.css`, `assets/js/home-lists.js`
Remove the big-text list and its cursor-following float. Replace with a "Find your mug." grid of category tiles: each tile is a small colour room in its lead mug's glaze (`--c`, mixed light as `.cats__thumb` does now), the mug render large and centred, the category name in `--t-3` heavy and the count below. 4 columns × 2 rows at ≥64rem, 2 columns on phones, square-ish tiles, links to `/collection/?category=slug`. Hover (fine pointer): tile lifts 2px, render scales 1.03 inside its frame, name underline draws. No scroll fade-ins. Delete JS and CSS that only the old list used. The section stays one screen tall at 1440×900.

**Follow-ups (lead, after review):** removed the "New in" section (markup, CSS, `home-lists.js`; Home no longer loads `tiles.css`), and a reload now starts at the top of the page (inline script in `inc/enqueue.php`; back/forward still restore the position).

**Verify (agent):** at 1440×900 and 375×812 in the browser pane on `http://127.0.0.1:9400`: header at top and scrolled, menu icon morph and sheet, nav hover, footer, hero load (front the tab; reload several times), pour by clicking dots quickly, mug whole on phones, category grid, Collection and Product pour/swatches still working, reduced motion. No console errors. Don't run `npm run export`, commit or push.

### Phase 11: About photo and foldable layouts ✅ done
About hero photo is 3:2 below 56rem, so the building isn't cropped to a strip of wall. Every page was audited and fixed at 280×653, 344×882, 540×720, 673×841, 717×512, 768×1024, 853×1280 and 882×1104, plus 375×812 and 1440×900. The fixes are short-screen hero and menu blocks, a side-by-side story on wide, short screens, a 3-column Collection grid from 36rem, a two-column Product page on wide, short screens, and side-by-side About steps and Contact directors between 36rem and 56rem. Still open: at 320×640 the Home hero is taller than one screen.

---

## Working agreement for the coding agent
- Phases in order; commit after each; stop at checkpoints for lead review.
- Follow this plan. If something is wrong or impossible, stop and report rather than improvising a different architecture.
- Don't push, create repos, or touch the live site. If a command is denied by permissions, report it; don't work around it.
