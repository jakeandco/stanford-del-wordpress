<?php

namespace JakeAndCo;

if (!defined('ABSPATH')) {
  exit;
}


class PostType
{
  public function __construct()
  {
    add_action('init', [$this, 'register_post_type_template']);
    add_action('pre_get_posts', array($this, 'customize_archive_query'));
  }

  public static $post_type_slug = 'post';

  public static $post_type_tag_taxonomies = [];

  public function register_post_type_template()
  {
  }

  /*
  |--------------------------------------------------------------------------
  | Customize Storys Archive Query
  |--------------------------------------------------------------------------
  */
  function customize_archive_query($query)
  {
  }

  public static function get_selections($block_id = false, $limit = false)
  {
  }

  public static function get_by__query($selection_type,  $selections,  $filter_by_role = false, $role = [], $limit = false)
  {
  }
  public static function get_by($selection_type,  $selections, $options)
  {
  }

  public static function get_tags($post)
  {
    $tags = [];

    foreach (self::$post_type_tag_taxonomies as $taxonomy) {
      $terms = get_the_terms($post, $taxonomy) ?: [];

      foreach ($terms as $term) {
        $tags[] = [
          'url' => get_term_link($term),
          'title' => $term->name
        ];
      }
    }

    return $tags;
  }
}
