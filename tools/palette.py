"""Clarté — calcul des couleurs claires et sombres à partir d'une couleur de prosilver ou de l'ACP.

- gris et blancs : échelle ardoise, luminosité inversée en mode sombre ;
- bleus : dérivés de la couleur principale (--accent, réglable dans l'ACP) par color-mix() ;
- rouges, verts, oranges… (alertes) : conservés, adoucis en mode sombre.
"""
import colorsys

NAMED = {'white': '#ffffff', 'black': '#000000'}

def hex6(h):
    h = h.lower().lstrip('#')
    if len(h) == 3:
        h = ''.join(c * 2 for c in h)
    return h

def hls(h):
    r, g, b = (int(h[i:i + 2], 16) / 255 for i in (0, 2, 4))
    return colorsys.rgb_to_hls(r, g, b)

def family(h):
    hh, l, s = hls(h)
    deg = hh * 360
    if s < 0.18:
        return 'neutral'
    if 180 <= deg <= 240 and s < 0.36:
        return 'slate'
    if 180 <= deg <= 240:
        return 'accent'
    return 'status'

def pct(x):
    return '%d%%' % max(0, min(100, round(x)))

def light_value(h):
    hh, l, s = hls(h)
    fam = family(h)
    if fam == 'neutral':
        return 'hsl(220 %s %s)' % (pct(0 if (l > 0.985 or l < 0.02) else 12), pct(l * 100))
    if fam == 'slate':
        return 'hsl(222 %s %s)' % (pct(min(s * 100, 24)), pct(l * 100))
    if fam == 'accent':
        if l >= 0.65:
            return 'color-mix(in srgb, var(--accent) %s, #fff)' % pct((1 - l) * 170 + 4)
        if l >= 0.35:
            if l >= 0.5:
                return 'color-mix(in srgb, var(--accent) %s, #fff)' % pct(100 - (l - 0.5) * 140)
            return 'color-mix(in srgb, var(--accent) %s, #000)' % pct(100 - (0.5 - l) * 120)
        return 'color-mix(in srgb, var(--accent) %s, #000)' % pct(l / 0.35 * 55 + 35)
    return '#' + h

def dark_value(h):
    hh, l, s = hls(h)
    fam = family(h)
    if fam in ('neutral', 'slate'):
        if l >= 0.985:
            return 'var(--dk-surface)'
        ld = 0.10 + (1 - l) * 0.80
        return 'hsl(222 %s %s)' % (pct(min(s * 100 + 8, 18)), pct(ld * 100))
    if fam == 'accent':
        if l >= 0.65:
            return 'color-mix(in srgb, var(--accent) %s, var(--dk-surface))' % pct((1 - l) * 110 + 8)
        if l >= 0.35:
            return 'color-mix(in srgb, var(--accent) 82%, #fff)'
        return 'color-mix(in srgb, var(--accent) %s, #fff)' % pct(40 + l * 60)
    if l > 0.8:
        return 'color-mix(in srgb, #%s 20%%, var(--dk-surface))' % h
    if l < 0.4:
        return 'color-mix(in srgb, #%s 55%%, #fff)' % h
    return '#' + h
