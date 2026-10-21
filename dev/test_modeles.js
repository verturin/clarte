// Test de bout en bout de la 1.3.0 (modèles, sauvegarde/restauration, date sur l'accueil) — node test_modeles.js <dossier de sortie>
// Forum local 127.0.0.1:8080 (php -S), compte admin / adminpass123, reset_modeles.sh avant chaque lancement.
const { chromium } = require('playwright');
const fs = require('fs');
const { execSync } = require('child_process');
const B = 'http://127.0.0.1:8080/';
const OUT = process.argv[2];
const cfg = k => execSync(`php /home/claude/phpbb/www/bin/phpbbcli.php config:get ${k}`).toString().trim();
const MOD = 'i=-verturin-clarte-acp-main_module&mode=';
let fails = 0;
const pause = () => new Promise(r => setTimeout(r, 1200));
const ok = (cond, msg) => { console.log((cond ? 'OK   ' : 'ÉCHEC ') + msg); if (!cond) fails++; };

(async () => {
	const b = await chromium.launch();
	const ctx = await b.newContext({ viewport: { width: 1280, height: 900 }, acceptDownloads: true });
	const p = await ctx.newPage();
	const errs = [];
	p.on('pageerror', e => errs.push(e.message));

	// Connexion + réauthentification ACP
	await p.goto(B + 'ucp.php?mode=login');
	await p.fill('input[name=username]', 'admin');
	await p.fill('input[name=password]', 'adminpass123');
	await pause(); await Promise.all([p.waitForNavigation(), p.click('input[name=login]')]);
	const sid0 = (await ctx.cookies()).find(c => c.name.endsWith('_sid')).value;
	await p.goto(B + 'adm/index.php?sid=' + sid0);
	console.log('ACP:', p.url());
	await p.fill('input[type=password]', 'adminpass123');
	await pause(); await Promise.all([p.waitForNavigation(), p.click('input[name=login]')]);
	const sid = new URL(p.url()).searchParams.get('sid');
	const acp = mode => `${B}adm/index.php?sid=${sid}&${MOD}${mode}`;
	const row = name => p.locator('tbody tr').filter({ has: p.locator('strong').getByText(name, { exact: true }) });
	const msg = async () => (await p.locator('#main .successbox, #main .errorbox').first().innerText()).replace(/\s+/g, ' ');

	// 1. Réglages : enregistrer comme modèle
	await p.goto(acp('settings'));
	ok(await p.locator('#save_model').count() === 1, 'page de réglages : bloc « Enregistrer comme modèle » présent');
	await p.selectOption('#clarte_cat_mode', 'tinted');
	await p.fill('#clarte_model_name', 'Violet teinté');
	await pause(); await Promise.all([p.waitForNavigation(), p.click('#save_model')]);
	ok((await msg()).includes('Violet teinté'), 'modèle enregistré depuis la page de réglages : ' + await msg());
	ok(cfg('clarte_cat_mode') === 'neutral', 'les réglages du forum ne sont pas changés par « Enregistrer comme modèle »');

	// 2. Liste des modèles
	await p.goto(acp('models'));
	ok(await p.locator('table.clarte-models tbody tr').count() === 1, 'liste : 1 modèle');
	// nouveau modèle à partir des réglages en service
	await p.fill('#clarte_model_name', 'Actuel');
	await pause(); await Promise.all([p.waitForNavigation(), p.click('input[name=save_current]')]);
	ok((await msg()).includes('Actuel'), 'nouveau modèle à partir des réglages en service');
	await p.goto(acp('models'));
	ok(await p.locator('.clarte-models__current').count() === 1, 'le modèle « Actuel » est marqué « En service »');

	// 3. Dupliquer
	await Promise.all([p.waitForNavigation(), row('Violet teinté').locator('a[href*="action=duplicate&"]').click()]);
	await p.waitForLoadState();
	ok(await p.locator('table.clarte-models tbody tr').count() === 3, 'dupliquer : 3 modèles');
	ok(await p.locator('td', { hasText: 'Violet teinté (copie)' }).count() === 1, 'la copie s’appelle « Violet teinté (copie) »');

	// 4. Modifier la copie
	await Promise.all([p.waitForNavigation(), row('Violet teinté (copie)').locator('a[href*="action=edit&"]').click()]);
	await p.waitForLoadState();
	ok(await p.locator('#clarte_allow_toggle').count() === 0, 'modification : les options propres au forum sont masquées');
	await p.fill('#clarte_model_name', 'Été vert');
	await p.fill('#clarte_accent', '#059669'); await p.dispatchEvent('#clarte_accent', 'input');
	await p.selectOption('#clarte_cat_mode', 'custom');
	await p.fill('#clarte_cat_colour', '#fde68a'); await p.dispatchEvent('#clarte_cat_colour', 'input');
	await p.fill('#clarte_radius', '6');
	await p.screenshot({ path: OUT + '/acp-modele-modifier.png', fullPage: true });
	await pause(); await Promise.all([p.waitForNavigation(), p.click('#submit')]);
	ok((await msg()).includes('Été vert'), 'modèle modifié et renommé : ' + await msg());

	// 5. Exporter un modèle
	await p.goto(acp('models'));
	let [dl] = await Promise.all([p.waitForEvent('download'), row('Été vert').locator('a[href*="action=export&"]').click()]);
	const one = JSON.parse(fs.readFileSync(await dl.path(), 'utf8'));
	ok(dl.suggestedFilename() === 'clarte-modele-ete-vert.json', 'export : nom du fichier ' + dl.suggestedFilename());
	ok(one.format === 'clarte-models' && one.models[0].settings.clarte_accent === '#059669' && one.models[0].settings.clarte_cat_colour === '#fde68a', 'export : contenu JSON correct');
	fs.copyFileSync(await dl.path(), OUT + '/ete-vert.json');

	// 6. Appliquer (confirmation phpBB)
	await Promise.all([p.waitForNavigation(), row('Été vert').locator('a[href*="action=apply&"]').click()]);
	await p.waitForLoadState();
	await Promise.all([p.waitForNavigation(), p.click('input[name=confirm]')]);
	await p.waitForLoadState();
	ok(cfg('clarte_accent') === '#059669' && cfg('clarte_cat_mode') === 'custom' && cfg('clarte_radius') === '6', 'appliquer : réglages du forum remplacés');
	ok(cfg('clarte_acp') === '1' && cfg('clarte_allow_toggle') === '1', 'appliquer : options propres au forum conservées');

	// 7. Annuler une suppression, puis supprimer
	await p.goto(acp('models'));
	await Promise.all([p.waitForNavigation(), row('Actuel').locator('a[href*="action=delete&"]').click()]);
	await p.waitForLoadState();
	await Promise.all([p.waitForNavigation(), p.click('input[name=cancel]')]);
	await p.waitForLoadState();
	ok(await p.locator('table.clarte-models tbody tr').count() === 3, 'suppression annulée : retour à la liste, 3 modèles');
	await Promise.all([p.waitForNavigation(), row('Actuel').locator('a[href*="action=delete&"]').click()]);
	await p.waitForLoadState();
	await Promise.all([p.waitForNavigation(), p.click('input[name=confirm]')]);
	await p.waitForLoadState();
	await p.goto(acp('models'));
	ok(await p.locator('table.clarte-models tbody tr').count() === 2, 'suppression : 2 modèles');
	await p.screenshot({ path: OUT + '/acp-modeles.png', fullPage: true });

	// 8. Sauvegarde
	await p.goto(acp('backup'));
	[dl] = await Promise.all([p.waitForEvent('download'), p.click('a.clarte-button-link')]);
	const bak = JSON.parse(fs.readFileSync(await dl.path(), 'utf8'));
	const bakPath = OUT + '/sauvegarde.json'; fs.copyFileSync(await dl.path(), bakPath);
	ok(bak.format === 'clarte-backup' && bak.settings.clarte_accent === '#059669' && bak.models.length === 2, 'sauvegarde : réglages + 2 modèles (' + dl.suggestedFilename() + ')');
	await p.screenshot({ path: OUT + '/acp-sauvegarde.png', fullPage: true });

	// 9. Tout changer, puis restaurer (remplacer les modèles)
	execSync('php /home/claude/phpbb/www/bin/phpbbcli.php config:set clarte_accent "#e11d48"');
	await p.goto(acp('models'));
	await Promise.all([p.waitForNavigation(), row('Violet teinté').locator('a[href*="action=delete&"]').click()]);
	await Promise.all([p.waitForNavigation(), p.click('input[name=confirm]')]); await p.waitForLoadState();
	await p.goto(acp('backup'));
	await p.setInputFiles('#clarte_file', bakPath);
	await p.check('input[name=models_mode][value=replace]');
	p.once('dialog', d => d.accept());
	await pause(); await Promise.all([p.waitForNavigation(), p.click('input[name=restore]')]);
	await p.waitForLoadState();
	ok((await msg()).includes('Sauvegarde restaurée'), 'restauration : ' + await msg());
	ok(cfg('clarte_accent') === '#059669', 'restauration : couleur principale rétablie');
	await p.goto(acp('models'));
	ok(await p.locator('table.clarte-models tbody tr').count() === 2, 'restauration (remplacer) : 2 modèles');

	// 10. Importer un modèle exporté (nom déjà pris -> numéroté)
	await p.setInputFiles('#clarte_file', OUT + '/ete-vert.json');
	await pause(); await Promise.all([p.waitForNavigation(), p.click('input[name=import]')]);
	await p.waitForLoadState();
	ok((await msg()).includes('1 modèle importé'), 'import : ' + await msg());
	await p.goto(acp('models'));
	ok(await p.locator('td', { hasText: 'Été vert (2)' }).count() === 1, 'import : nom déjà pris devenu « Été vert (2) »');

	// 10 bis. Nom piégé (HTML + guillemets) : affiché comme du texte
	fs.writeFileSync(OUT + '/piege.json', JSON.stringify({ format: 'clarte-models', models: [{ name: '<img src=x onerror="window.pwned=1">Nuit "noire" & co', settings: { clarte_accent: '#475569' } }] }));
	await p.setInputFiles('#clarte_file', OUT + '/piege.json');
	await pause(); await Promise.all([p.waitForNavigation(), p.click('input[name=import]')]);
	await p.goto(acp('models'));
	ok(await row('<img src=x onerror="window.pwned=1">Nuit "noire" & co').count() === 1, 'nom piégé affiché tel quel (texte)');
	await p.goto(acp('settings'));
	ok(!(await p.evaluate(() => window.pwned)), 'nom piégé : aucun code exécuté');
	await p.goto(acp('models'));
	let [dl2] = await Promise.all([p.waitForEvent('download'), row('<img src=x onerror="window.pwned=1">Nuit "noire" & co').locator('a[href*="action=export&"]').click()]);
	ok(JSON.parse(fs.readFileSync(await dl2.path(), 'utf8')).models[0].name === '<img src=x onerror="window.pwned=1">Nuit "noire" & co', 'nom piégé : exporté en texte brut (' + dl2.suggestedFilename() + ')');

	// 11. Fichiers refusés
	fs.writeFileSync(OUT + '/faux.json', 'pas du json');
	await p.setInputFiles('#clarte_file', OUT + '/faux.json');
	await pause(); await Promise.all([p.waitForNavigation(), p.click('input[name=import]')]);
	ok((await msg()).includes('pas un fichier Clarté valide'), 'import d’un faux fichier refusé : ' + await msg());
	await p.goto(acp('backup'));
	await p.setInputFiles('#clarte_file', OUT + '/ete-vert.json');
	p.once('dialog', d => d.accept());
	await pause(); await Promise.all([p.waitForNavigation(), p.click('input[name=restore]')]);
	ok((await msg()).includes('pas une sauvegarde complète'), 'restauration d’un fichier de modèles refusée');

	// 12. Modèle dans la page de réglages (puce « Mes modèles »)
	await p.goto(acp('settings'));
	await p.locator('.clarte-model-chip', { hasText: 'Violet teinté' }).click();
	ok(await p.inputValue('#clarte_accent') === '#7c3aed' && await p.inputValue('#clarte_cat_mode') === 'tinted', 'puce « Mes modèles » : formulaire rempli');
	await p.selectOption('#clarte_index_time', 'none');
	await pause(); await Promise.all([p.waitForNavigation(), p.click('#submit')]);
	ok(cfg('clarte_index_time') === 'none' && cfg('clarte_accent') === '#7c3aed', 'réglages enregistrés (modèle + date masquée)');
	await p.goto(acp('settings'));
	await p.screenshot({ path: OUT + '/acp-reglages.png', fullPage: true });

	// 13. Date sur l'accueil
	const shown = async () => p.evaluate(() => [...document.querySelectorAll('.page-body > p.time')].map(e => (e.classList.contains('rightside') ? 'derniere' : 'jour') + ':' + (getComputedStyle(e).display !== 'none')).join(' '));
	await p.goto(B + 'index.php');
	ok(await shown() === 'derniere:false jour:false', 'accueil, tout masquer : ' + await shown());
	for (const [v, want] of [['now', 'derniere:false jour:true'], ['last', 'derniere:true jour:false'], ['both', 'derniere:true jour:true']]) {
		execSync(`php /home/claude/phpbb/www/bin/phpbbcli.php config:set clarte_index_time ${v}`);
		await p.goto(B + 'index.php');
		ok(await shown() === want, `accueil, ${v} : ${await shown()}`);
	}
	await p.screenshot({ path: OUT + '/forum-accueil.png' });

	// 14. Mode sombre
	await p.evaluate(() => localStorage.setItem('clarte_mode', 'dark'));
	await p.goto(acp('models'));
	await p.screenshot({ path: OUT + '/acp-modeles-sombre.png', fullPage: true });

	ok(errs.length === 0, 'aucune erreur JavaScript ' + errs.join(' | '));
	console.log(fails ? `${fails} échec(s)` : 'TOUT EST BON');
	await b.close();
})().catch(e => { console.error(e); process.exit(1); });
