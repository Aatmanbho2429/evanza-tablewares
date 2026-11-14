// Copies the front-end libraries and fonts from node_modules into the theme,
// so the theme zip is self-contained. Run with: npm run vendor
import { copyFile, mkdir, readFile, writeFile } from 'node:fs/promises';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';
import { transform } from 'esbuild';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const nm = (p) => join(root, 'node_modules', p);
const out = (p) => join(root, 'wp/evanza/assets', p);

async function copy(from, to) {
	await mkdir(dirname(out(to)), { recursive: true });
	await copyFile(from, out(to));
}

// Minify a module file in place of copying it (three ships unminified).
async function copyMinified(from, to) {
	const { code } = await transform(await readFile(from, 'utf8'), { minify: true, format: 'esm', legalComments: 'none' });
	await mkdir(dirname(out(to)), { recursive: true });
	await writeFile(out(to), code);
}

// three.js: core build plus the two add-ons the mug viewer uses. The importmap
// maps "three" to vendor/three/three.module.js and "three/addons/" to vendor/three/addons/.
await copyMinified(nm('three/build/three.module.js'), 'vendor/three/three.module.js');
await copyMinified(nm('three/build/three.core.js'), 'vendor/three/three.core.js');
await copyMinified(nm('three/examples/jsm/environments/RoomEnvironment.js'), 'vendor/three/addons/environments/RoomEnvironment.js');
await copyMinified(nm('three/examples/jsm/controls/OrbitControls.js'), 'vendor/three/addons/controls/OrbitControls.js');

// GSAP + ScrollTrigger (classic scripts, expose window.gsap / window.ScrollTrigger)
await copy(nm('gsap/dist/gsap.min.js'), 'vendor/gsap/gsap.min.js');
await copy(nm('gsap/dist/ScrollTrigger.min.js'), 'vendor/gsap/ScrollTrigger.min.js');

// Lenis (classic script, exposes window.Lenis)
await copy(nm('lenis/dist/lenis.min.js'), 'vendor/lenis/lenis.min.js');

// Fonts: latin subset only
await copy(nm('@fontsource/instrument-serif/files/instrument-serif-latin-400-normal.woff2'), 'fonts/instrument-serif-400.woff2');
await copy(nm('@fontsource/instrument-serif/files/instrument-serif-latin-400-italic.woff2'), 'fonts/instrument-serif-400-italic.woff2');
await copy(nm('@fontsource-variable/geist/files/geist-latin-wght-normal.woff2'), 'fonts/geist-variable.woff2');

console.log('vendor: done');
