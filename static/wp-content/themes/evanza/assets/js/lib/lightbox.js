// Accessible lightbox on a native <dialog>: focus is trapped and Escape closes it by the browser;
// arrow keys, the Previous and Next buttons and a swipe move through the group of photos.
// Triggers are buttons with data-lightbox="<group>", data-src and data-caption.
let dialog;
let items = [];
let index = 0;

const $ = (sel) => dialog.querySelector(sel);

function build() {
	dialog = document.createElement('dialog');
	dialog.className = 'lb';
	dialog.setAttribute('aria-label', 'Photo viewer');
	dialog.innerHTML = `<div class="lb__bar"><p class="lb__count" aria-live="polite"></p><button class="text-btn" type="button" data-lb-close>Close</button></div>
		<div class="lb__stage" data-lb-stage>
			<button class="lb__nav lb__nav--prev text-btn" type="button" data-lb-prev>Previous</button>
			<img class="lb__img" alt="" draggable="false">
			<button class="lb__nav lb__nav--next text-btn" type="button" data-lb-next>Next</button>
		</div>
		<p class="lb__caption" data-lb-caption></p>`;
	document.body.append(dialog);

	$('[data-lb-close]').addEventListener('click', () => dialog.close());
	$('[data-lb-prev]').addEventListener('click', () => go(-1));
	$('[data-lb-next]').addEventListener('click', () => go(1));
	dialog.addEventListener('click', (e) => e.target === dialog && dialog.close());
	dialog.addEventListener('keydown', (e) => {
		if (e.key === 'ArrowRight') go(1);
		else if (e.key === 'ArrowLeft') go(-1);
	});
	dialog.addEventListener('close', () => {
		document.documentElement.classList.remove('lb-open');
		window.evanzaLenis?.start();
	});

	// Swipe: a short, quick horizontal drag or a long one changes photo.
	let start = null;
	const stage = $('[data-lb-stage]');
	stage.addEventListener('pointerdown', (e) => {
		if (e.target.closest('button') || start) return;
		start = { id: e.pointerId, x: e.clientX, t: performance.now() };
	});
	const end = (e) => {
		if (!start || e.pointerId !== start.id) return;
		const dx = e.clientX - start.x;
		const v = Math.abs(dx) / Math.max(1, performance.now() - start.t);
		start = null;
		if (Math.abs(dx) > 70 || (Math.abs(dx) > 30 && v > 0.4)) go(dx < 0 ? 1 : -1);
	};
	stage.addEventListener('pointerup', end);
	stage.addEventListener('pointercancel', () => (start = null));
}

function show() {
	const item = items[index];
	const img = $('.lb__img');
	img.classList.add('is-loading');
	img.onload = () => img.classList.remove('is-loading');
	img.src = item.dataset.src;
	img.alt = item.dataset.caption;
	$('[data-lb-caption]').textContent = item.dataset.caption;
	$('.lb__count').textContent = `${index + 1} of ${items.length}`;
	const multiple = items.length > 1;
	$('[data-lb-prev]').hidden = !multiple;
	$('[data-lb-next]').hidden = !multiple;
	// Warm the neighbours.
	for (const n of [index - 1, index + 1]) if (items[n]) new Image().src = items[n].dataset.src;
}

function go(step) {
	index = (index + step + items.length) % items.length;
	show();
}

export function initLightbox() {
	document.addEventListener('click', (e) => {
		const trigger = e.target.closest('[data-lightbox]');
		if (!trigger) return;
		dialog || build();
		items = [...document.querySelectorAll(`[data-lightbox="${trigger.dataset.lightbox}"]`)];
		index = items.indexOf(trigger);
		show();
		document.documentElement.classList.add('lb-open');
		window.evanzaLenis?.stop();
		dialog.showModal();
	});
}
