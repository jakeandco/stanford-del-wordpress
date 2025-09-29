<?php

namespace LimeRockTheme;

/**
 * Class AdminCustomizer
 */
class AdminCustomizer
{
  public static function init()
  {
    add_action('admin_enqueue_scripts', 'LimeRockTheme\AdminCustomizer::includes');
    add_action('after_setup_theme', 'LimeRockTheme\AdminCustomizer::mce');
    add_filter('upload_mimes', 'LimeRockTheme\AdminCustomizer::mime_types');
  }

  public static function includes()
  {
    wp_enqueue_style('editor-stylesheet', get_template_directory_uri() . '/dist/css/editor-base.css', [], time());
    wp_enqueue_script('editor-script', get_template_directory_uri() . '/dist/js/admin.js', ['acf-input'], time());
    wp_deregister_style('wp-block-library-theme');
  }

  public static function mce()
  {
    add_filter('tiny_mce_before_init', 'LimeRockTheme\AdminCustomizer::mce_before_init_insert_formats');
    add_filter('mce_buttons', 'LimeRockTheme\AdminCustomizer::mce_buttons');
  }

  public static function mime_types($mimes)
  {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
  }

  public static $tinymce_styles = [
    [
      'title' => 'H1 (110px)',
      'selector' => 'p,h1,h2,h3,h4,h5,h6',
      'classes' => 'h1',
      'wrapper' => false
    ],
    [
      'title' => 'H2 (75px)',
      'selector' => 'p,h1,h2,h3,h4,h5,h6',
      'classes' => 'h2',
      'wrapper' => false,
    ],
    [
      'title' => 'H3 (65px)',
      'selector' => 'p,h1,h2,h3,h4,h5,h6',
      'classes' => 'h3',
      'wrapper' => false
    ],
    [
      'title' => 'H4 (40px)',
      'selector' => 'p,h1,h2,h3,h4,h5,h6',
      'classes' => 'h4',
      'wrapper' => false
    ],
    [
      'title' => 'H5 (24px)',
      'selector' => 'p,h1,h2,h3,h4,h5,h6',
      'classes' => 'h5',
      'wrapper' => false
    ],
    [
      'title' => 'H6 (16px)',
      'selector' => 'p,h1,h2,h3,h4,h5,h6',
      'classes' => 'h6',
      'wrapper' => false
    ],
    [
      'title' => 'Primary Button',
      'selector' => 'a',
      'classes' => 'btn btn--primary',
      'wrapper' => false
    ],
    [
      'title' => 'Secondary Button',
      'selector' => 'a',
      'classes' => 'btn btn--secondary',
      'wrapper' => false
    ],
    [
      'title' => 'Tertiary Button',
      'selector' => 'a',
      'classes' => 'btn btn--tertiary',
      'wrapper' => false
    ],
  ];

  public static function mce_buttons($buttons)
  {
    array_splice($buttons, 1, 0, 'styleselect');
    return $buttons;
  }

  public static function mce_before_init_insert_formats($init_array)
  {
    $init_array['style_formats'] = json_encode(self::$tinymce_styles);

    return $init_array;
  }
}
