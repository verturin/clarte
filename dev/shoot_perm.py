import asyncio, sys
S = '/tmp/claude-0/-home-claude/fe2161b7-9548-5d52-bb66-82c7834b2ee8/scratchpad'
sys.path.insert(0, S + '/tests')
from client import Client
from playwright.async_api import async_playwright
adm = Client(); adm.login('admin', 'adminadmin'); sid = adm.admin_login()
async def main():
    async with async_playwright() as p:
        br = await p.chromium.launch()
        for mode in ('light', 'dark'):
            ctx = await br.new_context(viewport={'width': 1280, 'height': 900}, user_agent='pf-test')
            await ctx.add_cookies([{'name': k.name, 'value': k.value, 'domain': '127.0.0.1', 'path': '/'} for k in adm.jar])
            await ctx.add_init_script("try{localStorage.setItem('clarte_mode','%s')}catch(e){}" % mode)
            page = await ctx.new_page()
            await page.goto('http://127.0.0.1:8080/adm/index.php?sid=%s&i=acp_permissions&mode=setting_forum_local&forum_id[]=2&group_id[]=2' % sid)
            await page.wait_for_timeout(500)
            a = await page.query_selector('a:has-text("Advanced Permissions")')
            if a: await a.click(); await page.wait_for_timeout(500)
            await page.screenshot(path='acp/perm_adv_%s.png' % mode, full_page=True)
            # anonymous ACP login screen
            ctx2 = await br.new_context(viewport={'width': 1280, 'height': 800}, user_agent='pf-test')
            await ctx2.add_init_script("try{localStorage.setItem('clarte_mode','%s')}catch(e){}" % mode)
            c = Client(); c.login('admin', 'adminadmin')
            await ctx2.add_cookies([{'name': k.name, 'value': k.value, 'domain': '127.0.0.1', 'path': '/'} for k in c.jar])
            pg = await ctx2.new_page(); await pg.goto('http://127.0.0.1:8080/adm/index.php'); await pg.wait_for_timeout(400)
            await pg.screenshot(path='acp/relogin_%s.png' % mode, full_page=True)
            await ctx.close(); await ctx2.close()
        await br.close()
asyncio.run(main())
