# Evanza Tablewares website

Custom WordPress theme (`wp/evanza/`) for Evanza Tablewares, plus a generated static preview. See `PLAN.md` for the spec and `DESIGN.md` for the design system and wireframes.

## Run locally

WordPress runs in WordPress Playground (no PHP or Docker needed).

```bash
npm install
npm run dev          # http://127.0.0.1:9400, auto-logged in as admin
```

The first boot downloads WordPress and takes a minute or two. `wp/blueprint.json` turns on `WP_DEBUG`, sets `/%postname%/` permalinks and activates the theme; activation creates the pages, menus and demo products. The site is rebuilt from scratch on every start, and theme edits show on reload (the theme folder is mounted live).

## Scripts

| Command | What it does |
|---|---|
| `npm run dev` | Start WordPress on port 9400 |
| `npm run vendor` | Copy three, GSAP, Lenis and the font from `node_modules` into the theme |
| `npm run images` | Convert `images/` into WebP files and monochrome logos under `wp/evanza/assets/img/` |
| `npm run mock-images` | Generate the transparent SVG mug renders for variants with no photo |
| `npm run export` | Crawl the running dev server into `static/` (GitHub Pages preview) |
| `npm run preview:static` | Serve `static/` at http://127.0.0.1:9500/evanza-tablewares/ |

## Publish the static preview

Live preview: https://aatmanbho2429.github.io/evanza-tablewares/

1. `npm run dev` (keep it running)
2. `npm run export` (rebuilds `static/` and fails if any referenced file is missing)
3. Optional: `npm run preview:static` and click through the site
4. Commit and push `static/` to `main`. `.github/workflows/pages.yml` deploys it.

On the static copy the contact form opens a prefilled email instead of posting to WordPress.
