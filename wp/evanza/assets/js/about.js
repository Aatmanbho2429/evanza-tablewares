// About: the making process swaps its photo as each step passes the middle of the screen,
// and the certificates open in the lightbox.
import { initLightbox } from './lib/lightbox.js';

initLightbox();

const process = document.querySelector('[data-process]');
if (process) {
	const steps = [...process.querySelectorAll('[data-step]')];
	const frames = [...process.querySelectorAll('[data-frame]')];
	const setActive = (i) => {
		steps.forEach((s, n) => s.classList.toggle('is-active', n === i));
		frames.forEach((f, n) => f.classList.toggle('is-active', n === i));
	};
	const io = new IntersectionObserver(
		(entries) => {
			for (const entry of entries) if (entry.isIntersecting) setActive(Number(entry.target.dataset.step));
		},
		{ rootMargin: '-45% 0px -45% 0px' }
	);
	steps.forEach((s) => io.observe(s));
}
