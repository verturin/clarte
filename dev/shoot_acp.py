"""Captures de l'ACP habillé par Clarté (clair / sombre)."""
import asyncio, sys
S = '/tmp/claude-0/-home-claude/fe2161b7-9548-5d52-bb66-82c7834b2ee8/scratchpad'
sys.path.insert(0, S + '/tests')
from client import Client
from playwright.async_api import async_playwright
B = 'http://127.0.0.1:8080/adm/index.php?sid=%s&'
OUT = S + '/clarte/acp/'
import os; os.makedirs(OUT, exist_ok=True)
args = sys.argv[1:]
modes = [a for a in args if a in ('light', 'dark')] or ['light', 'dark']
width = 390 if 'm' in args else 1280
only = [a for a in args if a not in ('light', 'dark', 'm')]
adm = Client(); adm.login('admin', 'adminadmin'); sid = adm.admin_login()
PAGES = [
    ('index', ''),
    ('board', 'i=acp_board&mode=settings'),
    ('forums', 'i=acp_forums&mode=manage'),
    ('forum_edit', 'i=acp_forums&mode=manage&action=edit&f=2'),
    ('users', 'i=acp_users&mode=overview&u=58'),
    ('groups', 'i=acp_groups&mode=manage'),
    ('perms', 'i=acp_permissions&mode=setting_group_global&group_id[]=2&type=u_'),
    ('ext', 'i=acp_extensions&mode=main'),
    ('styles', 'i=acp_styles&mode=style'),
    ('logs', 'i=acp_logs&mode=admin'),
    ('pf_settings', 'i=-verturin-portfolio-acp-main_module&mode=settings'),
    ('pf_albums', 'i=-verturin-portfolio-acp-main_module&mode=albums'),
    ('pf_migrate', 'i=-verturin-portfolio-acp-main_module&mode=migrate'),
    ('pf_watermark', 'i=-verturin-portfolio-acp-main_module&mode=watermark'),
    ('clarte', 'i=-verturin-clarte-acp-main_module&mode=settings'),
]
def jar(c):
    return [{'name': k.name, 'value': k.value, 'domain': '127.0.0.1', 'path': '/'} for k in c.jar]
async def main():
    async with async_playwright() as p:
        br = await p.chromium.launch()
        for mode in modes:
            ctx = await br.new_context(viewport={'width': width, 'height': 900}, user_agent='pf-test', device_scale_factor=1 if width > 500 else 2, locale='fr-FR')
            await ctx.add_cookies(jar(adm))
            await ctx.add_init_script("try{localStorage.setItem('clarte_mode','%s')}catch(e){}" % mode)
            for name, q in PAGES:
                if only and name not in only: continue
                page = await ctx.new_page()
                await page.goto((B % sid) + q); await page.wait_for_timeout(400)
                await page.screenshot(path='%s%s_%s%s.png' % (OUT, name, mode, '_m' if width < 500 else ''), full_page=True)
                await page.close()
            await ctx.close()
        await br.close()
    print('ok')
asyncio.run(main())
