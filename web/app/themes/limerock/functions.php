<?php

/**
 * Timber starter-theme
 * https://github.com/timber/starter-theme
 */

// Load Composer dependencies.
require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/lib/jakeandco-wordpress-plugin/jakeandco-wordpress-plugin.php';
require_once __DIR__ . '/lib/LimeRockTheme/WordpressSite.php';

Timber\Timber::init();

Timber\Timber::$dirname = ['templates', 'views'];

new LimeRockTheme\WordpressSite();

require_once __DIR__ . '/lib/global_functions.php';
