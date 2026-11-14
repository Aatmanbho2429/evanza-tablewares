// A horizontal rail: native scroll-snap on touch, drag with momentum for a mouse.
export function initRail(el) {
	let down = false;
	let moved = false;
	let startX = 0;
	let startScroll = 0;
	let velocity = 0;
	let last = { x: 0, t: 0 };
	let raf = 0;

	el.addEventListener('pointerdown', (e) => {
		if (e.pointerType !== 'mouse' || e.button !== 0) return;
		cancelAnimationFrame(raf);
		down = true;
		moved = false;
		startX = last.x = e.clientX;
		last.t = performance.now();
		startScroll = el.scrollLeft;
		velocity = 0;
	});
	window.addEventListener('pointermove', (e) => {
		if (!down) return;
		const dx = e.clientX - startX;
		if (!moved && Math.abs(dx) > 4) {
			moved = true;
			el.classList.add('is-dragging');
		}
		if (!moved) return;
		el.scrollLeft = startScroll - dx;
		const now = performance.now();
		velocity = 0.7 * velocity + 0.3 * ((last.x - e.clientX) / Math.max(1, now - last.t)); // px per ms
		last = { x: e.clientX, t: now };
	});
	window.addEventListener('pointerup', () => {
		if (!down) return;
		down = false;
		if (!moved) return;
		let v = Math.max(-30, Math.min(30, velocity * 16)); // px per frame
		const glide = () => {
			el.scrollLeft += v;
			v *= 0.94;
			if (Math.abs(v) > 0.4) raf = requestAnimationFrame(glide);
			else el.classList.remove('is-dragging');
		};
		glide();
	});
	// A drag is not a click.
	el.addEventListener('click', (e) => moved && (e.preventDefault(), e.stopPropagation(), (moved = false)), true);
	el.addEventListener('dragstart', (e) => e.preventDefault());
}
