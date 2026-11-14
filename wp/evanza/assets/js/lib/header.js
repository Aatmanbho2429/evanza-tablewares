// Header: a floating bar that takes the colour and tone of the room beneath it, gains an edge once the
// page has scrolled, hides on scroll down and returns on scroll up, and runs the accessible mobile menu.
const DESKTOP = window.matchMedia('(min-width: 56rem)');

export function initHeader() {
	const header = document.querySelector('[data-header]');
	if (!header) return;
	const rooms = [...document.querySelectorAll('[data-room]')];
	const menuOpen = () => document.documentElement.classList.contains('menu-open');

	// Tone: copy the colour and tone of the room under the header.
	function syncRoom() {
		if (menuOpen()) return;
		const y = header.getBoundingClientRect().top + header.offsetHeight / 2;
		const room = rooms.find((r) => {
			const b = r.getBoundingClientRect();
			return b.top <= y && b.bottom > y;
		});
		if (!room) return;
		header.dataset.tone = room.dataset.tone || 'light';
		const style = getComputedStyle(room);
		header.style.setProperty('--room', (style.getPropertyValue('--room-header') || style.getPropertyValue('--room')).trim());
	}

	// Hide on scroll down, show on scroll up. The state is mirrored on <html> so sticky bars can follow.
	function setHidden(hidden) {
		header.toggleAttribute('data-hidden', hidden);
		document.documentElement.toggleAttribute('data-header-hidden', hidden);
	}
	let lastY = window.scrollY;
	let ticking = false;
	function onScroll() {
		if (ticking) return;
		ticking = true;
		requestAnimationFrame(() => {
			ticking = false;
			const y = window.scrollY;
			header.toggleAttribute('data-scrolled', y > 40);
			const delta = y - lastY;
			if (y < 40 || delta < -6 || menuOpen()) setHidden(false);
			else if (delta > 6 && y > header.offsetHeight * 2) setHidden(true);
			if (Math.abs(delta) > 6) lastY = y;
			syncRoom();
		});
	}
	header.toggleAttribute('data-scrolled', window.scrollY > 40);
	window.addEventListener('scroll', onScroll, { passive: true });
	window.addEventListener('resize', syncRoom);
	document.addEventListener('evanza:room', syncRoom);
	header.addEventListener('focusin', () => setHidden(false));
	syncRoom();

	// Mobile menu
	const toggle = header.querySelector('[data-menu-toggle]');
	const toggleLabel = toggle?.querySelector('[data-menu-label]');
	const menu = document.querySelector('[data-mobile-menu]');
	if (!toggle || !menu) return;
	const links = [...menu.querySelectorAll('a')];
	menu.querySelectorAll('.nav-list li').forEach((li, i) => li.style.setProperty('--i', i));
	menu.inert = true;
	const trap = [toggle, ...links];
	const isOpen = () => menu.classList.contains('is-open');

	function setOpen(open, restoreFocus = false) {
		menu.classList.toggle('is-open', open);
		menu.inert = !open;
		document.documentElement.classList.toggle('menu-open', open);
		toggle.setAttribute('aria-expanded', String(open));
		if (toggleLabel) toggleLabel.textContent = open ? 'Close menu' : 'Open menu';
		if (open) {
			setHidden(false);
			header.dataset.tone = 'dark';
			header.style.setProperty('--room', 'var(--graphite)');
			links[0]?.focus({ preventScroll: true });
		} else {
			syncRoom();
			if (restoreFocus) toggle.focus();
		}
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
	DESKTOP.addEventListener('change', (e) => e.matches && isOpen() && setOpen(false));
}
