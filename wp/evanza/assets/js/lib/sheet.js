// Bottom sheet (mobile filters). Opens with a drawer curve, closes with Escape, the backdrop,
// the close button or a drag on its handle. The drag tracks the finger 1:1; on release a flick
// or a pull past 40% dismisses, otherwise it springs back. Focus is trapped while open.
const FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]), select, textarea, [tabindex]:not([tabindex="-1"])';

export function createSheet(root, { onClose } = {}) {
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

	// Drag to dismiss
	let start = null;
	handle.addEventListener('pointerdown', (e) => {
		if (!isOpen || start) return; // ignore a second finger
		handle.setPointerCapture(e.pointerId);
		panel.style.transition = 'none';
		backdrop.style.transition = 'none';
		start = { id: e.pointerId, y: e.clientY, samples: [{ y: e.clientY, t: performance.now() }] };
	});
	handle.addEventListener('pointermove', (e) => {
		if (!start || e.pointerId !== start.id) return;
		const dy = e.clientY - start.y;
		// Pulling up meets resistance; pulling down follows the finger.
		const y = dy < 0 ? -Math.sqrt(-dy) * 2.2 : dy;
		panel.style.transform = `translateY(${y}px)`;
		backdrop.style.opacity = String(Math.max(0, 1 - y / panel.offsetHeight));
		start.samples.push({ y: e.clientY, t: performance.now() });
		if (start.samples.length > 5) start.samples.shift();
	});
	const release = (e) => {
		if (!start || e.pointerId !== start.id) return;
		const dy = e.clientY - start.y;
		const a = start.samples[0];
		const b = start.samples[start.samples.length - 1];
		const velocity = b.t > a.t ? (b.y - a.y) / (b.t - a.t) : 0; // px per ms, down is positive
		start = null;
		panel.style.transition = '';
		backdrop.style.transition = '';
		if (dy > panel.offsetHeight * 0.4 || velocity > 0.5) close();
		else {
			panel.style.transform = '';
			backdrop.style.opacity = '';
		}
	};
	handle.addEventListener('pointerup', release);
	handle.addEventListener('pointercancel', release);

	return { open, close, get isOpen() { return isOpen; } };
}
