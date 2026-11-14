// FLIP re-order for a grid of keyed items. Choreography: leaving items stop taking space at once
// (so the others close the gap while they fade), staying items slide to their new place, and
// entering items fade in. No stagger: filtering is a frequent, user-driven action.
import { reducedMotion } from './motion.js';

const MOVE = { duration: 420, easing: 'cubic-bezier(0.32, 0.72, 0, 1)' };
const FADE_OUT = { duration: 160, easing: 'cubic-bezier(0.23, 1, 0.32, 1)', fill: 'forwards' };
const FADE_IN = { duration: 260, delay: 80, easing: 'cubic-bezier(0.23, 1, 0.32, 1)', fill: 'backwards' };

/**
 * @param {HTMLElement} grid  Container (position: relative).
 * @param {Map<string, HTMLElement>} elements  Every element ever created, by key.
 * @param {string[]} nextKeys  Keys to show, in order.
 */
export function flipTo(grid, elements, nextKeys) {
	const animate = !reducedMotion.matches && grid.children.length > 0;
	const gridBox = grid.getBoundingClientRect();
	const first = new Map();
	if (animate) for (const el of grid.children) first.set(el.dataset.key, el.getBoundingClientRect());

	const next = new Set(nextKeys);
	const leaving = [...grid.children].filter((el) => !next.has(el.dataset.key));

	// Leaving: lift out of the flow at the current position, then fade.
	for (const el of leaving) {
		const r = first.get(el.dataset.key);
		if (!animate || !r) {
			el.remove();
			continue;
		}
		Object.assign(el.style, {
			position: 'absolute',
			top: `${r.top - gridBox.top}px`,
			left: `${r.left - gridBox.left}px`,
			width: `${r.width}px`,
			pointerEvents: 'none',
		});
		el.setAttribute('aria-hidden', 'true');
		el.animate([{ opacity: 1, transform: 'scale(1)' }, { opacity: 0, transform: 'scale(0.96)' }], FADE_OUT).finished
			.then(() => el.remove())
			.catch(() => el.remove());
	}

	// Place the kept and entering items in order.
	const entering = [];
	for (const key of nextKeys) {
		const el = elements.get(key);
		if (!el.isConnected || el.parentElement !== grid || !first.has(key)) entering.push(el);
		el.style.cssText = '';
		el.removeAttribute('aria-hidden');
		grid.append(el);
	}

	if (!animate) return;
	for (const key of nextKeys) {
		const el = elements.get(key);
		el.getAnimations().forEach((a) => a.cancel());
		if (entering.includes(el)) {
			el.animate([{ opacity: 0, transform: 'scale(0.96)' }, { opacity: 1, transform: 'scale(1)' }], FADE_IN);
			continue;
		}
		const before = first.get(key);
		const after = el.getBoundingClientRect();
		const dx = before.left - after.left;
		const dy = before.top - after.top;
		if (Math.abs(dx) < 1 && Math.abs(dy) < 1) continue;
		el.animate([{ transform: `translate(${dx}px, ${dy}px)` }, { transform: 'none' }], MOVE);
	}
}
