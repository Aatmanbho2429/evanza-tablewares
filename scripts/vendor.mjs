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

// three.js: core build plus the environment add-on the mug viewer uses. The importmap
// maps "three" to vendor/three/three.module.js and "three/addons/" to vendor/three/addons/.
await copyMinified(nm('three/build/three.module.js'), 'vendor/three/three.module.js');
await copyMinified(nm('three/build/three.core.js'), 'vendor/three/three.core.js');
await copyMinified(nm('three/examples/jsm/environments/RoomEnvironment.js'), 'vendor/three/addons/environments/RoomEnvironment.js');

// Lenis (ES module, minified)
await copyMinified(nm('lenis/dist/lenis.mjs'), 'vendor/lenis/lenis.mjs');

// Font: Bricolage Grotesque variable (opsz, wdth, wght), latin subset
await copy(nm('@fontsource-variable/bricolage-grotesque/files/bricolage-grotesque-latin-standard-normal.woff2'), 'fonts/bricolage-grotesque.woff2');

console.log('vendor: done');
