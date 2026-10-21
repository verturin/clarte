<?php
/**
 * Clarté — réglages du style
 *
 * @copyright (c) 2026 verturin
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace verturin\clarte;

class ext extends \phpbb\extension\base
{
	/** phpBB 3.3.15 minimum */
	public function is_enableable()
	{
		$config = $this->container->get('config');

		return phpbb_version_compare($config['version'], '3.3.15', '>=') && version_compare(PHP_VERSION, '7.2.0', '>=');
	}
}
