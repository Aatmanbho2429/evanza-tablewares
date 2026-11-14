// Serve static/ under the GitHub Pages base path for a local check.
// Usage: npm run preview:static  →  http://127.0.0.1:9500/evanza-tablewares/
import { createServer } from 'node:http';
import { readFile, stat } from 'node:fs/promises';
import { dirname, extname, join, normalize } from 'node:path';
import { fileURLToPath } from 'node:url';

const OUT = join(dirname(fileURLToPath(import.meta.url)), '..', 'static');
const BASE = '/evanza-tablewares';
const PORT = 9500;
const TYPES = {
	'.html': 'text/html; charset=utf-8', '.css': 'text/css', '.js': 'text/javascript', '.mjs': 'text/javascript',
	'.json': 'application/json', '.svg': 'image/svg+xml', '.webp': 'image/webp', '.png': 'image/png',
	'.jpg': 'image/jpeg', '.woff2': 'font/woff2', '.pdf': 'application/pdf', '.ico': 'image/x-icon',
};

async function resolve(urlPath) {
	let p = join(OUT, normalize(decodeURIComponent(urlPath)));
	if (!p.startsWith(OUT)) return null;
	try {
		if ((await stat(p)).isDirectory()) p = join(p, 'index.html');
		await stat(p);
		return p;
	} catch { return null; }
}

createServer(async (req, res) => {
	const url = new URL(req.url, `http://${req.headers.host}`);
	if (url.pathname === '/') { res.writeHead(302, { Location: `${BASE}/` }).end(); return; }
	const file = url.pathname.startsWith(BASE) ? await resolve(url.pathname.slice(BASE.length) || '/') : null;
	const path = file ?? join(OUT, '404.html');
	res.writeHead(file ? 200 : 404, { 'Content-Type': TYPES[extname(path)] ?? 'application/octet-stream' });
	res.end(await readFile(path));
}).listen(PORT, '127.0.0.1', () => console.log(`Static preview: http://127.0.0.1:${PORT}${BASE}/`));
