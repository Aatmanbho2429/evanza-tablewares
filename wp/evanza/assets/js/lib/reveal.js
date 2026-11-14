// Reveal on scroll: adds .is-in to [data-reveal] elements as they enter the viewport.
// A parent with [data-reveal-group] staggers its [data-reveal] children.
import { reducedMotion } from './motion.js';

let observer;

function getObserver() {
	if (observer) return observer;
	observer = new IntersectionObserver(
		(entries) => {
			for (const entry of entries) {
				if (!entry.isIntersecting) continue;
				entry.target.classList.add('is-in');
				observer.unobserve(entry.target);
			}
		},
		{ rootMargin: '0px 0px -8% 0px', threshold: 0.08 }
	);
	return observer;
}

/** Observe every [data-reveal] inside root. Safe to call again for content added later. */
export function observeReveals(root = document) {
	const items = root.querySelectorAll('[data-reveal]:not(.is-in)');
	if (reducedMotion.matches || !('IntersectionObserver' in window)) {
		items.forEach((el) => el.classList.add('is-in'));
		return;
	}
	root.querySelectorAll('[data-reveal-group]').forEach((group) => {
		group.querySelectorAll(':scope > [data-reveal]').forEach((el, i) => el.style.setProperty('--i', i));
	});
	const io = getObserver();
	items.forEach((el) => io.observe(el));
}
