// The glaze pour: a new colour floods a room as a circle growing from the pressed control.
// Used on Home, Collection and Product. A lighter "wave" leads the colour so the edge reads as liquid.
import { reducedMotion } from './motion.js';

const WAVE_MS = 520;
const FILL_MS = 650;
const FILL_DELAY = 70;
const EASE = 'cubic-bezier(0.23, 1, 0.32, 1)';

function mix(hex, other, amount) {
	const a = hex.match(/\w\w/g).map((h) => parseInt(h, 16));
	const b = other.match(/\w\w/g).map((h) => parseInt(h, 16));
	return '#' + a.map((v, i) => Math.round(v + (b[i] - v) * amount).toString(16).padStart(2, '0')).join('');
}

/** Relative luminance of #rrggbb, 0 to 1. */
export function luminance(hex) {
	const [r, g, b] = hex.slice(1).match(/\w\w/g).map((h) => {
		const c = parseInt(h, 16) / 255;
		return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
	});
	return 0.2126 * r + 0.7152 * g + 0.0722 * b;
}
export const toneFor = (hex) => (luminance(hex) < 0.2 ? 'dark' : 'light');

/**
 * @param {HTMLElement} room Element with a --room colour and data-tone.
 * @returns {(color: string, origin?: HTMLElement|{x:number,y:number}) => void}
 */
export function createPour(room) {
	let current = null; // { layers, timer, color, tone }

	function commit() {
		if (!current) return;
		clearTimeout(current.timer);
		current.layers.forEach((l) => l.remove());
		room.style.setProperty('--room', current.color);
		room.style.removeProperty('--room-header');
		room.dataset.tone = current.tone;
		current = null;
		room.dispatchEvent(new CustomEvent('evanza:room', { bubbles: true }));
	}

	return function pour(color, origin) {
		const tone = toneFor(color);
		commit(); // an interrupted pour finishes at once, then the next starts from the new press

		if (reducedMotion.matches) {
			room.style.setProperty('--room', color);
			room.dataset.tone = tone;
			room.dispatchEvent(new CustomEvent('evanza:room', { bubbles: true }));
			return;
		}

		const box = room.getBoundingClientRect();
		let x = box.width / 2;
		let y = box.height / 2;
		if (origin instanceof Element) {
			const o = origin.getBoundingClientRect();
			x = o.left + o.width / 2 - box.left;
			y = o.top + o.height / 2 - box.top;
		} else if (origin) {
			x = origin.x - box.left;
			y = origin.y - box.top;
		}
		const radius = Math.hypot(Math.max(x, box.width - x), Math.max(y, box.height - y)) + 2;

		const makeLayer = (bg) => {
			const el = document.createElement('span');
			el.className = 'pour';
			el.style.background = bg;
			el.style.clipPath = `circle(0px at ${x}px ${y}px)`;
			room.prepend(el);
			return el;
		};
		const wave = makeLayer(mix(color, '#ffffff', 0.28));
		const fill = makeLayer(color);
		const grow = (el, duration, delay) =>
			el.animate(
				[{ clipPath: `circle(0px at ${x}px ${y}px)` }, { clipPath: `circle(${radius}px at ${x}px ${y}px)` }],
				{ duration, delay, easing: EASE, fill: 'forwards' }
			);
		grow(wave, WAVE_MS, 0);
		const done = grow(fill, FILL_MS, FILL_DELAY);

		current = { layers: [wave, fill], color, tone, timer: setTimeout(() => {
			room.dataset.tone = tone; // text colour flips while the colour is mid-flood
			room.style.setProperty('--room-header', color); // the header follows
			room.dispatchEvent(new CustomEvent('evanza:room', { bubbles: true }));
		}, (FILL_DELAY + FILL_MS) * 0.4) };
		done.finished.then(commit).catch(() => {});
	};
}
