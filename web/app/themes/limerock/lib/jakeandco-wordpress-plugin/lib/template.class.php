<?php

namespace JakeAndCo;

use LimeRockTheme\Util;

if (!defined('ABSPATH')) {
  exit;
}

class Template
{
  public static function is_ajax()
  {
    return (strtolower(Util::array_value($_SERVER, 'HTTP_X_REQUESTED_WITH') ?: '') === 'xmlhttprequest' and is_search());
  }

  public static function get_template_name()
  {
    return (Util::array_value($_GET, 'ajax') == 'template-header') ? 'header' : 'default';
  }

  public static function get_template_part_string($path = '', $name = '', $args = [])
  {
    ob_start();
    get_template_part($path, $name, $args);
    return ob_get_clean();
  }


  static public function get_post_type_single_name($post_id)
  {
    $post_type = get_post_type($post_id);
    $post_type_obj = get_post_type_object($post_type);

    if ($post_type_obj) {
      return esc_html($post_type_obj->labels->singular_name);
    }

    return '';
  }

  public static function get_previous_post_id($post_id)
  {
    // Get a global post reference since get_adjacent_post() references it
    global $post;
    // Store the existing post object for later so we don't lose it
    $oldGlobal = $post;
    // Get the post object for the specified post and place it in the global variable
    $post = get_post($post_id);
    // Get the post object for the previous post
    $previous_post = get_previous_post();
    // Reset our global object
    $post = $oldGlobal;
    if ('' == $previous_post) {
      return 0;
    }
    return $previous_post;
  }

  public static function get_next_post_id($post_id)
  {
    // Get a global post reference since get_adjacent_post() references it
    global $post;
    // Store the existing post object for later so we don't lose it
    $oldGlobal = $post;
    // Get the post object for the specified post and place it in the global variable
    $post = get_post($post_id);
    // Get the post object for the previous post
    $next_post = get_next_post();
    // Reset our global object
    $post = $oldGlobal;
    if ('' == $next_post) {
      return 0;
    }
    return $next_post;
  }


  /*
  |--------------------------------------------------------------------------
  | The Asset (dist) Path
  |--------------------------------------------------------------------------
  |
  | Prints the full path to the dist directory.
  |
  */
  public static function get_asset_path($type)
  {
    $theme_location = get_template_directory_uri();
    $output_directory = \JakeAndCo\PluginBase::$dist_path;

    switch ($type) {
      case "js":
        return $theme_location . $output_directory . '/js';
      case "css":
        return $theme_location . $output_directory . '/css';
      case "images":
        return $theme_location . $output_directory . '/images';
      default:
        return $theme_location . $output_directory;
    }
  }

  public static function the_asset_path($type)
  {

    echo self::get_asset_path($type);
  }
}
