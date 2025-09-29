<?php

/**
 * Plugin Name:     Jake & Co - Wordpress Helpers
 * Plugin URI:      https://github.com/jakeandco/jakeandco-wordpress-plugin
 * Description:     Common helpers, classes, and utilities for Jake & Co custom Wordpress themes
 * Author:          Jake & Co
 * Author URI:      http://jakeandco.com
 * Text Domain:     jakeandco-wordpress-plugin
 * Version:         0.1.0
 *
 * @package         JakeAndCo_Wordpress_Plugin
 */

namespace JakeAndCo;

use LimeRockTheme\Util;

require_once dirname(__FILE__) . '/lib/template.class.php';
require_once dirname(__FILE__) . '/lib/query.class.php';
require_once dirname(__FILE__) . '/lib/post-type.class.php';
require_once dirname(__FILE__) . '/lib/menu.class.php';
require_once dirname(__FILE__) . '/lib/menu-tree.class.php';
require_once dirname(__FILE__) . '/lib/media.class.php';
require_once dirname(__FILE__) . '/lib/media-video.class.php';
require_once dirname(__FILE__) . '/lib/media-image.class.php';
require_once dirname(__FILE__) . '/lib/format.class.php';
require_once dirname(__FILE__) . '/lib/block.class.php';
require_once dirname(__FILE__) . '/lib/acf-helpers.class.php';

class PluginBase
{
  public static $dist_path = '/dist';
  public static function initialize($args)
  {
    self::$dist_path = Util::array_value($args, 'dist_path') ?: self::$dist_path;
  }
}
