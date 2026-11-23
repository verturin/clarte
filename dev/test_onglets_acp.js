// Test de bout en bout de la 1.8.0 : onglets de la rubrique en cours en haut des pages de l'ACP.
// node test_onglets_acp.js <dossier de sortie>
// Forum local 127.0.0.1:8080 (php -S), compte admin / adminpass123, lancer reset_forums_styles.sh avant.
const { chromium } = require('playwright');
const { execSync } = require('child_process');
const B = 'http://127.0.0.1:8080/';
const WWW = '/home/claude/phpbb/www';
const OUT = process.argv[2];
const cli = c => execSync(`php ${WWW}/bin/phpbbcli.php ${c}`).toString().trim();
let fails = 0;
const ok = (cond, msg) => { console.log((cond ? 'OK   ' : 'ÉCHEC ') + msg); if (!cond) fails++; };
const pause = () => new Promise(r => setTimeout(r, 1200));

(async () => {
	const b = await chromium.launch();
	const ctx = await b.newContext({ viewport: { width: 1280, height: 700 } });
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
	const url = q => `${B}adm/index.php?sid=${sid}&${q}`;
	const state = () => p.evaluate(() => {
		const nav = document.querySelector('.clarte-acp-subnav');
		const block = document.getElementById('activemenu') && document.getElementById('activemenu').closest('.menu-block');
		return {
			nav: !!nav,
			title: nav ? nav.getAttribute('aria-label') : '', shown: nav ? nav.innerText : '',
			tabs: nav ? Array.from(nav.querySelectorAll('li a')).map(a => a.textContent) : [],
			current: nav ? (nav.querySelector('.is-current a') || {}).textContent : '',
			menu: block ? Array.from(block.querySelectorAll('ul > li > a')).map(a => a.textContent.trim()) : [],
			active: document.querySelector('#activemenu a') ? document.querySelector('#activemenu a').textContent.trim() : '',
			first: (document.querySelector('#main > .main') || document.getElementById('main')).firstElementChild.className,
			clear: nav ? nav.getBoundingClientRect().left >= document.getElementById('menu').getBoundingClientRect().right : false,
		};
	});

	// Page d'une extension (Style Clarté)
	await p.goto(url('i=-verturin-clarte-acp-main_module&mode=settings'));
	let s = await state();
	ok(s.nav && s.first === 'clarte-acp-subnav', 'onglets en tête de la page');
	ok(s.clear, 'onglets au-dessus des pages de l’extension, sans recouvrir le menu de gauche');
	ok(s.tabs.length === s.menu.length && s.tabs.length >= 4 && s.tabs.join('|') === s.menu.join('|'), `seulement les pages de la rubrique en cours : ${s.tabs.join(', ')}`);
	ok(s.current === s.active, `page en cours marquée : ${s.current}`);
	ok(s.title.length > 0 && !s.shown.includes(s.title), `nom de la rubrique non affiché (seulement pour les lecteurs d'écran : ${s.title})`);
	await p.screenshot({ path: OUT + '/acp-onglets.png' });

	// Reste visible en défilant
	await p.mouse.wheel(0, 2500); await p.waitForTimeout(300);
	const top = await p.evaluate(() => document.querySelector('.clarte-acp-subnav').getBoundingClientRect().top);
	ok(Math.abs(top) < 2 && await p.evaluate(() => window.scrollY) > 500, `collés en haut après défilement (top = ${Math.round(top)})`);
	await p.screenshot({ path: OUT + '/acp-onglets-defile.png' });

	// Clic sur un onglet : on change de page, l'onglet devient le courant
	const target = s.tabs[1];
	await Promise.all([p.waitForNavigation(), p.click(`.clarte-acp-subnav li:nth-child(2) a`)]);
	s = await state();
	ok(s.current === target && s.active === target, `clic sur « ${target} » : page ouverte et onglet courant`);

	// Pages de phpBB : pas d'onglets (seulement pour les extensions)
	await p.goto(url('i=acp_board&mode=settings'));
	ok(!(await state()).nav, 'page de phpBB (Configuration du forum) : pas d’onglets');
	await p.goto(url('i=acp_extensions&mode=main'));
	ok(!(await state()).nav, 'onglet Extensions, page de phpBB (Gérer les extensions) : pas d’onglets');

	// Fenêtre étroite : les onglets passent à la ligne, tous visibles
	await p.setViewportSize({ width: 820, height: 700 });
	await p.goto(url('i=-verturin-clarte-acp-main_module&mode=settings'));
	const wrap = await p.evaluate(() => {
		const n = document.querySelector('.clarte-acp-subnav'), r = n.getBoundingClientRect();
		const items = Array.from(n.querySelectorAll('li')).map(li => li.getBoundingClientRect());
		return { lines: new Set(items.map(i => Math.round(i.top))).size, inside: items.every(i => i.left >= r.left - 1 && i.right <= r.right + 1), scroll: n.querySelector('ul').scrollWidth <= n.querySelector('ul').clientWidth + 1 };
	});
	ok(wrap.lines >= 2 && wrap.inside && wrap.scroll, `trop d’onglets pour une ligne : ${wrap.lines} lignes, tous visibles, sans défilement`);
	await p.screenshot({ path: OUT + '/acp-onglets-lignes.png' });
	await p.setViewportSize({ width: 1280, height: 700 });

	// Accueil de l'ACP : pas de rubrique à une seule page → rien, ou la bonne rubrique
	await p.goto(url('i=acp_main&mode=main'));
	s = await state();
	ok(!s.nav || s.tabs.join('|') === s.menu.join('|'), 'accueil de l’ACP : pas d’onglets en trop');

	// Mobile
	const m = await ctx.newPage(); await m.setViewportSize({ width: 390, height: 800 });
	await m.goto(url('i=-verturin-clarte-acp-main_module&mode=settings'));
	ok(await m.evaluate(() => { const n = document.querySelector('.clarte-acp-subnav'); return !!n && n.scrollWidth <= document.documentElement.clientWidth + 1 && Array.from(n.querySelectorAll('li')).every(li => li.getBoundingClientRect().right <= n.getBoundingClientRect().right + 1); }), 'mobile : tous les onglets visibles, sans débordement');
	await m.screenshot({ path: OUT + '/acp-onglets-mobile.png' });
	await m.close();

	// Option désactivée, puis habillage désactivé
	cli('config:set clarte_acp_tabs 0'); cli('cache:purge');
	await p.goto(url('i=-verturin-clarte-acp-main_module&mode=settings'));
	ok(!(await state()).nav, 'option « Onglets de la rubrique en cours » sur Non : pas d’onglets');
	ok(await p.isChecked('input[name=clarte_acp_tabs][value="0"]'), 'la page de réglages montre l’option sur Non');
	cli('config:set clarte_acp_tabs 1'); cli('config:set clarte_acp 0'); cli('cache:purge');
	await p.goto(url('i=-verturin-clarte-acp-main_module&mode=settings'));
	ok(!(await state()).nav, 'habillage de l’ACP désactivé : pas d’onglets');
	cli('config:set clarte_acp 1'); cli('cache:purge');

	ok(errs.length === 0, 'aucune erreur JavaScript ' + errs.join(' | '));
	console.log(fails ? `${fails} échec(s)` : 'TOUT EST BON');
	await b.close();
})().catch(e => { console.error(e); process.exit(1); });
