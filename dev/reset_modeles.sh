cd /home/claude/phpbb/www
python3 -c "
import sqlite3;c=sqlite3.connect('../data/forum.db')
c.execute(\"update phpbb_config_text set config_value='{\\\"next_id\\\":1,\\\"items\\\":[]}' where config_name='clarte_models'\")
for k,v in [('clarte_accent','#7c3aed'),('clarte_cat_mode','neutral'),('clarte_index_time','both'),('clarte_radius','12'),('clarte_header_from','#0f172a'),('clarte_header_to','#1e3a8a'),('clarte_default_mode','auto'),('clarte_font','inter')]:
    c.execute('update phpbb_config set config_value=? where config_name=?',(v,k))
c.execute('delete from phpbb_login_attempts');c.execute('update phpbb_users set user_login_attempts=0');c.commit()"
php bin/phpbbcli.php cache:purge >/dev/null
