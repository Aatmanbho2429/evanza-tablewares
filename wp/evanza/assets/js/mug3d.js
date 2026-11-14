// A procedural ceramic mug in WebGL (three.js). One module for the Home hero, the Product page
// and the Home scroll story.
//
//   const viewer = createMugViewer(canvas, { colors: { body, inner, handle }, shape: 'classic' });
//   viewer.setColors({ body: '#78a04a' }, { animate: true });
//   viewer.setState('glazing', 0.4);   // bisque | glazing | fired | glazed
//   viewer.enter();                    // the mug rises and settles
//   viewer.dispose();
//
// Shapes: classic, latte, belly, espresso, heart. Option onFail runs if a shader does not compile. The canvas is transparent; the page room colour
// shows through. Rendering pauses while the canvas is offscreen or the tab is hidden.
import * as THREE from 'three';
import { RoomEnvironment } from 'three/addons/environments/RoomEnvironment.js';

// Units: the classic mug is 1 tall and 1 wide. Each shape: height, rim and base radius, belly bulge,
// wall thickness, base corner radius and the handle (attach heights as fractions of height).
const SHAPES = {
	classic: { H: 1, rt: 0.5, rb: 0.45, bulge: 0, tw: 0.05, rc: 0.08, handle: { type: 'd', y: [0.8, 0.24], reach: 0.34, r: 0.058 } },
	latte: { H: 0.96, rt: 0.6, rb: 0.34, bulge: 0, tw: 0.05, rc: 0.1, handle: { type: 'd', y: [0.8, 0.28], reach: 0.32, r: 0.056 } },
	belly: { H: 0.94, rt: 0.47, rb: 0.4, bulge: 0.09, tw: 0.05, rc: 0.1, handle: { type: 'd', y: [0.78, 0.26], reach: 0.3, r: 0.056 } },
	espresso: { H: 0.66, rt: 0.42, rb: 0.31, bulge: 0.03, tw: 0.045, rc: 0.07, handle: { type: 'd', y: [0.76, 0.3], reach: 0.22, r: 0.045 } },
};
SHAPES.heart = { ...SHAPES.classic, handle: { type: 'heart', y: [0.78, 0.3], reach: 0.3, r: 0.05 } };

const BISQUE = new THREE.Color('#cdbba3');
const easeOutCubic = (t) => 1 - Math.pow(1 - t, 3);
const easeOutBack = (t) => 1 + 2.2 * Math.pow(t - 1, 3) + 1.2 * Math.pow(t - 1, 2);
const clamp01 = (v) => Math.min(1, Math.max(0, v));

// --- geometry ---------------------------------------------------------------

function wallRadius(s, y) {
	const t = y / s.H;
	return s.rb + (s.rt - s.rb) * t + s.bulge * Math.sin(Math.PI * t);
}

/** Outer shell from the base centre up over the rolled rim to its crest, and the inner cup from the crest down. */
function profiles(s) {
	const V = (x, y) => new THREE.Vector2(x, y);
	const outer = [V(0, 0.012), V(s.rb * 0.68, 0.012), V(s.rb * 0.68 + 0.014, 0)];
	// base corner
	const cx = s.rb - s.rc;
	for (let i = 0; i <= 8; i++) {
		const a = -Math.PI / 2 + (i / 8) * (Math.PI / 2);
		outer.push(V(cx + Math.cos(a) * s.rc, s.rc + Math.sin(a) * s.rc));
	}
	for (let i = 1; i <= 36; i++) {
		const y = s.rc + ((s.H - s.rc) * i) / 36;
		outer.push(V(wallRadius(s, y), y));
	}
	// rolled rim: half circle over the wall thickness
	const R = s.tw / 2;
	const rimX = s.rt - R;
	for (let i = 1; i <= 10; i++) {
		const a = (i / 10) * (Math.PI / 2);
		outer.push(V(rimX + Math.cos(a) * R, s.H + Math.sin(a) * R * 0.9));
	}
	const inner = [];
	for (let i = 0; i <= 10; i++) {
		const a = Math.PI / 2 + (i / 10) * (Math.PI / 2);
		inner.push(V(rimX + Math.cos(a) * R, s.H + Math.sin(a) * R * 0.9));
	}
	const floorY = 0.075;
	const ri = s.rc * 0.7;
	for (let i = 1; i <= 36; i++) {
		const y = s.H - ((s.H - floorY - ri) * i) / 36;
		inner.push(V(wallRadius(s, y) - s.tw, y));
	}
	const icx = wallRadius(s, floorY + ri) - s.tw - ri;
	for (let i = 1; i <= 8; i++) {
		const a = (i / 8) * (Math.PI / 2);
		inner.push(V(icx + Math.cos(a) * ri, floorY + ri - Math.sin(a) * ri));
	}
	inner.push(V(0, floorY));
	return { outer, inner };
}

function handleGeometry(s) {
	const h = s.handle;
	const [yTop, yBot] = h.y.map((f) => f * s.H);
	if (h.type === 'heart') {
		const pts = [];
		const k = 0.0125;
		const yMid = (yTop + yBot) / 2;
		const tipX = wallRadius(s, yMid) - 0.03;
		for (let i = 0; i < 64; i++) {
			const t = (i / 64) * Math.PI * 2;
			const hx = 16 * Math.pow(Math.sin(t), 3);
			const hy = 13 * Math.cos(t) - 5 * Math.cos(2 * t) - 2 * Math.cos(3 * t) - Math.cos(4 * t);
			// rotate so the tip points at the wall: (x, y) -> (y, -x)
			pts.push(new THREE.Vector3(tipX + (hy + 17) * k, yMid - hx * k, 0));
		}
		return new THREE.TubeGeometry(new THREE.CatmullRomCurve3(pts, true, 'centripetal'), 160, h.r, 32, true);
	}
	const x1 = wallRadius(s, yTop) - 0.04;
	const x2 = wallRadius(s, yBot) - 0.04;
	const curve = new THREE.CubicBezierCurve3(
		new THREE.Vector3(x1, yTop, 0),
		new THREE.Vector3(x1 + h.reach * 1.3, yTop + 0.1 * s.H, 0),
		new THREE.Vector3(x2 + h.reach * 1.45, yBot - 0.08 * s.H, 0),
		new THREE.Vector3(x2, yBot, 0)
	);
	return new THREE.TubeGeometry(curve, 96, h.r, 32, false);
}

function shadowTexture() {
	const c = document.createElement('canvas');
	c.width = c.height = 256;
	const g = c.getContext('2d');
	const grad = g.createRadialGradient(128, 128, 8, 128, 128, 128);
	grad.addColorStop(0, 'rgba(0,0,0,0.62)');
	grad.addColorStop(0.35, 'rgba(0,0,0,0.34)');
	grad.addColorStop(0.7, 'rgba(0,0,0,0.08)');
	grad.addColorStop(1, 'rgba(0,0,0,0)');
	g.fillStyle = grad;
	g.fillRect(0, 0, 256, 256);
	const tex = new THREE.CanvasTexture(c);
	tex.colorSpace = THREE.SRGBColorSpace;
	return tex;
}

// --- glaze material -----------------------------------------------------------
// A physical material with a clearcoat glaze. A "glaze" uniform rises the glaze up the mug from
// the base over matte raw clay (bisque); "kiln" adds a warm glow.

function glazeMaterial(color, uniforms) {
	const mat = new THREE.MeshPhysicalMaterial({
		color,
		roughness: 0.1,
		metalness: 0,
		// Below 1: a full-strength clearcoat turns thin edges (the rim crest, the handle) into a dotted dark line.
		clearcoat: 0.6,
		clearcoatRoughness: 0.08,
		ior: 1.5,
		specularIntensity: 1,
		side: THREE.FrontSide,
	});
	// The cup's inside shares the shell's mesh (so the rim is one smooth surface) and takes uInnerColor.
	mat.userData.innerColor = new THREE.Color(color);
	mat.onBeforeCompile = (shader) => {
		Object.assign(shader.uniforms, uniforms);
		shader.uniforms.uInnerColor = { value: mat.userData.innerColor };
		shader.vertexShader = shader.vertexShader
			.replace('#include <common>', '#include <common>\nattribute float aInner;\nvarying vec3 vOP;\nvarying float vInner;')
			.replace('#include <begin_vertex>', '#include <begin_vertex>\nvOP = position;\nvInner = aInner;');
		shader.fragmentShader = shader.fragmentShader
			.replace(
				'#include <common>',
				`#include <common>
varying vec3 vOP;
varying float vInner;
uniform vec3 uInnerColor;
uniform float uGlaze;
uniform float uKiln;
uniform float uH;
uniform vec3 uBisque;
float h31(vec3 p){ p = fract(p * 0.3183099 + 0.1); p *= 17.0; return fract(p.x * p.y * p.z * (p.x + p.y + p.z)); }
float vnoise(vec3 x){
	vec3 i = floor(x); vec3 f = fract(x); f = f * f * (3.0 - 2.0 * f);
	return mix(mix(mix(h31(i), h31(i + vec3(1,0,0)), f.x), mix(h31(i + vec3(0,1,0)), h31(i + vec3(1,1,0)), f.x), f.y),
		mix(mix(h31(i + vec3(0,0,1)), h31(i + vec3(1,0,1)), f.x), mix(h31(i + vec3(0,1,1)), h31(i + vec3(1,1,1)), f.x), f.y), f.z);
}`
			)
			.replace(
				'#include <color_fragment>',
				`#include <color_fragment>
diffuseColor.rgb = mix(diffuseColor.rgb, uInnerColor, vInner);
float hN = clamp(vOP.y / uH, 0.0, 1.0);
float gMask = smoothstep(0.0, 0.08, uGlaze * 1.4 - hN - 0.1 + (vnoise(vOP * 7.0) - 0.5) * 0.2);
vec3 clay = uBisque * (0.9 + 0.2 * vnoise(vOP * 38.0));
diffuseColor.rgb = mix(clay, diffuseColor.rgb, gMask);
diffuseColor.rgb *= mix(1.0, mix(0.5, 1.0, smoothstep(0.0, 1.0, hN)), vInner);`
			)
			.replace('#include <roughnessmap_fragment>', '#include <roughnessmap_fragment>\nroughnessFactor = mix(0.96, roughnessFactor, gMask);')
			.replace('#include <lights_physical_fragment>', '#include <lights_physical_fragment>\nmaterial.clearcoat *= gMask;')
			.replace(
				'#include <emissivemap_fragment>',
				'#include <emissivemap_fragment>\ntotalEmissiveRadiance += vec3(1.0, 0.34, 0.07) * uKiln * (0.55 + 0.45 * vnoise(vOP * 5.0));'
			);
	};
	return mat;
}

// --- viewer -------------------------------------------------------------------

export function createMugViewer(canvas, options = {}) {
	const opts = { colors: {}, shape: 'classic', autoRotate: true, interactive: true, state: 'glazed', fit: 0.62, offsetY: 0, ...options };
	const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');

	let renderer;
	try {
		renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true, powerPreference: 'high-performance' });
	} catch (error) {
		const err = new Error('WebGL unavailable');
		err.code = 'NO_WEBGL';
		err.cause = error;
		throw err;
	}
	// A rejected shader leaves an empty canvas: tell the page so it can show its still image.
	renderer.debug.onShaderError = () => opts.onFail?.();
	renderer.setClearColor(0x000000, 0);
	renderer.outputColorSpace = THREE.SRGBColorSpace;
	renderer.toneMapping = THREE.NeutralToneMapping;
	renderer.toneMappingExposure = 1;

	const scene = new THREE.Scene();
	const pmrem = new THREE.PMREMGenerator(renderer);
	const envScene = new RoomEnvironment();
	// Tall softboxes give the glaze long, clean highlights, like a product photograph.
	const softbox = (x, y, z, w, h, power) => {
		const m = new THREE.Mesh(new THREE.PlaneGeometry(w, h), new THREE.MeshBasicMaterial({ color: new THREE.Color(power, power, power), side: THREE.DoubleSide }));
		m.position.set(x, y, z);
		m.lookAt(0, y, 0);
		envScene.add(m);
	};
	// Lift the room's walls a little so edges that reflect them at grazing angles do not turn black.
	envScene.traverse((o) => {
		if (o.isMesh && o.material?.side === THREE.BackSide) o.material.emissive?.setScalar(0.35);
	});
	softbox(-4.2, 3.2, 1.2, 0.9, 5.2, 22);
	softbox(4.4, 3, -0.8, 0.35, 5, 14);
	softbox(0.5, 6.2, 1.5, 4, 1, 8);
	const envTexture = pmrem.fromScene(envScene, 0.035).texture;
	scene.environment = envTexture;
	scene.environmentIntensity = 0.7;

	const key = new THREE.DirectionalLight(0xffffff, 1.5);
	key.position.set(-2.2, 3.4, 2.4);
	const rim = new THREE.DirectionalLight(0xffffff, 0.7);
	rim.position.set(2.6, 1.6, -2.4);
	scene.add(key, rim);

	const camera = new THREE.PerspectiveCamera(24, 1, 0.1, 50);

	const uniforms = {
		uGlaze: { value: opts.state === 'bisque' ? 0 : 1 },
		uKiln: { value: 0 },
		uH: { value: 1 },
		uBisque: { value: BISQUE },
	};

	const colors = {
		body: new THREE.Color(opts.colors.body || '#cfcfca'),
		inner: new THREE.Color(opts.colors.inner || opts.colors.body || '#cfcfca'),
		handle: new THREE.Color(opts.colors.handle || opts.colors.body || '#cfcfca'),
	};
	const mats = {
		body: glazeMaterial(colors.body, uniforms),
		handle: glazeMaterial(colors.handle, uniforms),
	};
	// Body and cup colour live on one material; this applies a colour set to it.
	const paint = (key) => (key === 'inner' ? mats.body.userData.innerColor : mats[key].color).copy(colors[key]);
	mats.body.userData.innerColor.copy(colors.inner);

	const group = new THREE.Group(); // positioned and scaled by the entrance
	const spin = new THREE.Group(); // rotated by auto-rotate and drag
	group.add(spin);
	scene.add(group);

	const shadowMat = new THREE.MeshBasicMaterial({ map: shadowTexture(), transparent: true, depthWrite: false, toneMapped: false });
	const shadow = new THREE.Mesh(new THREE.PlaneGeometry(1, 1), shadowMat);
	shadow.rotation.x = -Math.PI / 2;
	shadow.position.y = -0.004;
	group.add(shadow);

	let shape = SHAPES[opts.shape] ? opts.shape : 'classic';
	let meshes = [];

	function buildMug() {
		for (const m of meshes) {
			spin.remove(m);
			m.geometry.dispose();
		}
		const s = SHAPES[shape];
		const { outer, inner } = profiles(s);
		// One continuous profile: outside up over the rim, then down the inside (the crest point is shared).
		const geometry = new THREE.LatheGeometry([...outer, ...inner.slice(1)], 320);
		const perRing = outer.length + inner.length - 1;
		const flags = new Float32Array(geometry.attributes.position.count);
		for (let i = 0; i < flags.length; i++) flags[i] = i % perRing >= outer.length ? 1 : 0;
		geometry.setAttribute('aInner', new THREE.BufferAttribute(flags, 1));
		const body = new THREE.Mesh(geometry, mats.body);
		const handle = new THREE.Mesh(handleGeometry(s), mats.handle);
		handle.scale.z = 0.8;
		meshes = [body, handle];
		spin.add(...meshes);
		uniforms.uH.value = s.H;
		spin.position.y = 0;
		shadow.scale.set(s.rt * 3.6, s.rt * 3.6, 1);
		shadow.position.x = s.handle.reach * 0.25;
		frame();
	}

	// Camera framing: the whole mug (with handle) fits the canvas, centred.
	let width = 1;
	let height = 1;
	function frame() {
		const s = SHAPES[shape];
		const aspect = width / height;
		const mugW = s.rt * 2 + s.handle.reach * 1.3 + 0.2;
		const visH = Math.max(s.H / opts.fit, mugW / (0.9 * aspect));
		const dist = visH / (2 * Math.tan(THREE.MathUtils.degToRad(camera.fov / 2)));
		const tilt = THREE.MathUtils.degToRad(13);
		const target = new THREE.Vector3(0, s.H * 0.5 + opts.offsetY * visH, 0);
		camera.position.set(0, target.y + Math.sin(tilt) * dist, Math.cos(tilt) * dist);
		camera.lookAt(target);
		camera.aspect = aspect;
		camera.updateProjectionMatrix();
	}

	function resize() {
		width = Math.max(1, canvas.clientWidth);
		height = Math.max(1, canvas.clientHeight);
		renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
		renderer.setSize(width, height, false);
		frame();
		requestRender();
	}

	// --- state ---
	const BASE_ANGLE = -0.62;
	let angle = BASE_ANGLE;
	let velocity = 0; // rad/s from a drag release
	let dragging = false;
	let lastInput = -Infinity;
	let autoRotate = opts.autoRotate && !reduced.matches;
	let colorAnim = null;
	let intro = null;
	let introSpin = 0;
	let visible = true;
	let disposed = false;
	let rafId = 0;
	let lastTime = 0;

	function setColors(next, { animate = true, duration = 600, delay = 0 } = {}) {
		const src = { body: next.body, inner: next.inner ?? next.body, handle: next.handle ?? next.body };
		const to = {};
		for (const key of Object.keys(src)) if (src[key]) to[key] = new THREE.Color(src[key]);
		if (!animate || reduced.matches) {
			for (const key of Object.keys(to)) {
				colors[key].copy(to[key]);
				paint(key);
			}
			colorAnim = null;
			requestRender();
			return;
		}
		const from = {};
		for (const key of Object.keys(to)) from[key] = colors[key].clone();
		colorAnim = { from, to, start: performance.now() + delay, duration };
		requestRender();
	}

	function setShape(name) {
		if (!SHAPES[name] || name === shape) return;
		shape = name;
		buildMug();
		requestRender();
	}

	function setState(name, progress = 1) {
		const p = clamp01(progress);
		uniforms.uGlaze.value = name === 'bisque' ? 0 : name === 'glazing' ? p : 1;
		uniforms.uKiln.value = name === 'fired' ? p : 0;
		requestRender();
	}

	function enter({ duration = 1100, delay = 0 } = {}) {
		if (reduced.matches) return;
		intro = { start: performance.now() + delay, duration };
		group.position.y = -0.55;
		group.scale.setScalar(0.88);
		introSpin = 1.1;
		requestRender();
	}

	function updateIntro(now) {
		if (!intro) return false;
		const t = clamp01((now - intro.start) / intro.duration);
		const e = easeOutBack(t);
		const eRot = easeOutCubic(t);
		group.position.y = -0.55 * (1 - e);
		group.scale.setScalar(0.88 + 0.12 * Math.min(1, e));
		introSpin = 1.1 * (1 - eRot);
		if (t >= 1) {
			intro = null;
			introSpin = 0;
			group.position.y = 0;
			group.scale.setScalar(1);
		}
		return true;
	}

	function updateColors(now) {
		if (!colorAnim) return false;
		const t = clamp01((now - colorAnim.start) / colorAnim.duration);
		const e = easeOutCubic(t);
		for (const key of Object.keys(colorAnim.to)) {
			colors[key].copy(colorAnim.from[key]).lerp(colorAnim.to[key], e);
			paint(key);
		}
		if (t >= 1) colorAnim = null;
		return true;
	}

	function render(now) {
		const dt = Math.min(0.05, (now - lastTime) / 1000 || 0);
		lastTime = now;
		let moving = false;
		if (updateIntro(now)) moving = true;
		if (updateColors(now)) moving = true;

		if (!dragging) {
			if (Math.abs(velocity) > 0.02) {
				angle += velocity * dt;
				velocity *= Math.exp(-dt * 3.2);
				moving = true;
			} else if (autoRotate && !intro && now - lastInput > 2500) {
				angle += dt * 0.32;
				moving = true;
			}
		}
		spin.rotation.y = angle + introSpin;
		renderer.render(scene, camera);
		return moving || dragging;
	}

	function tick(now) {
		rafId = 0;
		if (disposed) return;
		const again = render(now);
		if (again && visible && !document.hidden) rafId = requestAnimationFrame(tick);
	}
	function requestRender() {
		if (!rafId && !disposed && visible && !document.hidden) rafId = requestAnimationFrame(tick);
	}

	// --- input: drag to spin, with inertia ---
	let pointerId = null;
	let lastX = 0;
	let lastT = 0;
	function onDown(e) {
		if (!opts.interactive || (e.pointerType === 'mouse' && e.button !== 0)) return;
		pointerId = e.pointerId;
		canvas.setPointerCapture(pointerId);
		dragging = true;
		velocity = 0;
		lastX = e.clientX;
		lastT = performance.now();
		canvas.classList.add('is-dragging');
		requestRender();
	}
	function onMove(e) {
		if (!dragging || e.pointerId !== pointerId) return;
		const now = performance.now();
		const dx = e.clientX - lastX;
		const rotation = dx * 0.0095;
		angle += rotation;
		const dtMs = Math.max(1, now - lastT);
		velocity = 0.8 * velocity + 0.2 * ((rotation / dtMs) * 1000);
		lastX = e.clientX;
		lastT = now;
		lastInput = now;
		requestRender();
	}
	function onUp(e) {
		if (!dragging || e.pointerId !== pointerId) return;
		dragging = false;
		pointerId = null;
		lastInput = performance.now();
		if (performance.now() - lastT > 90) velocity = 0;
		velocity = Math.max(-12, Math.min(12, velocity));
		canvas.classList.remove('is-dragging');
		requestRender();
	}
	canvas.addEventListener('pointerdown', onDown);
	canvas.addEventListener('pointermove', onMove);
	canvas.addEventListener('pointerup', onUp);
	canvas.addEventListener('pointercancel', onUp);
	canvas.addEventListener('lostpointercapture', onUp);
	canvas.style.touchAction = 'pan-y';

	// --- pause offscreen / hidden tab ---
	const io = new IntersectionObserver(
		(entries) => {
			visible = entries[entries.length - 1].isIntersecting;
			if (visible) requestRender();
		},
		{ rootMargin: '80px' }
	);
	io.observe(canvas);
	const onVisibility = () => requestRender();
	document.addEventListener('visibilitychange', onVisibility);
	const ro = new ResizeObserver(resize);
	ro.observe(canvas);
	const onMotionChange = () => {
		autoRotate = opts.autoRotate && !reduced.matches;
		requestRender();
	};
	reduced.addEventListener('change', onMotionChange);
	canvas.addEventListener('webglcontextlost', (e) => e.preventDefault());
	canvas.addEventListener('webglcontextrestored', requestRender);

	buildMug();
	resize();
	requestRender();

	return {
		setColors,
		setShape,
		setState,
		enter,
		/** fit: share of the canvas height the mug fills; offsetY: positive moves the mug down (fraction of the view). */
		setFraming(next) {
			Object.assign(opts, next);
			frame();
			requestRender();
		},
		setAutoRotate(value) {
			autoRotate = !!value && !reduced.matches;
			requestRender();
		},
		dispose() {
			disposed = true;
			cancelAnimationFrame(rafId);
			io.disconnect();
			ro.disconnect();
			document.removeEventListener('visibilitychange', onVisibility);
			reduced.removeEventListener('change', onMotionChange);
			canvas.removeEventListener('pointerdown', onDown);
			canvas.removeEventListener('pointermove', onMove);
			canvas.removeEventListener('pointerup', onUp);
			canvas.removeEventListener('pointercancel', onUp);
			canvas.removeEventListener('lostpointercapture', onUp);
			for (const m of meshes) m.geometry.dispose();
			Object.values(mats).forEach((m) => m.dispose());
			shadow.geometry.dispose();
			shadowMat.map.dispose();
			shadowMat.dispose();
			envTexture.dispose();
			pmrem.dispose();
			renderer.dispose();
		},
	};
}
