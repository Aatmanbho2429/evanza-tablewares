// The enquiry list: a localStorage store, the header count and the slide-in drawer.
// Buyers collect mugs and send the whole list in one WhatsApp message, an email, or the contact form.
import { config } from './config.js';
import { createSheet } from './sheet.js';

const KEY = 'evanza-enquiry';
const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);

// ---------- store ----------
export function getItems() {
	try {
		const items = JSON.parse(localStorage.getItem(KEY) || '[]');
		return Array.isArray(items) ? items : [];
	} catch {
		return [];
	}
}
function save(items) {
	try {
		localStorage.setItem(KEY, JSON.stringify(items));
	} catch {
		/* private mode: the list lives for this page only */
	}
	window.dispatchEvent(new CustomEvent('evanza:enquiry-change'));
}
export const itemId = (slug, variant) => `${slug}:${variant}`;

/** item: { slug, variant, name, variantName, hex, render, url, qty } */
export function addItem(item) {
	const items = getItems();
	const id = itemId(item.slug, item.variant);
	const found = items.find((i) => i.id === id);
	if (found) found.qty += item.qty;
	else items.push({ ...item, id });
	save(items);
}
export function setQty(id, qty) {
	save(getItems().map((i) => (i.id === id ? { ...i, qty: Math.max(1, Math.round(qty) || 1) } : i)));
}
export function removeItem(id) {
	save(getItems().filter((i) => i.id !== id));
}
export function clearItems() {
	save([]);
}

// ---------- messages ----------
const itemUrl = (i) => `${i.url}${i.url.includes('?') ? '&' : '?'}colour=${i.variant}`;

/** Plain-text message for a list of items (one or many). */
export function messageFor(items) {
	const lines = items.map((i, n) => `${items.length > 1 ? `${n + 1}. ` : ''}${i.name}, ${i.variantName}, ${i.qty} pcs\n${itemUrl(i)}`);
	const intro = items.length > 1 ? 'I would like to enquire about these mugs:' : 'I would like to enquire about this mug:';
	return `Hello ${config().siteName}, ${intro}\n\n${lines.join('\n\n')}\n\nPlease share pricing and delivery details.`;
}
export const whatsappUrl = (items) => `https://wa.me/${config().whatsapp}?text=${encodeURIComponent(messageFor(items))}`;
export const mailtoUrl = (items) => `mailto:${config().email}?subject=${encodeURIComponent('Enquiry from the website')}&body=${encodeURIComponent(messageFor(items))}`;

// ---------- header count ----------
function renderCount() {
	const n = getItems().length;
	document.querySelectorAll('[data-enquiry-count]').forEach((el) => (el.textContent = String(n)));
	document.querySelectorAll('[data-enquiry-open]').forEach((el) => el.setAttribute('aria-label', n ? `Enquiry list, ${n} mug${n === 1 ? '' : 's'}` : 'Enquiry list, empty'));
}

// ---------- drawer ----------
let drawer;

function buildDrawer() {
	const root = document.createElement('div');
	root.className = 'drawer';
	root.hidden = true;
	root.innerHTML = `<div class="drawer__backdrop" data-sheet-backdrop></div>
		<aside class="drawer__panel" data-sheet-panel role="dialog" aria-modal="true" aria-labelledby="drawer-title">
			<div class="drawer__head" data-sheet-handle>
				<h2 id="drawer-title" class="t-3">Enquiry list</h2>
				<button class="text-btn" type="button" data-sheet-close>Close</button>
			</div>
			<div class="drawer__body" data-drawer-body></div>
			<div class="drawer__foot" data-drawer-foot></div>
		</aside>`;
	document.body.append(root);
	const api = createSheet(root, { axis: 'x' });
	const body = root.querySelector('[data-drawer-body]');
	const foot = root.querySelector('[data-drawer-foot]');

	function render() {
		const items = getItems();
		if (!items.length) {
			body.innerHTML = `<div class="drawer__empty"><p class="t-3">Your list is empty.</p><p>Add mugs from the collection, then send the whole list to us in one message.</p><a class="btn" href="${esc(config().collection)}">See the collection</a></div>`;
			foot.hidden = true;
			return;
		}
		body.innerHTML = `<ul class="lines">${items
			.map(
				(i) => `<li class="line" data-id="${esc(i.id)}">
					<a class="line__art" href="${esc(itemUrl(i))}" style="--c: ${esc(i.hex)}" aria-hidden="true" tabindex="-1"><img src="${esc(i.render)}" alt="" width="160" height="160"></a>
					<div class="line__info">
						<a class="line__name link" href="${esc(itemUrl(i))}">${esc(i.name)}</a>
						<span class="line__colour"><span class="glaze glaze--static" style="--glaze: ${esc(i.hex)}" aria-hidden="true"></span>${esc(i.variantName)}</span>
						<div class="qty qty--sm"><button class="qty__btn" type="button" data-step="-50" aria-label="Fewer ${esc(i.name)}">&minus;</button><input class="qty__input" type="number" inputmode="numeric" min="1" value="${i.qty}" aria-label="Pieces of ${esc(i.name)}, ${esc(i.variantName)}" data-qty-input><button class="qty__btn" type="button" data-step="50" aria-label="More ${esc(i.name)}">+</button></div>
					</div>
					<button class="text-btn" type="button" data-remove aria-label="Remove ${esc(i.name)}, ${esc(i.variantName)}">Remove</button>
				</li>`
			)
			.join('')}</ul>`;
		foot.hidden = false;
		foot.innerHTML = `<a class="btn btn--block" href="${esc(whatsappUrl(items))}" target="_blank" rel="noopener">Send on WhatsApp</a>
			<a class="btn btn--block btn--outline" href="${esc(mailtoUrl(items))}">Send by email</a>
			<a class="btn btn--block btn--outline" href="${esc(config().contactUrl)}?enquiry=1">Use the contact form</a>
			<button class="text-btn" type="button" data-clear-list>Clear list</button>`;
	}

	body.addEventListener('click', (e) => {
		const line = e.target.closest('.line');
		if (!line) return;
		if (e.target.closest('[data-remove]')) removeItem(line.dataset.id);
		const step = e.target.closest('[data-step]');
		if (step) setQty(line.dataset.id, Number(line.querySelector('[data-qty-input]').value) + Number(step.dataset.step));
	});
	body.addEventListener('change', (e) => {
		const input = e.target.closest('[data-qty-input]');
		if (input) setQty(input.closest('.line').dataset.id, Number(input.value));
	});
	foot.addEventListener('click', (e) => e.target.closest('[data-clear-list]') && clearItems());
	window.addEventListener('evanza:enquiry-change', () => {
		const before = document.activeElement?.closest?.('.line')?.dataset.id;
		render();
		if (before) body.querySelector(`[data-id="${CSS.escape(before)}"] [data-qty-input]`)?.focus();
	});
	render();
	return api;
}

export function openEnquiryDrawer(from) {
	drawer ||= buildDrawer();
	drawer.open(from);
}

export function initEnquiry() {
	renderCount();
	window.addEventListener('storage', (e) => e.key === KEY && (renderCount(), window.dispatchEvent(new CustomEvent('evanza:enquiry-change'))));
	window.addEventListener('evanza:enquiry-change', renderCount);
	document.addEventListener('click', (e) => {
		const opener = e.target.closest('[data-enquiry-open]');
		if (!opener || e.metaKey || e.ctrlKey || e.shiftKey || e.button !== 0) return;
		e.preventDefault();
		openEnquiryDrawer(opener);
	});
}
