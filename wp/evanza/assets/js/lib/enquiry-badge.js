// Keeps the header's enquiry-list count in sync with localStorage.
const KEY = 'evanza-enquiry';

function countItems() {
	try {
		const items = JSON.parse(localStorage.getItem(KEY) || '[]');
		return Array.isArray(items) ? items.reduce((sum, item) => sum + (Number(item.qty) || 1), 0) : 0;
	} catch {
		return 0;
	}
}

export function initEnquiryBadge() {
	const render = () => {
		const n = countItems();
		document.querySelectorAll('[data-enquiry-count]').forEach((badge) => {
			badge.textContent = String(n);
			badge.hidden = n === 0;
		});
		document.querySelectorAll('[data-enquiry-open]').forEach((el) => {
			el.setAttribute('aria-label', n ? `Enquiry list, ${n} item${n === 1 ? '' : 's'}` : 'Enquiry list');
		});
	};
	render();
	window.addEventListener('storage', (e) => e.key === KEY && render());
	window.addEventListener('evanza:enquiry-change', render);
}
