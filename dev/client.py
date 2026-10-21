import http.cookiejar, urllib.request, urllib.parse, urllib.error, json, re, uuid, time
from html.parser import HTMLParser

import os
BASE = os.environ.get('PF_BASE', 'http://127.0.0.1:8080/')


class Forms(HTMLParser):
    def __init__(self):
        super().__init__()
        self.forms = []
        self.cur = None

    def handle_starttag(self, tag, attrs):
        a = dict(attrs)
        if tag == 'form':
            self.cur = {'action': a.get('action', ''), 'id': a.get('id', ''), 'fields': []}
            self.forms.append(self.cur)
        elif self.cur is not None and tag in ('input', 'textarea', 'select', 'button'):
            self.cur['fields'].append((tag, a))

    def handle_endtag(self, tag):
        if tag == 'form':
            self.cur = None


class Client:
    def __init__(self):
        self.jar = http.cookiejar.CookieJar()
        self.op = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(self.jar))
        self.op.addheaders = [('User-Agent', 'pf-test')]
        self.last_url = ''

    def req(self, url, data=None, headers=None, files=None):
        url = url.replace('&amp;', '&')
        if url.startswith('.') and self.last_url:
            url = urllib.parse.urljoin(self.last_url, url)
        elif not url.startswith('http'):
            url = BASE + url.lstrip('/')
        h = dict(headers or {})
        body = None
        if data is not None or files:
            time.sleep(1.05)
        if files:
            boundary = uuid.uuid4().hex
            parts = []
            for k, v in (data or []):
                parts.append(('--%s\r\nContent-Disposition: form-data; name="%s"\r\n\r\n%s\r\n' % (boundary, k, v)).encode())
            for k, (fname, content, ctype) in files.items():
                parts.append(('--%s\r\nContent-Disposition: form-data; name="%s"; filename="%s"\r\nContent-Type: %s\r\n\r\n' % (boundary, k, fname, ctype)).encode() + content + b'\r\n')
            parts.append(('--%s--\r\n' % boundary).encode())
            body = b''.join(parts)
            h['Content-Type'] = 'multipart/form-data; boundary=' + boundary
        elif data is not None:
            body = urllib.parse.urlencode(data).encode()
            h['Content-Type'] = 'application/x-www-form-urlencoded'
        r = urllib.request.Request(url, data=body, headers=h)
        try:
            resp = self.op.open(r)
            code, content = resp.getcode(), resp.read()
            self.last_url = resp.geturl()
        except urllib.error.HTTPError as e:
            code, content = e.code, e.read()
            self.last_url = url
        return code, content

    def get(self, url, **kw):
        code, c = self.req(url, **kw)
        return code, c.decode('utf-8', 'replace')

    def ajax(self, url, data=None, files=None):
        code, c = self.req(url, data=data, files=files, headers={'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'})
        try:
            return code, json.loads(c)
        except Exception:
            return code, c.decode('utf-8', 'replace')[:800]

    @staticmethod
    def forms(html):
        p = Forms()
        p.feed(html)
        return p.forms

    @staticmethod
    def hidden(form):
        return [(a['name'], a.get('value', '')) for tag, a in form['fields'] if tag == 'input' and a.get('type') == 'hidden' and a.get('name')]

    def login(self, username, password='password123'):
        code, html = self.get('ucp.php?mode=login')
        f = [f for f in self.forms(html) if 'login' in f['action']][0]
        code, html = self.get('ucp.php?mode=login', data=self.hidden(f) + [('username', username), ('password', password), ('login', 'Login')])
        assert 'mode=logout' in html, 'login failed for ' + username

    def admin_login(self, username='admin', password='adminadmin'):
        sid = [c.value for c in self.jar if c.name.endswith('_sid')][0]
        code, html = self.get('adm/index.php?sid=' + sid)
        f = [f for f in self.forms(html) if any(a.get('type') == 'password' for t, a in f['fields'])][0]
        data = self.hidden(f)
        for tag, a in f['fields']:
            if a.get('type') == 'text' and a.get('name'):
                data.append((a['name'], username))
            if a.get('type') == 'password':
                data.append((a['name'], password))
        data.append(('login', 'Login'))
        code, html = self.get('adm/' + f['action'], data=data)
        m = re.search(r'sid=([0-9a-f]{32})', self.last_url + html)
        return m.group(1) if m else None

    def post_form(self, html, pick, extra, base='', ajax=False):
        f = [f for f in self.forms(html) if pick(f)][0]
        action = f['action'].replace('&amp;', '&')
        if base and not action.startswith(('http', '/')):
            action = base + action.lstrip('./')
        return self.get(action, data=self.hidden(f) + extra)


def errors_in(html):
    text = re.sub(r'<[^>]+>', '', html)
    return re.findall(r'(Fatal error|\[phpBB Debug\][^\n]*|General Error|SQL ERROR[^\n]*)', text)


def sql(q):
    import subprocess, os, re
    S = '/tmp/claude-0/-home-claude/fe2161b7-9548-5d52-bb66-82c7834b2ee8/scratchpad'
    if os.environ.get('PF_DB') == 'mysql':
        q = q.replace('phpbb_', 'cetc_').replace('sqlite_master', 'information_schema.tables')
        q = re.sub(r"select count\(\*\) from information_schema.tables where name='([a-z_]+)'", r"select count(*) from information_schema.tables where table_schema='phpbb' and table_name='\1'", q)
        q = re.sub(r'(\w+) \|\| "([^"]*)" \|\| (\w+)', r'CONCAT(\1, "\2", \3)', q)
        out = subprocess.run(['mysql', '-uroot', '-N', '-B', 'phpbb', '-e', q], capture_output=True, text=True).stdout.strip()
        return '\n'.join(' | '.join(line.split('\t')) for line in out.split('\n')) if out else ''
    return subprocess.run(['php', S + '/q.php', S + '/db/phpbb.sqlite', q], capture_output=True, text=True).stdout.strip()
