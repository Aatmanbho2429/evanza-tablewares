// Contact form: client-side checks, sending without a page load, and a prefill from the enquiry list.
import { getItems, messageFor } from './lib/enquiry.js';

const form = document.querySelector('[data-contact-form]');
if (form) init(form);

function init(form) {
	const success = document.querySelector('[data-success]');
	const formError = form.querySelector('[data-form-error]');
	const submit = form.querySelector('[data-submit]');
	const message = form.elements.message;

	// ?enquiry=1: the list from the enquiry drawer becomes the message.
	if (new URLSearchParams(location.search).get('enquiry') && !message.value) {
		const items = getItems();
		if (items.length) {
			message.value = messageFor(items);
			const note = form.querySelector('[data-prefill-note]');
			note.textContent = `${items.length} mug${items.length === 1 ? '' : 's'} from your enquiry list ${items.length === 1 ? 'is' : 'are'} in the message below. Add anything else you need.`;
			note.hidden = false;
		}
	}

	const field = (name) => form.elements[name];
	const setError = (name, text) => {
		const input = field(name);
		const out = form.querySelector(`[data-error="${name}"]`);
		if (!input || !out) return;
		out.textContent = text || '';
		if (text) {
			input.setAttribute('aria-invalid', 'true');
			input.setAttribute('aria-describedby', out.id);
		} else {
			input.removeAttribute('aria-invalid');
			input.removeAttribute('aria-describedby');
		}
	};
	const rules = {
		name: (v) => (v.trim() ? '' : 'Enter your name.'),
		email: (v) => (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()) ? '' : 'Enter a valid email address.'),
		message: (v) => (v.trim().length >= 5 ? '' : 'Tell us what you are looking for.'),
		phone: (v) => (!v.trim() || /^[0-9+()\-\s.]{5,25}$/.test(v.trim()) ? '' : 'Use digits, spaces and + only.'),
	};
	const validate = () => {
		let first = null;
		for (const [name, rule] of Object.entries(rules)) {
			const text = rule(field(name).value);
			setError(name, text);
			if (text && !first) first = field(name);
		}
		return first;
	};
	// An error clears as soon as the field is corrected.
	for (const name of Object.keys(rules)) {
		field(name).addEventListener('input', () => field(name).hasAttribute('aria-invalid') && setError(name, rules[name](field(name).value)));
	}

	form.addEventListener('submit', async (e) => {
		e.preventDefault();
		formError.hidden = true;
		const invalid = validate();
		if (invalid) return invalid.focus();

		submit.disabled = true;
		submit.textContent = 'Sending...';
		try {
			const res = await fetch(form.getAttribute('action'), { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json' }, credentials: 'same-origin' });
			const data = await res.json();
			if (data.ok) {
				form.hidden = true;
				success.hidden = false;
				success.focus();
				return;
			}
			let first = null;
			for (const [name, text] of Object.entries(data.errors || {})) {
				if (name === 'form') continue;
				setError(name, text);
				first ||= field(name);
			}
			if (data.errors?.form) {
				formError.textContent = data.errors.form;
				formError.hidden = false;
			}
			first?.focus();
		} catch {
			formError.textContent = 'We could not send that. Check your connection, or email us instead.';
			formError.hidden = false;
		}
		submit.disabled = false;
		submit.textContent = 'Send enquiry';
	});
}
