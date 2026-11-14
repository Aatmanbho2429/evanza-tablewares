// Converts the original client photos in images/ into WebP files in the theme.
// Output: wp/evanza/assets/img/<group>/<name>-<width>.webp. Run with: npm run images
import { mkdir, rm } from 'node:fs/promises';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';
import sharp from 'sharp';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const src = join(root, 'images');
const dest = join(root, 'wp/evanza/assets/img');

const WA = 'WhatsApp-Image-2023-';
const PHOTO = [800, 1600];

// Product shots keep their file stem as the name.
const products = [
	'001-2.jpg', '009-01.jpg', '012-01.jpg', '016A1265.jpg', '016A1271.jpg', '016A1290.jpg', '016A1307.jpg',
	'016A1315.jpg', '016A1383.jpg', '016A1396.jpg', 'AURA.jpg', 'IMG_3697.jpg', 'IMG_3706.jpg', 'IMG_3730-1.jpg',
	'IMG_3836.jpg', 'IMG_3977.jpg', 'IMG_4022.jpg', 'IMG_4085.jpg', 'IMG_4196.jpg', 'IMG_4215.jpg', 'IMG_4531-1.jpg',
	'IMG_4540.jpg', 'IMG_4551.jpg', 'IMG_4718.jpg', 'IMG_4730.jpg', 'IMG_4750.jpg', 'IMG_4793.jpg', 'IMG_4800-2.jpg',
	'IMG_4802.jpg', 'IMG_4815-3.jpg', 'IMG_4846.jpg', 'IMG_4848.jpg', 'IMG_4851.jpg', 'IMG_4857.jpg', 'IMG_4858.jpg',
	'IMG_4861.jpg', 'IMG_4866.jpg', 'IMG_5235-2.jpg', 'IMG_5241.jpg', 'IMG_5244-1.jpg', 'IMG_5249.jpg', 'IMG_5250.jpg',
	'IMG_5251.jpg', 'IMG_5264-1.jpg', 'IMG_5364-1.jpg', WA + '09-14-at-5.53.36-AM.jpeg',
	'coffee-1.webp',
].map((file) => ({
	group: 'products',
	file,
	name: file === 'coffee-1.webp' ? 'coffee-cup' : file.startsWith('WhatsApp') ? 'black-mug-desk' : file.replace(/\.\w+$/, ''),
	widths: PHOTO,
}));

// Factory photos get names that say what is in the frame (duplicates skipped).
const factory = [
	['08-29-at-4.30.33-PM-1.jpeg', 'raw-material'],
	['08-29-at-4.30.06-PM-7.jpeg', 'ball-mill'],
	['08-29-at-4.30.06-PM-4.jpeg', 'clay-extruder'],
	['08-29-at-4.30.06-PM-3-1.jpeg', 'forming'],
	['08-29-at-4.30.06-PM-2-1.jpeg', 'pressing'],
	['08-29-at-4.30.05-PM-2-1.jpeg', 'turntable'],
	['08-29-at-4.30.05-PM-3.jpeg', 'drying-racks'],
	['08-29-at-4.29.45-PM-1.jpeg', 'bisque-racks'],
	['08-29-at-4.29.45-PM-5.jpeg', 'finishing'],
	['08-29-at-4.29.45-PM-6.jpeg', 'handling'],
	['08-29-at-4.30.34-PM-4.jpeg', 'glazing'],
	['08-29-at-4.30.34-PM-6-1.jpeg', 'glazed-racks'],
	['08-29-at-4.30.34-PM-1.jpeg', 'kiln-control'],
	['08-29-at-4.30.34-PM-11.jpeg', 'kiln-rollers'],
	['08-29-at-4.30.34-PM-12.jpeg', 'kiln-line'],
	['08-29-at-4.30.34-PM-21.jpeg', 'kiln-mugs'],
	['08-29-at-4.30.34-PM-18.jpeg', 'packing'],
	['08-29-at-4.30.34-PM-20.jpeg', 'dispatch'],
	['09-13-at-12.34.54-PM-1.jpeg', 'factory-exterior'],
].map(([f, name]) => ({ group: 'factory', file: WA + f, name, widths: PHOTO }));
factory.push({ group: 'factory', file: 'high-angle-hands-doing-pottery.jpg', name: 'hands-pottery', widths: PHOTO });

const other = [
	{ group: 'certificates', file: 'LAYER-08.png', name: 'qsa-ce', widths: PHOTO },
	{ group: 'certificates', file: 'Layer-06-1.png', name: 'iso-9001', widths: PHOTO },
	{ group: 'brand', file: 'iStock-1141639313.jpg', name: 'contact', widths: PHOTO },
];

// Logos are trimmed to their artwork and keep transparency.
const logos = [
	{ file: 'Evanza-LOGO-1.png', name: 'logo-dark', widths: [240, 480] },
	{ file: 'white-logo.png', name: 'logo-light', widths: [240, 480] },
];

// Only the folders this script owns; assets/img/mock is written by make-mock-images.mjs.
for (const group of ['products', 'factory', 'certificates', 'brand']) await rm(join(dest, group), { recursive: true, force: true });

async function writeSizes(input, group, name, widths, opts = {}) {
	await mkdir(join(dest, group), { recursive: true });
	for (const w of widths) {
		await sharp(input)
			.resize({ width: w, withoutEnlargement: true })
			.webp({ quality: opts.quality ?? 78, alphaQuality: 90 })
			.toFile(join(dest, group, `${name}-${w}.webp`));
	}
}

let count = 0;
for (const p of [...products, ...factory, ...other]) {
	await writeSizes(join(src, p.file), p.group, p.name, p.widths);
	count++;
}
for (const l of logos) {
	const trimmed = await sharp(join(src, l.file)).trim({ threshold: 1 }).toBuffer();
	await writeSizes(trimmed, 'brand', l.name, l.widths, { quality: 92 });
	count++;
}

// Favicon / app icon: the white mark recoloured to the brand teal.
const markSrc = join(src, 'cropped-white-1-1.png');
const alpha = await sharp(markSrc).extractChannel('alpha').toBuffer();
const { width, height } = await sharp(markSrc).metadata();
const teal = await sharp({ create: { width, height, channels: 3, background: '#0081A5' } }).joinChannel(alpha).png().toBuffer();
const trimmedMark = await sharp(teal).trim({ threshold: 1 }).toBuffer();
await mkdir(join(dest, 'brand'), { recursive: true });
for (const size of [32, 180, 512]) {
	const pad = Math.round(size * 0.08);
	await sharp(trimmedMark)
		.resize(size - pad * 2, size - pad * 2, { fit: 'contain', background: { r: 0, g: 0, b: 0, alpha: 0 } })
		.extend({ top: pad, bottom: pad, left: pad, right: pad, background: { r: 0, g: 0, b: 0, alpha: 0 } })
		.png()
		.toFile(join(dest, 'brand', `mark-${size}.png`));
}
count++;

console.log(`images: ${count} sources written to ${dest}`);
