// Customizer values printed by PHP (#evanza-config).
let cache;
export function config() {
	if (!cache) {
		const el = document.getElementById('evanza-config');
		cache = el ? JSON.parse(el.textContent) : { whatsapp: '919898798986', email: 'evanzatableware@gmail.com', contactUrl: '/contact/', collection: '/collection/', siteName: 'Evanza Tablewares' };
	}
	return cache;
}
