// Captures de démonstration pour phpBB.com : node demo_shots.js en|fr <dossier de sortie>
// À lancer après « python3 demo_setup.py en|fr » (forum local 127.0.0.1:8080, compte Demo / adminpass123).
const { chromium } = require('playwright');
const { execSync } = require('child_process');
const fs = require('fs');
const LANG = process.argv[2];
const OUT = process.argv[3];
const B = 'http://127.0.0.1:8080/';
const WWW = '/home/claude/phpbb/www';
const DB = '/home/claude/phpbb/data/forum.db';
const MOD = 'i=-verturin-clarte-acp-main_module&mode=';
const pause = (ms = 1200) => new Promise(r => setTimeout(r, ms));
const py = code => execSync('python3 -', { input: `import sqlite3,json\nc=sqlite3.connect('${DB}')\n${code}\nc.commit()\n` }).toString().trim();
const purge = () => execSync(`php ${WWW}/bin/phpbbcli.php cache:purge`);

const TXT = {
	en: {
		users: ['Camille', 'Julien'],
		topics: [
			[2, 'Welcome to the new look of the board', `Hello everyone,

The board now uses [b]Clarté[/b], a modern style for phpBB:
[list]
[*]light, dark or automatic mode, with an [i]Appearance[/i] button in the navigation bar;
[*]a main colour set in the ACP, used everywhere on the board;
[*]the whole administration panel gets the same look.
[/list]
Enjoy, and tell us what you think!`, []],
			[3, 'Light or dark mode: which one do you prefer?', `I switched to dark mode in the evening and light mode during the day. The [i]Automatic[/i] setting does it for me. What about you?`,
				[[1, `[quote=Demo]The [i]Automatic[/i] setting does it for me.[/quote]
Same here, automatic all the way. The dark mode is really easy on the eyes.`], [0, `Dark mode, always! :)`]]],
			[3, 'Your favourite autumn recipes', `Pumpkin soup, apple pie, mushroom risotto… Share your favourite autumn recipes here.`, [[0, `Roasted chestnuts with a little salt, nothing beats them.`]]],
			[4, 'Hello everyone!', `Hi, I just joined the board. I love photography and gardening. Nice to meet you all!`, [[1, `Welcome aboard!`]]],
			[6, 'When should I plant my tomatoes?', `I live in a rather cold area. When is the best time to plant tomatoes outside?`, [[0, `Wait until the last frost is over, usually mid-May.`]]],
			[7, 'Sunset over the lake', `Taken last week with a simple compact camera. The colours were amazing that evening.`, []],
		],
		models: [['Pink', '#db2777', '#500724', '#9d174d', 'tinted'], ['Forest', '#059669', '#04201a', '#065f46', 'tinted'], ['Ocean', '#0284c7', '#082f49', '#075985', 'header']],
	},
	fr: {
		users: ['Camille', 'Julien'],
		topics: [
			[2, 'Bienvenue sur la nouvelle présentation du forum', `Bonjour à tous,

Le forum utilise maintenant [b]Clarté[/b], un style moderne pour phpBB :
[list]
[*]mode clair, sombre ou automatique, avec un bouton [i]Apparence[/i] dans la barre de navigation ;
[*]une couleur principale réglée dans l’administration, reprise partout sur le forum ;
[*]tout le panneau d’administration prend la même présentation.
[/list]
Bonne découverte, et dites-nous ce que vous en pensez !`, []],
			[3, 'Mode clair ou sombre : vous préférez lequel ?', `Je passe en mode sombre le soir et en mode clair la journée. Le réglage [i]Automatique[/i] le fait pour moi. Et vous ?`,
				[[1, `[quote=Demo]Le réglage [i]Automatique[/i] le fait pour moi.[/quote]
Pareil, automatique sans hésiter. Le mode sombre est vraiment reposant.`], [0, `Mode sombre, toujours ! :)`]]],
			[3, 'Vos recettes d’automne préférées', `Soupe de potiron, tarte aux pommes, risotto aux champignons… Partagez ici vos recettes d’automne préférées.`, [[0, `Des châtaignes grillées avec un peu de sel, rien ne vaut ça.`]]],
			[4, 'Bonjour à tous !', `Bonjour, je viens de m’inscrire. J’aime la photo et le jardinage. Ravi de vous rencontrer !`, [[1, `Bienvenue parmi nous !`]]],
			[6, 'Quand planter mes tomates ?', `J’habite une région plutôt froide. Quel est le meilleur moment pour planter les tomates dehors ?`, [[0, `Attendez la fin des gelées, en général à la mi-mai.`]]],
			[7, 'Coucher de soleil sur le lac', `Prise la semaine dernière avec un simple compact. Les couleurs étaient magnifiques ce soir-là.`, []],
		],
		models: [['Rose', '#db2777', '#500724', '#9d174d', 'tinted'], ['Forêt', '#059669', '#04201a', '#065f46', 'tinted'], ['Océan', '#0284c7', '#082f49', '#075985', 'header']],
	},
}[LANG];

(async () => {
	fs.mkdirSync(OUT, { recursive: true });
	const b = await chromium.launch();
	const ctx = await b.newContext({ viewport: { width: 1280, height: 800 }, locale: LANG === 'fr' ? 'fr-FR' : 'en-GB', colorScheme: 'light' });
	const p = await ctx.newPage();
	const shot = async (name, opts = {}) => { await p.mouse.move(1, 1); await pause(300); await p.screenshot({ path: `${OUT}/${name}.png`, ...opts }); console.log('  ' + name); };

	// Deux membres de plus (copies du compte Demo, groupe des membres inscrits)
	py(`
cols=[r[1] for r in c.execute('pragma table_info(phpbb_users)')]
demo=dict(zip(cols,c.execute('select * from phpbb_users where user_id=2').fetchone()))
reg=c.execute("select group_id from phpbb_groups where group_name='REGISTERED'").fetchone()[0]
for i,name in enumerate(${JSON.stringify(TXT.users)}):
    u=dict(demo); u.update(user_id=3+i,username=name,username_clean=name.lower(),user_email=name.lower()+'@example.com',user_type=0,group_id=reg,user_colour='',user_permissions='',user_posts=0,user_rank=0)
    c.execute('delete from phpbb_users where user_id=?',(3+i,)); c.execute('delete from phpbb_user_group where user_id=?',(3+i,))
    c.execute('insert into phpbb_users (%s) values (%s)'%(','.join(u),','.join('?'*len(u))),list(u.values()))
    c.execute('insert into phpbb_user_group (group_id,user_id,user_pending) values (?,?,0)',(reg,3+i))
`);
	purge();

	// Connexion
	await p.goto(B + 'ucp.php?mode=login');
	await p.fill('input[name=username]', 'Demo');
	await p.fill('input[name=password]', 'adminpass123');
	await pause(); await Promise.all([p.waitForNavigation(), p.click('input[name=login]')]);

	// Sujets et réponses (tous publiés par Demo, puis attribués à Camille / Julien)
	const reassign = [];
	for (const [forum, subject, text, replies] of TXT.topics) {
		await p.goto(`${B}posting.php?mode=post&f=${forum}`);
		await p.fill('#subject', subject);
		await p.fill('#message', text);
		await pause(); await Promise.all([p.waitForNavigation(), p.click('input[name=post]')]);
		const topic = Number(py(`print(c.execute('select max(topic_id) from phpbb_topics').fetchone()[0])`));
		for (const [who, reply] of replies) {
			await p.goto(`${B}posting.php?mode=reply&t=${topic}`);
			await p.fill('#message', reply);
			await pause(); await Promise.all([p.waitForNavigation(), p.click('input[name=post]')]);
			reassign.push([Number(py(`print(c.execute('select max(post_id) from phpbb_posts').fetchone()[0])`)), 3 + who]);
		}
	}
	py(`
for post_id,user_id in ${JSON.stringify(reassign)}:
    name=c.execute('select username from phpbb_users where user_id=?',(user_id,)).fetchone()[0]
    c.execute('update phpbb_posts set poster_id=?, post_username=? where post_id=?',(user_id,'',post_id))
    c.execute("update phpbb_topics set topic_last_poster_id=?, topic_last_poster_name=?, topic_last_poster_colour='' where topic_last_post_id=?",(user_id,name,post_id))
    c.execute("update phpbb_forums set forum_last_poster_id=?, forum_last_poster_name=?, forum_last_poster_colour='' where forum_last_post_id=?",(user_id,name,post_id))
for uid in (2,3,4):
    c.execute('update phpbb_users set user_posts=(select count(*) from phpbb_posts where poster_id=?) where user_id=?',(uid,uid))
# trois de ces messages paraissent plus anciens, pour une liste vivante
c.execute("update phpbb_users set user_lastvisit=strftime('%s','now')-86400 where user_id=2")
`);

	// Modèles, couleurs par forum (jardinage : modèle 1, photo : modèle 2), réglages généraux d'origine
	const now = Math.floor(Date.now() / 1000);
	const models = { next_id: 4, items: TXT.models.map(([name, a, f, t, cat], i) => ({ id: i + 1, name, created: now, updated: now,
		settings: { clarte_accent: a, clarte_header_from: f, clarte_header_to: t, clarte_cat_mode: cat, clarte_cat_colour: t, clarte_default_mode: 'auto', clarte_font: 'inter', clarte_radius: 12 } })) };
	py(`
c.execute("update phpbb_config_text set config_value=? where config_name='clarte_models'",(${JSON.stringify(JSON.stringify(models))},))
c.execute("update phpbb_config_text set config_value='{\\"6\\":1,\\"7\\":2}' where config_name='clarte_forum_models'")
for k,v in [('clarte_accent','#2563eb'),('clarte_header_from','#0f172a'),('clarte_header_to','#1e3a8a'),('clarte_cat_mode','neutral'),('clarte_index_time','both'),('clarte_radius','12'),('clarte_default_mode','auto'),('clarte_font','inter')]:
    c.execute('update phpbb_config set config_value=? where config_name=?',(v,k))
`);
	purge();

	const mode = async m => { await p.goto(B + 'index.php'); await p.evaluate(v => localStorage.setItem('clarte_mode', v), m); };

	// ---- Forum ----
	console.log(LANG);
	await mode('light');
	await p.goto(B + 'index.php'); await shot('01-index-light');
	await mode('dark');
	await p.goto(B + 'index.php'); await shot('02-index-dark');
	await mode('light');
	const t2 = Number(py(`print(c.execute("select topic_id from phpbb_topics where topic_posts_approved>2 and forum_id=3 order by topic_id limit 1").fetchone()[0])`));
	await p.goto(`${B}viewtopic.php?t=${t2}`); await shot('03-topic-light');
	await mode('dark');
	await p.goto(`${B}viewtopic.php?t=${t2}`); await shot('04-topic-dark');
	await mode('light');
	await p.goto(B + 'viewforum.php?f=6'); await shot('05-forum-colours');

	// ---- Panneau du membre : style au choix ----
	// ---- Administration ----
	const sid0 = (await ctx.cookies()).find(c => c.name.endsWith('_sid')).value;
	await p.goto(B + 'adm/index.php?sid=' + sid0);
	await p.fill('input[type=password]', 'adminpass123');
	await pause(); await Promise.all([p.waitForNavigation(), p.click('input[name=login]')]);
	const sid = new URL(p.url()).searchParams.get('sid');
	const acp = md => `${B}adm/index.php?sid=${sid}&${MOD}${md}`;

	// le modèle 1 devient un style au choix
	await p.goto(acp('models'));
	await Promise.all([p.waitForNavigation(), p.locator('a[href*="action=style_create&id=1&"]').click()]);

	await p.goto(acp('settings'));
	await p.selectOption('#clarte_cat_mode', 'tinted'); await p.dispatchEvent('#clarte_cat_mode', 'change');
	await shot('07-acp-settings', { fullPage: true });
	await p.goto(acp('models')); await shot('08-acp-models', { fullPage: true });
	await p.goto(acp('forums')); await shot('09-acp-forum-colours', { fullPage: true });
	await p.goto(acp('backup')); await shot('10-acp-backup', { fullPage: true });
	await mode('dark');
	await p.goto(`${B}adm/index.php?sid=${sid}&i=acp_board&mode=settings`); await shot('11-acp-dark');
	await p.goto(acp('models')); await shot('11b-acp-models-dark', { fullPage: true });
	await mode('light');

	// style au choix dans le panneau du membre, puis forum vu avec ce style
	const styleId = Number(py(`print(c.execute("select style_id from phpbb_styles where style_path='clarte_m1'").fetchone()[0])`));
	await p.goto(B + 'ucp.php?i=ucp_prefs&mode=personal');
	await p.selectOption('select[name=user_style]', String(styleId));
	await p.locator('select[name=user_style]').scrollIntoViewIfNeeded();
	await p.evaluate(() => { const d = document.querySelector('select[name=user_style]').closest('dl'); d.style.outline = '3px solid #db2777'; d.style.outlineOffset = '4px'; d.style.borderRadius = '8px'; });
	await shot('12-ucp-style-choice');
	await pause(); await Promise.all([p.waitForNavigation(), p.click('input[name=submit]')]);
	await p.goto(B + 'index.php'); await shot('13-member-style');
	py(`c.execute("update phpbb_users set user_style=(select style_id from phpbb_styles where style_path='clarte') where user_id=2")`);
	purge();

	// Mobile, clair et sombre côte à côte
	const mob = await b.newContext({ viewport: { width: 390, height: 780 }, deviceScaleFactor: 2, isMobile: true, hasTouch: true, locale: LANG === 'fr' ? 'fr-FR' : 'en-GB' });
	// connexion propre au mobile (phpBB vérifie le navigateur de la session)
	const m = await mob.newPage();
	await m.goto(B + 'ucp.php?mode=login');
	await m.fill('input[name=username]', 'Demo');
	await m.fill('input[name=password]', 'adminpass123');
	await pause(); await Promise.all([m.waitForNavigation(), m.click('input[name=login]')]);
	for (const v of ['light', 'dark']) {
		await m.goto(B + 'index.php'); await m.evaluate(x => localStorage.setItem('clarte_mode', x), v);
		await m.goto(B + 'index.php'); await m.mouse.move(1, 1); await pause(300);
		await m.screenshot({ path: `${OUT}/_mobile-${v}.png` });
	}
	await mob.close();

	await b.close();
})().catch(e => { console.error(e); process.exit(1); });
