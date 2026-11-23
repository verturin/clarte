// Test de bout en bout de la 1.5.x : taille du texte (ACP) et choix du membre dans son panneau.
// node test_taille_texte.js <dossier de sortie>
// Forum local 127.0.0.1:8080 (php -S), compte admin / adminpass123, lancer reset_forums_styles.sh avant.
const { chromium } = require('playwright');
const { execSync } = require('child_process');
const B = 'http://127.0.0.1:8080/';
const WWW = '/home/claude/phpbb/www';
const OUT = process.argv[2];
const MOD = 'i=-verturin-clarte-acp-main_module&mode=';
const cli = c => execSync(`php ${WWW}/bin/phpbbcli.php ${c}`).toString().trim();
const sql = q => execSync(`python3 -c "import sqlite3,json;c=sqlite3.connect('/home/claude/phpbb/data/forum.db');print(json.dumps(c.execute(\\"${q}\\").fetchall()))"`).toString().trim();
let fails = 0;
const ok = (cond, msg) => { console.log((cond ? 'OK   ' : 'ÉCHEC ') + msg); if (!cond) fails++; };
const pause = () => new Promise(r => setTimeout(r, 1200));

(async () => {
	const b = await chromium.launch();
	const ctx = await b.newContext({ viewport: { width: 1280, height: 900 } });
	const p = await ctx.newPage();
	const errs = [];
	p.on('pageerror', e => errs.push(e.message));
	const size = async (url, sel = 'body') => { await p.goto(B + url); return p.evaluate(s => parseFloat(getComputedStyle(document.querySelector(s)).fontSize), sel); };

	// Invité : taille normale
	ok(await size('index.php') === 10.5, 'invité, taille normale : base 10,5 px');

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

	// Option fermée : rien dans le panneau du membre
	await p.goto(B + 'ucp.php?i=ucp_prefs&mode=personal');
	ok(await p.locator('#clarte_text').count() === 0, 'membres non autorisés : pas de choix dans le panneau');

	// ACP : curseur à 113 % (pas de 1), membres autorisés
	await p.goto(acp('settings'));
	ok(await p.getAttribute('#clarte_text_size', 'type') === 'range' && await p.getAttribute('#clarte_text_size', 'step') === '1', 'ACP : curseur de 1 en 1');
	await p.fill('#clarte_text_size', '113');
	ok((await p.innerText('#clarte_text_size_out')).startsWith('113'), 'ACP : pourcentage affiché en direct (113 %)');
	await p.locator('#clarte_text_size').scrollIntoViewIfNeeded(); await p.screenshot({ path: OUT + '/acp-curseur.png' });
	await p.check('input[name=clarte_text_allow][value="1"]');
	await pause(); await Promise.all([p.waitForNavigation(), p.click('#submit')]);
	ok(cli('config:get clarte_text_size') === '113' && cli('config:get clarte_text_allow') === '1', 'ACP : 113 % et choix des membres enregistrés');
	const b1 = await size('index.php');
	ok(Math.abs(b1 - 10.5 * 1.13) < 0.05, `taille du forum 113 % : base ${b1} px`);
	const post = await size('viewtopic.php?t=1', '.postbody .content');
	ok(Math.abs(post - 10.5 * 1.13 * 1.3) < 0.1, `message : ${post.toFixed(2)} px`);

	// Panneau du membre : « Très grande »
	await p.goto(B + 'ucp.php?i=ucp_prefs&mode=personal');
	ok(await p.locator('#clarte_text').count() === 1, 'membres autorisés : « Taille du texte » dans le panneau');
	ok(await p.isChecked('#clarte_text_board') && await p.isDisabled('#clarte_text'), 'par défaut : « Taille du forum » cochée, curseur grisé');
	ok((await p.innerText('label:has(#clarte_text_board)')).includes('113'), 'libellé : « Taille du forum (113 %) »');
	await p.uncheck('#clarte_text_board');
	await p.fill('#clarte_text', '137');
	ok((await p.innerText('#clarte_text_out')).startsWith('137') && Math.abs(await p.evaluate(() => parseFloat(getComputedStyle(document.body).fontSize)) - 10.5 * 1.37) < 0.05, 'aperçu immédiat à 137 %');
	await p.selectOption('select[name=tz]', 'Europe/Paris');
	await p.locator('#clarte_text').scrollIntoViewIfNeeded();
	await p.screenshot({ path: OUT + '/ucp-taille-texte.png' });
	await pause(); await Promise.all([p.waitForNavigation(), p.click('input[name=submit]')]);
	ok(JSON.parse(sql('select user_clarte_text from phpbb_users where user_id=2'))[0][0] === '137', 'choix du membre enregistré (137)');
	const b2 = await size('index.php');
	ok(Math.abs(b2 - 10.5 * 1.37) < 0.05, `membre 137 % : base ${b2} px`);
	await p.goto(B + 'viewtopic.php?t=1'); await p.screenshot({ path: OUT + '/sujet-137.png' });

	// Retour à « taille du forum »
	await p.goto(B + 'ucp.php?i=ucp_prefs&mode=personal');
	ok(!await p.isChecked('#clarte_text_board') && await p.inputValue('#clarte_text') === '137', 'panneau rouvert : curseur sur 137');
	await p.check('#clarte_text_board');
	await p.selectOption('select[name=tz]', 'Europe/Paris');
	await pause(); await Promise.all([p.waitForNavigation(), p.click('input[name=submit]')]);
	ok(Math.abs(await size('index.php') - 10.5 * 1.13) < 0.05, 'membre revenu à la taille du forum : 113 %');

	// Option refermée : le choix du membre est ignoré
	execSync(`python3 -c "import sqlite3;c=sqlite3.connect('/home/claude/phpbb/data/forum.db');c.execute(\\"update phpbb_users set user_clarte_text='80' where user_id=2\\");c.commit()"`);
	cli('config:set clarte_text_allow 0'); cli('cache:purge');
	ok(Math.abs(await size('index.php') - 10.5 * 1.13) < 0.05, 'membres non autorisés : choix 80 % ignoré, taille du forum appliquée');

	// Autre style (prosilver) : aucun effet
	const pro = await size('index.php?style=1');
	ok(pro === 10, `prosilver : base 10 px, pas touchée (${pro})`);

	// Valeur hors bornes envoyée à la main : ramenée à 150
	cli('config:set clarte_text_size 999'); cli('cache:purge');
	ok(Math.abs(await size('index.php?style=2') - 10.5 * 1.5) < 0.05, 'valeur 999 ramenée à 150 %');
	cli('config:set clarte_text_size 100'); cli('cache:purge');
	await p.goto(B + 'index.php?style=2');
	ok(errs.length === 0, 'aucune erreur JavaScript ' + errs.join(' | '));
	console.log(fails ? `${fails} échec(s)` : 'TOUT EST BON');
	await b.close();
})().catch(e => { console.error(e); process.exit(1); });
