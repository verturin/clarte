<?php
/**
 * Clarté — réglages du style
 *
 * @copyright (c) 2026 verturin
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace verturin\clarte\controller;

use verturin\clarte\core\forum_models;
use verturin\clarte\core\images;
use verturin\clarte\core\model_styles;
use verturin\clarte\core\models;
use verturin\clarte\event\listener;

class acp_controller
{
	/** Valeurs d'origine */
	const DEFAULTS = models::DEFAULTS;

	/** Palettes proposées : couleur principale, haut et bas de l'en-tête */
	const PRESETS = [
		'blue'		=> ['#2563eb', '#0f172a', '#1e3a8a'],
		'indigo'	=> ['#4f46e5', '#1e1b4b', '#3730a3'],
		'violet'	=> ['#7c3aed', '#1e1035', '#5b21b6'],
		'rose'		=> ['#e11d48', '#2a0a14', '#9f1239'],
		'orange'	=> ['#ea580c', '#1c1008', '#9a3412'],
		'green'		=> ['#059669', '#04201a', '#065f46'],
		'teal'		=> ['#0d9488', '#042f2e', '#115e59'],
		'graphite'	=> ['#475569', '#0b0f17', '#334155'],
	];

	/** Taille maximale d'un fichier importé (octets) : les sauvegardes contiennent les images de fond */
	const MAX_UPLOAD = 20971520;

	/** Formats des fichiers JSON produits par l'extension */
	const FORMAT_MODELS = 'clarte-models';
	const FORMAT_BACKUP = 'clarte-backup';

	protected $config;
	protected $language;
	protected $log;
	protected $request;
	protected $template;
	protected $user;
	protected $models;
	protected $forum_models;
	protected $model_styles;
	protected $images;
	protected $u_action;

	public function __construct(\phpbb\config\config $config, \phpbb\language\language $language, \phpbb\log\log_interface $log,
		\phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, models $models,
		forum_models $forum_models, model_styles $model_styles, images $images)
	{
		$this->images = $images;
		$this->forum_models = $forum_models;
		$this->model_styles = $model_styles;
		$this->config = $config;
		$this->language = $language;
		$this->log = $log;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->models = $models;
	}

	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}

	/* ====================================================================================
	   Page « Couleurs et apparence »
	   ==================================================================================== */

	public function settings()
	{
		if ($this->request->variable('action', '') === 'image_delete')
		{
			$file = images::valid_name($this->request->variable('file', ''));
			if (!check_link_hash($this->request->variable('hash', ''), 'clarte_images'))
			{
				trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}
			if ($file === '' || isset($this->images_in_use()[$file]))
			{
				trigger_error($this->language->lang('ACP_CLARTE_IMAGE_IN_USE') . adm_back_link($this->u_action), E_USER_WARNING);
			}
			$this->images->delete($file);
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_CLARTE_IMAGE_DELETED', false, [$file]);
			redirect($this->u_action);
		}

		add_form_key('verturin_clarte');
		$errors = [];
		$values = $this->current();

		if ($this->request->is_set_post('reset') || $this->request->is_set_post('submit') || $this->request->is_set_post('save_model'))
		{
			if (!check_form_key('verturin_clarte'))
			{
				trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			if ($this->request->is_set_post('reset'))
			{
				$this->apply(self::DEFAULTS);
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_CLARTE_SETTINGS');
				trigger_error($this->language->lang('ACP_CLARTE_RESET_DONE') . adm_back_link($this->u_action));
			}

			$values = $this->read_form(array_keys(self::DEFAULTS), $errors);

			if (!$errors && $this->request->is_set_post('save_model'))
			{
				$name = models::clean_name($this->request->variable('clarte_model_name', '', true));
				if ($name === '')
				{
					$errors[] = $this->language->lang('ACP_CLARTE_MODEL_NAME_EMPTY');
				}
				else if (!$this->models->add($name, $values))
				{
					$errors[] = $this->language->lang('ACP_CLARTE_MODEL_LIMIT', models::MAX);
				}
				else
				{
					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_CLARTE_MODEL_SAVED', false, [$name]);
					trigger_error($this->language->lang('ACP_CLARTE_MODEL_SAVED', $name) . $this->links_back(true));
				}
			}
			else if (!$errors)
			{
				$this->apply($values);
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_CLARTE_SETTINGS');
				trigger_error($this->language->lang('ACP_CLARTE_SAVED') . adm_back_link($this->u_action));
			}
		}

		$this->assign_form($values, $errors);
		$this->template->assign_vars([
			'S_CLARTE_INSTALLED'	=> $this->style_state() !== 'missing',
			'S_CLARTE_DEFAULT'		=> $this->style_state() === 'default',
			'U_ACP_STYLES'			=> append_sid($this->admin_path() . 'index.php', 'i=acp_styles&amp;mode=style', true, $this->user->session_id),
			'U_CLARTE_MODELS'		=> $this->mode_url('models'),
		]);

		// Bibliothèque d'images : les images qui ne servent nulle part peuvent être supprimées
		$used = $this->images_in_use();
		$hash = generate_link_hash('clarte_images');
		foreach ($this->images->all() as $name => $info)
		{
			$this->template->assign_block_vars('library', [
				'NAME'		=> $name,
				'URL'		=> $this->image_url($name),
				'SIZE'		=> get_formatted_filesize($info['size']),
				'DIMS'		=> $info['width'] . ' × ' . $info['height'],
				'USES'		=> isset($used[$name]) ? implode(', ', $used[$name]) : '',
				'U_DELETE'	=> isset($used[$name]) ? '' : $this->u_action . '&amp;action=image_delete&amp;file=' . $name . '&amp;hash=' . $hash,
			]);
		}
	}

	/** Images utilisées : nom => [où] (réglages du forum, modèles) */
	protected function images_in_use()
	{
		$used = [];
		$current = $this->current();
		foreach (models::IMAGE_KEYS as $key)
		{
			if ($current[$key] !== '')
			{
				$used[$current[$key]][] = $this->language->lang('ACP_CLARTE_IMAGE_USED_BOARD');
			}
		}
		foreach ($this->models->all() as $item)
		{
			foreach (models::IMAGE_KEYS as $key)
			{
				if (!empty($item['settings'][$key]))
				{
					$used[$item['settings'][$key]][] = $item['name'];
				}
			}
		}

		return array_map('array_unique', $used);
	}

	/** Images citées par des réglages */
	protected static function image_names(array $settings)
	{
		return array_values(array_filter(array_intersect_key($settings, array_flip(models::IMAGE_KEYS))));
	}

	/** Adresse d'une image de la bibliothèque, depuis l'ACP */
	protected function image_url($name)
	{
		global $phpbb_root_path;

		return $phpbb_root_path . images::DIR . $name;
	}

	/* ====================================================================================
	   Page « Modèles enregistrés »
	   ==================================================================================== */

	/** @return string nom du gabarit à afficher */
	public function models_page()
	{
		$action = $this->request->variable('action', '');
		$id = $this->request->variable('id', 0);
		$model = $id ? $this->models->get($id) : null;

		if (in_array($action, ['edit', 'apply', 'duplicate', 'delete', 'export', 'style_create', 'style_delete'], true) && !$model)
		{
			trigger_error($this->language->lang('ACP_CLARTE_MODEL_NOT_FOUND') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		switch ($action)
		{
			case 'edit':
				return $this->model_edit($model);

			case 'apply':
				if (confirm_box(true))
				{
					$this->apply($model['settings']);
					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_CLARTE_MODEL_APPLIED', false, [$model['name']]);
					trigger_error($this->language->lang('ACP_CLARTE_MODEL_APPLIED', $model['name']) . adm_back_link($this->u_action));
				}
				confirm_box(false, $this->language->lang('ACP_CLARTE_MODEL_APPLY_CONFIRM', $model['name']), build_hidden_fields(['action' => 'apply', 'id' => $model['id']]));
				redirect($this->u_action);
			break;

			case 'delete':
				if (confirm_box(true))
				{
					$this->model_styles->delete($model['id']);
					$this->forum_models->remove_model($model['id']);
					$this->models->delete($model['id']);
					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_CLARTE_MODEL_DELETED', false, [$model['name']]);
					trigger_error($this->language->lang('ACP_CLARTE_MODEL_DELETED', $model['name']) . adm_back_link($this->u_action));
				}
				confirm_box(false, $this->language->lang('ACP_CLARTE_MODEL_DELETE_CONFIRM', $model['name'])
					. $this->usage_note($model['id']), build_hidden_fields(['action' => 'delete', 'id' => $model['id']]));
				redirect($this->u_action);
			break;

			case 'duplicate':
				if (!check_link_hash($this->request->variable('hash', ''), 'clarte_models'))
				{
					trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
				}
				if (!$this->models->add($this->language->lang('ACP_CLARTE_MODEL_COPY_OF', $model['name']), $model['settings']))
				{
					trigger_error($this->language->lang('ACP_CLARTE_MODEL_LIMIT', models::MAX) . adm_back_link($this->u_action), E_USER_WARNING);
				}
				redirect($this->u_action);
			break;

			case 'style_create':
				if (!check_link_hash($this->request->variable('hash', ''), 'clarte_models'))
				{
					trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
				}
				$error = $this->model_styles->create($model, $this->language->lang('ACP_CLARTE_STYLE_PREFIX'));
				if ($error)
				{
					trigger_error($this->language->lang($error) . adm_back_link($this->u_action), E_USER_WARNING);
				}
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_CLARTE_STYLE_CREATED', false, [$model['name']]);
				trigger_error($this->language->lang('ACP_CLARTE_STYLE_CREATED', $this->language->lang('ACP_CLARTE_STYLE_PREFIX') . $model['name']) . adm_back_link($this->u_action));
			break;

			case 'style_delete':
				if (confirm_box(true))
				{
					$moved = $this->model_styles->delete($model['id']);
					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_CLARTE_STYLE_DELETED', false, [$model['name']]);
					trigger_error($this->language->lang('ACP_CLARTE_STYLE_DELETED', $moved) . adm_back_link($this->u_action));
				}
				confirm_box(false, $this->language->lang('ACP_CLARTE_STYLE_DELETE_CONFIRM', $model['name']), build_hidden_fields(['action' => 'style_delete', 'id' => $model['id']]));
				redirect($this->u_action);
			break;

			case 'export':
				$this->send_json([
					'format'	=> self::FORMAT_MODELS,
					'generator'	=> $this->generator(),
					'models'	=> models::export_list([$model]),
					'images'	=> $this->images->encode(self::image_names($model['settings'])),
				], 'clarte-modele-' . $this->slug($model['name']) . '.json');
			break;

			case 'export_all':
				$all = $this->models->all();
				$this->send_json([
					'format'	=> self::FORMAT_MODELS,
					'generator'	=> $this->generator(),
					'models'	=> models::export_list($all),
					'images'	=> $this->images->encode($this->models_image_names($all)),
				], 'clarte-modeles-' . gmdate('Y-m-d') . '.json');
			break;
		}

		add_form_key('verturin_clarte_models');
		$errors = [];

		// Nouveau modèle à partir des réglages en service
		if ($this->request->is_set_post('save_current'))
		{
			$this->check_key('verturin_clarte_models');
			$name = models::clean_name($this->request->variable('clarte_model_name', '', true));
			if ($name === '')
			{
				$errors[] = $this->language->lang('ACP_CLARTE_MODEL_NAME_EMPTY');
			}
			else if (!$this->models->add($name, $this->current()))
			{
				$errors[] = $this->language->lang('ACP_CLARTE_MODEL_LIMIT', models::MAX);
			}
			else
			{
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_CLARTE_MODEL_SAVED', false, [$name]);
				trigger_error($this->language->lang('ACP_CLARTE_MODEL_SAVED', $name) . adm_back_link($this->u_action));
			}
		}

		// Import d'un fichier de modèles (export ou sauvegarde)
		if ($this->request->is_set_post('import'))
		{
			$this->check_key('verturin_clarte_models');
			$data = $this->read_upload($errors);
			if ($data !== null)
			{
				if (!isset($data['models']) || !is_array($data['models']) || !in_array($data['format'], [self::FORMAT_MODELS, self::FORMAT_BACKUP], true))
				{
					$errors[] = $this->language->lang('ACP_CLARTE_FILE_NO_MODELS');
				}
				else
				{
					$added = count($this->models->import($this->relink_models($data['models'], $this->import_images($data))));
					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_CLARTE_MODELS_IMPORTED', false, [$added]);
					trigger_error($this->language->lang('ACP_CLARTE_MODELS_IMPORTED', $added) . adm_back_link($this->u_action));
				}
			}
		}

		$this->model_styles->sync();
		$styles = $this->model_styles->map();
		$style_rows = $this->model_styles->rows();
		$forum_names = $this->forum_names();
		$hash = generate_link_hash('clarte_models');
		foreach ($this->models->all() as $item)
		{
			$s = $item['settings'];
			$style_id = isset($styles[$item['id']]) ? $styles[$item['id']] : 0;
			$style = $style_id && isset($style_rows[$style_id]) ? $style_rows[$style_id] : null;
			$forums = array_values(array_intersect_key($forum_names, array_flip($this->forum_models->forums_of($item['id']))));
			$this->template->assign_block_vars('models', [
				'NAME'			=> $item['name'],
				'ACCENT'		=> $s['clarte_accent'],
				'FROM'			=> $s['clarte_header_from'],
				'TO'			=> $s['clarte_header_to'],
				'CAT_BG'		=> $this->cat_swatch($s),
				'SUMMARY'		=> $this->summary($s),
				'UPDATED'		=> $this->user->format_date($item['updated']),
				'S_CURRENT'		=> $this->is_current($s),
				'U_APPLY'		=> $this->u_action . '&amp;action=apply&amp;id=' . $item['id'],
				'U_EDIT'		=> $this->u_action . '&amp;action=edit&amp;id=' . $item['id'],
				'U_DUPLICATE'	=> $this->u_action . '&amp;action=duplicate&amp;id=' . $item['id'] . '&amp;hash=' . $hash,
				'U_EXPORT'		=> $this->u_action . '&amp;action=export&amp;id=' . $item['id'],
				'U_DELETE'		=> $this->u_action . '&amp;action=delete&amp;id=' . $item['id'],
				'S_STYLE'		=> (bool) $style,
				'STYLE_NAME'	=> $style ? $style['style_name'] : '',
				'STYLE_MEMBERS'	=> $style ? (int) $style['members'] : 0,
				'FORUMS'		=> implode(', ', $forums),
				'U_STYLE_CREATE'	=> $this->u_action . '&amp;action=style_create&amp;id=' . $item['id'] . '&amp;hash=' . $hash,
				'U_STYLE_DELETE'	=> $this->u_action . '&amp;action=style_delete&amp;id=' . $item['id'],
			]);
		}

		$this->template->assign_vars([
			'U_ACTION'				=> $this->u_action,
			'U_EXPORT_ALL'			=> $this->u_action . '&amp;action=export_all',
			'U_CLARTE_SETTINGS'		=> $this->mode_url('settings'),
			'U_CLARTE_BACKUP'		=> $this->mode_url('backup'),
			'U_CLARTE_FORUMS'		=> $this->mode_url('forums'),
			'S_ERROR'				=> (bool) $errors,
			'ERROR_MSG'				=> implode('<br>', $errors),
			'CLARTE_MODEL_COUNT'	=> $this->models->count(),
			'CLARTE_MODEL_MAX'		=> models::MAX,
			'CLARTE_NAME_MAX'		=> models::NAME_MAX,
		]);

		return 'acp_clarte_models';
	}

	/** Modification d'un modèle : même formulaire que la page de réglages, avec le nom en plus */
	protected function model_edit(array $model)
	{
		$u_edit = $this->u_action . '&amp;action=edit&amp;id=' . $model['id'];
		add_form_key('verturin_clarte');
		$errors = [];
		$values = $model['settings'];
		$name = $model['name'];

		if ($this->request->is_set_post('submit'))
		{
			$this->check_key('verturin_clarte', $u_edit);
			$values = $this->read_form(models::KEYS, $errors);
			$name = models::clean_name($this->request->variable('clarte_model_name', '', true));
			if ($name === '')
			{
				$errors[] = $this->language->lang('ACP_CLARTE_MODEL_NAME_EMPTY');
			}
			if (!$errors)
			{
				$this->models->update($model['id'], $name, $values);
				$updated = $this->models->get($model['id']);
				$this->model_styles->rename($model['id'], $updated['name'], $this->language->lang('ACP_CLARTE_STYLE_PREFIX'));
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_CLARTE_MODEL_SAVED', false, [$name]);
				trigger_error($this->language->lang('ACP_CLARTE_MODEL_UPDATED', $name) . adm_back_link($this->u_action));
			}
		}

		$this->assign_form($values + $this->current(), $errors);
		$this->template->assign_vars([
			'U_ACTION'				=> $u_edit,
			'U_CLARTE_MODELS'		=> $this->u_action,
			'S_MODEL_EDIT'			=> true,
			'CLARTE_MODEL_NAME'		=> $name,
			'CLARTE_NAME_MAX'		=> models::NAME_MAX,
		]);

		return 'acp_clarte_settings';
	}

	/* ====================================================================================
	   Page « Sauvegarde et restauration »
	   ==================================================================================== */

	public function backup_page()
	{
		if ($this->request->variable('action', '') === 'download')
		{
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_CLARTE_BACKUP_DOWNLOADED');
			$this->send_json([
				'format'	=> self::FORMAT_BACKUP,
				'generator'	=> $this->generator(),
				'date'		=> gmdate('c'),
				'board'		=> (string) $this->config['sitename'],
				'settings'	=> $this->current(),
				'models'	=> $this->backup_models(),
				'images'	=> $this->images->encode(array_merge(self::image_names($this->current()), $this->models_image_names($this->models->all()))),
			], 'clarte-sauvegarde-' . gmdate('Y-m-d-His') . '.json');
		}

		add_form_key('verturin_clarte_backup');
		$errors = [];

		if ($this->request->is_set_post('restore'))
		{
			$this->check_key('verturin_clarte_backup');
			$do_settings = (bool) $this->request->variable('restore_settings', 0);
			$do_models = (bool) $this->request->variable('restore_models', 0);
			$replace = $this->request->variable('models_mode', 'add') === 'replace';

			if (!$do_settings && !$do_models)
			{
				$errors[] = $this->language->lang('ACP_CLARTE_RESTORE_NOTHING');
			}

			$data = $errors ? null : $this->read_upload($errors);
			if ($data !== null)
			{
				if ($data['format'] !== self::FORMAT_BACKUP || !isset($data['settings']) || !is_array($data['settings']))
				{
					$errors[] = $this->language->lang('ACP_CLARTE_FILE_NOT_BACKUP');
				}
				else
				{
					$done = [];
					$image_map = $this->import_images($data);
					if ($image_map)
					{
						$done[] = $this->language->lang('ACP_CLARTE_RESTORED_IMAGES', count($image_map));
					}
					if ($do_settings)
					{
						$bad = [];
						$settings = models::clean($this->images->relink($data['settings'], $image_map) + $this->current(), array_keys(self::DEFAULTS), $bad);
						$this->apply($settings);
						$done[] = $this->language->lang('ACP_CLARTE_RESTORED_SETTINGS');
					}
					if ($do_models)
					{
						if ($replace)
						{
							$this->model_styles->delete_all();
							$this->forum_models->save([]);
							$this->models->clear();
						}
						$list = $this->relink_models(isset($data['models']) && is_array($data['models']) ? $data['models'] : [], $image_map);
						$ids = $this->models->import($list);
						$done[] = $this->language->lang('ACP_CLARTE_RESTORED_MODELS', count($ids));
						$done = array_merge($done, $this->restore_links($list, $ids));
					}
					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_CLARTE_BACKUP_RESTORED');
					trigger_error($this->language->lang('ACP_CLARTE_RESTORE_DONE') . '<br>' . implode('<br>', $done) . adm_back_link($this->u_action));
				}
			}
		}

		$current = $this->current();
		$this->template->assign_vars([
			'U_ACTION'				=> $this->u_action,
			'U_DOWNLOAD'			=> $this->u_action . '&amp;action=download',
			'U_CLARTE_MODELS'		=> $this->mode_url('models'),
			'S_ERROR'				=> (bool) $errors,
			'ERROR_MSG'				=> implode('<br>', $errors),
			'CLARTE_MODEL_COUNT'	=> $this->models->count(),
			'CLARTE_ACCENT'			=> $current['clarte_accent'],
			'CLARTE_HEADER_FROM'	=> $current['clarte_header_from'],
			'CLARTE_HEADER_TO'		=> $current['clarte_header_to'],
			'CLARTE_SUMMARY'		=> $this->summary($current),
			'CLARTE_MAX_UPLOAD'		=> get_formatted_filesize(self::MAX_UPLOAD),
		]);
	}

	/* ====================================================================================
	   Page « Couleurs par forum »
	   ==================================================================================== */

	public function forums_page()
	{
		add_form_key('verturin_clarte_forums');
		$models = [];
		foreach ($this->models->all() as $item)
		{
			$models[$item['id']] = $item;
		}

		if ($this->request->is_set_post('submit'))
		{
			$this->check_key('verturin_clarte_forums');
			$in = $this->request->variable('forum_model', [0 => 0]);
			$map = [];
			foreach ($in as $forum_id => $model_id)
			{
				if ($model_id === forum_models::GENERAL || isset($models[$model_id]))
				{
					$map[$forum_id] = $model_id;
				}
			}
			$this->forum_models->save($map);
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_CLARTE_FORUMS');
			trigger_error($this->language->lang('ACP_CLARTE_FORUMS_SAVED') . adm_back_link($this->u_action));
		}

		// Styles du forum : un forum qui impose un autre style que Clarté (ou un style enfant) n'en reçoit pas les couleurs
		global $db;
		$styles = [];
		$result = $db->sql_query('SELECT style_id, style_name, style_path, style_parent_tree FROM ' . STYLES_TABLE);
		while ($row = $db->sql_fetchrow($result))
		{
			$tree = explode('/', (string) $row['style_parent_tree']);
			$styles[(int) $row['style_id']] = [
				'name'		=> $row['style_name'],
				'clarte'	=> $row['style_path'] === 'clarte' || in_array('clarte', $tree, true),
			];
		}
		$db->sql_freeresult($result);
		$other_styles = 0;

		$map = $this->forum_models->map();
		$inherit = [];
		foreach ($this->forum_models->tree() as $forum)
		{
			$forum_style = (int) $forum['forum_style'];
			$other_style = $forum_style && isset($styles[$forum_style]) && !$styles[$forum_style]['clarte'] ? $styles[$forum_style]['name'] : '';
			$other_styles += $other_style !== '' ? 1 : 0;
			$id = (int) $forum['forum_id'];
			$depth = (int) $forum['depth'];
			$parent = $depth > 0 && isset($inherit[$depth - 1]) ? $inherit[$depth - 1] : [0, ''];
			$own = isset($map[$id]) ? $map[$id] : 0;
			if ($own > 0 && !isset($models[$own]))
			{
				$own = 0;
			}
			$effective = $own > 0 ? [$own, $forum['forum_name']] : ($own === forum_models::GENERAL ? [0, ''] : $parent);
			$inherit[$depth] = $effective;

			$model = $effective[0] ? $models[$effective[0]] : null;
			$this->template->assign_block_vars('forums', [
				'ID'			=> $id,
				'NAME'			=> $forum['forum_name'],
				'DEPTH'			=> $depth,
				'S_CAT'			=> (int) $forum['forum_type'] === FORUM_CAT,
				'OTHER_STYLE'	=> $other_style,
				'VALUE'			=> $own,
				'S_INHERITED'	=> $own === 0 && $model,
				'INHERITED_FROM'	=> $own === 0 && $model ? $effective[1] : '',
				'PARENT_MODEL'	=> $parent[0] ? $models[$parent[0]]['name'] : '',
				'MODEL_NAME'	=> $model ? $model['name'] : '',
				'ACCENT'		=> $model ? $model['settings']['clarte_accent'] : '',
				'FROM'			=> $model ? $model['settings']['clarte_header_from'] : '',
				'TO'			=> $model ? $model['settings']['clarte_header_to'] : '',
			]);
		}

		$options = [];
		foreach ($models as $item)
		{
			$options[] = ['ID' => $item['id'], 'NAME' => $item['name']];
		}
		$this->template->assign_vars([
			'U_ACTION'				=> $this->u_action,
			'U_CLARTE_MODELS'		=> $this->mode_url('models'),
			'CLARTE_MODEL_OPTIONS'	=> $options,
			'CLARTE_OTHER_STYLES'	=> $other_styles,
			'U_ACP_FORUMS'			=> append_sid($this->admin_path() . 'index.php', 'i=acp_forums&amp;mode=manage', true, $this->user->session_id),
			'CLARTE_GENERAL'		=> forum_models::GENERAL,
		]);
	}

	/** Forums et style d'un modèle, pour le message de suppression */
	protected function usage_note($model_id)
	{
		$notes = [];
		$forums = array_intersect_key($this->forum_names(), array_flip($this->forum_models->forums_of($model_id)));
		if ($forums)
		{
			$notes[] = $this->language->lang('ACP_CLARTE_MODEL_USED_FORUMS', implode(', ', $forums));
		}
		$styles = $this->model_styles->map();
		if (isset($styles[$model_id]))
		{
			$rows = $this->model_styles->rows();
			$row = isset($rows[$styles[$model_id]]) ? $rows[$styles[$model_id]] : null;
			if ($row)
			{
				$notes[] = $this->language->lang('ACP_CLARTE_MODEL_USED_STYLE', (int) $row['members'], $row['style_name']);
			}
		}

		return $notes ? '<br><br>' . implode('<br>', $notes) : '';
	}

	/** forum_id => nom */
	protected function forum_names()
	{
		$names = [];
		foreach ($this->forum_models->tree() as $forum)
		{
			$names[(int) $forum['forum_id']] = $forum['forum_name'];
		}

		return $names;
	}

	/** Modèles pour la sauvegarde : avec leurs forums et leur style au choix */
	protected function backup_models()
	{
		$items = $this->models->all();
		$list = models::export_list($items);
		$styles = $this->model_styles->map();
		foreach (array_values($items) as $i => $item)
		{
			$list[$i]['forums'] = $this->forum_models->forums_of($item['id']);
			$list[$i]['style'] = isset($styles[$item['id']]);
		}

		return $list;
	}

	/** Après restauration : forums de chaque modèle (s'ils existent ici) et styles au choix recréés */
	protected function restore_links(array $list, array $ids)
	{
		$existing = $this->forum_names();
		$map = $this->forum_models->map();
		$forums = 0;
		$styles = 0;
		$failed = '';
		foreach ($ids as $index => $model_id)
		{
			$entry = $list[$index];
			foreach (isset($entry['forums']) && is_array($entry['forums']) ? $entry['forums'] : [] as $forum_id)
			{
				if (isset($existing[(int) $forum_id]))
				{
					$map[(int) $forum_id] = $model_id;
					$forums++;
				}
			}
			if (!empty($entry['style']) && ($model = $this->models->get($model_id)))
			{
				$error = $this->model_styles->create($model, $this->language->lang('ACP_CLARTE_STYLE_PREFIX'));
				$error ? $failed = $error : $styles++;
			}
		}
		if ($forums)
		{
			$this->forum_models->save($map);
		}

		$done = [];
		if ($forums)
		{
			$done[] = $this->language->lang('ACP_CLARTE_RESTORED_FORUMS', $forums);
		}
		if ($styles)
		{
			$done[] = $this->language->lang('ACP_CLARTE_RESTORED_STYLES', $styles);
		}
		if ($failed)
		{
			$done[] = $this->language->lang($failed);
		}

		return $done;
	}

	/* ====================================================================================
	   Outils communs
	   ==================================================================================== */

	/** Réglages en service (valeurs d'origine pour les clés absentes) */
	public function current()
	{
		$values = [];
		foreach (self::DEFAULTS as $key => $default)
		{
			$values[$key] = isset($this->config[$key]) && (string) $this->config[$key] !== '' ? $this->config[$key] : $default;
		}

		return models::clean($values, array_keys(self::DEFAULTS));
	}

	/** Écrit des réglages (déjà vérifiés) dans la configuration */
	protected function apply(array $values)
	{
		foreach (models::clean($values + $this->current(), array_keys(self::DEFAULTS)) as $key => $value)
		{
			$this->config->set($key, $value);
		}
	}

	/** Lit le formulaire de réglages ; seules les clés demandées sont gardées */
	protected function read_form(array $keys, array &$errors)
	{
		$in = [];
		foreach ($keys as $key)
		{
			$in[$key] = in_array($key, ['clarte_allow_toggle', 'clarte_acp', 'clarte_acp_tabs', 'clarte_radius', 'clarte_text_allow', 'clarte_text_size', 'clarte_bg_fixed',
				'clarte_bg_overlay', 'clarte_bg_blur', 'clarte_bg_glass', 'clarte_bg_mobile', 'clarte_header_overlay', 'clarte_bg_allow'], true)
				? $this->request->variable($key, 0)
				: $this->request->variable($key, '');

			// Image envoyée avec le formulaire : prend la place de celle choisie dans la liste
			if (in_array($key, models::IMAGE_KEYS, true))
			{
				$error = '';
				$types = models::image_types($key);
				$name = $this->images->store_upload($this->request->file('upload_' . $key), $error, $types);
				if ($name === false)
				{
					$errors[] = $this->language->lang('ACP_CLARTE_IMAGE_ERROR', $this->language->lang(strtoupper($key)), $this->language->lang($error, get_formatted_filesize(images::MAX_SIZE), images::MAX_DIM, strtoupper(implode(', ', $types))));
				}
				else if ($name !== '')
				{
					$in[$key] = $name;
				}
				else if ($in[$key] !== '' && !$this->images->exists($in[$key]))
				{
					$in[$key] = '';
				}
			}
		}

		$bad = [];
		$values = models::clean($in, $keys, $bad);
		foreach ($bad as $key => $raw)
		{
			$errors[] = $this->language->lang('ACP_CLARTE_BAD_COLOUR', $this->language->lang(strtoupper($key)), utf8_htmlspecialchars($raw));
		}

		return $values;
	}

	/** Variables du formulaire de réglages (page de réglages et modification d'un modèle) */
	protected function assign_form(array $values, array $errors)
	{
		foreach (self::PRESETS as $name => $colours)
		{
			$this->template->assign_block_vars('presets', [
				'NAME'		=> $name,
				'LABEL'		=> $this->language->lang('ACP_CLARTE_PRESET_' . strtoupper($name)),
				'ACCENT'	=> $colours[0],
				'FROM'		=> $colours[1],
				'TO'		=> $colours[2],
			]);
		}

		foreach ($this->models->all() as $item)
		{
			$this->template->assign_block_vars('saved_models', [
				'NAME'		=> $item['name'],
				'ACCENT'	=> $item['settings']['clarte_accent'],
				'FROM'		=> $item['settings']['clarte_header_from'],
				'SETTINGS'	=> utf8_htmlspecialchars(json_encode($item['settings'])),
			]);
		}

		$this->template->assign_vars([
			'U_ACTION'				=> $this->u_action,
			'S_ERROR'				=> (bool) $errors,
			'ERROR_MSG'				=> implode('<br>', $errors),
			'CLARTE_ACCENT'			=> $values['clarte_accent'],
			'CLARTE_HEADER_FROM'	=> $values['clarte_header_from'],
			'CLARTE_HEADER_TO'		=> $values['clarte_header_to'],
			'CLARTE_DEFAULT_MODE'	=> $values['clarte_default_mode'],
			'CLARTE_ALLOW_TOGGLE'	=> (bool) $values['clarte_allow_toggle'],
			'CLARTE_ACP_ENABLED'	=> (bool) $values['clarte_acp'],
			'CLARTE_ACP_TABS_ENABLED'	=> (bool) $values['clarte_acp_tabs'],
			'CLARTE_FONT'			=> $values['clarte_font'],
			'CLARTE_RADIUS'			=> (int) $values['clarte_radius'],
			'CLARTE_CAT_MODE'		=> $values['clarte_cat_mode'],
			'CLARTE_CAT_MODES'		=> listener::CAT_MODES,
			'CLARTE_CAT_COLOUR'		=> $values['clarte_cat_colour'],
			'CLARTE_NAME_MAX'		=> models::NAME_MAX,
			'CLARTE_INDEX_TIME'		=> $values['clarte_index_time'],
			'CLARTE_TEXT_SIZE'		=> $values['clarte_text_size'],
			'CLARTE_TEXT_MIN'		=> listener::TEXT_MIN,
			'CLARTE_TEXT_MAX'		=> listener::TEXT_MAX,
			'CLARTE_TEXT_ALLOW'		=> (bool) $values['clarte_text_allow'],
			'CLARTE_INDEX_TIMES'	=> listener::INDEX_TIMES,
			'CLARTE_BG_MODES'		=> models::BG_MODES,
			'CLARTE_POSITIONS'		=> models::POSITIONS,
			'CLARTE_BG_MODE'		=> $values['clarte_bg_mode'],
			'CLARTE_BG_POSITION'	=> $values['clarte_bg_position'],
			'CLARTE_BG_FIXED'		=> (bool) $values['clarte_bg_fixed'],
			'CLARTE_BG_OVERLAY'		=> (int) $values['clarte_bg_overlay'],
			'CLARTE_BG_BLUR'		=> (int) $values['clarte_bg_blur'],
			'CLARTE_BG_GLASS'		=> (int) $values['clarte_bg_glass'],
			'CLARTE_BG_MOBILE'		=> (bool) $values['clarte_bg_mobile'],
			'CLARTE_HEADER_POSITION'	=> $values['clarte_header_position'],
			'CLARTE_HEADER_OVERLAY'	=> (int) $values['clarte_header_overlay'],
			'CLARTE_BG_ALLOW'		=> (bool) $values['clarte_bg_allow'],
			'CLARTE_RANGES'			=> models::RANGES,
			'CLARTE_IMAGE_MAX'		=> get_formatted_filesize(images::MAX_SIZE),
		]);

		// Emplacements d'image : liste des images de la bibliothèque + envoi d'une nouvelle
		$library = $this->images->all();
		foreach (models::IMAGE_KEYS as $key)
		{
			$options = [];
			$types = models::image_types($key);
			foreach ($library as $name => $info)
			{
				if (!in_array(substr($name, strrpos($name, '.') + 1), $types, true))
				{
					continue;
				}
				$options[] = ['NAME' => $name, 'URL' => $this->image_url($name), 'LABEL' => $info['width'] . ' × ' . $info['height'] . ' · ' . get_formatted_filesize($info['size']) . ' · ' . substr($name, 0, 6) . '…' . substr($name, strrpos($name, '.'))];
			}
			$block = in_array($key, models::ICON_KEYS, true) ? 'icon_slots' : (in_array($key, models::HEADER_KEYS, true) ? 'header_slots' : 'bg_slots');
			$this->template->assign_block_vars($block, [
				'KEY'		=> $key,
				'VALUE'		=> $values[$key],
				'URL'		=> $values[$key] !== '' ? $this->image_url($values[$key]) : '',
				'S_DARK'	=> substr($key, -5) === '_dark',
				'LABEL'		=> $this->language->lang(strtoupper($key)),
				'EXPLAIN'	=> $this->language->lang(strtoupper($key) . '_EXPLAIN'),
				'OPTIONS'	=> $options,
				'ACCEPT'	=> implode(',', array_map(function ($t) {
					return $t === 'ico' ? 'image/x-icon,image/vnd.microsoft.icon,.ico' : 'image/' . ($t === 'jpg' ? 'jpeg' : $t);
				}, $types)),
			]);
		}
	}

	/** Images citées par une liste de modèles */
	protected function models_image_names(array $items)
	{
		$names = [];
		foreach ($items as $item)
		{
			$names = array_merge($names, self::image_names($item['settings']));
		}

		return $names;
	}

	/** Enregistre les images d'un fichier importé ; renvoie l'ancien nom => le nouveau */
	protected function import_images(array $data)
	{
		return isset($data['images']) && is_array($data['images']) ? $this->images->decode($data['images']) : [];
	}

	/** Modèles importés : noms d'images mis à jour */
	protected function relink_models(array $list, array $map)
	{
		foreach ($list as $i => $entry)
		{
			if (is_array($entry) && isset($entry['settings']) && is_array($entry['settings']))
			{
				$list[$i]['settings'] = $this->images->relink($entry['settings'], $map);
			}
		}

		return $list;
	}

	/** Le modèle correspond-il aux réglages en service ? */
	protected function is_current(array $settings)
	{
		$current = $this->current();
		foreach (models::KEYS as $key)
		{
			if ((string) $settings[$key] !== (string) $current[$key])
			{
				return false;
			}
		}

		return true;
	}

	/** Résumé lisible : mode, bandeau, police, arrondi */
	protected function summary(array $s)
	{
		return implode(' · ', [
			$this->language->lang('CLARTE_MODE_' . strtoupper($s['clarte_default_mode'])),
			$this->language->lang('ACP_CLARTE_SUMMARY_CAT', $this->language->lang('CLARTE_CAT_SHORT_' . strtoupper($s['clarte_cat_mode']))),
			$this->language->lang($s['clarte_font'] === 'system' ? 'ACP_CLARTE_SUMMARY_FONT_SYSTEM' : 'ACP_CLARTE_SUMMARY_FONT_INTER'),
			$this->language->lang('ACP_CLARTE_SUMMARY_RADIUS', (int) $s['clarte_radius']),
		] + (!empty($s['clarte_bg_image']) || !empty($s['clarte_header_image']) ? [4 => $this->language->lang('ACP_CLARTE_SUMMARY_IMAGES')] : []));
	}

	/** Fond de la pastille « bandeau » dans la liste des modèles */
	protected function cat_swatch(array $s)
	{
		switch ($s['clarte_cat_mode'])
		{
			case 'tinted':
				return 'color-mix(in srgb, ' . $s['clarte_accent'] . ' 18%, #fff)';
			case 'header':
				return 'linear-gradient(90deg, ' . $s['clarte_header_from'] . ', ' . $s['clarte_header_to'] . ')';
			case 'custom':
				return $s['clarte_cat_colour'];
		}

		return '#f5f7fb';
	}

	/** Lit et décode le fichier JSON envoyé (champ clarte_file) ; null en cas d'erreur */
	protected function read_upload(array &$errors)
	{
		$file = $this->request->file('clarte_file');
		if (empty($file['tmp_name']) || !empty($file['error']) || !is_uploaded_file($file['tmp_name']))
		{
			$errors[] = $this->language->lang('ACP_CLARTE_FILE_MISSING');
			return null;
		}
		if ((int) $file['size'] > self::MAX_UPLOAD)
		{
			$errors[] = $this->language->lang('ACP_CLARTE_FILE_TOO_BIG', get_formatted_filesize(self::MAX_UPLOAD));
			return null;
		}

		$raw = file_get_contents($file['tmp_name']);
		$raw = preg_replace('/^\xEF\xBB\xBF/', '', (string) $raw);
		$data = json_decode($raw, true);
		if (!is_array($data) || !isset($data['format']) || !is_string($data['format']))
		{
			$errors[] = $this->language->lang('ACP_CLARTE_FILE_INVALID');
			return null;
		}

		return $data;
	}

	/** Envoie un fichier JSON à télécharger et s'arrête */
	protected function send_json(array $data, $filename)
	{
		$json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

		$response = new \Symfony\Component\HttpFoundation\Response($json, 200, [
			'Content-Type'			=> 'application/json; charset=utf-8',
			'Content-Disposition'	=> 'attachment; filename="' . $filename . '"',
			'Content-Length'		=> strlen($json),
			'Cache-Control'			=> 'no-store, no-cache, must-revalidate',
		]);
		$response->send();

		garbage_collection();
		exit_handler();
	}

	protected function check_key($key, $back = null)
	{
		if (!check_form_key($key))
		{
			trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($back ?: $this->u_action), E_USER_WARNING);
		}
	}

	protected function generator()
	{
		return 'Clarté ' . (isset($this->config['clarte_version']) ? $this->config['clarte_version'] : '');
	}

	/** Nom de fichier sûr à partir d'un nom de modèle */
	protected function slug($name)
	{
		$name = strtr(models::plain_name($name), [
			'à' => 'a', 'â' => 'a', 'ä' => 'a', 'á' => 'a', 'ã' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c',
			'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'î' => 'i', 'ï' => 'i', 'í' => 'i', 'ì' => 'i',
			'ô' => 'o', 'ö' => 'o', 'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ø' => 'o', 'œ' => 'oe',
			'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ú' => 'u', 'ÿ' => 'y', 'ñ' => 'n', 'ß' => 'ss',
			'À' => 'a', 'Â' => 'a', 'Ä' => 'a', 'Á' => 'a', 'Æ' => 'ae', 'Ç' => 'c', 'É' => 'e', 'È' => 'e',
			'Ê' => 'e', 'Ë' => 'e', 'Î' => 'i', 'Ï' => 'i', 'Ô' => 'o', 'Ö' => 'o', 'Œ' => 'oe',
			'Ù' => 'u', 'Û' => 'u', 'Ü' => 'u', 'Ÿ' => 'y', 'Ñ' => 'n',
		]);
		$slug = trim(strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $name)), '-');

		return $slug !== '' ? substr($slug, 0, 40) : 'modele';
	}

	/** Adresse d'une autre page du module (même i=, autre mode) */
	protected function mode_url($mode)
	{
		return preg_replace('/([?&](?:amp;)?mode=)[a-z_]+/', '${1}' . $mode, $this->u_action);
	}

	/** Liens de retour après l'enregistrement d'un modèle depuis la page de réglages */
	protected function links_back($with_models)
	{
		$links = adm_back_link($this->u_action);
		if ($with_models)
		{
			$links .= '<br><br><a href="' . $this->mode_url('models') . '">' . $this->language->lang('ACP_CLARTE_GO_MODELS') . '</a>';
		}

		return $links;
	}

	/** missing : le style n'est pas installé ; installed ; default : style par défaut du forum */
	protected function style_state()
	{
		global $db;

		$sql = 'SELECT style_id FROM ' . STYLES_TABLE . " WHERE style_path = 'clarte'";
		$result = $db->sql_query($sql);
		$style_id = (int) $db->sql_fetchfield('style_id');
		$db->sql_freeresult($result);

		if (!$style_id)
		{
			return 'missing';
		}

		return (int) $this->config['default_style'] === $style_id ? 'default' : 'installed';
	}

	protected function admin_path()
	{
		global $phpbb_admin_path, $phpbb_root_path;

		return isset($phpbb_admin_path) ? $phpbb_admin_path : $phpbb_root_path . 'adm/';
	}
}
