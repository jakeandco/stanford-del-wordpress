<?php

namespace LimeRockTheme\PostType;

/**
 * Class Post
 */
class Post extends PostTypeClass
{
  public static string $post_slug = 'post';
  public static ?int $posts_per_page = 15;

  // public static $post_type_template = [];

  public static function init()
  {
    parent::init();
    add_action('init', function () {
      unregister_taxonomy_for_object_type('post_tag', 'post');
      unregister_taxonomy_for_object_type('category', 'post');
    });
  }

  // public static function register_post_type() {
  //   parent::register_post_type();
  // }

  // public static function register_template() {
  //   parent::register_template();
  // }

  // public static function register_supports() {
  //   parent::register_supports();
  // }

  // static function register_save_hooks() {
  //   parent::register_save_hooks();
  // }

  // static function register_filters() {
  //   parent::register_filters();
  // }


}
