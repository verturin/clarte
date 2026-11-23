#!/bin/sh
# Remet le forum de test à zéro avant chaque test (modèles, forums, styles générés, taille du texte, images de fond)
cd /home/claude/phpbb/www
python3 - <<'PY'
import sqlite3
c=sqlite3.connect('/home/claude/phpbb/data/forum.db')
c.execute("update phpbb_config_text set config_value='{\"next_id\":1,\"items\":[]}' where config_name='clarte_models'")
c.execute("update phpbb_config_text set config_value='{}' where config_name in ('clarte_forum_models','clarte_model_styles')")
clarte=c.execute("select style_id from phpbb_styles where style_path='clarte'").fetchone()[0]
c.execute("delete from phpbb_styles where style_path like 'clarte_m%'")
c.execute("update phpbb_users set user_style=?",(clarte,))
for col,val in (('user_clarte_text',"''"),('user_clarte_bg','0')):
    try: c.execute("update phpbb_users set %s=%s" % (col,val))
    except Exception: pass
for k,v in [('clarte_accent','#7c3aed'),('clarte_cat_mode','neutral'),('clarte_index_time','both'),('clarte_radius','12'),('clarte_header_from','#0f172a'),('clarte_header_to','#1e3a8a'),('clarte_default_mode','auto'),('clarte_font','inter'),('clarte_text_size','100'),('clarte_text_allow','0'),('clarte_bg_image',''),('clarte_bg_image_dark',''),('clarte_bg_mode','cover'),('clarte_bg_position','center'),('clarte_bg_fixed','1'),('clarte_bg_overlay','30'),('clarte_bg_blur','0'),('clarte_bg_glass','0'),('clarte_bg_mobile','1'),('clarte_header_image',''),('clarte_header_image_dark',''),('clarte_header_position','center'),('clarte_header_overlay','40'),('clarte_bg_allow','0'),('clarte_favicon',''),('clarte_favicon_dark',''),('clarte_touch_icon',''),('clarte_acp','1'),('clarte_acp_tabs','1'),('default_style',str(clarte))]:
    c.execute('update phpbb_config set config_value=? where config_name=?',(v,k))
c.execute('delete from phpbb_login_attempts');c.execute('update phpbb_users set user_login_attempts=0');c.commit()
PY
rm -rf styles/clarte_m* images/clarte
php bin/phpbbcli.php cache:purge >/dev/null
