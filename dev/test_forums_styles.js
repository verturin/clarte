// Test de bout en bout de la 1.4.0 : couleurs par forum (héritage) et modèles proposés comme styles.
// node test_forums_styles.js <dossier de sortie>
// Forum local 127.0.0.1:8080 (php -S), compte admin / adminpass123, forums : 1 catégorie { 2 forum { 3 sous-forum } } 4 catégorie { 5 forum }.
const { chromium } = require('playwright');
const fs = require('fs');
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
	const ctx = await b.newContext({ viewport: { width: 1280, height: 900 }, acceptDownloads: true });
	const p = await ctx.newPage();
	const errs = [];
	p.on('pageerror', e => errs.push(e.message));

	await p.goto(B + 'ucp.php?mode=login');
	await p.fill('input[name=username]', 'admin');
	await p.fill('input[name=password]', 'adminpass123');
	await pause(); await Promise.all([p.waitForNavigation(), p.click('input[name=login]')]);
	const sid0 = (await ctx.cookies()).find(c => c.name.endsWith('_sid')).value;
	await p.goto(B + 'adm/index.php?sid=' + sid0);
	await p.fill('input[type=password]', 'adminpass123');
	await pause(); await Promise.all([p.waitForNavigation(), p.click('input[name=login]')]);
	const sid = new URL(p.url()).searchParams.get('sid');
	const acp = mode => `${B}adm/index.php?sid=${sid}&${MOD}${mode}`;
	const msg = async () => (await p.locator('#main .successbox, #main .errorbox').first().innerText()).replace(/\s+/g, ' ');
	const row = name => p.locator('tbody tr').filter({ has: p.locator('strong').getByText(name, { exact: true }) });
	const accent = async url => { await p.goto(B + url); return p.evaluate(() => getComputedStyle(document.documentElement).getPropertyValue('--accent').trim()); };

	// Deux modèles : Rose et Vert
	for (const [name, a, f, t] of [['Rose', '#db2777', '#500724', '#9d174d'], ['Vert', '#059669', '#04201a', '#065f46']]) {
		await p.goto(acp('settings'));
		await p.fill('#clarte_accent', a); await p.dispatchEvent('#clarte_accent', 'input');
		await p.fill('#clarte_header_from', f); await p.fill('#clarte_header_to', t);
		await p.selectOption('#clarte_cat_mode', 'tinted');
		await p.fill('#clarte_model_name', name);
		await pause(); await Promise.all([p.waitForNavigation(), p.click('#save_model')]);
	}
	const ids = JSON.parse(cli('config:get clarte_version') && sql("select config_value from phpbb_config_text where config_name='clarte_models'"))[0][0];
	const models = JSON.parse(ids).items; const rose = models.find(m => m.name === 'Rose').id, vert = models.find(m => m.name === 'Vert').id;
	ok(rose && vert, `modèles créés (Rose = ${rose}, Vert = ${vert})`);
	const general = cli('config:get clarte_accent');

	// Couleurs par forum : Rose sur le forum 2 (le sous-forum 3 hérite), Vert sur le forum 5
	await p.goto(acp('forums'));
	ok(await p.locator('select[name^=forum_model]').count() === 5, 'page Couleurs par forum : 5 forums listés');
	await p.selectOption('select[name="forum_model[2]"]', String(rose));
	await p.selectOption('select[name="forum_model[5]"]', String(vert));
	await pause(); await Promise.all([p.waitForNavigation(), p.click('#submit')]);
	ok((await msg()).includes('enregistrées'), 'couleurs des forums enregistrées');
	await p.goto(acp('forums'));
	ok((await p.locator('tbody tr').nth(2).innerText()).includes('hérité de « Your first forum »'), 'sous-forum : « Rose, hérité de Your first forum » affiché');
	await p.screenshot({ path: OUT + '/acp-couleurs-par-forum.png', fullPage: true });

	ok(await accent('index.php') === general, `accueil : couleurs générales (${general})`);
	ok(await accent('viewforum.php?f=2') === '#db2777', 'forum 2 : rose');
	ok(await accent('viewforum.php?f=3') === '#db2777', 'sous-forum 3 : rose (hérité)');
	ok(await accent('viewtopic.php?t=1') === '#db2777', 'sujet du forum 2 : rose');
	ok(await accent('posting.php?mode=reply&t=1') === '#db2777', 'répondre dans le forum 2 : rose');
	ok(await accent('posting.php?mode=post&f=3') === '#db2777', 'nouveau sujet dans le sous-forum 3 : rose');
	ok(await accent('viewforum.php?f=5') === '#059669', 'forum 5 : vert');
	ok(await accent('viewforum.php?f=4') === general, 'catégorie 4 : couleurs générales');
	await p.goto(B + 'viewforum.php?f=2'); await p.screenshot({ path: OUT + '/forum-rose.png' });

	// Le sous-forum choisit « couleurs générales »
	await p.goto(acp('forums'));
	await p.selectOption('select[name="forum_model[3]"]', '-1');
	await pause(); await Promise.all([p.waitForNavigation(), p.click('#submit')]);
	ok(await accent('viewforum.php?f=3') === general, 'sous-forum 3 réglé sur « couleurs générales » : n’hérite plus');

	// Modèle proposé comme style
	await p.goto(acp('models'));
	await Promise.all([p.waitForNavigation(), row('Rose').locator('a[href*="action=style_create&"]').click()]);
	ok((await msg()).includes('Clarté – Rose'), 'style créé : ' + await msg());
	const st = JSON.parse(sql("select style_id,style_name,style_path,style_parent_tree,style_active from phpbb_styles where style_path like 'clarte_m%'"));
	ok(st.length === 1 && st[0][1] === 'Clarté – Rose' && st[0][3] === 'prosilver/clarte' && st[0][4] === 1, 'style en base : ' + JSON.stringify(st));
	const dir = `${WWW}/styles/clarte_m${rose}`;
	ok(fs.existsSync(dir + '/style.cfg') && fs.existsSync(dir + '/theme/stylesheet.css') && fs.existsSync(dir + '/theme/fr/stylesheet.css'), 'fichiers du style écrits (' + dir + ')');
	const styleId = st[0][0];

	// Le membre choisit ce style dans son panneau
	cli('config:set override_user_style 0');
	await p.goto(B + 'ucp.php?i=ucp_prefs&mode=personal');
	await p.selectOption('select[name=user_style]', String(styleId));
	await p.selectOption('select[name=tz]', 'Europe/Paris'); // le forum de test n'a pas de fuseau par défaut
	await pause(); await Promise.all([p.waitForNavigation(), p.click('input[name=submit]')]);
	ok(JSON.parse(sql('select user_style from phpbb_users where user_id=2'))[0][0] === styleId, 'panneau du membre : style « Clarté – Rose » choisi');
	ok(await accent('index.php') === '#db2777', 'accueil avec le style Rose : rose');
	const sheets = await p.evaluate(() => [...document.styleSheets].map(s => s.href || '').filter(h => h.includes('styles/')));
	ok(sheets.some(h => h.includes('clarte_m' + rose + '/theme/stylesheet.css')), 'la feuille du style généré est chargée');
	const okSheets = await p.evaluate(() => [...document.styleSheets].every(s => { try { return s.cssRules.length >= 0; } catch (e) { return true; } }));
	ok(await p.locator('.clarte-mode-toggle').count() > 0 && await p.evaluate(() => getComputedStyle(document.querySelector('.forabg')).borderRadius) !== '0px', 'présentation Clarté complète (bouton Apparence, blocs arrondis)');
	ok(await accent('viewforum.php?f=5') === '#059669', 'style Rose + forum 5 Vert : le forum l’emporte (vert)');
	await p.goto(B + 'index.php'); await p.screenshot({ path: OUT + '/forum-style-rose.png' });

	// Renommer le modèle : le style suit
	await p.goto(acp('models'));
	await Promise.all([p.waitForNavigation(), row('Rose').locator('a[href*="action=edit&"]').click()]);
	await p.fill('#clarte_model_name', 'Rose poudré');
	await pause(); await Promise.all([p.waitForNavigation(), p.click('#submit')]);
	ok(JSON.parse(sql(`select style_name from phpbb_styles where style_id=${styleId}`))[0][0] === 'Clarté – Rose poudré', 'modèle renommé : le style s’appelle « Clarté – Rose poudré »');
	await p.goto(acp('models'));
	await p.screenshot({ path: OUT + '/acp-modeles-styles.png', fullPage: true });

	// Sauvegarde (avec forums et style), puis retrait du style
	await p.goto(acp('backup'));
	let [dl] = await Promise.all([p.waitForEvent('download'), p.click('a.clarte-button-link')]);
	const bakPath = OUT + '/sauvegarde-140.json'; fs.copyFileSync(await dl.path(), bakPath);
	const bak = JSON.parse(fs.readFileSync(bakPath, 'utf8'));
	const brose = bak.models.find(m => m.name === 'Rose poudré');
	ok(brose && brose.style === true && brose.forums.includes(2), 'sauvegarde : le modèle garde ses forums et son style');

	await p.goto(acp('models'));
	await Promise.all([p.waitForNavigation(), row('Rose poudré').locator('a[href*="action=style_delete&"]').click()]);
	await pause(); await Promise.all([p.waitForNavigation(), p.click('input[name=confirm]')]);
	ok((await msg()).includes('1 membre est revenu'), 'style retiré : ' + await msg());
	const clarteId = JSON.parse(sql("select style_id from phpbb_styles where style_path='clarte'"))[0][0];
	ok(JSON.parse(sql('select user_style from phpbb_users where user_id=2'))[0][0] === clarteId, 'le membre est revenu au style Clarté');
	ok(JSON.parse(sql("select count(*) from phpbb_styles where style_path like 'clarte_m%'"))[0][0] === 0 && !fs.existsSync(dir), 'style effacé (base et dossier)');
	ok(await accent('index.php') === general, 'accueil : couleurs générales de nouveau');

	// Restaurer (remplacer) : modèles, forums et style reviennent
	await p.goto(acp('backup'));
	await p.setInputFiles('#clarte_file', bakPath);
	await p.check('input[name=models_mode][value=replace]');
	p.once('dialog', d => d.accept());
	await pause(); await Promise.all([p.waitForNavigation(), p.click('input[name=restore]')]);
	const rmsg = await msg();
	ok(rmsg.includes('forums ont retrouvé') && rmsg.includes('1 style au choix recréé'), 'restauration : ' + rmsg);
	ok(await accent('viewforum.php?f=2') === '#db2777' && await accent('viewforum.php?f=5') === '#059669', 'restauration : forums 2 (rose) et 5 (vert) de nouveau colorés');
	const st2 = JSON.parse(sql("select style_id,style_path from phpbb_styles where style_path like 'clarte_m%'"));
	ok(st2.length === 1 && fs.existsSync(`${WWW}/styles/${st2[0][1]}/style.cfg`), 'restauration : style au choix recréé (' + st2[0][1] + ')');

	// Un style retiré dans ACP › Styles : l'extension l'oublie et efface son dossier
	execSync(`python3 -c "import sqlite3;c=sqlite3.connect('/home/claude/phpbb/data/forum.db');c.execute('delete from phpbb_styles where style_id=${st2[0][0]}');c.commit()"`);
	cli('cache:purge');
	await p.goto(acp('models'));
	ok(await p.locator('.clarte-models__style').count() === 0 && !fs.existsSync(`${WWW}/styles/${st2[0][1]}`), 'style supprimé ailleurs : oublié et dossier effacé');

	// Supprimer un modèle utilisé par un forum (message + nettoyage)
	await p.goto(acp('models'));
	await Promise.all([p.waitForNavigation(), row('Vert').locator('a[href*="action=delete&"]').click()]);
	ok((await p.locator('#main').innerText()).includes('Ce modèle colore les forums : Annonces'), 'confirmation de suppression : forums cités');
	await pause(); await Promise.all([p.waitForNavigation(), p.click('input[name=confirm]')]);
	ok(await accent('viewforum.php?f=5') === general, 'modèle supprimé : le forum 5 reprend les couleurs générales');

	ok(errs.length === 0, 'aucune erreur JavaScript ' + errs.join(' | '));
	console.log(fails ? `${fails} échec(s)` : 'TOUT EST BON');
	await b.close();
})().catch(e => { console.error(e); process.exit(1); });
