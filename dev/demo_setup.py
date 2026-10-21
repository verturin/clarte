#!/usr/bin/env python3
"""Prépare le forum de démonstration (captures pour phpBB.com) : python3 demo_setup.py en|fr
Part de data/base.db (forum de test vierge, Clarté 1.4.x activé) et le remplace par une arborescence neutre."""
import sqlite3, shutil, sys, time
LANG = sys.argv[1]
DATA = '/home/claude/phpbb/data/'
shutil.copy(DATA + 'base.db', DATA + 'forum.db')
c = sqlite3.connect(DATA + 'forum.db')
T = {
 'en': dict(site='Clarté demo board', desc='A modern, clean style for phpBB', user='Demo',
   forums=[(0,'Community','',0),(1,'Announcements','News about the board and its new look.',1),(1,'General discussion','Talk about anything, in light or dark mode.',1),(3,'Introductions','New here? Say hello!',1),
           (0,'Clubs','',0),(5,'Gardening club','Seeds, flowers and vegetable patches.',1),(5,'Photo corner','Share your best shots.',1)]),
 'fr': dict(site='Forum de démonstration Clarté', desc='Un style moderne et épuré pour phpBB', user='Demo',
   forums=[(0,'Communauté','',0),(1,'Annonces','Les nouvelles du forum et de sa nouvelle présentation.',1),(1,'Discussions générales','Parlez de tout, en mode clair ou sombre.',1),(3,'Présentations','Nouveau ici ? Venez dire bonjour !',1),
           (0,'Clubs','',0),(5,'Club jardinage','Graines, fleurs et potagers.',1),(5,'Coin photo','Partagez vos plus belles photos.',1)]),
}[LANG]

cols = [r[1] for r in c.execute('pragma table_info(phpbb_forums)')]
base = dict(zip(cols, c.execute('select * from phpbb_forums where forum_id=2').fetchone()))
acl = c.execute('select * from phpbb_acl_groups where forum_id=2').fetchall()
gcols = [r[1] for r in c.execute('pragma table_info(phpbb_acl_groups)')]
for t in ('phpbb_forums', 'phpbb_topics', 'phpbb_posts', 'phpbb_topics_posted', 'phpbb_acl_groups WHERE forum_id > 0', 'phpbb_search_wordmatch', 'phpbb_search_wordlist', 'phpbb_forums_track', 'phpbb_topics_track'):
    c.execute('delete from ' + t)

# arbre : ids 1..7, parent donné ; ensembles imbriqués calculés
forums = [dict(id=i + 1, parent=p, name=n, desc=d, type=ty) for i, (p, n, d, ty) in enumerate(T['forums'])]
counter = [0]
def walk(parent):
    for f in [f for f in forums if f['parent'] == parent]:
        counter[0] += 1; f['left'] = counter[0]
        walk(f['id'])
        counter[0] += 1; f['right'] = counter[0]
walk(0)
for f in forums:
    r = dict(base)
    r.update(forum_id=f['id'], parent_id=f['parent'], forum_name=f['name'], forum_desc=f['desc'], forum_type=f['type'], left_id=f['left'], right_id=f['right'],
             forum_parents='', forum_topics_approved=0, forum_posts_approved=0, forum_topics_unapproved=0, forum_topics_softdeleted=0, forum_posts_unapproved=0, forum_posts_softdeleted=0,
             forum_last_post_id=0, forum_last_poster_id=0, forum_last_post_subject='', forum_last_post_time=0, forum_last_poster_name='', forum_last_poster_colour='', forum_style=0)
    c.execute('insert into phpbb_forums (%s) values (%s)' % (','.join(r), ','.join('?' * len(r))), list(r.values()))
    for a in acl:
        d = dict(zip(gcols, a)); d['forum_id'] = f['id']
        c.execute('insert into phpbb_acl_groups (%s) values (%s)' % (','.join(d), ','.join('?' * len(d))), list(d.values()))

for k, v in [('sitename', T['site']), ('site_desc', T['desc']), ('default_lang', LANG), ('board_timezone', 'Europe/Paris'), ('num_posts', '0'), ('num_topics', '0'),
             ('override_user_style', '0'), ('flood_interval', '0'), ('max_login_attempts', '0'), ('ip_login_limit_max', '0')]:
    c.execute('update phpbb_config set config_value=? where config_name=?', (v, k))
c.execute("update phpbb_users set username=?, username_clean=?, user_lang=?, user_timezone='Europe/Paris', user_posts=0, user_lastpost_time=0 where user_id=2", (T['user'], T['user'].lower(), LANG))
c.execute("update phpbb_users set user_lang=? where user_id=1", (LANG,))
c.execute("update phpbb_users set user_lastvisit=? where user_id=2", (int(time.time()) - 3 * 3600,))
c.execute("delete from phpbb_login_attempts")
c.execute("delete from phpbb_sessions")
c.commit()
print('forum de démonstration prêt :', LANG)
