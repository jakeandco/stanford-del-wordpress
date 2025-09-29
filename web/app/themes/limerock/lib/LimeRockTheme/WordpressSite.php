<?php

namespace LimeRockTheme;

use Timber;

require_once 'Util.php';
require_once 'ThemeCustomizer.php';
require_once 'AdminCustomizer.php';
require_once 'PostTypes.php';
require_once 'Taxonomies.php';
require_once 'ACF.php';
require_once 'Blocks.php';
require_once 'Menus.php';
require_once 'TimberCustomizer.php';
require_once 'TwigCustomizer.php';
require_once 'Shortcodes.php';
require_once 'RenderFilters.php';

/**
 * Class WordpressSite
 */
class WordpressSite extends Timber\Site
{
	public static $instance = null;

	public function __construct()
	{
		self::$instance = $this;

		Util::init();

		ThemeCustomizer::init();

		AdminCustomizer::init();

		PostTypes::init();
		Taxonomies::init();
		ACF::init();
		Blocks::init();
		Menus::init();

		TimberCustomizer::init();
		TwigCustomizer::init();

		Shortcodes::init();
		RenderFilters::init();

		parent::__construct();
	}
}
