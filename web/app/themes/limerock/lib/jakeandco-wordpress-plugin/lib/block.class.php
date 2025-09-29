<?php

namespace JakeAndCo;

use DirectoryIterator;
use LimeRockTheme\Util;
use WP_Block_Supports;
use WP_Block_Type_Registry;

if (!defined('ABSPATH')) {
  exit;
}

class Block {


  /*
  |--------------------------------------------------------------------------
  | Get Custom Blocks Paths
  |--------------------------------------------------------------------------
  |
  | Retrieves paths of all custom blocks from /blocks directory.
  |
  */
  public static function get_custom_block_paths()
  {
    $block_paths = [];
    $blocks_directory = get_template_directory() . '/blocks';

    $directory_listing = new DirectoryIterator($blocks_directory);

    foreach ($directory_listing as $file_info) {
      if ($file_info->isDir() && !$file_info->isDot()) {
        $block_json_file = $file_info->getPathname() . '/block.json';

        // If block.json is present, add the path.
        if (file_exists($block_json_file)) {
          $block_paths[] = $file_info->getPathname();
        }
      }
    }

    return $block_paths;
  }

  /*
  |--------------------------------------------------------------------------
  | Get Custom Blocks Names
  |--------------------------------------------------------------------------
  |
  | Parses block.json files and gets names of all custom blocks from
  | /blocks directory in theme.
  |
  */
  public static function get_custom_block_names()
  {
    $block_names = [];
    $blocks_directory = get_template_directory() . '/blocks';

    $dir_iterator = new DirectoryIterator($blocks_directory);

    foreach ($dir_iterator as $file_info) {
      if ($file_info->isDir() && !$file_info->isDot()) {
        $block_json_file = $file_info->getPathname() . '/block.json';

        if (file_exists($block_json_file)) {
          $block_json_content = file_get_contents($block_json_file);
          $block_json = json_decode($block_json_content, true);

          $block_name = Util::array_value($block_json, 'name');
          if ($block_name) {
            $block_names[] = $block_name;
          }
        }
      }
    }

    return $block_names;
  }

  /*
  |--------------------------------------------------------------------------
  | Register Custom Blocks
  |--------------------------------------------------------------------------
  */

  public static function register_block()
  {

    $block_paths = self::get_custom_block_paths();

    // Loop through all found blocks and register them.
    foreach ($block_paths as $block_path) {
      register_block_type($block_path);
    }
  }

  /*
  |--------------------------------------------------------------------------
  | Allowlist Custom Gutenberg Blocks
  |--------------------------------------------------------------------------
  */

  public static function filter_allowed_block_types($allowed_block_types, $post)
  {

    $custom_block_names = self::get_custom_block_names();

    return $custom_block_names;
  }

  /*
  |--------------------------------------------------------------------------
  | Register Block Categories
  |--------------------------------------------------------------------------
  |
  | Add new block categories for our custom blocks.
  |
  */
  public static function register_block_categories($block_categories, $editor_context)
  {
    if (!empty($editor_context->post)) {
      array_push(
        $block_categories,
        array(
          'slug'  => 'featured-content',
          'title' => __('Featured Content', 'groundsforreclamation'),
          'icon'  => null,
        ),
      );
    }
    return $block_categories;
  }

  /*
  |--------------------------------------------------------------------------
  | Register Block Templates
  |--------------------------------------------------------------------------
  |
  | Predefine default blocks on specific post types.
  |
  */

  public static function register_block_templates()
  {
  }

  /**
   * Check if block is of the style in question
   * @param string $style
   * @param mixed $block
   * @return bool
   */
  public static function is_block_style($style, $block)
  {
    $classes = explode(" ", $block['className']);
    foreach ($classes as $class) {
      if ($class == 'is-style-' . $style) {
        return true;
      }
    }
    return false;
  }

  /*
  |--------------------------------------------------------------------------
  | Check for Block Content
  |--------------------------------------------------------------------------
  */

  public static function has_content($block)
  {
    $fields = get_fields($block['id']);

    if (!$fields) {
      return false;
    }

    foreach ($fields as $field) {
      if (!empty($field)) {
        return true;
      } else {
      }
    }

    // No fields with content found.
    return false;
  }
  public static function get_wrapper_attributes($extra_attributes = [])
  {
    $block = WP_Block_Supports::get_instance()::$block_to_render;
    $blockDefinition = WP_Block_Type_Registry::get_instance()->get_registered($block['blockName']);

    $default_background = Util::array_value($blockDefinition->attributes, ['backgroundColor', 'default']);
    $default_color = Util::array_value($blockDefinition->attributes, ['color', 'default']);

    if ($default_background) {
      WP_Block_Supports::get_instance()::$block_to_render['attrs']['backgroundColor'] ??= $default_background;
    }

    if ($default_color) {
      WP_Block_Supports::get_instance()::$block_to_render['attrs']['color'] ??= $default_color;
    }

    return get_block_wrapper_attributes($extra_attributes);
  }
}

new Block();
