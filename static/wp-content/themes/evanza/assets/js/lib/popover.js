// Disclosure popover: a button that shows a panel. Escape or an outside click closes it and
// only one is open at a time. The panel follows the button in the DOM, so Tab order is natural.
const open = new Set();

export function initPopover(root) {
	const button = root.querySelector('[data-pop-button]');
	const panel = root.querySelector('[data-pop-panel]');

	function setOpen(value, returnFocus = false) {
		if (value) for (const other of open) if (other.root !== root) other.close();
		button.setAttribute('aria-expanded', String(value));
		panel.hidden = !value;
		if (value) open.add(api);
		else open.delete(api);
		if (!value && returnFocus) button.focus();
	}
	const api = { root, close: () => setOpen(false) };

	button.addEventListener('click', () => setOpen(panel.hidden));
	root.addEventListener('keydown', (e) => {
		if (e.key === 'Escape' && !panel.hidden) {
			e.stopPropagation();
			setOpen(false, true);
		}
	});
	return api;
}

document.addEventListener('pointerdown', (e) => {
	for (const pop of open) if (!pop.root.contains(e.target)) pop.close();
});
document.addEventListener('focusin', (e) => {
	for (const pop of open) if (!pop.root.contains(e.target)) pop.close();
});
