#!/bin/sh
# Remet le forum de test à zéro avant test_forums_styles.js (modèles, forums, styles générés)
cd /home/claude/phpbb/www
python3 - <<'PY'
import sqlite3
c=sqlite3.connect('/home/claude/phpbb/data/forum.db')
c.execute("update phpbb_config_text set config_value='{\"next_id\":1,\"items\":[]}' where config_name='clarte_models'")
c.execute("update phpbb_config_text set config_value='{}' where config_name in ('clarte_forum_models','clarte_model_styles')")
clarte=c.execute("select style_id from phpbb_styles where style_path='clarte'").fetchone()[0]
c.execute("delete from phpbb_styles where style_path like 'clarte_m%'")
c.execute("update phpbb_users set user_style=?",(clarte,))
for k,v in [('clarte_accent','#7c3aed'),('clarte_cat_mode','neutral'),('clarte_index_time','both'),('clarte_radius','12'),('clarte_header_from','#0f172a'),('clarte_header_to','#1e3a8a'),('clarte_default_mode','auto'),('clarte_font','inter'),('default_style',str(clarte))]:
    c.execute('update phpbb_config set config_value=? where config_name=?',(v,k))
c.execute('delete from phpbb_login_attempts');c.execute('update phpbb_users set user_login_attempts=0');c.commit()
PY
rm -rf styles/clarte_m*
php bin/phpbbcli.php cache:purge >/dev/null
