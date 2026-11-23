<?php
/**
 * Clarté — réglages du style
 *
 * @copyright (c) 2026 verturin
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace verturin\clarte\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use verturin\clarte\core\models;

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
			'core.ucp_prefs_personal_data'			=> 'ucp_prefs_data',
			'core.ucp_prefs_personal_update_data'	=> 'ucp_prefs_update',
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
		// Icône du site (1.7.0) : sur tous les styles, comme l'icône d'un site
		$this->template->assign_var('CLARTE_ICON_TAGS', $this->icon_tags());

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
			'CLARTE_BG_CSS'			=> $this->member_hides_images() ? '' : self::background_css($settings, generate_board_url() . '/' . \verturin\clarte\core\images::DIR),
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

		// Taille du texte choisie par le membre, si l'ACP le permet (sinon celle du forum)
		if ($this->member_text_allowed() && !empty($this->user->data['user_clarte_text']) && self::member_text($this->user->data['user_clarte_text']) !== '')
		{
			$settings['clarte_text_size'] = self::member_text($this->user->data['user_clarte_text']);
		}

		$forum_model = $this->forum_models->resolve($this->current_forum_id($event));
		if ($forum_model && ($model = $this->models->get($forum_model)))
		{
			$keys = self::FORUM_KEYS;
			// Images du modèle du forum : remplacent celles du forum seulement si le modèle en a une
			if (!empty($model['settings']['clarte_bg_image']))
			{
				$keys = array_merge($keys, models::BG_KEYS);
			}
			if (!empty($model['settings']['clarte_header_image']))
			{
				$keys = array_merge($keys, models::HEADER_KEYS);
			}
			$settings = array_merge($settings, array_intersect_key($model['settings'], array_flip($keys)));
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
		$this->template->assign_var('CLARTE_ICON_TAGS', $this->icon_tags());

		if (empty($this->config['clarte_acp']))
		{
			return;
		}

		$this->template->assign_vars([
			'CLARTE_ACP'			=> true,
			'CLARTE_ACP_TABS'		=> !empty($this->config['clarte_acp_tabs']),
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
		$size = self::text_size(isset($config['clarte_text_size']) ? $config['clarte_text_size'] : '100');
		if ($size !== '100')
		{
			$vars[] = '--clarte-scale:' . ((int) $size / 100);
		}
		$radius = isset($config['clarte_radius']) ? (int) $config['clarte_radius'] : 12;
		if ($radius !== 12)
		{
			$radius = max(0, min(20, $radius));
			$vars[] = '--radius:' . $radius . 'px;--radius-sm:' . max(0, (int) round($radius * 2 / 3)) . 'px';
		}

		return implode(';', $vars);
	}

	/**
	 * Images de fond (1.6.0) : feuille de style produite ici, à partir de réglages déjà vérifiés par models::clean().
	 * Fond de page : couche fixe (ou qui défile) derrière la page, voile à la couleur de la page (clair ou sombre),
	 * flou ; image sombre facultative (sinon la même, plus voilée) ; blocs translucides ; masquée sur mobile si demandé.
	 * En-tête : image sous un voile aux couleurs de l'en-tête (le titre reste lisible).
	 */
	public static function background_css($config, $url)
	{
		$s = models::clean(is_array($config) || $config instanceof \ArrayAccess ? self::to_array($config) : [], array_merge(models::BG_KEYS, models::HEADER_KEYS));
		$url = str_replace(['"', '\\', "\n", "\r", '<', '>'], '', (string) $url);
		$css = [];
		$dark_css = [];

		if ($s['clarte_bg_image'] !== '')
		{
			$blur = (int) $s['clarte_bg_blur'];
			$veil = $s['clarte_bg_overlay'] / 100;
			$dark = $s['clarte_bg_image_dark'];
			$veil_dark = $dark !== '' ? $veil : round($veil + (1 - $veil) * 0.45, 2);
			$sizes = ['cover' => 'cover no-repeat', 'tile' => 'auto repeat', 'center' => 'auto no-repeat'];
			list($size, $repeat) = explode(' ', $sizes[$s['clarte_bg_mode']]);
			$edge = $blur ? -2 * $blur : 0;
			$fixed = (bool) $s['clarte_bg_fixed'];

			$layer = 'html::before,html::after{content:"";position:' . ($fixed ? 'fixed' : 'absolute') . ';top:' . $edge . 'px;right:' . $edge . 'px;bottom:' . $edge . 'px;left:' . $edge . 'px;z-index:-1;pointer-events:none}'
				. 'html::before{background:url("' . $url . $s['clarte_bg_image'] . '") ' . $s['clarte_bg_position'] . '/' . $size . ' ' . $repeat . ($blur ? ';filter:blur(' . $blur . 'px)' : '') . '}'
				. 'html::after{background:var(--page);opacity:' . $veil . '}'
				. 'body{background:transparent}'
				// Textes posés directement sur l'image : blocs « Qui est en ligne », statistiques, connexion rapide en cartes ; halo pour le reste
				. '.stat-block,.page-body>form.headerspace{background:var(--surface);border:1px solid var(--line);border-radius:var(--radius);padding:10px 14px;margin:0 0 10px;box-shadow:var(--shadow)}'
				. '.stat-block h3,form.headerspace h3{border-bottom-color:var(--line);margin-top:2px;padding-top:0}'
				. '.page-body>p,.page-body>h2,.page-body>.action-bar,.page-body>.topic-actions,.copyright,.page-footer .copyright{text-shadow:0 0 3px var(--page),0 0 8px var(--page),0 0 14px var(--page)}';
			if (!$fixed)
			{
				$layer = 'html{position:relative;min-height:100%}' . $layer;
			}
			$css[] = $s['clarte_bg_mobile'] ? $layer : '@media (min-width:701px){' . $layer . '}';
			$dark_css[] = ($dark !== '' ? '%s::before{background-image:url("' . $url . $dark . '")}' : '') . '%s::after{opacity:' . $veil_dark . '}';

			$glass = (int) $s['clarte_bg_glass'];
			if ($glass > 0)
			{
				$a = round(1 - $glass / 100, 2);
				$rule = ':root{--surface:rgb(255 255 255 / ' . $a . ');--surface-2:rgb(247 248 252 / ' . $a . ');--surface-3:rgb(239 241 246 / ' . $a . ');--clarte-solid:#fff}'
					. '.navbar,.forabg,.forumbg,.panel,.post,.cp-menu,.cp-main .panel,ul.topiclist.forums,ul.topiclist.topics,.action-bar .pagination li a,.jumpbox,.stat-block,.tabs .activetab>a{-webkit-backdrop-filter:blur(10px);backdrop-filter:blur(10px)}'
					. '.dropdown .dropdown-contents,.dropdown .pointer-inner,.dropdown-select{background:var(--clarte-solid)}';
				$css[] = $s['clarte_bg_mobile'] ? $rule : '@media (min-width:701px){' . $rule . '}';
				$glass_dark = '%s{--surface:rgb(21 28 41 / ' . $a . ');--surface-2:rgb(25 33 47 / ' . $a . ');--surface-3:rgb(30 39 56 / ' . $a . ');--clarte-solid:#151c29}';
				$dark_css[] = $s['clarte_bg_mobile'] ? $glass_dark : '@media (min-width:701px){' . $glass_dark . '}';
			}
		}

		if ($s['clarte_header_image'] !== '')
		{
			$v = $s['clarte_header_overlay'];
			$veil = 'linear-gradient(135deg,color-mix(in srgb,var(--header-from) ' . $v . '%,transparent),color-mix(in srgb,var(--header-to) ' . $v . '%,transparent))';
			$css[] = '.headerbar{background:' . $veil . ',url("' . $url . $s['clarte_header_image'] . '") ' . $s['clarte_header_position'] . '/cover no-repeat,var(--header-from)}';
			if ($s['clarte_header_image_dark'] !== '')
			{
				$dark_css[] = '%s .headerbar{background:' . $veil . ',url("' . $url . $s['clarte_header_image_dark'] . '") ' . $s['clarte_header_position'] . '/cover no-repeat,var(--header-from)}';
			}
		}

		if ($dark_css)
		{
			$dark = implode('', $dark_css);
			$css[] = str_replace('%s', 'html[data-theme="dark"]', $dark);
			$css[] = '@media (prefers-color-scheme:dark){' . str_replace('%s', 'html:not([data-theme="light"])', $dark) . '}';
		}

		return implode('', $css);
	}

	/**
	 * Balises de l'icône du site : icône (ICO ou PNG), version pour le mode sombre du système
	 * (media prefers-color-scheme), icône de l'écran d'accueil (apple-touch-icon, PNG).
	 */
	public function icon_tags()
	{
		$s = models::clean([
			'clarte_favicon'		=> isset($this->config['clarte_favicon']) ? $this->config['clarte_favicon'] : '',
			'clarte_favicon_dark'	=> isset($this->config['clarte_favicon_dark']) ? $this->config['clarte_favicon_dark'] : '',
			'clarte_touch_icon'		=> isset($this->config['clarte_touch_icon']) ? $this->config['clarte_touch_icon'] : '',
		], models::ICON_KEYS);
		if ($s['clarte_favicon'] === '' && $s['clarte_touch_icon'] === '')
		{
			return '';
		}
		$url = generate_board_url() . '/' . \verturin\clarte\core\images::DIR;
		$type = function ($name) {
			return substr($name, -4) === '.ico' ? 'image/x-icon' : 'image/png';
		};
		$tags = '';
		if ($s['clarte_favicon'] !== '')
		{
			$tags .= '<link rel="icon" type="' . $type($s['clarte_favicon']) . '" href="' . $url . $s['clarte_favicon'] . '">';
			if ($s['clarte_favicon_dark'] !== '')
			{
				$tags .= '<link rel="icon" type="' . $type($s['clarte_favicon_dark']) . '" href="' . $url . $s['clarte_favicon_dark'] . '" media="(prefers-color-scheme: dark)">';
			}
		}
		if ($s['clarte_touch_icon'] !== '')
		{
			$tags .= '<link rel="apple-touch-icon" href="' . $url . $s['clarte_touch_icon'] . '">';
		}

		return $tags;
	}

	/** Réglages (tableau ou objet config de phpBB) en tableau simple */
	protected static function to_array($config)
	{
		if (is_array($config))
		{
			return $config;
		}
		$out = [];
		foreach (array_merge(models::BG_KEYS, models::HEADER_KEYS) as $key)
		{
			if (isset($config[$key]))
			{
				$out[$key] = $config[$key];
			}
		}

		return $out;
	}

	/** Le membre a choisi de masquer les images de fond (si l'ACP le permet) */
	protected function member_hides_images()
	{
		return $this->member_bg_allowed() && !empty($this->user->data['user_clarte_bg']);
	}

	/** Le membre peut-il masquer les images de fond ? (réglage ACP, membre inscrit) */
	protected function member_bg_allowed()
	{
		return !empty($this->config['clarte_bg_allow']) && !empty($this->user->data['is_registered']);
	}

	/** Taille du texte en %, de 1 en 1 : facteur appliqué à la base (10,5 px) et aux tailles fixes du texte (100 = normal) */
	const TEXT_MIN = 80;
	const TEXT_MAX = 150;

	/** Taille valide (« 80 » à « 150 »), ramenée dans les bornes ; « 100 » si la valeur n'est pas un nombre */
	public static function text_size($value)
	{
		$value = trim((string) $value);
		if ($value === '' || !ctype_digit($value))
		{
			return '100';
		}

		return (string) max(self::TEXT_MIN, min(self::TEXT_MAX, (int) $value));
	}

	/** Choix d'un membre : « 80 » à « 150 », ou « » (taille du forum) */
	public static function member_text($value)
	{
		$value = trim((string) $value);

		return $value !== '' && ctype_digit($value) && (int) $value >= self::TEXT_MIN && (int) $value <= self::TEXT_MAX ? (string) (int) $value : '';
	}

	/** Le membre peut-il choisir sa taille du texte ? (réglage ACP, membre inscrit) */
	protected function member_text_allowed()
	{
		return !empty($this->config['clarte_text_allow']) && !empty($this->user->data['is_registered']);
	}

	/**
	 * Panneau de l'utilisateur › Préférences du forum › Paramètres généraux : choix de la taille du texte.
	 * « Taille du forum » (valeur vide) suit le réglage de l'ACP.
	 */
	public function ucp_prefs_data($event)
	{
		if (!$this->member_text_allowed() && !$this->member_bg_allowed())
		{
			return;
		}
		$this->user->add_lang_ext('verturin/clarte', 'ucp_clarte');

		$data = $event['data'];
		if ($this->member_bg_allowed())
		{
			$data['clarte_bg'] = $event['submit'] ? (int) (bool) $this->request->variable('clarte_bg_hide', 0) : (int) !empty($this->user->data['user_clarte_bg']);
			$this->template->assign_vars([
				'S_CLARTE_BG_ALLOW'	=> true,
				'S_CLARTE_BG_HIDE'	=> (bool) $data['clarte_bg'],
			]);
		}
		if (!$this->member_text_allowed())
		{
			$event['data'] = $data;
			return;
		}
		$stored = isset($this->user->data['user_clarte_text']) ? self::member_text($this->user->data['user_clarte_text']) : '';
		if ($event['submit'])
		{
			$data['clarte_text'] = $this->request->variable('clarte_text_board', 0) ? '' : self::text_size($this->request->variable('clarte_text', 100));
		}
		else
		{
			$data['clarte_text'] = $stored;
		}
		$event['data'] = $data;

		$board = self::text_size($this->config['clarte_text_size']);
		$this->template->assign_vars([
			'S_CLARTE_TEXT_ALLOW'	=> true,
			'S_CLARTE_TEXT_BOARD'	=> $data['clarte_text'] === '',
			'CLARTE_TEXT_VALUE'		=> $data['clarte_text'] === '' ? $board : $data['clarte_text'],
			'CLARTE_TEXT_FORUM'		=> $board,
			'CLARTE_TEXT_MIN'		=> self::TEXT_MIN,
			'CLARTE_TEXT_MAX'		=> self::TEXT_MAX,
		]);
	}

	public function ucp_prefs_update($event)
	{
		$data = $event['data'];
		$sql_ary = $event['sql_ary'];
		if ($this->member_bg_allowed())
		{
			$sql_ary['user_clarte_bg'] = !empty($data['clarte_bg']) ? 1 : 0;
		}
		if ($this->member_text_allowed())
		{
			$sql_ary['user_clarte_text'] = isset($data['clarte_text']) ? self::member_text($data['clarte_text']) : '';
		}
		$event['sql_ary'] = $sql_ary;
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
