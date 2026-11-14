// Product page: live 3D mug in a room that takes its colour from the selected glaze, glaze pour on
// colour change, photo gallery, enquiry buttons and the related-products rail.
import { reducedMotion } from './lib/motion.js';
import { createPour, POUR_MS } from './lib/glaze-pour.js';
import { loadCatalogue, roomFor, photosOf } from './lib/catalogue.js';
import { config } from './lib/config.js';
import { addItem, openEnquiryDrawer } from './lib/enquiry.js';
import { initRail } from './lib/rail.js';

const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);

async function init(root) {
	const data = loadCatalogue();
	const product = data.products.find((p) => p.slug === root.dataset.slug);
	if (!product) return;
	const room = root.querySelector('[data-product-room]');
	const track = root.querySelector('[data-track]');
	const thumbs = root.querySelector('[data-thumbs]');
	const canvas = root.querySelector('[data-canvas]');
	const still = root.querySelector('[data-still]');
	const nameEl = root.querySelector('[data-variant-name]');
	const swatches = [...root.querySelectorAll('[data-swatches] [data-variant]')];
	const pour = createPour(room);
	const slide3d = root.querySelector('[data-slide="3d"]');

	const slug = new URLSearchParams(location.search).get('colour');
	let variant = product.variants.find((v) => v.slug === slug) || product.variants[0];
	let viewer = null;

	const colorsOf = (v) => ({ body: v.body || v.hex, inner: v.inner || v.body || v.hex, handle: v.handle || v.body || v.hex });
	const productUrl = (v) => `${product.url}?colour=${v.slug}`;

	// ---------- gallery: 3D slide plus one slide per photo ----------
	function buildGallery() {
		track.querySelectorAll('[data-photo]').forEach((el) => el.remove());
		const photos = photosOf(variant);
		photos.forEach((src, i) => {
			const slide = document.createElement('div');
			slide.className = 'gallery__slide';
			slide.dataset.photo = '';
			slide.innerHTML = `<img src="${esc(src.replace('-1600.', '-800.'))}" srcset="${esc(src.replace('-1600.', '-800.'))} 800w, ${esc(src)} 1600w" sizes="(min-width: 56rem) 50vw, 100vw" alt="${esc(`${product.name}, ${variant.name}, photo ${i + 1}`)}" width="1600" height="1067" loading="lazy" decoding="async" draggable="false">`;
			track.append(slide);
		});
		const items = ['3D view', ...photos.map((_, i) => `Photo ${i + 1}`)];
		thumbs.hidden = photos.length === 0;
		thumbs.innerHTML = items
			.map((label, i) => {
				const art = i === 0 ? `<img src="${esc(variant.render)}" alt="" width="160" height="160"><span class="thumb__tag">3D</span>` : `<img src="${esc(photos[i - 1].replace('-1600.', '-800.'))}" alt="" width="160" height="160">`;
				return `<button class="thumb" type="button" data-index="${i}" aria-label="${esc(label)}" aria-pressed="${i === 0}">${art}</button>`;
			})
			.join('');
		track.scrollTo({ left: 0, behavior: 'auto' });
	}

	const slideWidth = () => track.clientWidth || 1;
	thumbs.addEventListener('click', (e) => {
		const btn = e.target.closest('[data-index]');
		if (btn) track.scrollTo({ left: Number(btn.dataset.index) * slideWidth(), behavior: reducedMotion.matches ? 'auto' : 'smooth' });
	});
	let ticking = false;
	track.addEventListener('scroll', () => {
		if (ticking) return;
		ticking = true;
		requestAnimationFrame(() => {
			ticking = false;
			const index = Math.round(track.scrollLeft / slideWidth());
			thumbs.querySelectorAll('[data-index]').forEach((b) => b.setAttribute('aria-pressed', String(Number(b.dataset.index) === index)));
		});
	}, { passive: true });

	// ---------- colour ----------
	function select(next, origin, { push = true } = {}) {
		if (next === variant) return;
		variant = next;
		const r = roomFor(variant.hex);
		if (origin) pour(r.color, origin);
		else {
			room.style.setProperty('--room', r.color);
			room.dataset.tone = r.tone;
			document.dispatchEvent(new CustomEvent('evanza:room'));
		}
		viewer?.setColors(colorsOf(variant), { animate: true, duration: origin ? POUR_MS : 600, delay: 0 }); // starts with the pour
		still.src = variant.render;
		still.alt = `${product.name}, ${variant.name}`;
		nameEl.textContent = variant.name;
		swatches.forEach((s) => s.setAttribute('aria-checked', String(s.dataset.variant === variant.slug)));
		buildGallery();
		updateLinks();
		if (push) history.replaceState(null, '', productUrl(variant));
	}

	function updateLinks() {
		const wa = root.querySelector('[data-whatsapp]');
		const text = `Hello ${config().siteName}, I would like to enquire about the ${product.name}, ${variant.name}. ${productUrl(variant)}`;
		wa.href = `https://wa.me/${config().whatsapp}?text=${encodeURIComponent(text)}`;
		canvas.setAttribute('aria-label', `3D view of the ${product.name} in ${variant.name}. Drag to turn it.`);
	}

	swatches.forEach((s, i) => {
		s.addEventListener('click', (e) => {
			e.preventDefault();
			select(product.variants.find((v) => v.slug === s.dataset.variant), s);
		});
		s.addEventListener('keydown', (e) => {
			const dir = { ArrowRight: 1, ArrowDown: 1, ArrowLeft: -1, ArrowUp: -1 }[e.key];
			if (!dir) return;
			e.preventDefault();
			const next = swatches[(i + dir + swatches.length) % swatches.length];
			next.focus();
			select(product.variants.find((v) => v.slug === next.dataset.variant), next);
		});
		s.tabIndex = s.getAttribute('aria-checked') === 'true' ? 0 : -1;
	});
	// roving tab stop: only the checked swatch is tabbable
	new MutationObserver(() => swatches.forEach((s) => (s.tabIndex = s.getAttribute('aria-checked') === 'true' ? 0 : -1))).observe(root.querySelector('[data-swatches]'), { attributes: true, subtree: true, attributeFilter: ['aria-checked'] });

	// ---------- quantity and enquiry ----------
	const qtyInput = root.querySelector('[data-qty-input]');
	root.querySelector('[data-qty]').addEventListener('click', (e) => {
		const step = e.target.closest('[data-step]');
		if (step) qtyInput.value = Math.max(1, (Number(qtyInput.value) || 0) + Number(step.dataset.step));
	});
	root.querySelector('[data-add-enquiry]').addEventListener('click', (e) => {
		addItem({
			slug: product.slug,
			variant: variant.slug,
			name: product.name,
			variantName: variant.name,
			hex: variant.hex,
			render: variant.render,
			url: product.url,
			qty: Math.max(1, Math.round(Number(qtyInput.value)) || 1),
		});
		openEnquiryDrawer(e.currentTarget);
	});

	// ---------- initial state ----------
	buildGallery();
	updateLinks();
	nameEl.textContent = variant.name;
	swatches.forEach((s) => s.setAttribute('aria-checked', String(s.dataset.variant === variant.slug)));
	room.querySelectorAll('.glaze').forEach((g) => g.removeAttribute('href'));
	root.querySelectorAll('[data-rail]').forEach(initRail);

	try {
		const { createMugViewer } = await import('./mug3d.js');
		viewer = createMugViewer(canvas, {
			colors: colorsOf(variant),
			shape: product.shape,
			autoRotate: true,
			interactive: true,
			onFail: () => {
				still.hidden = false;
				canvas.hidden = true;
			},
		});
		viewer.enter({ duration: 900 });
		canvas.classList.add('is-ready');
		still.hidden = true;
	} catch (error) {
		console.warn('Mug viewer unavailable, showing the still render.', error);
	}
}

const root = document.querySelector('[data-product]');
if (root) init(root);
