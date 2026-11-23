// Test de bout en bout de la 1.6.0 : images de fond (page et en-tête), bibliothèque, choix du membre, modèle de forum, sauvegarde.
// node test_images_fond.js <dossier de sortie> <dossier des images de test>
// Forum local 127.0.0.1:8080 (php -S), compte admin / adminpass123, lancer reset_forums_styles.sh avant.
// Images de test : paysage.jpg, nuit.jpg, bandeau.jpg (photos), motif.png (petit motif), faux.jpg (pas une image), immense.png (9000 px).
const { chromium } = require('playwright');
const { execSync, execFileSync } = require('child_process');
const fs = require('fs');
const B = 'http://127.0.0.1:8080/';
const WWW = '/home/claude/phpbb/www';
const DB = '/home/claude/phpbb/data/forum.db';
const OUT = process.argv[2];
const IMG = process.argv[3];
const MOD = 'i=-verturin-clarte-acp-main_module&mode=';
const cli = c => execSync(`php ${WWW}/bin/phpbbcli.php ${c}`).toString().trim();
const sql = q => JSON.parse(execFileSync('python3', ['-c', `import sqlite3,json,sys;c=sqlite3.connect('${DB}');r=c.execute(sys.argv[1]).fetchall();c.commit();print(json.dumps(r))`, q]).toString());
let fails = 0;
const ok = (cond, msg) => { console.log((cond ? 'OK   ' : 'ÉCHEC ') + msg); if (!cond) fails++; };
const pause = () => new Promise(r => setTimeout(r, 1200));
const NAME = /^[0-9a-f]{16}\.(jpg|png|gif|webp)$/;

(async () => {
	const b = await chromium.launch();
	const ctx = await b.newContext({ viewport: { width: 1280, height: 900 }, acceptDownloads: true });
	const p = await ctx.newPage();
	const errs = [];
	p.on('pageerror', e => errs.push(e.message));
	const pseudo = (sel, which, prop) => p.evaluate(([s, w, pr]) => getComputedStyle(document.querySelector(s), w).getPropertyValue(pr), [sel, which, prop]);

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

	// Sans image : rien d'ajouté au forum
	await p.goto(B + 'index.php');
	ok(await p.locator('#clarte-bg').count() === 0, 'sans image : pas de feuille #clarte-bg');

	// Fichier qui n'est pas une image : refusé, rien n'est enregistré
	await p.goto(acp('settings'));
	ok(await p.locator('fieldset.clarte-images').count() === 1, 'ACP : section « Images de fond »');
	await p.setInputFiles('#upload_clarte_bg_image', IMG + '/faux.jpg');
	await submit();
	ok((await p.locator('.errorbox').innerText()).includes('JPG'), 'faux.jpg refusé (pas une image)');
	ok(cli('config:get clarte_bg_image') === '', 'rien enregistré après un refus');
	await p.setInputFiles('#upload_clarte_bg_image', IMG + '/immense.png');
	await submit();
	ok((await p.locator('.errorbox').innerText()).includes('8000'), 'image de 9000 px refusée');

	// Envoi des trois images et réglages
	await p.goto(acp('settings'));
	await p.setInputFiles('#upload_clarte_bg_image', IMG + '/paysage.jpg');
	await p.setInputFiles('#upload_clarte_bg_image_dark', IMG + '/nuit.jpg');
	await p.setInputFiles('#upload_clarte_header_image', IMG + '/bandeau.jpg');
	ok((await p.getAttribute('#clarte_bg_image_thumb', 'style') || '').includes('blob:'), 'vignette immédiate de l’image choisie');
	await p.fill('#clarte_bg_overlay', '40');
	await p.fill('#clarte_bg_blur', '4');
	await p.fill('#clarte_bg_glass', '20');
	ok((await p.innerText('#clarte_bg_glass_out')).startsWith('20'), 'curseur « Blocs translucides » affiché en direct');
	await p.check('input[name=clarte_bg_mobile][value="0"]');
	await p.locator('fieldset.clarte-images').scrollIntoViewIfNeeded();
	await p.screenshot({ path: OUT + '/acp-images.png', fullPage: false });
	await submit();
	const bg = cli('config:get clarte_bg_image'), bgDark = cli('config:get clarte_bg_image_dark'), head = cli('config:get clarte_header_image');
	ok(NAME.test(bg) && NAME.test(bgDark) && NAME.test(head) && new Set([bg, bgDark, head]).size === 3, `trois images enregistrées (${bg}, ${bgDark}, ${head})`);
	ok([bg, bgDark, head].every(f => fs.existsSync(`${WWW}/images/clarte/${f}`)), 'fichiers présents dans images/clarte/');
	ok(cli('config:get clarte_bg_overlay') === '40' && cli('config:get clarte_bg_blur') === '4' && cli('config:get clarte_bg_glass') === '20' && cli('config:get clarte_bg_mobile') === '0', 'voile 40, flou 4, translucide 20, pas sur mobile');

	// Le même fichier envoyé deux fois n'est stocké qu'une fois
	await p.goto(acp('settings'));
	await p.setInputFiles('#upload_clarte_bg_image', IMG + '/paysage.jpg');
	await submit();
	ok(fs.readdirSync(`${WWW}/images/clarte`).filter(f => NAME.test(f)).length === 3, 'même image renvoyée : pas de doublon');

	// Forum, mode clair
	await p.goto(B + 'viewtopic.php?t=1');
	await p.evaluate(() => localStorage.setItem('clarte_mode', 'light')); await p.reload();
	ok((await pseudo('html', '::before', 'background-image')).includes(bg), 'page : image de fond derrière la page');
	ok(await pseudo('html', '::before', 'position') === 'fixed', 'image fixe');
	ok((await pseudo('html', '::before', 'filter')).includes('blur(4px)'), 'flou 4 px');
	ok(await pseudo('html', '::after', 'opacity') === '0.4', 'voile 40 %');
	ok(await pseudo('body', null, 'background-color') === 'rgba(0, 0, 0, 0)', 'corps de page transparent');
	ok((await pseudo('.headerbar', null, 'background-image')).includes(head), 'en-tête : image');
	ok((await pseudo('.post', null, 'background-color')).includes('0.8'), 'messages translucides (80 %)');
	ok((await pseudo('.post', null, 'backdrop-filter')).includes('blur'), 'flou derrière les blocs');
	await p.screenshot({ path: OUT + '/sujet-clair.png' });

	// Mode sombre : image de nuit
	await p.evaluate(() => localStorage.setItem('clarte_mode', 'dark')); await p.reload();
	ok((await pseudo('html', '::before', 'background-image')).includes(bgDark), 'sombre : image de nuit');
	ok(await pseudo('html', '::after', 'opacity') === '0.4', 'sombre avec sa propre image : même voile');
	await p.goto(B + 'index.php'); await p.screenshot({ path: OUT + '/accueil-sombre.png' });

	// Sombre sans image dédiée : même image, plus voilée
	cli('config:set clarte_bg_image_dark ""'); cli('cache:purge');
	await p.reload();
	ok((await pseudo('html', '::before', 'background-image')).includes(bg) && await pseudo('html', '::after', 'opacity') === '0.67', 'sombre sans image dédiée : même image, voile 67 %');
	cli(`config:set clarte_bg_image_dark ${bgDark}`); cli('cache:purge');
	await p.evaluate(() => localStorage.setItem('clarte_mode', 'light'));

	// Mobile : pas d'image de fond, en-tête gardé
	const m = await b.newPage({ viewport: { width: 390, height: 844 } });
	await m.goto(B + 'index.php');
	ok(await m.evaluate(() => getComputedStyle(document.documentElement, '::before').content) === 'none', 'mobile : pas d’image de fond');
	ok((await m.evaluate(() => getComputedStyle(document.querySelector('.headerbar')).backgroundImage)).includes(head), 'mobile : image de l’en-tête gardée');
	ok(await m.evaluate(() => getComputedStyle(document.querySelector('.forabg')).backgroundColor) !== 'rgba(255, 255, 255, 0.8)', 'mobile : blocs opaques');
	await m.screenshot({ path: OUT + '/mobile.png' });
	await m.close();

	// Image qui défile, motif répété
	cli('config:set clarte_bg_fixed 0'); cli('config:set clarte_bg_mode tile'); cli('cache:purge');
	await p.goto(B + 'index.php');
	ok(await pseudo('html', '::before', 'position') === 'absolute' && await pseudo('html', '::before', 'background-repeat') === 'repeat', 'image qui défile, motif répété');
	cli('config:set clarte_bg_fixed 1'); cli('config:set clarte_bg_mode cover'); cli('cache:purge');

	// prosilver : rien
	await p.goto(B + 'index.php?style=1');
	ok(await p.locator('#clarte-bg').count() === 0, 'prosilver : aucune image');
	await p.goto(B + 'index.php?style=2');

	// Bibliothèque : toutes utilisées ; l'en-tête retiré, son image peut être supprimée
	await p.goto(acp('settings'));
	ok(await p.locator('.clarte-library__item').count() === 3 && await p.locator('.clarte-library__delete').count() === 0, 'bibliothèque : 3 images, toutes utilisées');
	await p.selectOption('#clarte_header_image', '');
	await submit();
	await p.goto(acp('settings'));
	ok(await p.locator('.clarte-library__delete').count() === 1, 'image d’en-tête retirée : « Supprimer » proposé');
	p.once('dialog', d => d.accept());
	await Promise.all([p.waitForNavigation(), p.click('.clarte-library__delete')]);
	ok(!fs.existsSync(`${WWW}/images/clarte/${head}`) && await p.locator('.clarte-library__item').count() === 2, 'image supprimée du disque et de la liste');
	await p.goto(acp('settings') + `&action=image_delete&file=${bg}&hash=x`);
	ok(fs.existsSync(`${WWW}/images/clarte/${bg}`), 'lien de suppression falsifié : image gardée');

	// Choix de la liste : l'image d'en-tête reprend une image existante
	await p.goto(acp('settings'));
	await p.selectOption('#clarte_header_image', bg);
	await submit();
	ok(cli('config:get clarte_header_image') === bg, 'image choisie dans la liste');

	// Membre : masquer les images
	await p.goto(acp('settings'));
	await p.check('input[name=clarte_bg_allow][value="1"]');
	await submit();
	await p.goto(B + 'ucp.php?i=ucp_prefs&mode=personal');
	ok(await p.locator('input[name=clarte_bg_hide]').count() === 2, 'panneau : « Images de fond : Afficher / Masquer »');
	await p.check('input[name=clarte_bg_hide][value="1"]');
	await p.selectOption('select[name=tz]', 'Europe/Paris');
	await pause(); await Promise.all([p.waitForNavigation(), p.click('input[name=submit]')]);
	ok(sql('select user_clarte_bg from phpbb_users where user_id=2')[0][0] === 1, 'choix « Masquer » enregistré');
	await p.goto(B + 'index.php');
	ok(await p.locator('#clarte-bg').count() === 0, 'membre qui masque : aucune image');
	cli('config:set clarte_bg_allow 0'); cli('cache:purge');
	await p.goto(B + 'index.php');
	ok(await p.locator('#clarte-bg').count() === 1, 'option refermée : images de nouveau affichées');

	// Modèle avec images, appliqué à un forum : le forum a son image, l'accueil garde la sienne
	await p.goto(acp('settings'));
	await p.fill('#clarte_model_name', 'Fond nuit');
	await p.selectOption('#clarte_bg_image', bgDark);
	await pause(); await Promise.all([p.waitForNavigation(), p.click('#save_model')]);
	const models = JSON.parse(sql("select config_value from phpbb_config_text where config_name='clarte_models'")[0][0]);
	const model = models.items.find(i => i.name === 'Fond nuit');
	ok(model && model.settings.clarte_bg_image === bgDark && model.settings.clarte_bg_glass === 20, 'modèle « Fond nuit » : image et réglages gardés');
	sql(`update phpbb_config_text set config_value='{"2":${model.id}}' where config_name='clarte_forum_models'`); cli('cache:purge');
	await p.goto(B + 'viewforum.php?f=2');
	ok((await pseudo('html', '::before', 'background-image')).includes(bgDark), 'forum avec le modèle : son image');
	await p.goto(B + 'index.php');
	ok((await pseudo('html', '::before', 'background-image')).includes(bg), 'accueil : image du forum');
	sql(`update phpbb_config_text set config_value='{}' where config_name='clarte_forum_models'`); cli('cache:purge');

	// Sauvegarde : images comprises ; restauration après effacement
	await p.goto(acp('backup'));
	const [dl] = await Promise.all([p.waitForEvent('download'), p.click('a.clarte-button-link')]);
	const file = OUT + '/sauvegarde.json'; await dl.saveAs(file);
	const backup = JSON.parse(fs.readFileSync(file, 'utf8'));
	ok(backup.images && backup.images[bg] && backup.images[bgDark] && Object.keys(backup.images).length === 2, 'sauvegarde : les 2 images en service, en base64');
	fs.readdirSync(`${WWW}/images/clarte`).forEach(f => fs.unlinkSync(`${WWW}/images/clarte/${f}`));
	cli('config:set clarte_bg_image ""'); cli('cache:purge');
	await p.goto(acp('backup'));
	await p.setInputFiles('#clarte_file', file);
	await p.uncheck('input[name=restore_models]');
	p.once('dialog', d => d.accept());
	await pause(); await Promise.all([p.waitForNavigation(), p.click('input[name=restore]')]);
	ok((await p.innerText('#main')).match(/2 images/), 'restauration : « 2 images enregistrées »');
	ok(cli('config:get clarte_bg_image') === bg && fs.existsSync(`${WWW}/images/clarte/${bg}`) && fs.existsSync(`${WWW}/images/clarte/${bgDark}`), 'images et réglages restaurés');

	// Aucune erreur PHP visible ni JavaScript
	await p.goto(B + 'index.php');
	ok(!(await p.content()).match(/\[phpBB Debug\]|Fatal error|Warning:/), 'aucune erreur PHP dans la page');
	ok(errs.length === 0, 'aucune erreur JavaScript ' + errs.join(' | '));
	console.log(fails ? `${fails} échec(s)` : 'TOUT EST BON');
	await b.close();
})().catch(e => { console.error(e); process.exit(1); });
