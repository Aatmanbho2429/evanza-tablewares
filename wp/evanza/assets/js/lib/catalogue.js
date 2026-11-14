// Product data helpers shared by the Collection and Product pages.
// The catalogue is printed inline by PHP (evanza_get_products) as #evanza-products.

export const CHALK = '#efefec';
export const GRAPHITE = '#161616';

export function loadCatalogue() {
	const el = document.getElementById('evanza-products');
	const data = JSON.parse(el.textContent);
	data.familyBySlug = Object.fromEntries(data.colorFamilies.map((f) => [f.slug, f]));
	data.categoryBySlug = Object.fromEntries(data.categories.map((c) => [c.slug, c]));
	return data;
}

const toRgb = (hex) => [1, 3, 5].map((i) => parseInt(hex.slice(i, i + 2), 16));
export function mixHex(a, b, amount) {
	const x = toRgb(a);
	const y = toRgb(b);
	return '#' + x.map((v, i) => Math.round(v + (y[i] - v) * amount).toString(16).padStart(2, '0')).join('');
}
export function luminance(hex) {
	const [r, g, b] = toRgb(hex).map((v) => {
		const c = v / 255;
		return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
	});
	return 0.2126 * r + 0.7152 * g + 0.0722 * b;
}

/**
 * The room colour and tone for a glaze: 35% of the glaze over Chalk. Very dark glazes
 * flip to a dark room (the glaze lifted 12% toward Chalk) with light text.
 */
export function roomFor(hex) {
	if (luminance(hex) < 0.05) return { color: mixHex(hex, CHALK, 0.12), tone: 'dark' };
	return { color: mixHex(CHALK, hex, 0.35), tone: 'light' };
}

/** The variant to show for a colour filter: the first that matches, else the default. */
export function variantFor(product, colors) {
	if (colors.length) {
		const hit = product.variants.find((v) => colors.includes(v.family));
		if (hit) return hit;
	}
	return product.variants[0];
}

/** Real photos of a variant (anything that is not a generated render). */
export const photosOf = (variant) => variant.images.filter((src) => !src.includes('/assets/img/mock/') && !src.startsWith('data:'));

/** state: { categories: string[], colors: string[], sort }. `skip` leaves one facet out (for facet counts). */
export function matches(product, state, skip) {
	if (skip !== 'categories' && state.categories.length && !state.categories.includes(product.category)) return false;
	if (skip !== 'colors' && state.colors.length && !product.variants.some((v) => state.colors.includes(v.family))) return false;
	return true;
}

export function sortProducts(list, sort) {
	const byIndex = (a, b) => a.index - b.index;
	const copy = [...list];
	if (sort === 'name') return copy.sort((a, b) => a.name.localeCompare(b.name));
	if (sort === 'new') return copy.sort((a, b) => b.added.localeCompare(a.added) || byIndex(a, b));
	return copy.sort((a, b) => Number(b.featured) - Number(a.featured) || byIndex(a, b));
}

export const plural = (n, one, many) => `${n} ${n === 1 ? one : many}`;
