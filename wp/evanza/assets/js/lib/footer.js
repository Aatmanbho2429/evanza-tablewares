// Footer: the "Back to top" button scrolls with Lenis when it is running, instantly under reduced motion.
import { reducedMotion } from './motion.js';

export function initFooter() {
	document.querySelector('[data-back-top]')?.addEventListener('click', () => {
		if (reducedMotion.matches) window.scrollTo(0, 0);
		else if (window.evanzaLenis) window.evanzaLenis.scrollTo(0);
		else window.scrollTo({ top: 0, behavior: 'smooth' });
	});
}
