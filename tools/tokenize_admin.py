"""Génère les couleurs de l'administration habillée par Clarté.

Lit une ou plusieurs feuilles de style de l'ACP (adm/style/admin.css de phpBB, celle de Portfolio…) et
n'en garde que les déclarations de couleur, sur les mêmes sélecteurs. Chaque couleur devient une
variable (--k-xxxxxx) avec sa valeur claire et sa valeur sombre (même calcul que pour le forum).
Chargé après les feuilles d'origine, le fichier produit les recolore sans toucher à la mise en page.

    python3 tools/tokenize_admin.py <sortie> admin.css [portfolio_acp.css ...]
"""
import re, sys
from pathlib import Path
from palette import NAMED, hex6, pct, light_value, dark_value

OUT = Path(sys.argv[1])
SOURCES = [Path(p) for p in sys.argv[2:]]

# Images de fond remplacées par des aplats (dégradés gif de l'ancien thème)
DROP_IMAGES = ('bg_button', 'bg_tabs', 'gradient2b', 'innerbox_bg', 'bg_header', 'bg_list')

COLOUR_RE = re.compile(r'#[0-9a-fA-F]{6}\b|#[0-9a-fA-F]{3}\b|rgba?\([^)]*\)|(?<![\w-])(?:white|black)(?![\w-])', re.I)
SIMPLE = {'color', 'background-color', 'border-color', 'border-top-color', 'border-right-color', 'border-bottom-color',
          'border-left-color', 'outline-color', 'accent-color', 'fill', 'stroke', 'caret-color', 'text-decoration-color',
          'column-rule-color', 'box-shadow', 'text-shadow'}
SHORT_BORDER = {'border': 'border-color', 'border-top': 'border-top-color', 'border-right': 'border-right-color',
                'border-bottom': 'border-bottom-color', 'border-left': 'border-left-color', 'outline': 'outline-color',
                'column-rule': 'column-rule-color'}

found = {}


def token(m):
    raw = m.group(0)
    low = raw.lower()
    if low in NAMED:
        h = hex6(NAMED[low])
        found[h] = True
        return 'var(--k-%s)' % h
    if low.startswith('rgb'):
        parts = [p.strip() for p in raw[raw.index('(') + 1:-1].split(',')]
        r, g, b = (int(float(x)) for x in parts[:3])
        a = parts[3] if len(parts) > 3 else '1'
        h = '%02x%02x%02x' % (r, g, b)
        if h in ('000000', 'ffffff') and a != '1':
            return raw  # voiles noirs / blancs : gardés tels quels
        found[h] = True
        if a == '1':
            return 'var(--k-%s)' % h
        return 'color-mix(in srgb, var(--k-%s) %s, transparent)' % (h, pct(float(a) * 100))
    h = hex6(raw)
    found[h] = True
    return 'var(--k-%s)' % h


def tok(value):
    return COLOUR_RE.sub(token, value)


def colours_in(value):
    return [m.group(0) for m in COLOUR_RE.finditer(value)]


def convert_decl(prop, value):
    """Liste de (propriété, valeur) à garder pour une déclaration d'origine."""
    prop = prop.strip().lower()
    value = value.strip()
    important = ''
    if value.lower().endswith('!important'):
        important = ' !important'
        value = value[:-10].strip()
    out = []
    if prop.startswith('-') or 'gradient(' in value and re.search(r'-(moz|webkit|o|ms)-', value):
        return out
    if prop in SIMPLE:
        if colours_in(value):
            out.append((prop, tok(value)))
    elif prop in SHORT_BORDER:
        cols = colours_in(value)
        if cols:
            out.append((SHORT_BORDER[prop], tok(cols[-1])))
    elif prop in ('background', 'background-image'):
        if 'gradient(' in value:
            if colours_in(value):
                out.append((prop, tok(value)))
        elif 'url(' in value:
            if any(d in value for d in DROP_IMAGES):
                out.append(('background-image', 'none'))
            cols = colours_in(value.split('url(')[0] + value.split(')')[-1])
            if cols:
                out.append(('background-color', tok(cols[0])))
        else:
            cols = colours_in(value)
            if cols:
                out.append(('background-color', tok(cols[0])))
    return [(p, v + important) for p, v in out]


def parse_block(css):
    """Découpe en règles ; renvoie [(entête, corps | sous-règles)]."""
    items, i, n = [], 0, len(css)
    while i < n:
        j = css.find('{', i)
        if j < 0:
            break
        head = css[i:j].strip()
        depth, k = 1, j + 1
        while k < n and depth:
            if css[k] == '{':
                depth += 1
            elif css[k] == '}':
                depth -= 1
            k += 1
        body = css[j + 1:k - 1]
        if head.startswith('@media') or head.startswith('@supports'):
            items.append((head, parse_block(body)))
        elif not head.startswith('@'):
            items.append((head, body))
        i = k
    return items


def render(items, indent=''):
    out = []
    for head, body in items:
        if isinstance(body, list):
            inner = render(body, indent + '\t')
            if inner:
                out.append('%s%s {\n%s\n%s}' % (indent, head, '\n'.join(inner), indent))
            continue
        decls = []
        for d in body.split(';'):
            if ':' not in d:
                continue
            p, v = d.split(':', 1)
            decls += convert_decl(p, v)
        if decls:
            sel = re.sub(r'\s+', ' ', head)
            out.append('%s%s { %s }' % (indent, sel, ' '.join('%s: %s;' % pv for pv in decls)))
    return out


def main():
    parts = []
    for src in SOURCES:
        css = re.sub(r'/\*.*?\*/', '', src.read_text(), flags=re.S)
        rules = render(parse_block(css))
        parts.append('/* --- %s --- */\n%s' % (src.name, '\n'.join(rules)))
    light = '\n'.join('\t--k-%s: %s;' % (h, light_value(h)) for h in sorted(found))
    dark = '\n'.join('\t--k-%s: %s;' % (h, dark_value(h)) for h in sorted(found))
    tokens = ':root {\n%s\n}\nhtml[data-theme="dark"] {\n%s\n}\n@media (prefers-color-scheme: dark) {\n\thtml:not([data-theme="light"]) {\n%s\n\t}\n}\n' % (
        light, dark, '\n'.join('\t' + l for l in dark.split('\n')))
    head = '/* Clarté — couleurs de l\'administration (généré par tools/tokenize_admin.py, ne pas modifier à la main). */\n'
    OUT.write_text(head + tokens + '\n' + '\n\n'.join(parts) + '\n')
    print(len(found), 'couleurs ;', sum(p.count('{') for p in parts), 'règles')


main()
