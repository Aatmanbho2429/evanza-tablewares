// Home hero: split headline with the 3D mug between its two lines, glaze dots and the glaze pour.
import { reducedMotion } from './lib/motion.js';
import { createPour } from './lib/glaze-pour.js';

const hero = document.querySelector('[data-hero]');
if (hero) initHero(hero);

/** Wraps each word and character of every [data-split] line so they can rise one by one. */
function splitLines(root, startAt) {
	root.querySelectorAll('[data-split]').forEach((line, lineIndex) => {
		const text = line.textContent;
		line.textContent = '';
		let n = 0;
		text.split(' ').forEach((word, wi, words) => {
			const w = document.createElement('span');
			w.className = 'word';
			for (const ch of word) {
				const c = document.createElement('span');
				c.className = 'char';
				c.setAttribute('aria-hidden', 'true');
				c.style.setProperty('--d', startAt + lineIndex * 90 + n * 22);
				c.textContent = ch;
				w.append(c);
				n++;
			}
			line.append(w);
			if (wi < words.length - 1) line.append(' ');
		});
	});
}

async function initHero(hero) {
	const back = hero.querySelector('[data-hero-title]');
	const front = hero.querySelector('[data-hero-front]');
	splitLines(back, 0);
	splitLines(front, 260);
	hero.classList.add('is-split');
	document.documentElement.classList.add('js-ready');

	await Promise.race([document.fonts.ready, new Promise((r) => setTimeout(r, 900))]);
	hero.classList.add('is-entering');

	// Glaze dots
	const dots = [...hero.querySelectorAll('[data-glaze]')];
	const label = hero.querySelector('[data-glaze-name]');
	const pour = createPour(hero);
	let viewer = null;
	let current = dots.find((d) => d.getAttribute('aria-pressed') === 'true') || dots[0];

	const glaze = (d) => ({ body: d.dataset.body, inner: d.dataset.inner || d.dataset.body, handle: d.dataset.handle || d.dataset.body });

	for (const dot of dots) {
		dot.addEventListener('click', () => {
			if (dot === current) return;
			current.setAttribute('aria-pressed', 'false');
			dot.setAttribute('aria-pressed', 'true');
			current = dot;
			label.textContent = dot.dataset.name;
			pour(dot.dataset.room, dot);
			viewer?.setColors(glaze(dot), { animate: true, duration: 600, delay: 110 });
			const still = hero.querySelector('[data-hero-still]');
			if (still && dot.dataset.still) still.src = dot.dataset.still;
		});
	}

	// 3D mug
	const canvas = hero.querySelector('[data-hero-canvas]');
	try {
		const { createMugViewer } = await import('./mug3d.js');
		viewer = createMugViewer(canvas, { colors: glaze(current), shape: 'classic', autoRotate: true, interactive: true });
		viewer.enter({ delay: reducedMotion.matches ? 0 : 120 });
		canvas.classList.add('is-ready');
		hero.dataset.gl = 'on';
	} catch (error) {
		hero.dataset.gl = 'off';
		console.warn('Mug viewer unavailable, showing the still image.', error);
	}
}
