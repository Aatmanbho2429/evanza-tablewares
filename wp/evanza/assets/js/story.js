// Clay to cup: a pinned scroll story. The mug starts as raw clay (bisque), glaze rises up it, the
// kiln glow fires, and it ends glazed. This is the only scroll-driven 3D on the site.
// The section is a tall track with a sticky stage, so no scroll library or pin spacer is needed.
import { reducedMotion } from './lib/motion.js';

const section = document.querySelector('[data-story]');
if (section) init(section);

const clamp01 = (v) => Math.min(1, Math.max(0, v));
const smooth = (v) => v * v * (3 - 2 * v);

async function init(section) {
	const canvas = section.querySelector('[data-story-canvas]');
	const steps = [...section.querySelectorAll('[data-step]')];
	let colour = { body: canvas.dataset.body };
	document.addEventListener('evanza:glaze', (e) => {
		colour = e.detail;
		viewer?.setColors(colour, { animate: !reducedMotion.matches });
	});

	let viewer = null;
	let near = false;
	let started = false;

	// Phases of the scroll (0 to 1): hold the clay, glaze, fire, cool.
	function apply(p) {
		let active = 0;
		if (p < 0.14) viewer.setState('bisque');
		else if (p < 0.58) {
			viewer.setState('glazing', smooth(clamp01((p - 0.14) / 0.44)));
			active = 1;
		} else {
			const kiln = p < 0.7 ? (p - 0.58) / 0.12 : Math.max(0, 1 - (p - 0.7) / 0.16);
			if (kiln > 0) viewer.setState('fired', kiln);
			else viewer.setState('glazed');
			active = 2;
		}
		if (p >= 0.14 && p < 0.2) active = 0;
		steps.forEach((el, i) => el.toggleAttribute('data-active', i === active));
	}

	function progress() {
		const r = section.querySelector('.story__track').getBoundingClientRect();
		return clamp01(-r.top / Math.max(1, r.height - window.innerHeight));
	}

	async function start() {
		if (started) return;
		started = true;
		try {
			const { createMugViewer } = await import('./mug3d.js');
			viewer = createMugViewer(canvas, {
				colors: colour,
				shape: 'classic',
				autoRotate: true,
				interactive: true,
				state: 'bisque',
				onFail: () => (canvas.hidden = true),
			});
			if (reducedMotion.matches) {
				// A static sequence: the finished mug and all three lines.
				viewer.setState('glazed');
				steps.forEach((el) => el.setAttribute('data-active', ''));
				return;
			}
			const tick = () => {
				if (near) apply(progress());
			};
			window.addEventListener('scroll', () => requestAnimationFrame(tick), { passive: true });
			tick();
		} catch (error) {
			canvas.hidden = true;
			console.warn('Mug viewer unavailable for the scroll story.', error);
		}
	}

	new IntersectionObserver(
		(entries) => {
			near = entries[entries.length - 1].isIntersecting;
			if (near) start();
		},
		{ rootMargin: '120% 0px' }
	).observe(section);

	if (reducedMotion.matches) steps.forEach((el) => el.setAttribute('data-active', ''));
	else steps[0]?.setAttribute('data-active', '');
}
