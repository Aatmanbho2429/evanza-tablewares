// Collection page: glaze bar, filter bar (popovers on desktop, bottom sheet on phones), product
// tiles with colour dots, URL sync, the glaze pour and FLIP re-ordering.
import { reducedMotion } from './lib/motion.js';
import { createPour } from './lib/glaze-pour.js';
import { loadCatalogue, matches, sortProducts, roomFor, variantFor, photosOf, plural, CHALK } from './lib/catalogue.js';
import { flipTo } from './lib/flip.js';
import { initPopover } from './lib/popover.js';
import { createSheet } from './lib/sheet.js';

const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);
const SORTS = [
	['featured', 'Featured'],
	['new', 'Newest'],
	['name', 'Name A to Z'],
];
const MAX_DOTS = 5;

function init(root) {
	const data = loadCatalogue();
	data.products.forEach((p, i) => (p.index = i));
	const head = root.querySelector('[data-coll-head]');
	const glazeBar = root.querySelector('[data-glaze-bar]');
	const filterBar = root.querySelector('[data-filter-bar]');
	const strip = root.querySelector('[data-cat-strip]');
	const grid = root.querySelector('[data-grid]');
	const empty = root.querySelector('[data-empty]');
	const countEls = () => document.querySelectorAll('[data-count]');
	const pour = createPour(head);

	// ---------- state and URL ----------
	const valid = {
		category: new Set(data.categories.map((c) => c.slug)),
		color: new Set(data.colorFamilies.map((f) => f.slug)),
	};
	const list = (value, set) => (value ? value.split(',').filter((v) => set.has(v)) : []);
	function readUrl() {
		const q = new URLSearchParams(location.search);
		const sort = q.get('sort');
		return {
			categories: list(q.get('category'), valid.category),
			colors: list(q.get('color'), valid.color),
			sort: SORTS.some(([v]) => v === sort) ? sort : 'featured',
		};
	}
	function urlFor(s) {
		const parts = [];
		if (s.categories.length) parts.push(`category=${s.categories.join(',')}`);
		if (s.colors.length) parts.push(`color=${s.colors.join(',')}`);
		if (s.sort !== 'featured') parts.push(`sort=${s.sort}`);
		return location.pathname + (parts.length ? `?${parts.join('&')}` : '');
	}
	let state = readUrl();

	// ---------- static UI ----------
	const familyHex = (slug) => data.familyBySlug[slug].hex;
	const glazeStyle = (f) => `--glaze: ${f.hex}`;
	const glazeClass = (f) => `glaze${f.slug === 'multi' ? ' glaze--multi' : ''}`;

	glazeBar.innerHTML = data.colorFamilies
		.map(
			(f) => `<div class="glaze-bar__item">
				<button class="${glazeClass(f)}" type="button" style="${glazeStyle(f)}" aria-pressed="false" data-color="${f.slug}" data-name="${esc(f.name)}"></button>
				<span class="glaze-bar__count" data-color-count="${f.slug}" aria-hidden="true"></span>
			</div>`
		)
		.join('');

	const catChecks = (id) =>
		data.categories
			.map(
				(c) => `<label class="check"><input type="checkbox" data-cat="${c.slug}" id="${id}-${c.slug}"> <span>${esc(c.name)}</span> <span class="chip__count" data-cat-count="${c.slug}"></span></label>`
			)
			.join('');
	const colourOpts = () =>
		data.colorFamilies
			.map(
				(f) => `<button class="colour-opt" type="button" aria-pressed="false" data-color="${f.slug}" data-name="${esc(f.name)}">
					<span class="${glazeClass(f)} glaze--static" style="${glazeStyle(f)}" aria-hidden="true"></span>
					<span>${esc(f.name)}</span> <span class="chip__count" data-color-count="${f.slug}"></span>
				</button>`
			)
			.join('');
	const sortRadios = (id) =>
		SORTS.map(([v, label]) => `<label class="check"><input type="radio" name="${id}-sort" data-sort="${v}"> <span>${label}</span></label>`).join('');

	filterBar.innerHTML = `<div class="container filter-bar__inner">
		<div class="filter-bar__desktop">
			<div class="pop" data-pop>
				<button class="text-btn pop__btn" type="button" data-pop-button aria-expanded="false" aria-haspopup="true" data-pop-label="Category">Category</button>
				<div class="pop__panel" data-pop-panel hidden role="group" aria-label="Category">${catChecks('pc')}</div>
			</div>
			<div class="pop" data-pop>
				<button class="text-btn pop__btn" type="button" data-pop-button aria-expanded="false" aria-haspopup="true" data-pop-label="Colour">Colour</button>
				<div class="pop__panel pop__panel--colours" data-pop-panel hidden role="group" aria-label="Colour">${colourOpts()}</div>
			</div>
			<div class="pop" data-pop>
				<button class="text-btn pop__btn" type="button" data-pop-button aria-expanded="false" aria-haspopup="true" data-sort-label>Sort: Featured</button>
				<fieldset class="pop__panel pop__panel--sort" data-pop-panel hidden><legend class="visually-hidden">Sort by</legend>${sortRadios('ps')}</fieldset>
			</div>
		</div>
		<button class="btn btn--outline btn--sm filter-bar__phone" type="button" data-sheet-open aria-haspopup="dialog">Filter &amp; sort</button>
		<div class="chips" data-chips aria-label="Active filters"></div>
		<p class="filter-bar__count" data-count aria-live="polite"></p>
	</div>`;

	const sheet = document.createElement('div');
	sheet.className = 'sheet';
	sheet.hidden = true;
	sheet.innerHTML = `<div class="sheet__backdrop" data-sheet-backdrop></div>
		<div class="sheet__panel" data-sheet-panel role="dialog" aria-modal="true" aria-labelledby="sheet-title">
			<div class="sheet__top" data-sheet-handle><span class="sheet__grip" aria-hidden="true"></span>
				<div class="sheet__head"><h2 id="sheet-title" class="t-3">Filter &amp; sort</h2><button class="text-btn" type="button" data-sheet-close>Close</button></div>
			</div>
			<div class="sheet__body">
				<fieldset class="sheet__group"><legend>Category</legend>${catChecks('sc')}</fieldset>
				<fieldset class="sheet__group"><legend>Colour</legend><div class="sheet__colours">${colourOpts()}</div></fieldset>
				<fieldset class="sheet__group"><legend>Sort by</legend>${sortRadios('ss')}</fieldset>
			</div>
			<div class="sheet__foot"><button class="btn btn--block" type="button" data-sheet-close data-sheet-show>Show mugs</button><button class="text-btn" type="button" data-clear>Clear all</button></div>
		</div>`;
	document.body.append(sheet);
	const sheetApi = createSheet(sheet);
	filterBar.querySelector('[data-sheet-open]').addEventListener('click', (e) => sheetApi.open(e.currentTarget));
	filterBar.querySelectorAll('[data-pop]').forEach(initPopover);

	// ---------- tiles ----------
	const tiles = new Map();
	const catName = (slug) => data.categoryBySlug[slug]?.name || '';
	function createTile(p) {
		const li = document.createElement('li');
		li.className = 'tile';
		li.dataset.key = p.slug;
		const dots = p.variants.slice(0, MAX_DOTS);
		li.innerHTML = `<a class="tile__link" href="${esc(p.url)}">
				<span class="tile__art">
					<img class="tile__render" alt="" width="800" height="800" loading="lazy" decoding="async">
					<img class="tile__photo" alt="" width="800" height="800" decoding="async" hidden>
				</span>
				<span class="tile__name t-3">${esc(p.name)}<span class="visually-hidden" data-tile-variant></span></span>
				<span class="tile__meta">${esc([p.specs.capacity, p.badges[0]].filter(Boolean).join(', '))}</span>
			</a>
			<div class="tile__dots" role="group" aria-label="Colours of ${esc(p.name)}">
				${dots
					.map(
						(v) => `<button class="glaze glaze--tile" type="button" style="--glaze: ${v.hex}" aria-pressed="false" aria-label="${esc(v.name)}" data-variant="${v.slug}"></button>`
					)
					.join('')}
				${p.variants.length > MAX_DOTS ? `<a class="tile__more" href="${esc(p.url)}">+${p.variants.length - MAX_DOTS}<span class="visually-hidden"> more colours</span></a>` : ''}
			</div>`;
		li._product = p;
		return li;
	}
	const photoSrc = (url) => url.replace('-1600.webp', '-800.webp');

	function showVariant(li, variant) {
		const p = li._product;
		li._variant = variant;
		const art = li.querySelector('.tile__art');
		art.style.setProperty('--c', variant.hex);
		const render = li.querySelector('.tile__render');
		render.src = variant.render || variant.images[0];
		const photo = li.querySelector('.tile__photo');
		const photos = photosOf(variant);
		// The hover photo loads on first hover, not with the page.
		photo.removeAttribute('src');
		if (photos.length) {
			photo.dataset.src = photoSrc(photos[0]);
			photo.hidden = false;
			art.classList.add('has-photo');
		} else {
			photo.hidden = true;
			delete photo.dataset.src;
			art.classList.remove('has-photo');
		}
		li.querySelector('.tile__link').href = `${p.url}?colour=${variant.slug}`;
		li.querySelector('[data-tile-variant]').textContent = `, ${variant.name}`;
		li.querySelectorAll('[data-variant]').forEach((b) => b.setAttribute('aria-pressed', String(b.dataset.variant === variant.slug)));
	}

	grid.addEventListener('pointerover', (e) => {
		const photo = e.target.closest('.tile__art')?.querySelector('.tile__photo');
		if (photo && !photo.getAttribute('src') && photo.dataset.src) photo.src = photo.dataset.src;
	});

	// Dots: hover previews a colour with a mouse, a click or tap pins it.
	grid.addEventListener('pointerover', (e) => {
		const dot = e.target.closest('[data-variant]');
		if (!dot || e.pointerType !== 'mouse') return;
		const li = dot.closest('.tile');
		showVariant(li, li._product.variants.find((v) => v.slug === dot.dataset.variant));
	});
	grid.addEventListener('pointerout', (e) => {
		const dot = e.target.closest('[data-variant]');
		if (!dot || e.pointerType !== 'mouse' || dot.contains(e.relatedTarget)) return;
		const li = dot.closest('.tile');
		if (li.contains(e.relatedTarget) && e.relatedTarget?.closest?.('[data-variant]')) return;
		showVariant(li, li._pinned || variantFor(li._product, state.colors));
	});
	grid.addEventListener('click', (e) => {
		const dot = e.target.closest('[data-variant]');
		if (!dot) return;
		const li = dot.closest('.tile');
		li._pinned = li._product.variants.find((v) => v.slug === dot.dataset.variant);
		showVariant(li, li._pinned);
	});

	// ---------- render ----------
	function visibleProducts() {
		return sortProducts(data.products.filter((p) => matches(p, state)), state.sort);
	}

	function render({ animate = true } = {}) {
		const shown = visibleProducts();
		for (const p of shown) {
			let li = tiles.get(p.slug);
			if (!li) {
				li = createTile(p);
				tiles.set(p.slug, li);
			}
			li._pinned = null;
			showVariant(li, variantFor(p, state.colors));
		}
		if (animate) flipTo(grid, tiles, shown.map((p) => p.slug));
		else {
			grid.replaceChildren(...shown.map((p) => tiles.get(p.slug)));
		}
		empty.hidden = shown.length > 0;
		syncControls(shown.length);
	}

	// The room follows the most recently picked colour; with none picked it is Chalk.
	const currentRoom = () => {
		const last = state.colors[state.colors.length - 1];
		return last ? roomFor(familyHex(last)) : { color: CHALK, tone: 'light' };
	};

	function syncControls(shownCount) {
		const sel = (list) => new Set(list);
		const cats = sel(state.categories);
		const cols = sel(state.colors);
		document.querySelectorAll('[data-cat]').forEach((el) => {
			el.checked = cats.has(el.dataset.cat);
			const n = data.products.filter((p) => p.category === el.dataset.cat && matches(p, state, 'categories')).length;
			el.disabled = n === 0 && !el.checked;
		});
		document.querySelectorAll('[data-cat-count]').forEach((el) => {
			el.textContent = data.products.filter((p) => p.category === el.dataset.catCount && matches(p, state, 'categories')).length;
		});
		document.querySelectorAll('[data-color]').forEach((el) => {
			const slug = el.dataset.color;
			const n = data.products.filter((p) => p.variants.some((v) => v.family === slug) && matches(p, state, 'colors')).length;
			const on = cols.has(slug);
			el.setAttribute('aria-pressed', String(on));
			el.disabled = n === 0 && !on;
			el.setAttribute('aria-label', `${el.dataset.name}, ${plural(n, 'mug', 'mugs')}`);
			document.querySelectorAll(`[data-color-count="${slug}"]`).forEach((c) => (c.textContent = n));
		});
		document.querySelectorAll('[data-sort]').forEach((el) => (el.checked = el.dataset.sort === state.sort));
		const sortName = SORTS.find(([v]) => v === state.sort)[1];
		filterBar.querySelector('[data-sort-label]').textContent = `Sort: ${sortName}`;

		// Popover and phone button labels carry the selection count.
		const n = state.categories.length + state.colors.length;
		filterBar.querySelectorAll('[data-pop-label]').forEach((b) => {
			const count = b.dataset.popLabel === 'Category' ? state.categories.length : state.colors.length;
			b.textContent = count ? `${b.dataset.popLabel} (${count})` : b.dataset.popLabel;
		});
		filterBar.querySelector('[data-sheet-open]').innerHTML = `Filter &amp; sort${n ? ` (${n})` : ''}`;

		const label = plural(shownCount, 'mug', 'mugs');
		countEls().forEach((el) => (el.textContent = label));
		sheet.querySelector('[data-sheet-show]').textContent = shownCount ? `Show ${label}` : 'No mugs match';

		// Active filter chips
		const chips = filterBar.querySelector('[data-chips]');
		const chipHtml = [
			...state.categories.map((s) => ({ type: 'cat', slug: s, name: catName(s) })),
			...state.colors.map((s) => ({ type: 'color', slug: s, name: data.familyBySlug[s].name })),
		].map((c) => `<button class="chip is-active" type="button" data-remove="${c.type}:${c.slug}" aria-label="Remove ${esc(c.name)} filter">${esc(c.name)} <span aria-hidden="true">&times;</span></button>`);
		if (chipHtml.length) chipHtml.push(`<button class="text-btn" type="button" data-clear>Clear all</button>`);
		chips.innerHTML = chipHtml.join('');
		filterBar.classList.toggle('has-chips', chipHtml.length > 0);

		// Per-category catalogue
		if (state.categories.length === 1) {
			const c = data.categoryBySlug[state.categories[0]];
			strip.hidden = false;
			strip.innerHTML = `<div class="container cat-strip__inner"><p class="t-3">${esc(c.name)}, the full range.</p><a class="btn" href="${esc(c.catalogue)}" target="_blank" rel="noopener">Download ${esc(c.name)} catalogue (PDF)</a></div>`;
		} else strip.hidden = true;
	}

	// ---------- changes ----------
	const toggle = (arr, v) => (arr.includes(v) ? arr.filter((x) => x !== v) : [...arr, v]);

	function commit(next, { origin } = {}) {
		const colourChanged = next.colors.join() !== state.colors.join();
		state = next;
		history.pushState(null, '', urlFor(state));
		if (colourChanged) pour(currentRoom().color, origin);
		render();
	}

	function instantRoom() {
		const { color, tone } = currentRoom();
		head.style.setProperty('--room', color);
		head.dataset.tone = tone;
		document.dispatchEvent(new CustomEvent('evanza:room'));
	}

	document.addEventListener('click', (e) => {
		const colour = e.target.closest('[data-color]');
		if (colour && !colour.disabled) {
			commit({ ...state, colors: toggle(state.colors, colour.dataset.color) }, { origin: colour });
			return;
		}
		const remove = e.target.closest('[data-remove]');
		if (remove) {
			const [type, slug] = remove.dataset.remove.split(':');
			commit(type === 'cat' ? { ...state, categories: state.categories.filter((c) => c !== slug) } : { ...state, colors: state.colors.filter((c) => c !== slug) }, { origin: remove });
			return;
		}
		if (e.target.closest('[data-clear]')) commit({ ...state, categories: [], colors: [] }, { origin: e.target.closest('[data-clear]') });
	});
	document.addEventListener('change', (e) => {
		const cat = e.target.closest('[data-cat]');
		if (cat) commit({ ...state, categories: toggle(state.categories, cat.dataset.cat) });
		const sort = e.target.closest('[data-sort]');
		if (sort) commit({ ...state, sort: sort.dataset.sort });
	});
	window.addEventListener('popstate', () => {
		const prev = state;
		state = readUrl();
		if (prev.colors.join() !== state.colors.join()) instantRoom();
		render();
	});

	// First paint: state from the URL, no animation.
	history.replaceState(null, '', urlFor(state));
	if (state.colors.length) instantRoom();
	render({ animate: false });
	root.classList.add('is-ready');
}

const root = document.querySelector('[data-collection]');
if (root) init(root);
