// Bottom sheet (mobile filters) or side drawer (enquiry list, axis: 'x'). Opens with a drawer curve, closes with Escape, the backdrop,
// the close button or a drag on its handle. The drag tracks the finger 1:1; on release a flick
// or a pull past 40% dismisses, otherwise it springs back. Focus is trapped while open.
const FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]), select, textarea, [tabindex]:not([tabindex="-1"])';

export function createSheet(root, { onClose, axis = 'y' } = {}) {
	const horizontal = axis === 'x';
	const panel = root.querySelector('[data-sheet-panel]');
	const backdrop = root.querySelector('[data-sheet-backdrop]');
	const handle = root.querySelector('[data-sheet-handle]');
	let opener = null;
	let isOpen = false;

	const outside = () => [...document.body.children].filter((el) => el !== root && !el.matches('script, style, link'));
	const focusables = () => [...panel.querySelectorAll(FOCUSABLE)].filter((el) => !el.closest('[hidden]'));

	function open(from) {
		if (isOpen) return;
		isOpen = true;
		opener = from || document.activeElement;
		root.hidden = false;
		outside().forEach((el) => (el.inert = true));
		document.documentElement.classList.add('sheet-open');
		// Next frame so the transition runs from the closed position.
		requestAnimationFrame(() => {
			root.classList.add('is-open');
			focusables()[0]?.focus({ preventScroll: true });
		});
	}

	function close({ restoreFocus = true } = {}) {
		if (!isOpen) return;
		isOpen = false;
		root.classList.remove('is-open');
		panel.style.transform = '';
		backdrop.style.opacity = '';
		outside().forEach((el) => (el.inert = false));
		document.documentElement.classList.remove('sheet-open');
		const done = () => {
			if (!isOpen) root.hidden = true;
		};
		panel.addEventListener('transitionend', done, { once: true });
		setTimeout(done, 520);
		if (restoreFocus) opener?.focus({ preventScroll: true });
		onClose?.();
	}

	backdrop.addEventListener('click', () => close());
	root.querySelectorAll('[data-sheet-close]').forEach((b) => b.addEventListener('click', () => close()));
	root.addEventListener('keydown', (e) => {
		if (!isOpen) return;
		if (e.key === 'Escape') {
			e.stopPropagation();
			close();
		} else if (e.key === 'Tab') {
			const items = focusables();
			const first = items[0];
			const last = items[items.length - 1];
			if (e.shiftKey && document.activeElement === first) {
				e.preventDefault();
				last.focus();
			} else if (!e.shiftKey && document.activeElement === last) {
				e.preventDefault();
				first.focus();
			}
		}
	});

	// Drag to dismiss (down for a bottom sheet, right for a side drawer)
	const pos = (e) => (horizontal ? e.clientX : e.clientY);
	const size = () => (horizontal ? panel.offsetWidth : panel.offsetHeight);
	const move = (d) => `translate${horizontal ? 'X' : 'Y'}(${d}px)`;
	let start = null;
	handle.addEventListener('pointerdown', (e) => {
		if (!isOpen || start) return; // ignore a second finger
		handle.setPointerCapture(e.pointerId);
		panel.style.transition = 'none';
		backdrop.style.transition = 'none';
		start = { id: e.pointerId, p: pos(e), samples: [{ p: pos(e), t: performance.now() }] };
	});
	handle.addEventListener('pointermove', (e) => {
		if (!start || e.pointerId !== start.id) return;
		const d = pos(e) - start.p;
		// Pulling the wrong way meets resistance; pulling toward closed follows the finger.
		const v = d < 0 ? -Math.sqrt(-d) * 2.2 : d;
		panel.style.transform = move(v);
		backdrop.style.opacity = String(Math.max(0, 1 - v / size()));
		start.samples.push({ p: pos(e), t: performance.now() });
		if (start.samples.length > 5) start.samples.shift();
	});
	const release = (e) => {
		if (!start || e.pointerId !== start.id) return;
		const d = pos(e) - start.p;
		const a = start.samples[0];
		const b = start.samples[start.samples.length - 1];
		const velocity = b.t > a.t ? (b.p - a.p) / (b.t - a.t) : 0; // px per ms, toward closed is positive
		start = null;
		panel.style.transition = '';
		backdrop.style.transition = '';
		if (d > size() * 0.4 || velocity > 0.5) close();
		else {
			panel.style.transform = '';
			backdrop.style.opacity = '';
		}
	};
	handle.addEventListener('pointerup', release);
	handle.addEventListener('pointercancel', release);

	return { open, close, get isOpen() { return isOpen; } };
}
