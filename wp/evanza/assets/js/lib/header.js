// Header: solid background after scrolling, accessible full-screen mobile menu.
const DESKTOP = window.matchMedia('(min-width: 56rem)');

export function initHeader() {
	const header = document.querySelector('[data-header]');
	if (!header) return;

	const onScroll = () => header.toggleAttribute('data-scrolled', window.scrollY > 12);
	onScroll();
	window.addEventListener('scroll', onScroll, { passive: true });

	const toggle = header.querySelector('[data-menu-toggle]');
	const menu = document.querySelector('[data-mobile-menu]');
	if (!toggle || !menu) return;

	const links = [...menu.querySelectorAll('a')];
	menu.querySelectorAll('.nav-list li').forEach((li, i) => li.style.setProperty('--i', i));
	menu.inert = true;

	// Keep keyboard focus inside the menu and its button while open.
	const trap = [toggle, ...links];
	const isOpen = () => menu.classList.contains('is-open');

	function setOpen(open, restoreFocus = false) {
		menu.classList.toggle('is-open', open);
		menu.inert = !open;
		document.documentElement.classList.toggle('menu-open', open);
		toggle.setAttribute('aria-expanded', String(open));
		toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
		if (open) links[0]?.focus({ preventScroll: true });
		else if (restoreFocus) toggle.focus();
	}

	toggle.addEventListener('click', () => setOpen(!isOpen()));
	links.forEach((a) => a.addEventListener('click', () => setOpen(false)));

	document.addEventListener('keydown', (e) => {
		if (!isOpen()) return;
		if (e.key === 'Escape') {
			setOpen(false, true);
		} else if (e.key === 'Tab') {
			const i = trap.indexOf(document.activeElement);
			const next = e.shiftKey ? (i <= 0 ? trap.length - 1 : i - 1) : (i + 1) % trap.length;
			e.preventDefault();
			trap[next].focus();
		}
	});

	DESKTOP.addEventListener('change', (e) => {
		if (e.matches && isOpen()) setOpen(false);
	});
}
