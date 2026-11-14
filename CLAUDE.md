# CLAUDE.md: Evanza Tablewares website

A new website for **Evanza Tablewares**, a ceramic mug manufacturer in Morbi, Gujarat (since 2022). It's built as a **custom WordPress theme** for Hostinger, with a **static export** published to GitHub Pages for design review.

- Current live site (old, WordPress on Hostinger): https://evanzatablewares.com/. **Never touch it.** Going live is the client's step, following `DEPLOY-HOSTINGER.md`.
- Review preview: https://aatmanbho2429.github.io/evanza-tablewares/
- Repo: https://github.com/Aatmanbho2429/evanza-tablewares (public, branch `main`)

## Read these first
- `PLAN.md`: the spec. It covers decisions, page designs, the phase list and the status notes. Phases 0–9 are done.
- `DESIGN.md`: the design system ("Colour rooms"). It has the tokens, type scale, motion curves, ASCII wireframes, the Do-not list and the finish-gate waivers.
- `DEPLOY-HOSTINGER.md`: plain go-live steps for the client.
- `README.md`: commands.

## Architecture
- **`wp/evanza/`** is the WordPress theme and the **source of truth**. It's a classic PHP theme with **no bundler**: plain CSS files, vanilla ES modules and an importmap for `three`. Vendored libs live in `assets/vendor/` (three, lenis), copied by `npm run vendor`.
- **`static/`** is **generated** by `npm run export`, which crawls the running dev server. Never hand-edit it.
- **Products:** CPT `evanza_product` (archive and rewrite slug `collection`) plus taxonomy `evanza_category`.
  - **Data flow:** `inc/products.php` `evanza_get_products()` prints inline JSON (`<script id="evanza-products">`). Collection, Product and Home JS read it, so there's no REST API.
  - **Demo data:** `data/products.json` (20 mugs, 8 categories), seeded on theme activation by `inc/seed.php` and flagged `_evanza_demo`.
  - **Admin editing:** `inc/admin-products.php` (meta boxes and colour-variant repeater).
  - **Tile renders:** `inc/render.php` (PHP SVG render) and `scripts/make-mock-images.mjs` (JS); the two must stay in sync.
- **3D:** `assets/js/mug3d.js` is the procedural three.js mug (shapes classic, latte, heart, belly, espresso; material states bisque → glazing → fired → glazed). It's used by the Home hero, the Home scroll story (`story.js`) and Product pages. three.js loads **only** on Home and Product pages.
- **Signature interaction:** `assets/js/lib/glaze-pour.js` is a circle colour flood from the pressed swatch. It's used on the Home hero, the Collection colour filter and Product swatches.
- **Enquiry list** (stands in for a cart; it's an enquiry catalogue with no prices or checkout): `lib/enquiry.js` stores it in localStorage and sends it via WhatsApp, email or the contact form.
- **Contact form:** `inc/contact.php` is an `admin-post.php` handler (nonce, honeypot, rate limit, `wp_mail`). It also saves every enquiry to the admin **Enquiries** screen. On the static site the export swaps form submit for a prefilled `mailto:`.
- **Settings:** in the Customizer (`inc/customizer.php`): WhatsApp number, enquiry email, global catalogue PDF. Business details (directors, phones, address) are in `inc/business.php`.
- **Theme version:** in `style.css` (currently 1.2.0). Bump it when an upgrade routine must re-run on existing installs (`inc/setup.php`).

## Commands
```bash
npm run dev            # WordPress Playground on http://127.0.0.1:9400 (no PHP/Docker needed)
npm run export         # dev server must be running → regenerates static/, fails on missing files
npm run preview:static # http://127.0.0.1:9500/evanza-tablewares/
npm run package        # dist/evanza-theme.zip for Hostinger (dist/ is gitignored)
npm run images         # images/ originals → optimized WebP in the theme (images/ is gitignored)
npm run mock-images    # regenerate SVG tile renders from products.json
npm run vendor         # copy three + lenis + font into the theme
```
`.claude/launch.json` has `evanza-wp` (9400) and `evanza-static` (9500) for the browser pane's `preview_start`.

## Gotchas
- **Always use `http://127.0.0.1:9400`, never `localhost:9400`.** WordPress serves assets from 127.0.0.1; on localhost the module scripts and font are blocked by CORS and pages look empty.
- **Playground resets on every start.** Admin edits and uploads are lost when the server stops; theme file edits show on reload (the folder is mounted live).
- **Local admin login:** http://127.0.0.1:9400/wp-admin/ with username `admin`, password `password` (Playground's local-only default). The browser is not auto-logged in.
- `wp_mail` returns false in Playground (no mail transport). That's expected; on Hostinger use an SMTP plugin.
- The export rewrites `http://127.0.0.1:9400` to `/evanza-tablewares`. Share tags, JSON-LD and WhatsApp links get the absolute Pages URL. It also downloads Media Library photos from `wp-content/uploads/`.
- Browser-pane screenshots of a **background tab** can show stale frames (e.g. the hero looks mid-animation). Front the tab before judging visuals.

## Design rules (see DESIGN.md for detail)
- **Concept "Colour rooms":** flat full-bleed colour fields taken from the glazes, one 3D mug per scene, and huge type, with the **glaze pour** as the single signature moment.
- **Palette:** Chalk `#EFEFEC`, Graphite `#161616`, Slate `#5C5C58`, plus the glaze rooms Pistachio, Butter, Powder, Blush, Bottle and Midnight. The logo is shown **monochrome**.
- **The client rejected the old look.** Don't reintroduce the brand teal `#0081A5`, cream/porcelain backgrounds, serif display fonts, Instrument Serif, Geist or Inter.
- **Type:** one family, **Bricolage Grotesque** (variable). Headlines are condensed and heavy; sentence case.
- **Motion:** one orchestrated moment per page; everything else moves only in response to the user. Respect `prefers-reduced-motion`.
- **Content voice:** Evanza makes mugs **for every use** (everyday, tea and coffee, gifting, cafés/hotels, corporate/custom printing, retail, bulk). The old tagline "Crafted for coffee lovers." is **retired**. Current tagline: "Ceramic mugs for every table."
- Installed design skills to use for UI work: `design-taste-frontend` (it has a finish-gate checker), `animate`, `choreograph`, `emil-design-eng`, `apple-design`, `gestures`, `animation-performance`, `mobile-native`.

## How the user likes to work
- The lead session plans and reviews; **one Sonnet subagent (high effort) writes the code** in phase batches. After each batch the lead reviews in the browser at 1440 px and 375 px, then the user approves before the next batch.
- The user reviews the design via the GitHub Pages preview. **Publishing is on request**: run `npm run export`, check it with `preview:static`, then commit `static/` and push to `main` (`.github/workflows/pages.yml` deploys). Ask before pushing.
- Commit after each phase; don't push or create repos without asking.

## Open items (as of 2026-11-15)
- A Lighthouse run was blocked by permissions; the client should run it in Chrome DevTools on the preview.
- Real product photos, real catalogue PDFs and real stats/copy from the client are still pending (all PDF buttons point to `assets/catalogues/catalogue-placeholder.pdf`).
- Hostinger go-live (theme upload, SMTP, swapping demo products for real ones) is the client's step, using `DEPLOY-HOSTINGER.md` and `dist/evanza-theme.zip`.
- Untested on real devices: the tile-picture upload, contact email delivery on Hostinger, and touch swipe on phones.
