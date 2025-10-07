<?php

/**
 * Timber starter-theme
 * https://github.com/timber/starter-theme
 */
// Load Composer dependencies.
/**
 * Check for the required plugin and exit the theme if it's not active.
 */
function required_plugin_check()
{
  $required_plugins = [
    [
      'path' => 'acf-field-group-composer/acf-field-group-composer.php',
      'name' => 'ACF Field Group Composer',
    ],
    [
      'path' => 'advanced-custom-fields-pro/acf.php',
      'name' => 'ACF Pro',
    ],
    // [
    //   'path' => 'acf-extended-pro/acf-extended-pro.php',
    //   'name' => 'ACF Extended Po',
    // ],
  ];

  // Only include the plugin function file if it isn't already available.
  if (! function_exists('is_plugin_active')) {
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
  }

  $missing_plugins = array_filter($required_plugins, fn($plugin_array) => !is_plugin_active($plugin_array['path']));

  if (!empty($missing_plugins)) {
    // // Deactivate the theme.
    switch_theme(WP_DEFAULT_THEME);
    // Display an error message and kill the script.
    $error_message = implode('<br />', [
      'This theme requires the following plugin(s) to be active:',
      '<ul>'
      .  implode(' ', array_map(fn($plugin_array) => '<li>' . $plugin_array['name'] . '</li>', $missing_plugins)) .
      '</ul>',
      'Please install and activate them before enabling the LimeRock theme.'
    ]);

    wp_die(
      $error_message,
      'Plugin Required',
      array('back_link' => true)
    );
  }
}

add_action('after_setup_theme', 'required_plugin_check');

require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/lib/jakeandco-wordpress-plugin/jakeandco-wordpress-plugin.php';
require_once __DIR__ . '/lib/LimeRockTheme/WordpressSite.php';

Timber\Timber::init();

Timber\Timber::$dirname = ['templates', 'views'];

new LimeRockTheme\WordpressSite();

require_once __DIR__ . '/lib/global_functions.php';
