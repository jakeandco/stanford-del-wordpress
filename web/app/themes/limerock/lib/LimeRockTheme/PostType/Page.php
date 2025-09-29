<?php

namespace LimeRockTheme\PostType;

/**
 * Class Page
 */
class Page extends PostTypeClass
{
  public static string $post_slug = 'page';

  // public static $post_type_template = [];

  // public static function init() {
  //   parent::init();
  // }

  // public static function register_post_type() {
  //   parent::register_post_type();
  // }

  // public static function register_template() {
  //   parent::register_template();
  // }

  public static function register_supports()
  {
    parent::register_supports();
    add_post_type_support(static::$post_slug, 'excerpt');
  }

  // static function register_save_hooks() {
  //   parent::register_save_hooks();
  // }

  // static function register_filters() {
  //   parent::register_filters();
  // }
}
