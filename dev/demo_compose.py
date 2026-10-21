#!/usr/bin/env python3
"""Assemble les captures de démonstration : python3 demo_compose.py <dossier>
06-mobile.png (clair + sombre côte à côte) et 00-cover.png (accueil moitié clair, moitié sombre)."""
import sys
from PIL import Image, ImageDraw
d = sys.argv[1].rstrip('/') + '/'
bg = (241, 244, 249)
a, b = Image.open(d + '_mobile-light.png'), Image.open(d + '_mobile-dark.png')
pad = 40
m = Image.new('RGB', (a.width + b.width + pad * 3, a.height + pad * 2), bg)
for i, im in enumerate((a, b)):
    mask = Image.new('L', im.size, 0)
    ImageDraw.Draw(mask).rounded_rectangle((0, 0, im.width - 1, im.height - 1), 48, fill=255)
    m.paste(im, (pad + i * (a.width + pad), pad), mask)
m = m.resize((m.width // 2, m.height // 2), Image.LANCZOS)
m.save(d + '06-mobile.png')
light, dark = Image.open(d + '01-index-light.png'), Image.open(d + '02-index-dark.png')
cover = light.copy()
mask = Image.new('L', light.size, 0)
w, h = light.size
ImageDraw.Draw(mask).polygon([(w * 0.58, 0), (w, 0), (w, h), (w * 0.42, h)], fill=255)
cover.paste(dark, (0, 0), mask)
cover.save(d + '00-cover.png')
print('ok', m.size, cover.size)
