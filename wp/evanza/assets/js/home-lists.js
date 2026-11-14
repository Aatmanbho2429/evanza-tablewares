// Home lists: the category rows float that category's render beside the cursor, and the
// "New in" rail drags with momentum.
import { initRail } from './lib/rail.js';
import { reducedMotion } from './lib/motion.js';

document.querySelectorAll('[data-rail]').forEach(initRail);

const list = document.querySelector('[data-cats]');
if (list && window.matchMedia('(hover: hover) and (pointer: fine)').matches && !reducedMotion.matches) {
	const float = document.createElement('div');
	float.className = 'cats__float';
	float.setAttribute('aria-hidden', 'true');
	float.innerHTML = '<img alt="" width="400" height="400">';
	document.body.append(float);
	const img = float.querySelector('img');

	let x = 0;
	let y = 0;
	let tx = 0;
	let ty = 0;
	let raf = 0;
	let shown = false;
	const loop = () => {
		x += (tx - x) * 0.2;
		y += (ty - y) * 0.2;
		float.style.transform = `translate3d(${x}px, ${y}px, 0)`;
		raf = shown || Math.abs(tx - x) > 0.5 || Math.abs(ty - y) > 0.5 ? requestAnimationFrame(loop) : 0;
	};
	const move = (e) => {
		tx = e.clientX + 28;
		ty = e.clientY - 150;
		if (!raf) raf = requestAnimationFrame(loop);
	};

	list.addEventListener('pointerover', (e) => {
		const row = e.target.closest('.cats__row');
		if (!row) return;
		img.src = row.dataset.render;
		float.style.setProperty('--c', row.dataset.hex);
		if (!shown) {
			x = tx = e.clientX + 28;
			y = ty = e.clientY - 150;
			shown = true;
			float.classList.add('is-on');
			if (!raf) raf = requestAnimationFrame(loop);
		}
	});
	list.addEventListener('pointermove', move);
	const hide = () => {
		shown = false;
		float.classList.remove('is-on');
	};
	list.addEventListener('pointerleave', hide);
	window.addEventListener('scroll', hide, { passive: true });
}
