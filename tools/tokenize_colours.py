"""Génère styles/clarte/theme/colours.css et tokens.css à partir de prosilver/theme/colours.css.

Chaque couleur de prosilver devient une variable CSS (--k-xxxxxx). Ses valeurs claire et sombre sont
calculées selon sa famille :
- gris et blancs : échelle ardoise, luminosité inversée en mode sombre ;
- bleus de prosilver : dérivés de la couleur principale (--accent, réglable dans l'ACP) par color-mix() ;
- rouges, verts, oranges… (alertes, messages signalés) : conservés, adoucis en mode sombre.
"""
import re, sys
from palette import NAMED, hex6, pct, light_value, dark_value
from pathlib import Path

SRC = Path(sys.argv[1])            # prosilver/theme/colours.css
OUT = Path(sys.argv[2])            # clarte/theme

def main():
    css = SRC.read_text()
    found = {}

    def repl_hex(m):
        h = hex6(m.group(0))
        found[h] = True
        return 'var(--k-%s)' % h

    def repl_rgba(m):
        parts = [p.strip() for p in m.group(1).split(',')]
        r, g, b = (int(float(x)) for x in parts[:3])
        a = parts[3] if len(parts) > 3 else '1'
        if (r, g, b) in ((0, 0, 0), (255, 255, 255)):
            return m.group(0)
        h = '%02x%02x%02x' % (r, g, b)
        found[h] = True
        return 'color-mix(in srgb, var(--k-%s) %s, transparent)' % (h, pct(float(a) * 100))

    out = re.sub(r'rgba?\(([^)]*)\)', repl_rgba, css)
    out = re.sub(r'#[0-9a-fA-F]{6}\b|#[0-9a-fA-F]{3}\b', repl_hex, out)
    for name, h in NAMED.items():
        out = re.sub(r'(?<=[\s:,])%s(?=\s*[;!,)])' % name, 'var(--k-%s)' % hex6(h), out)
        found[hex6(h)] = True
    # images de prosilver
    out = out.replace('url("./images/', 'url("../../prosilver/theme/images/').replace('url("./en/', 'url("../../prosilver/theme/en/')
    header = '/* Clarté — couleurs de prosilver converties en variables (généré par tools/tokenize_colours.py). */\n'
    (OUT / 'colours.css').write_text(header + out)

    light = '\n'.join('\t--k-%s: %s;' % (h, light_value(h)) for h in sorted(found))
    dark = '\n'.join('\t--k-%s: %s;' % (h, dark_value(h)) for h in sorted(found))
    tokens = """/* Clarté — jetons de couleur (généré par tools/tokenize_colours.py) */
:root {
%s
}
html[data-theme="dark"] {
%s
}
@media (prefers-color-scheme: dark) {
	html:not([data-theme="light"]) {
%s
	}
}
""" % (light, dark, '\n'.join('\t' + l for l in dark.split('\n')))
    (OUT / 'tokens.css').write_text(tokens)
    print(len(found), 'couleurs')

main()
