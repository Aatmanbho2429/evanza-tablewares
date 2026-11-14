// Smooth scrolling on desktop pointers only. Off for touch and reduced motion.
import { reducedMotion } from './motion.js';

export async function initSmoothScroll() {
	if (reducedMotion.matches || !window.matchMedia('(hover: hover) and (pointer: fine)').matches) return;
	const { default: Lenis } = await import('../../vendor/lenis/lenis.mjs');
	const lenis = new Lenis({ lerp: 0.1, wheelMultiplier: 1, anchors: true });
	window.evanzaLenis = lenis;
	const raf = (time) => {
		lenis.raf(time);
		requestAnimationFrame(raf);
	};
	requestAnimationFrame(raf);
	reducedMotion.addEventListener('change', (e) => e.matches && lenis.destroy());
}
