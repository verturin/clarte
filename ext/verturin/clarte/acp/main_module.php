<?php
/**
 * Clarté — réglages du style
 *
 * @copyright (c) 2026 verturin
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace verturin\clarte\acp;

class main_module
{
	public $page_title;
	public $tpl_name;
	public $u_action;

	public function main($id, $mode)
	{
		global $phpbb_container;

		/** @var \verturin\clarte\controller\acp_controller $controller */
		$controller = $phpbb_container->get('verturin.clarte.acp_controller');
		$phpbb_container->get('language')->add_lang('acp_clarte', 'verturin/clarte');
		$controller->set_page_url($this->u_action);

		switch ($mode)
		{
			case 'models':
				$this->page_title = 'ACP_CLARTE_MODELS';
				$this->tpl_name = $controller->models_page();
			break;

			case 'forums':
				$this->page_title = 'ACP_CLARTE_FORUMS';
				$this->tpl_name = 'acp_clarte_forums';
				$controller->forums_page();
			break;

			case 'backup':
				$this->page_title = 'ACP_CLARTE_BACKUP';
				$this->tpl_name = 'acp_clarte_backup';
				$controller->backup_page();
			break;

			default:
				$this->page_title = 'ACP_CLARTE_SETTINGS';
				$this->tpl_name = 'acp_clarte_settings';
				$controller->settings();
			break;
		}
	}
}
