// Builds dist/evanza-theme.zip: the theme folder only, ready for Appearance > Themes > Add New > Upload.
// Usage: npm run package
import { mkdir, readdir, readFile, writeFile } from 'node:fs/promises';
import { dirname, join, relative, sep } from 'node:path';
import { fileURLToPath } from 'node:url';
import { zipSync } from 'fflate';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const themeDir = join(root, 'wp/evanza');
const out = join(root, 'dist/evanza-theme.zip');
const SKIP = /(^|\/)(\.DS_Store|Thumbs\.db|.*\.map)$/;

async function walk(dir) {
	const files = [];
	for (const entry of await readdir(dir, { withFileTypes: true })) {
		const full = join(dir, entry.name);
		if (entry.isDirectory()) files.push(...(await walk(full)));
		else files.push(full);
	}
	return files;
}

const entries = {};
for (const file of await walk(themeDir)) {
	const rel = relative(themeDir, file).split(sep).join('/');
	if (SKIP.test(rel)) continue;
	entries[`evanza/${rel}`] = new Uint8Array(await readFile(file));
}

// A theme zip must hold style.css and index.php at the top of its folder.
for (const required of ['evanza/style.css', 'evanza/index.php', 'evanza/functions.php']) {
	if (!entries[required]) throw new Error(`package: ${required} is missing`);
}

await mkdir(dirname(out), { recursive: true });
await writeFile(out, zipSync(entries, { level: 9 }));
const size = (await readFile(out)).length;
console.log(`package: ${Object.keys(entries).length} files, ${(size / 1048576).toFixed(1)} MB -> dist/evanza-theme.zip`);
