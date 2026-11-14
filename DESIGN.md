# Evanza Tablewares: design system (v2, "Colour rooms")

Written before building, reviewed against `PLAN.md` and its "Do not" list. If a screen needs something this file does not allow, change this file first.

## 1. The idea in one paragraph

Evanza's product is colour: one mug, a dozen glazes. So each section is a flat, full-bleed **room** in a glaze colour holding **one object** (a real-time 3D mug, a render, a photograph) and very large condensed type. The one signature move is the **glaze pour**: pick a colour and it floods the room as a circle growing from the dot you pressed, while the mug recolours in the same beat. Everything else is still unless the user touches it.

## 2. Tokens

### Colour
| Token | Hex | Role |
|---|---|---|
| `--chalk` | `#EFEFEC` | Base room, light text on dark rooms |
| `--graphite` | `#161616` | Text, UI, dark rooms |
| `--slate` | `#5C5C58` | Secondary text on Chalk (7.0:1) |
| `--pistachio` | `#C9D8A6` | Glaze room |
| `--butter` | `#F1DC8B` | Glaze room |
| `--powder` | `#BCD3E6` | Glaze room |
| `--blush` | `#EEC3C9` | Glaze room |
| `--bottle` | `#1F4A42` | Dark glaze room (Chalk text) |
| `--midnight` | `#1E2340` | Dark glaze room (Chalk text) |

A room sets two custom properties and everything inside follows them, so components never hard-code a colour:

- `--room`: the field colour. `--ink`: text and UI colour. `--ink-2`: secondary text.
- `data-tone="light"` (default) gives `--ink: Graphite`, `--ink-2: Slate`. `data-tone="dark"` gives `--ink: Chalk`, `--ink-2: Chalk at 70%`.
- Product and collection rooms derive the field from the variant: `color-mix(in oklch, <variant hex> 35%, var(--chalk))`; JS picks the tone from the field's luminance.

Contrast: Graphite on every light glaze is 12:1 or better. Slate on Pistachio and Powder is below 4.5:1, so on glaze rooms secondary text uses Graphite at 78% (`--ink-2` is recomputed by `color-mix` per room, never Slate).

No gradients anywhere, no shadows on tiles. The only shadow is the 3D mug's soft contact shadow.

### Type (one family: Bricolage Grotesque variable, `opsz` `wdth` `wght`)
| Token | Size | Settings | Use |
|---|---|---|---|
| `--t-hero` | `clamp(3.6rem, 14.6vw, 16rem)` | wdth 75, wght 800, lh .84, tracking -0.035em | Home hero headline |
| `--t-1` | `clamp(3rem, 9vw, 9rem)` | wdth 75, wght 800, lh .88, -0.03em | Page titles, big list rows |
| `--t-2` | `clamp(2.25rem, 5.4vw, 5rem)` | wdth 80, wght 750, lh .94, -0.025em | Section titles |
| `--t-3` | `clamp(1.5rem, 2.4vw, 2.25rem)` | wdth 90, wght 650, lh 1.05, -0.015em | Product names, lead lines |
| `--t-body` | `1.0625rem` / 1.55 | wdth 100, wght 400, opsz auto, max 66ch | Copy |
| `--t-small` | `0.875rem` / 1.45 | wdth 100, wght 500 | Meta, buttons, chips |

Sentence case everywhere. No tracked caps, no monospace, no italic emphasis, no coloured word in a headline. Hierarchy comes from size and width, not decoration.

### Space, shape, motion
- Space scale (4px base): 4, 8, 12, 16, 24, 32, 48, 72, 112, 176. Page gutter `clamp(1.25rem, 0.5rem + 3.2vw, 3rem)`; content max 1440px, equal left and right edges.
- Radii: 6px (inputs, chips), 14px (image tiles, sheets), 999px (dots only). Buttons are 8px, not pills.
- Easing: `--ease-out: cubic-bezier(.23,1,.32,1)` (UI and entrances), `--ease-in-out: cubic-bezier(.77,0,.175,1)` (on-screen movement), `--ease-drawer: cubic-bezier(.32,.72,0,1)` (sheets and drawers).
- Durations: press 140ms, hover/colour 200ms, popover 220ms, drawer/sheet 450ms, glaze pour 650ms (leading wave 520ms), hero entrance 1.1s total.
- Hover styles only inside `(hover: hover) and (pointer: fine)`. Press: `scale(.97)` on pointer down. Reduced motion: pour becomes an instant colour change, 3D is a still pose, no smooth scroll.

### Components
- **Button**: 52px tall, 8px radius, `--t-small` at wght 600, wdth 90. Primary = fill `--ink` with text `--room`; secondary = 1.5px `--ink` outline. They invert automatically on dark rooms. Hover lightens the fill 12%; press scales.
- **Link**: underlined (2px, 0.18em offset), underline thickens on hover. No arrows.
- **Glaze dot**: 44px tap area, 28px coloured dot, selected = 2px ring in `--ink` with 3px gap. One component used on Home, Collection and Product.
- **Chip**: 6px radius, 1.5px outline, selected = filled `--ink`.
- **Input**: 14px label above (sentence case), 52px field, 6px radius, 1.5px border at 30% ink, focus 2px ink ring.
- **Focus**: 2px `--ink` outline, 3px offset, always visible on keyboard.

## 3. The signature: glaze pour
1. Press a dot. Record its centre (x, y) relative to the room.
2. A *wave* layer (new colour mixed 22% with white) and a *colour* layer (the new colour) are inserted above the room background. Both use `clip-path: circle(0 at x y)` and animate to a circle covering the farthest room corner via WAAPI: wave 520ms, colour 650ms delayed 70ms, both `--ease-out`. The lighter leading edge reads as liquid.
3. At 40% of the pour the tone flips (text colour transitions over 200ms) and the 3D mug starts its recolour (colour lerp over 600ms).
4. On finish the layers are removed and the new colour becomes the room. Pressing another dot mid-pour commits the running pour first, then starts the next (interruptible).

## 4. Hero depth
Three stacked layers in one room: the real `h1` (back, "Crafted for" and "coffee lovers."), the WebGL canvas (the mug, transparent), and a clone of the second line in front (`aria-hidden`, `pointer-events: none`). The mug stands between the lines: its rim sits over the first line, its base is covered by the second. Characters are split into masked spans for the entrance.

Entrance (one orchestrated moment): back line chars rise (700ms, 22ms apart) → mug rises from below and settles (1.1s, starts 120ms in) → front line rises (starts 260ms in) → sub-line, buttons, then dots fade up (starts 700ms, 60ms apart).

## 5. Wireframes

### Header (all pages)
```
Desktop (taking the tone of the room under it; hides on scroll down, shows on scroll up)
+--------------------------------------------------------------------------------+
| [evanza]                      Collection   About   Gallery   Contact  Enquiry (0)|
+--------------------------------------------------------------------------------+
Mobile
+----------------------------------+      Menu open: full-screen Graphite
| [evanza]            Enquiry (0) Menu|     +----------------------------------+
+----------------------------------+      | [evanza]                    Close |
                                           |                                  |
                                           | Collection                       |
                                           | About                            |
                                           | Gallery                          |
                                           | Contact                          |
                                           |                                  |
                                           | WhatsApp   email                 |
                                           +----------------------------------+
```

### Home (Pistachio hero, tone follows the glaze)
```
+--------------------------------------------------------------------------------+
| [evanza]                      Collection   About   Gallery   Contact  Enquiry (0)|
|                                                                                |
|  Crafted for                      <- back layer, 14.6vw, wdth 75               |
|              .--------.                                                        |
|             (  MUG 3D  )--.       <- canvas between the layers, drag to spin   |
|  coffee lovers.'--------' |       <- front layer overlaps the mug base         |
|                                                                                |
|  Ceramic mugs made in Morbi,        [See the collection] [Download catalogue]  |
|  in every glaze.                                            o o o o o o  Pista.|
+--------------------------------------------------------------------------------+
2 Clay to cup (pinned scroll story, Phase 5)   3 Shop by category (big type list)
4 New in (drag rail)   5 Made in Morbi (photo + certificates line)   6 Butter catalogue room
```

### Collection (Phase 3)
```
+--------------------------------------------------------------------------------+
|  Mugs, by the case.                      [Download full catalogue (PDF)]       |
|  Browse 20 mugs in 14 colours.            20 mugs                              |
|  o o o o o o o o o o o o o o   <- glaze bar: pours colour into this header     |
+--------------------------------------------------------------------------------+
| Category v   Colour v   Sort: Featured v    [Teal x] [Clear all]     (sticky)   |
+--------------------------------------------------------------------------------+
| +--------+  +--------+  +--------+  +--------+   tile = flat tone-on-tone      |
| | render |  | render |  | render |  | render |   square, render centred,        |
| +--------+  +--------+  +--------+  +--------+   no shadow, no card chrome      |
| Name        Name        Name        Name                                       |
| 330 ml      350 ml      ...                                                    |
| o o o +2    o o o       (dot hover swaps render and tile colour)               |
+--------------------------------------------------------------------------------+
Mobile: [Filter & sort (2)] opens a bottom sheet with "Show 12 mugs".
```

### Product (Phase 4)
```
+----------------------------------------+---------------------------------------+
| room tinted to the selected colour     | Collection / Latte mugs               |
|                                        | Aura latte mug                        |
|          [ live 3D mug, drag ]         | A conical latte mug with...           |
|                                        | Colour: Midnight navy                 |
|                                        | o o o o o                             |
| [3D] [photo] [photo]                   | 350 ml, stoneware, glossy, MOQ 500    |
|                                        | [Enquire on WhatsApp] [Add to list]   |
|                                        | Download Latte mugs catalogue         |
+----------------------------------------+---------------------------------------+
| Related mugs: drag rail                                                        |
+--------------------------------------------------------------------------------+
```

### About (Phase 6)
```
+--------------------------------------------------------------------------------+
| [ full-bleed factory exterior photo ]                                          |
|  Made in Morbi since 2022.                                                     |
+--------------------------------------------------------------------------------+
| Making process (sticky photo left swaps per step, text right)                  |
|  1 Clay   2 Forming   3 Glazing   4 Firing   5 Packing and dispatch            |
+--------------------------------------------------------------------------------+
| Certificates (lightbox)        The four directors: name, tap to call, WhatsApp |
+--------------------------------------------------------------------------------+
```

### Gallery (Phase 6)
```
|  Gallery                                                                        |
|  Products     [ ][ ][ ][ ][ ][ ] ->   drag shelf, momentum, snap on touch        |
|  Factory      [ ][ ][ ][ ][ ][ ] ->                                              |
|  Certificates [ ][ ][ ] ->                       tap = lightbox                  |
```

### Contact (Phase 6, Blush room)
```
|  Let's talk.                                                                   |
|  +---------------------------+   Amish Patel     tap to call / WhatsApp        |
|  | Name   Company            |   Darshan Patel   ...                           |
|  | Email  Phone  Country     |   Ayush Kalariya  ...                           |
|  | Category v  Message       |   Shrey Manvar    ...                           |
|  | [Send enquiry]            |   email, address                               |
|  +---------------------------+   [ Google map ]                               |
```

### Footer (Graphite room)
```
|  Talk to the factory.                          <- t-1, link to WhatsApp          |
|  Collection  About  Gallery  Contact      Amish Patel  +91 98987 98986          |
|  [Download catalogue]                     Darshan ...  Ayush ...  Shrey ...     |
|  evanzatableware@gmail.com                Survey no. 79/2 79/3, Pipaliya Maliya |
|  [evanza logo, chalk]                     Highway, Chachavadarda, Morbi         |
|  (c) 2026 Evanza Tablewares                                                     |
```

## 6. Review against the brief and the Do-not list
| Do not | How this design avoids it |
|---|---|
| Cream + serif + terracotta/teal | Cool Chalk base, no serif, no accent colour (glazes are the colour) |
| Instrument Serif / Geist / Inter / Space Grotesk | Bricolage Grotesque only |
| Tracked ALL-CAPS eyebrows, monospace labels, middle-dot metas | None; sentence case, plain lines of text |
| Hairline-rule broadsheet | No rules; rooms separate sections by colour |
| Identical rounded cards, soft shadows | Tiles are flat tone-on-tone squares; no shadow except the 3D contact shadow |
| Gradient washes, glassmorphism | None; header is a solid colour copied from the room beneath |
| Italic or coloured headline word | None |
| Arrows on every link | No arrows |
| Fade-and-slide on every section, hover on everything | Only the hero entrance and user-triggered motion |
| Numbered markers, big-number stat hero | Numbers only for the making process |

Removed before finishing: the scroll-down cue under the hero and a small "Pick a glaze" label above the dots (the dots and their name already say it).
