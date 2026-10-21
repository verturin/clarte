<?php
/**
 * Clarté — réglages du style
 *
 * @copyright (c) 2026 verturin
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace verturin\clarte\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Transmet au style Clarté les couleurs et le mode réglés dans l'ACP.
 * Sans effet quand le membre utilise un autre style.
 */
class listener implements EventSubscriberInterface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string */
	protected $root_path;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \verturin\clarte\core\models */
	protected $models;

	/** @var \verturin\clarte\core\forum_models */
	protected $forum_models;

	/** Couleurs qu'un modèle de forum remplace (la présentation reste celle du forum) */
	const FORUM_KEYS = ['clarte_accent', 'clarte_header_from', 'clarte_header_to', 'clarte_cat_mode', 'clarte_cat_colour'];

	public function __construct(\phpbb\config\config $config, \phpbb\template\template $template, \phpbb\user $user, $root_path,
		\phpbb\request\request_interface $request, \verturin\clarte\core\models $models, \verturin\clarte\core\forum_models $forum_models)
	{
		$this->config = $config;
		$this->template = $template;
		$this->user = $user;
		$this->root_path = $root_path;
		$this->request = $request;
		$this->models = $models;
		$this->forum_models = $forum_models;
	}

	public static function getSubscribedEvents()
	{
		return [
			'core.page_header'		=> 'page_header',
			'core.adm_page_header'	=> 'adm_page_header',
		];
	}

	/** Le style actif est Clarté, ou un style enfant de Clarté */
	public function style_is_clarte()
	{
		$style = isset($this->user->style) && is_array($this->user->style) ? $this->user->style : [];
		$path = isset($style['style_path']) ? $style['style_path'] : '';
		$tree = isset($style['style_parent_tree']) ? explode('/', (string) $style['style_parent_tree']) : [];

		return $path === 'clarte' || in_array('clarte', $tree, true);
	}

	public function page_header($event)
	{
		if (!$this->style_is_clarte())
		{
			return;
		}

		$settings = $this->page_settings($event);

		$this->template->assign_vars([
			'CLARTE_EXT'			=> true,
			'CLARTE_STYLE_VARS'		=> self::style_vars($settings),
			'CLARTE_DEFAULT_MODE'	=> self::mode($settings['clarte_default_mode']),
			'CLARTE_ALLOW_TOGGLE'	=> (bool) $this->config['clarte_allow_toggle'],
			'CLARTE_INDEX_TIME'		=> self::index_time(isset($this->config['clarte_index_time']) ? $this->config['clarte_index_time'] : 'both'),
		]);
	}

	/**
	 * Réglages de la page : réglages généraux, puis modèle du style choisi par le membre (style généré),
	 * puis couleurs du modèle du forum affiché (forum, sujet, rédaction ; hérité du forum parent).
	 */
	public function page_settings($event = null)
	{
		$settings = [];
		foreach (\verturin\clarte\core\models::DEFAULTS as $key => $default)
		{
			$settings[$key] = isset($this->config[$key]) && (string) $this->config[$key] !== '' ? $this->config[$key] : $default;
		}

		$style_path = isset($this->user->style['style_path']) ? $this->user->style['style_path'] : '';
		$style_model = \verturin\clarte\core\model_styles::model_id_from_path($style_path);
		if ($style_model && ($model = $this->models->get($style_model)))
		{
			$settings = array_merge($settings, $model['settings']);
		}

		$forum_model = $this->forum_models->resolve($this->current_forum_id($event));
		if ($forum_model && ($model = $this->models->get($forum_model)))
		{
			$settings = array_merge($settings, array_intersect_key($model['settings'], array_flip(self::FORUM_KEYS)));
		}

		return $settings;
	}

	/** Forum affiché : celui transmis à page_header (forum, sujet), sinon le paramètre f (rédaction, modération) */
	protected function current_forum_id($event)
	{
		if ($event !== null && isset($event['item'], $event['item_id']) && $event['item'] === 'forum' && (int) $event['item_id'] > 0)
		{
			return (int) $event['item_id'];
		}
		$f = $this->request->variable('f', 0);
		if ($f > 0)
		{
			return $f;
		}
		global $forum_id;

		return isset($forum_id) ? (int) $forum_id : 0;
	}

	/**
	 * Administration : même habillage pour toutes les pages de l'ACP (réglage « Habiller l'administration »).
	 */
	public function adm_page_header()
	{
		if (empty($this->config['clarte_acp']))
		{
			return;
		}

		$this->template->assign_vars([
			'CLARTE_ACP'			=> true,
			'CLARTE_STYLE_VARS'		=> self::style_vars($this->config),
			'CLARTE_DEFAULT_MODE'	=> self::mode($this->config['clarte_default_mode']),
			'CLARTE_ACP_CSS_PATH'	=> $this->root_path . 'ext/verturin/clarte/adm/style/',
			'CLARTE_ASSETS_VERSION'	=> (int) $this->config['assets_version'],
		]);
	}

	/** Variables CSS à placer dans :root (couleurs vérifiées une à une) */
	public static function style_vars($config)
	{
		$vars = [];
		$map = [
			'clarte_accent'			=> '--accent',
			'clarte_header_from'	=> '--header-from',
			'clarte_header_to'		=> '--header-to',
		];
		foreach ($map as $key => $var)
		{
			$colour = self::colour(isset($config[$key]) ? $config[$key] : '');
			if ($colour !== '')
			{
				$vars[] = $var . ':' . $colour;
			}
		}
		if (isset($config['clarte_font']) && $config['clarte_font'] === 'system')
		{
			$vars[] = '--font:system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
		}
		$vars = array_merge($vars, self::category_vars(
			isset($config['clarte_cat_mode']) ? $config['clarte_cat_mode'] : 'neutral',
			isset($config['clarte_cat_colour']) ? $config['clarte_cat_colour'] : ''
		));
		$radius = isset($config['clarte_radius']) ? (int) $config['clarte_radius'] : 12;
		if ($radius !== 12)
		{
			$radius = max(0, min(20, $radius));
			$vars[] = '--radius:' . $radius . 'px;--radius-sm:' . max(0, (int) round($radius * 2 / 3)) . 'px';
		}

		return implode(';', $vars);
	}

	/** Date sur l'accueil : both (tout afficher), now (date du jour seule), last (dernière visite seule), none */
	const INDEX_TIMES = ['both', 'now', 'last', 'none'];

	public static function index_time($value)
	{
		return in_array($value, self::INDEX_TIMES, true) ? $value : 'both';
	}

	/** Modes du bandeau des catégories et des listes de sujets */
	const CAT_MODES = ['neutral', 'tinted', 'header', 'custom'];

	public static function cat_mode($value)
	{
		return in_array($value, self::CAT_MODES, true) ? $value : 'neutral';
	}

	/**
	 * Variables du bandeau (.forabg / .forumbg) : fond, texte, texte secondaire, trait.
	 * neutral : rien (gris clair du style) ; tinted : couleur principale adoucie (suit le mode sombre) ;
	 * header : dégradé de l'en-tête ; custom : couleur choisie, texte blanc ou foncé selon le contraste.
	 */
	public static function category_vars($mode, $colour)
	{
		switch (self::cat_mode($mode))
		{
			case 'tinted':
				return ['--cat-bg:var(--accent-soft)', '--cat-ink:var(--accent-ink)', '--cat-ink-2:var(--accent-ink)', '--cat-line:var(--accent-line)'];

			case 'header':
				return ['--cat-bg:linear-gradient(90deg,var(--header-from),var(--header-to))', '--cat-ink:#ffffff', '--cat-ink-2:rgba(255,255,255,.78)', '--cat-line:transparent'];

			case 'custom':
				$colour = self::colour($colour);
				if ($colour === '')
				{
					return [];
				}
				$light = self::ink($colour) === '#ffffff';

				return ['--cat-bg:' . $colour, '--cat-ink:' . ($light ? '#ffffff' : '#1d2433'), '--cat-ink-2:' . ($light ? 'rgba(255,255,255,.78)' : 'rgba(29,36,51,.72)'), '--cat-line:transparent'];
		}

		return [];
	}

	/** Texte le plus lisible sur une couleur #rrggbb : blanc ou ardoise foncée */
	public static function ink($colour)
	{
		$l = [];
		foreach ([1, 3, 5] as $i)
		{
			$c = hexdec(substr($colour, $i, 2)) / 255;
			$l[] = $c <= 0.03928 ? $c / 12.92 : pow(($c + 0.055) / 1.055, 2.4);
		}
		$lum = 0.2126 * $l[0] + 0.7152 * $l[1] + 0.0722 * $l[2];

		// contraste avec le blanc (1) et avec #1d2433 (luminance ≈ 0,017)
		return (1.05 / ($lum + 0.05)) >= (($lum + 0.05) / 0.067) ? '#ffffff' : '#1d2433';
	}

	/** #rrggbb valide, sinon chaîne vide */
	public static function colour($value)
	{
		$value = strtolower(trim((string) $value));

		return preg_match('/^#[0-9a-f]{6}$/', $value) ? $value : '';
	}

	public static function mode($value)
	{
		return in_array($value, ['auto', 'light', 'dark'], true) ? $value : 'auto';
	}
}
