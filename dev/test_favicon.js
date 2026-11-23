// Test de bout en bout de la 1.7.0 : icône du site (favicon, version sombre, icône de l'écran d'accueil).
// node test_favicon.js <dossier de sortie> <dossier des images de test>
// Forum local 127.0.0.1:8080 (php -S), compte admin / adminpass123, lancer reset_forums_styles.sh avant.
// Images de test : favicon.ico, favicon-sombre.png (48 px), touch.png (180 px), paysage.jpg.
const { chromium } = require('playwright');
const { execSync } = require('child_process');
const fs = require('fs');
const B = 'http://127.0.0.1:8080/';
const WWW = '/home/claude/phpbb/www';
const OUT = process.argv[2];
const IMG = process.argv[3];
const MOD = 'i=-verturin-clarte-acp-main_module&mode=';
const cli = c => execSync(`php ${WWW}/bin/phpbbcli.php ${c}`).toString().trim();
let fails = 0;
const ok = (cond, msg) => { console.log((cond ? 'OK   ' : 'ÉCHEC ') + msg); if (!cond) fails++; };
const pause = () => new Promise(r => setTimeout(r, 1200));

(async () => {
	const b = await chromium.launch();
	const ctx = await b.newContext({ viewport: { width: 1280, height: 900 }, acceptDownloads: true });
	const p = await ctx.newPage();
	const errs = [];
	p.on('pageerror', e => errs.push(e.message));
	const icons = () => p.evaluate(() => Array.from(document.querySelectorAll('link[rel="icon"], link[rel="apple-touch-icon"]')).map(l => [l.rel, l.getAttribute('href'), l.getAttribute('media') || '', l.type]));

	await p.goto(B + 'ucp.php?mode=login');
	await p.fill('input[name=username]', 'admin');
	await p.fill('input[name=password]', 'adminpass123');
	await pause(); await Promise.all([p.waitForNavigation(), p.click('input[name=login]')]);
	const sid0 = (await ctx.cookies()).find(c => c.name.endsWith('_sid')).value;
	await p.goto(B + 'adm/index.php?sid=' + sid0);
	await p.fill('input[type=password]', 'adminpass123');
	await pause(); await Promise.all([p.waitForNavigation(), p.click('input[name=login]')]);
	const sid = new URL(p.url()).searchParams.get('sid');
	const acp = m => `${B}adm/index.php?sid=${sid}&${MOD}${m}`;
	const submit = async () => { await pause(); await Promise.all([p.waitForNavigation(), p.click('#submit')]); };

	await p.goto(B + 'index.php');
	ok((await icons()).length === 0, 'sans réglage : aucune balise d’icône ajoutée');

	// Formats refusés
	await p.goto(acp('settings'));
	ok(await p.locator('fieldset.clarte-icons').count() === 1, 'ACP : section « Icône du site »');
	await p.setInputFiles('#upload_clarte_favicon', IMG + '/paysage.jpg');
	await submit();
	ok((await p.locator('.errorbox').innerText()).includes('ICO, PNG'), 'JPG refusé pour l’icône (accepté : ICO, PNG)');
	await p.goto(acp('settings'));
	await p.setInputFiles('#upload_clarte_bg_image', IMG + '/favicon.ico');
	await submit();
	ok((await p.locator('.errorbox').innerText()).includes('JPG, PNG, GIF, WEBP'), 'ICO refusé pour l’image de fond');
	await p.goto(acp('settings'));
	await p.setInputFiles('#upload_clarte_touch_icon', IMG + '/favicon.ico');
	await submit();
	ok((await p.locator('.errorbox').innerText()).includes('PNG'), 'ICO refusé pour l’icône de l’écran d’accueil (PNG seulement)');
	ok(cli('config:get clarte_favicon') === '' && cli('config:get clarte_touch_icon') === '', 'rien enregistré après les refus');

	// Envoi des trois icônes
	await p.goto(acp('settings'));
	await p.setInputFiles('#upload_clarte_favicon', IMG + '/favicon.ico');
	await p.setInputFiles('#upload_clarte_favicon_dark', IMG + '/favicon-sombre.png');
	await p.setInputFiles('#upload_clarte_touch_icon', IMG + '/touch.png');
	ok((await p.getAttribute('#clarte_favicon_thumb', 'style') || '').includes('blob:'), 'vignette immédiate');
	await p.locator('fieldset.clarte-icons').screenshot({ path: OUT + '/acp-icone.png' });
	await submit();
	const fav = cli('config:get clarte_favicon'), dark = cli('config:get clarte_favicon_dark'), touch = cli('config:get clarte_touch_icon');
	ok(/\.ico$/.test(fav) && /\.png$/.test(dark) && /\.png$/.test(touch), `trois icônes enregistrées (${fav}, ${dark}, ${touch})`);
	ok([fav, dark, touch].every(f => fs.existsSync(`${WWW}/images/clarte/${f}`)), 'fichiers dans images/clarte/');

	// Forum (Clarté), prosilver, ACP
	const want = list => list.length === 3
		&& list[0][0] === 'icon' && list[0][1].endsWith('/images/clarte/' + fav) && list[0][3] === 'image/x-icon' && list[0][2] === ''
		&& list[1][1].endsWith('/images/clarte/' + dark) && list[1][2] === '(prefers-color-scheme: dark)' && list[1][3] === 'image/png'
		&& list[2][0] === 'apple-touch-icon' && list[2][1].endsWith('/images/clarte/' + touch);
	await p.goto(B + 'index.php');
	ok(want(await icons()), 'forum (Clarté) : icône, icône sombre, icône de l’écran d’accueil');
	const r = await p.request.get((await icons())[0][1]);
	ok(r.status() === 200 && (r.headers()['content-type'] || '').match(/icon|octet/), 'le fichier .ico est servi (' + r.headers()['content-type'] + ')');
	await p.goto(B + 'viewtopic.php?t=1&style=1');
	ok(want(await icons()) && await p.locator('#clarte-vars').count() === 0, 'prosilver : l’icône aussi, sans rien d’autre de Clarté');
	await p.goto(B + 'index.php?style=2');
	await p.goto(acp('settings'));
	ok(want(await icons()), 'administration : icône');
	cli('config:set clarte_acp 0'); cli('cache:purge');
	await p.goto(acp('settings'));
	ok(want(await icons()), 'administration sans l’habillage Clarté : icône quand même');
	cli('config:set clarte_acp 1'); cli('cache:purge');

	// Listes : seules les images du bon format ; bibliothèque : icônes « utilisées »
	await p.goto(acp('settings'));
	const opts = await p.locator('#clarte_touch_icon option').allInnerTexts();
	ok(opts.length === 3 && !(await p.locator('#clarte_bg_image option').allInnerTexts()).some(t => t.includes('.ico')), 'listes filtrées par format (pas d’ICO pour le fond)');
	ok(await p.locator('.clarte-library__item').count() === 3 && await p.locator('.clarte-library__delete').count() === 0, 'bibliothèque : les 3 icônes, utilisées');

	// Sans icône sombre
	await p.selectOption('#clarte_favicon_dark', '');
	await submit();
	await p.goto(B + 'index.php');
	ok((await icons()).length === 2 && !(await icons()).some(i => i[2]), 'icône sombre retirée : deux balises');

	// Sauvegarde : icônes comprises
	await p.goto(acp('backup'));
	const [dl] = await Promise.all([p.waitForEvent('download'), p.click('a.clarte-button-link')]);
	const file = OUT + '/sauvegarde-icone.json'; await dl.saveAs(file);
	const backup = JSON.parse(fs.readFileSync(file, 'utf8'));
	ok(backup.images && backup.images[fav] && backup.images[touch] && backup.settings.clarte_favicon === fav, 'sauvegarde : icônes et réglage');

	// Valeur trafiquée en base : ignorée
	cli('config:set clarte_favicon ../../config.php'); cli('cache:purge');
	await p.goto(B + 'index.php');
	ok((await icons()).length === 1 && (await icons())[0][0] === 'apple-touch-icon', 'nom de fichier invalide : aucune balise d’icône');

	ok(errs.length === 0, 'aucune erreur JavaScript ' + errs.join(' | '));
	console.log(fails ? `${fails} échec(s)` : 'TOUT EST BON');
	await b.close();
})().catch(e => { console.error(e); process.exit(1); });
