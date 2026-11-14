// Global behaviour loaded on every page.
import { initHeader } from './lib/header.js';
import { observeReveals } from './lib/reveal.js';
import { initEnquiryBadge } from './lib/enquiry-badge.js';

initHeader();
initEnquiryBadge();
observeReveals();
