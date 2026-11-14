// Export the running WordPress site (npm run dev) to static/ for GitHub Pages.
// Usage: npm run export   (dev server must be running on 127.0.0.1:9400)
import { mkdir, rm, writeFile, cp, access } from 'node:fs/promises';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = join(dirname(fileURLToPath(import.meta.url)), '..');
const OUT = join(ROOT, 'static');
const ORIGIN = 'http://127.0.0.1:9400';
const BASE = process.env.BASE_PATH ?? '/evanza-tablewares';
const SITE_URL = (process.env.SITE_ORIGIN ?? 'https://aatmanbho2429.github.io') + BASE; // for links shared outside the site (WhatsApp)
const THEME_PATH = '/wp-content/themes/evanza';

// Contact form has no server on static hosting: open a prefilled email instead.
const STATIC_HEAD = `<script>window.EVANZA_STATIC=true;
window.addEventListener('submit',function(e){var f=e.target;if(!f.matches||!f.matches('[data-contact-form]'))return;
e.preventDefault();e.stopImmediatePropagation();
var m=document.querySelector('a[href^="mailto:"]'),to=m?m.getAttribute('href').slice(7).split('?')[0]:'';
var lines=[];new FormData(f).forEach(function(v,k){if(typeof v==='string'&&v.trim()&&!/^(_|action|website|hp)/.test(k))lines.push(k+': '+v.trim());});
location.href='mailto:'+to+'?subject='+encodeURIComponent('Enquiry from the Evanza website')+'&body='+encodeURIComponent(lines.join('\\n'));},true);</script>`;

async function get(path, okStatuses = [200]) {
	const res = await fetch(ORIGIN + path, { redirect: 'follow' });
	if (!okStatuses.includes(res.status)) throw new Error(`${path} → HTTP ${res.status}`);
	return res.text();
}

function transform(html) {
	return html
		// WP-only discovery tags that make no sense on a static host
		.replace(/<link[^>]+(wp-json|xmlrpc\.php|oembed|api\.w\.org|rel=["']shortlink|rel=["']dns-prefetch|\/feed\/)[^>]*>\s*/gi, '')
		.replaceAll('/__not-found__/', '/')
		.replaceAll(encodeURIComponent(ORIGIN), encodeURIComponent(SITE_URL))
		// Share tags and structured data need absolute URLs (scrapers do not resolve a bare path)
		.replace(/(<meta [^>]*content=")http:\/\/127\.0\.0\.1:9400([^"]*")/gi, (_, a, b) => a + SITE_URL + b)
		.replace(/(<script type="application\/ld\+json">)([\s\S]*?)(<\/script>)/gi, (_, a, body, c) => a + body.replaceAll(ORIGIN, SITE_URL) + c)
		.replace(/<head>/i, `<head>\n${STATIC_HEAD}`)
		.replaceAll(ORIGIN, BASE)
		.replaceAll(ORIGIN.replaceAll('/', '\\/'), BASE.replaceAll('/', '\\/'));
}

async function save(urlPath, html) {
	const file = join(OUT, urlPath, urlPath.endsWith('.html') ? '' : 'index.html');
	await mkdir(dirname(file), { recursive: true });
	await writeFile(file, transform(html));
}

async function exists(p) {
	try { await access(p); return true; } catch { return false; }
}

async function main() {
	await rm(OUT, { recursive: true, force: true });
	await mkdir(OUT, { recursive: true });

	const collection = await get('/collection/');
	const json = collection.match(/<script[^>]+id="evanza-products"[^>]*>([\s\S]*?)<\/script>/);
	if (!json) throw new Error('Product JSON not found on /collection/');
	const products = JSON.parse(json[1]).products;
	const productPaths = products.map((p) => new URL(p.url, ORIGIN).pathname);

	const pages = ['/', '/about/', '/gallery/', '/contact/', '/collection/', ...productPaths];
	for (const path of pages) {
		await save(path, path === '/collection/' ? collection : await get(path));
		console.log('page', path);
	}
	await save('/404.html', await get('/__not-found__/', [404]));

	await cp(join(ROOT, 'wp/evanza/assets'), join(OUT, THEME_PATH.slice(1), 'assets'), { recursive: true });
	await writeFile(join(OUT, '.nojekyll'), '');

	// Every local reference in the exported HTML must resolve to a file.
	const { readFile } = await import('node:fs/promises');
	const missing = new Set();
	let leftovers = 0;
	for (const path of [...pages, '/404.html']) {
		const file = join(OUT, path, path.endsWith('.html') ? '' : 'index.html');
		const html = await readFile(file, 'utf8');
		if (html.includes('127.0.0.1') || html.includes('localhost:9400')) leftovers++;
		const refs = html.replaceAll('\\/', '/').match(new RegExp(`${BASE}/[^"'\\s)<>,]*`, 'g')) ?? [];
		for (const ref of refs) {
			let p = decodeURI(ref.slice(BASE.length).split(/[?#]/)[0]);
			if (p.endsWith('/')) p += 'index.html';
			if (!(await exists(join(OUT, p)))) missing.add(ref);
		}
	}
	console.log(`\nExported ${pages.length} pages + 404 to static/`);
	if (leftovers) console.warn(`WARNING: ${leftovers} page(s) still mention the dev origin`);
	// three/addons/ is an importmap prefix; admin-post.php is the WP form endpoint (intercepted in static mode)
	const real = [...missing].filter((m) => !m.endsWith('/addons/') && !m.endsWith('/admin-post.php'));
	if (real.length) {
		console.warn('Missing files:\n  ' + real.join('\n  '));
		process.exitCode = 1;
	}
}

main().catch((err) => {
	console.error(err.message);
	console.error('Is the dev server running? Start it with: npm run dev');
	process.exit(1);
});
