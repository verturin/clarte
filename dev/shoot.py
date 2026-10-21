"""Captures du style Clarté : pages × modes (clair / sombre) × largeur."""
import asyncio, sys
S = '/tmp/claude-0/-home-claude/fe2161b7-9548-5d52-bb66-82c7834b2ee8/scratchpad'
sys.path.insert(0, S + '/tests')
from client import Client
from playwright.async_api import async_playwright
B = 'http://127.0.0.1:8080/'
OUT = S + '/clarte/img/'
args = sys.argv[1:]
modes = [a for a in args if a in ('light', 'dark')] or ['light', 'dark']
widths = {'d': 1280, 'm': 390}
wsel = [a for a in args if a in widths] or ['d']
only = [a for a in args if a not in ('light', 'dark') and a not in widths]

cam = Client(); cam.login('Camille')
adm = Client(); adm.login('admin', 'adminadmin'); sid = adm.admin_login()
PAGES = [
    ('index', cam, 'index.php'),
    ('forum', cam, 'viewforum.php?f=2'),
    ('topic', cam, 'viewtopic.php?t=4'),
    ('posting', cam, 'posting.php?mode=reply&t=4'),
    ('ucp', cam, 'ucp.php'),
    ('members', cam, 'memberlist.php'),
    ('profile', cam, 'memberlist.php?mode=viewprofile&u=58'),
    ('search', cam, 'search.php'),
    ('pf_home', cam, 'app.php/portfolio'),
    ('pf_user', cam, 'app.php/portfolio/user/58'),
    ('pf_album', cam, 'app.php/portfolio/album/6'),
    ('pf_photo', cam, 'app.php/portfolio/photo/11'),
    ('guest', None, 'index.php'),
    ('login', None, 'ucp.php?mode=login'),
    ('acp', adm, 'adm/index.php?i=-verturin-clarte-acp-main_module&mode=settings&sid=' + sid),
]

def jar(c):
    return [{'name': k.name, 'value': k.value, 'domain': '127.0.0.1', 'path': '/'} for k in c.jar]

async def main():
    async with async_playwright() as p:
        br = await p.chromium.launch()
        for w in wsel:
            for mode in modes:
                ctxs = {}
                for c in (cam, adm, None):
                    ctx = await br.new_context(viewport={'width': widths[w], 'height': 900}, user_agent='pf-test', device_scale_factor=1 if w == 'd' else 2, locale='fr-FR')
                    if c: await ctx.add_cookies(jar(c))
                    await ctx.add_init_script("try{localStorage.setItem('clarte_mode','%s')}catch(e){}" % mode)
                    ctxs[id(c)] = ctx
                for name, c, url in PAGES:
                    if only and name not in only: continue
                    page = await ctxs[id(c)].new_page()
                    await page.goto(B + url); await page.wait_for_timeout(500)
                    await page.screenshot(path='%s%s_%s_%s.png' % (OUT, name, mode, w), full_page=True)
                    await page.close()
                for ctx in ctxs.values(): await ctx.close()
        await br.close()
    print('ok')
asyncio.run(main())
