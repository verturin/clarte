"""Tests de l'extension Clarté : enregistrement, validation, sortie dans le forum, remise à zéro."""
import re, sys
from client import Client, errors_in, sql
adm = Client(); adm.login('admin', 'adminadmin'); sid = adm.admin_login()
U = 'adm/index.php?i=-verturin-clarte-acp-main_module&mode=settings&sid=' + sid
ok = 0
def check(cond, msg):
    global ok
    if not cond: print('ÉCHEC', msg); sys.exit(1)
    ok += 1

code, html = adm.get(U)
check(code == 200 and not errors_in(html), 'page ACP')
check('clarte_accent' in html and 'Violet' in html, 'formulaire + palettes')
pick = lambda f: 'clarte' in f['action'] or f.get('id') == 'clarte_settings'
base = 'adm/'
vals = [('clarte_accent', '#7C3AED'), ('clarte_header_from', '#1e1035'), ('clarte_header_to', '#5b21b6'),
        ('clarte_default_mode', 'dark'), ('clarte_allow_toggle', '0'), ('clarte_acp', '0'), ('clarte_font', 'system'), ('clarte_radius', '6'), ('submit', '1')]
code, r = adm.post_form(html, pick, vals, base=base)
check(not errors_in(r) and ('enregistr' in r or 'saved' in r.lower()), 'enregistrement')
cfg = dict(l.split(' | ') for l in sql("select config_name, config_value from phpbb_config where config_name like 'clarte_%'").splitlines())
check(cfg['clarte_acp'] == '0', 'habillage ACP désactivable')
code, page = adm.get('adm/index.php?sid=' + sid)
check('clarte_admin.css' not in page, 'ACP sans habillage quand désactivé')
check(cfg['clarte_accent'] == '#7c3aed' and cfg['clarte_default_mode'] == 'dark' and cfg['clarte_allow_toggle'] == '0' and cfg['clarte_radius'] == '6', 'valeurs en base %r' % cfg)

c = Client(); c.login('Camille')
code, page = c.get('index.php')
check('--accent:#7c3aed;--header-from:#1e1035;--header-to:#5b21b6' in page, 'variables dans la page')
check('--font:system-ui' in page and '--radius:6px;--radius-sm:4px' in page, 'police + arrondi')
check("def = 'dark'" in page and 'allow = false' in page, 'mode par défaut + choix désactivé')
check('clarte-mode-toggle" data' not in page, 'bouton caché quand le choix est désactivé')

# valeur invalide : refusée, rien n'est modifié
code, html = adm.get(U)
code, r = adm.post_form(html, pick, [('clarte_accent', 'red;}body{display:none'), ('clarte_header_from', '#000000'), ('clarte_header_to', '#000000'), ('clarte_default_mode', 'x'), ('clarte_allow_toggle', '1'), ('submit', '1')], base=base)
check('#rrggbb' in r, 'erreur de couleur affichée')
check(sql("select config_value from phpbb_config where config_name='clarte_accent'").strip() == '#7c3aed', 'rien modifié après erreur')

# autre style : rien n'est injecté
sql("update phpbb_users set user_style=1 where username='Camille'")
code, page = c.get('index.php')
check('clarte-vars' not in page and 'clarte_mode' not in page, 'aucune injection sous prosilver')
sql("update phpbb_users set user_style=2 where username='Camille'")

# remise à zéro
code, html = adm.get(U)
code, r = adm.post_form(html, pick, [('reset', '1')], base=base)
cfg = dict(l.split(' | ') for l in sql("select config_name, config_value from phpbb_config where config_name like 'clarte_%'").splitlines())
check(cfg['clarte_accent'] == '#2563eb' and cfg['clarte_default_mode'] == 'auto' and cfg['clarte_allow_toggle'] == '1', 'remise à zéro')
code, page = c.get('index.php')
check('clarte-mode-toggle" data' in page and "def = 'auto'" in page, 'bouton revenu')
code, page = adm.get('adm/index.php?sid=' + sid)
m = re.search(r'href="([^"]*clarte_admin\.css[^"]*)"', page)
check(m and 'clarte_admin_colours.css' in page and '--accent:#2563eb' in page, 'ACP habillé (feuilles + couleur)')
check('clarte-acp-mode' in page, 'bouton Apparence dans l\'ACP')
code, css = adm.get('adm/' + m.group(1).replace('&amp;', '&'))
check(code == 200 and '#page-header' in css, 'feuille de l\'ACP accessible (%s)' % m.group(1))
code, page = adm.get('adm/index.php?i=-verturin-portfolio-acp-main_module&mode=settings&sid=' + sid)
check('clarte_admin.css' in page and 'portfolio_acp.css' in page and page.index('portfolio_acp.css') < page.index('clarte_admin_colours.css'), 'pages Portfolio : habillage chargé après le leur')
code, log = adm.get('adm/index.php?i=acp_logs&mode=admin&sid=' + sid)
check('Clarté' in log, 'journal admin')
print('clarte OK', ok, 'vérifications')
