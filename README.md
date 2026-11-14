# Evanza Tablewares website

Custom WordPress theme (`wp/evanza/`) for Evanza Tablewares, plus a generated static preview. See `PLAN.md` for the full spec.

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
| `npm run vendor` | Copy three, GSAP, Lenis and fonts from `node_modules` into the theme |
| `npm run images` | Convert `images/` into WebP files under `wp/evanza/assets/img/` |
| `npm run mock-images` | Generate the tinted SVG mugs for variants with no photo |
