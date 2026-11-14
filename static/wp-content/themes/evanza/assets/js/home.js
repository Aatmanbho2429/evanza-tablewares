// Home hero: split headline with the 3D mug between its two lines, glaze dots and the glaze pour.
import { createPour, POUR_MS } from './lib/glaze-pour.js';

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
				c.style.setProperty('--d', startAt + lineIndex * 80 + n * 18);
				c.textContent = ch;
				w.append(c);
				n++;
			}
			line.append(w);
			if (wi < words.length - 1) line.append(' ');
		});
	});
}

// A frame, or 100ms in a hidden tab where frames do not run.
function nextFrame() {
	return Promise.race([new Promise((r) => requestAnimationFrame(r)), wait(100)]);
}
function wait(ms) {
	return new Promise((r) => setTimeout(r, ms));
}

async function initHero(hero) {
	const back = hero.querySelector('[data-hero-title]');
	const front = hero.querySelector('[data-hero-front]');
	// The mug lands as the back line finishes; the front line rises over its base just after.
	splitLines(back, 0);
	splitLines(front, 380);
	hero.classList.add('is-split');
	document.documentElement.classList.add('js-ready');

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
			document.dispatchEvent(new CustomEvent('evanza:glaze', { detail: glaze(dot) }));
			// The mug recolours in step with the pour: same start, same length.
			viewer?.setColors(glaze(dot), { animate: true, duration: POUR_MS, delay: 0 });
			const still = hero.querySelector('[data-hero-still]');
			if (still && dot.dataset.still) still.src = dot.dataset.still;
		});
	}

	// Load the 3D module while the font loads, so the headline and the mug start together.
	const canvas = hero.querySelector('[data-hero-canvas]');
	const moduleReady = import('./mug3d.js');
	moduleReady.catch(() => {});
	await Promise.race([Promise.all([document.fonts.load('800 1em "Bricolage Grotesque"'), document.fonts.ready]).catch(() => {}), wait(900)]);
	const mod = await Promise.race([moduleReady, wait(1500)]).catch(() => null);

	try {
		if (mod) {
			viewer = startViewer(mod);
			await nextFrame(); // let the first frame (shader compile) happen before anything moves
			await nextFrame();
		}
		beginEntrance();
		if (!mod) {
			// The module is slow: the headline went ahead, the mug follows when it is ready.
			viewer = startViewer(await moduleReady);
			await nextFrame();
			await nextFrame();
			beginMug();
		}
	} catch (error) {
		beginEntrance();
		hero.dataset.gl = 'off';
		console.warn('Mug viewer unavailable, showing the still image.', error);
	}

	function beginEntrance() {
		hero.classList.add('is-entering');
		// Once the characters have risen, the line masks are no longer needed.
		setTimeout(() => hero.classList.add('is-entered'), 2400);
		if (viewer && mod) beginMug();
	}

	function beginMug() {
		viewer.enter({ glaze: true, delay: 0 });
		canvas.classList.add('is-ready');
		hero.dataset.gl = 'on';
	}

	function startViewer({ createMugViewer }) {
		const v = createMugViewer(canvas, {
			colors: glaze(current),
			shape: 'classic',
			autoRotate: true,
			interactive: true,
			onFail: () => (hero.dataset.gl = 'off'),
		});
		// The mug stands between the lines: its top below the back line, its base overlapping the front line.
		// The canvas reaches up to the top of the hero (the mug drops in from there) and below the stage,
		// so the mug, its shadow and the landing settle stay inside it.
		const stage = hero.querySelector('.hero__stage');
		const bar = hero.querySelector('.hero__bar');
		const lineEl = back.querySelector('.hero__line');
		const place = () => {
			const S = stage.clientHeight;
			const L = lineEl.offsetHeight;
			const W = canvas.clientWidth;
			if (!S || !L) return;
			// Height the mug may take: the gap plus a 35% overlap with the front line, never wider than the canvas allows.
			const byGap = (S - 2.04 * L) / 0.65;
			const byWidth = (W * 0.8) / 1.64;
			const height = Math.max(S * 0.3, Math.min(byGap, byWidth, S - 1.1 * L));
			const bottom = Math.min(S - L + 0.35 * height, S - 0.05 * L);
			const up = stage.getBoundingClientRect().top - hero.getBoundingClientRect().top;
			const down = Math.min(0.3 * height, bar.offsetHeight * 0.8); // room for the contact shadow below the base
			canvas.style.top = `${-up}px`;
			canvas.style.bottom = `${-down}px`;
			const C = up + S + down;
			const centre = up + bottom - height / 2;
			v.setFraming({ fit: height / C, offsetY: (centre - C / 2) / C });
		};
		place();
		new ResizeObserver(place).observe(stage);
		return v;
	}
}
