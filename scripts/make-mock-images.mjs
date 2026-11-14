// Generates a transparent studio-style SVG mug, tinted with the variant colours, for every
// product variant. The path goes in variant.render (the Collection tile image). A variant
// with no real photo also gets it as images[0]: "mock" means images is empty or its first
// image is under assets/img/mock/.
// The look matches the 3D mug (mug3d.js): glossy glaze, rolled rim, inner colour, soft contact shadow.
// Run with: npm run mock-images
import { mkdir, readFile, rm, writeFile } from 'node:fs/promises';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const dataFile = join(root, 'wp/evanza/data/products.json');
const outDir = join(root, 'wp/evanza/assets/img/mock');
const MOCK_PREFIX = 'assets/img/mock/';

// --- colour helpers -------------------------------------------------------
const toRgb = (hex) => [1, 3, 5].map((i) => parseInt(hex.slice(i, i + 2), 16));
const toHex = (rgb) => '#' + rgb.map((v) => Math.round(Math.max(0, Math.min(255, v))).toString(16).padStart(2, '0')).join('');
// amount > 0 mixes toward white, < 0 toward black
const shade = (hex, amount) => {
	const target = amount > 0 ? 255 : 0;
	return toHex(toRgb(hex).map((v) => v + (target - v) * Math.abs(amount)));
};

// --- mug profiles (viewBox units; cx is the body centre) ---------------------
const CX = 330;
const BOTTOM = 560;
// top/bottom radius, bulge = extra radius at mid height, ry = rim ellipse depth
const SHAPES = {
	classic: { top: 250, rt: 150, rb: 140, bulge: 0, ry: 27, hy: [58, 82], reach: 112 },
	latte: { top: 250, rt: 172, rb: 98, bulge: 0, ry: 29, hy: [58, 82], reach: 112 },
	belly: { top: 262, rt: 132, rb: 112, bulge: 24, ry: 25, hy: [52, 78], reach: 104 },
	espresso: { top: 340, rt: 118, rb: 92, bulge: 6, ry: 22, hy: [38, 56], reach: 74 },
};
SHAPES.heart = SHAPES.classic;

function halfWidth(s, y) {
	const h = BOTTOM - s.top;
	const t = (y - s.top) / h;
	let hw = s.rt + (s.rb - s.rt) * t + s.bulge * Math.sin(Math.PI * t);
	const rc = 42;
	if (y > BOTTOM - rc) {
		const u = (y - (BOTTOM - rc)) / rc;
		hw -= rc * (1 - Math.sqrt(Math.max(0, 1 - u * u)));
	}
	return hw;
}

function bodyPath(s) {
	const pts = [];
	for (let y = s.top; y <= BOTTOM; y += 4) pts.push(y);
	if (pts[pts.length - 1] !== BOTTOM) pts.push(BOTTOM);
	const f = (n) => n.toFixed(1);
	const left = pts.map((y) => `${f(CX - halfWidth(s, y))} ${y}`);
	const right = [...pts].reverse().map((y) => `${f(CX + halfWidth(s, y))} ${y}`);
	const hb = halfWidth(s, BOTTOM);
	return `M${left.join(' L')} A${f(hb)} 15 0 0 0 ${right[0].replace(' ', ' ')} L${right.slice(1).join(' L')} A${s.rt} ${s.ry} 0 0 1 ${f(CX - s.rt)} ${s.top} Z`;
}

function handleMarkup(shape, s, color) {
	const dark = shade(color, -0.28);
	const light = shade(color, 0.3);
	const y1 = s.top + s.hy[0];
	const y2 = BOTTOM - s.hy[1];
	const x1 = CX + halfWidth(s, y1) - 8;
	const x2 = CX + halfWidth(s, y2) - 8;
	if (shape === 'heart') {
		const heart = 'M0 -22C-44 -66-110 -22-64 34L0 92 64 34C110-22 44-66 0-22Z';
		const cx = CX + halfWidth(s, 400) + 70;
		return (
			`<g transform="translate(${cx.toFixed(1)} 392) rotate(90) scale(.95)" fill="none" stroke-linejoin="round">` +
			`<path d="${heart}" stroke="${dark}" stroke-width="28"/><path d="${heart}" stroke="${color}" stroke-width="22"/>` +
			`<path d="${heart}" stroke="${light}" stroke-opacity=".55" stroke-width="5" transform="translate(-3 -3)"/></g>`
		);
	}
	const d = `M${x1.toFixed(1)} ${y1} C${(x1 + s.reach).toFixed(1)} ${y1 - 14} ${(x2 + s.reach + 12).toFixed(1)} ${y2 + 8} ${x2.toFixed(1)} ${y2}`;
	return (
		`<g fill="none" stroke-linecap="round"><path d="${d}" stroke="${dark}" stroke-width="30"/>` +
		`<path d="${d}" stroke="${color}" stroke-width="24"/>` +
		`<path d="${d}" stroke="${light}" stroke-opacity=".6" stroke-width="5" transform="translate(-2 -5)"/></g>`
	);
}

function mugSvg({ shape, body, inner, handle }) {
	const s = SHAPES[shape] || SHAPES.classic;
	const d = bodyPath(s);
	const rim = shade(body, 0.1);
	const rimEdge = shade(inner, -0.25);
	const hwMid = halfWidth(s, (s.top + BOTTOM) / 2);
	const hwBot = halfWidth(s, BOTTOM - 40);
	return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="105 105 560 560" width="800" height="800" role="img">
<defs>
<linearGradient id="shade" gradientUnits="userSpaceOnUse" x1="${CX - s.rt - 4}" y1="0" x2="${CX + s.rt + 4}" y2="0"><stop offset="0" stop-color="#000" stop-opacity=".34"/><stop offset=".1" stop-color="#000" stop-opacity=".12"/><stop offset=".28" stop-color="#fff" stop-opacity=".14"/><stop offset=".5" stop-color="#fff" stop-opacity="0"/><stop offset=".8" stop-color="#000" stop-opacity=".12"/><stop offset="1" stop-color="#000" stop-opacity=".42"/></linearGradient>
<linearGradient id="occl" x1="0" y1="${s.top}" x2="0" y2="${BOTTOM}" gradientUnits="userSpaceOnUse"><stop offset="0" stop-color="#000" stop-opacity="0"/><stop offset=".45" stop-color="#000" stop-opacity="0"/><stop offset="1" stop-color="#000" stop-opacity=".2"/></linearGradient>
<linearGradient id="cavity" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="${shade(inner, -0.5)}"/><stop offset=".55" stop-color="${shade(inner, -0.22)}"/><stop offset="1" stop-color="${shade(inner, 0.04)}"/></linearGradient>
<clipPath id="body"><path d="${d}"/></clipPath>
<filter id="soft" x="-30%" y="-100%" width="160%" height="300%"><feGaussianBlur stdDeviation="10"/></filter>
<filter id="tight" x="-30%" y="-100%" width="160%" height="300%"><feGaussianBlur stdDeviation="4"/></filter>
<filter id="blur3" x="-50%" y="-10%" width="200%" height="120%"><feGaussianBlur stdDeviation="3.2"/></filter>
<filter id="blur1"><feGaussianBlur stdDeviation="1"/></filter>
</defs>
<ellipse cx="${CX + 14}" cy="${BOTTOM + 8}" rx="${(hwBot * 1.35).toFixed(0)}" ry="17" fill="#000" opacity=".26" filter="url(#soft)"/>
<ellipse cx="${CX + 4}" cy="${BOTTOM + 3}" rx="${(hwBot * 0.95).toFixed(0)}" ry="8" fill="#000" opacity=".4" filter="url(#tight)"/>
${handleMarkup(shape, s, handle)}
<ellipse cx="${CX}" cy="${s.top}" rx="${s.rt}" ry="${s.ry}" fill="${rim}"/>
<ellipse cx="${CX}" cy="${s.top + 1}" rx="${s.rt - 11}" ry="${s.ry - 6.5}" fill="${rimEdge}"/>
<ellipse cx="${CX}" cy="${s.top + 2}" rx="${s.rt - 13}" ry="${s.ry - 8}" fill="url(#cavity)"/>
<path d="${d}" fill="${body}"/>
<path d="${d}" fill="url(#shade)"/>
<path d="${d}" fill="url(#occl)"/>
<g clip-path="url(#body)">
<rect x="${(CX - hwMid * 0.62).toFixed(1)}" y="${s.top + 24}" width="22" height="${BOTTOM - s.top - 70}" rx="11" fill="#fff" opacity=".5" filter="url(#blur3)"/>
<rect x="${(CX + hwMid * 0.7).toFixed(1)}" y="${s.top + 34}" width="7" height="${BOTTOM - s.top - 90}" rx="3.5" fill="#fff" opacity=".26" filter="url(#blur1)"/>
</g>
<path d="M${CX - s.rt + 12} ${s.top + s.ry * 0.55} A${s.rt - 12} ${s.ry - 3} 0 0 0 ${CX - s.rt * 0.35} ${s.top + s.ry - 1}" fill="none" stroke="#fff" stroke-opacity=".7" stroke-width="2.4" stroke-linecap="round" filter="url(#blur1)"/>
</svg>
`;
}

// --- run --------------------------------------------------------------------
const data = JSON.parse(await readFile(dataFile, 'utf8'));
await rm(outDir, { recursive: true, force: true });
await mkdir(outDir, { recursive: true });

let count = 0;
for (const product of data.products) {
	for (const variant of product.variants) {
		const rel = `${MOCK_PREFIX}${product.slug}-${variant.slug}.svg`;
		const svg = mugSvg({
			shape: product.shape,
			body: variant.body || variant.hex,
			inner: variant.inner || variant.hex,
			handle: variant.handle || variant.hex,
		});
		await writeFile(join(outDir, `${product.slug}-${variant.slug}.svg`), svg);
		variant.render = rel;
		const first = variant.images?.[0];
		if (!first || first.startsWith(MOCK_PREFIX)) {
			variant.images = [rel, ...(variant.images || []).filter((p) => !p.startsWith(MOCK_PREFIX))];
		}
		count++;
	}
}
await writeFile(dataFile, JSON.stringify(data, null, '\t') + '\n');
console.log(`mock-images: ${count} SVG mugs written`);
