// Gallery: draggable shelves and the lightbox.
import { initRail } from './lib/rail.js';
import { initLightbox } from './lib/lightbox.js';

document.querySelectorAll('[data-rail]').forEach(initRail);
initLightbox();
