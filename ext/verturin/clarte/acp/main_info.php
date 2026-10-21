<?php
/**
 * Clarté — réglages du style
 *
 * @copyright (c) 2026 verturin
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace verturin\clarte\acp;

class main_info
{
	public function module()
	{
		return [
			'filename'	=> '\verturin\clarte\acp\main_module',
			'title'		=> 'ACP_CLARTE_TITLE',
			'modes'		=> [
				'settings'	=> [
					'title'	=> 'ACP_CLARTE_SETTINGS',
					'auth'	=> 'ext_verturin/clarte && acl_a_board',
					'cat'	=> ['ACP_CLARTE_TITLE'],
				],
				'models'	=> [
					'title'	=> 'ACP_CLARTE_MODELS',
					'auth'	=> 'ext_verturin/clarte && acl_a_board',
					'cat'	=> ['ACP_CLARTE_TITLE'],
				],
				'forums'	=> [
					'title'	=> 'ACP_CLARTE_FORUMS',
					'auth'	=> 'ext_verturin/clarte && acl_a_board',
					'cat'	=> ['ACP_CLARTE_TITLE'],
					'after'	=> 'ACP_CLARTE_MODELS',
				],
				'backup'	=> [
					'title'	=> 'ACP_CLARTE_BACKUP',
					'auth'	=> 'ext_verturin/clarte && acl_a_board',
					'cat'	=> ['ACP_CLARTE_TITLE'],
				],
			],
		];
	}
}
