<?php

namespace LimeRockTheme\PostType;

use Timber\Timber;

/**
 * Class PostTypeClass
 */
class PostTypeClass
{
  public static string $post_slug = 'post';
  public static array $post_type_template = [];
  public static ?int $posts_per_page = null;

  public static function init()
  {
    if (static::$posts_per_page === null) {
      static::$posts_per_page = intval(get_option('posts_per_page'));
    }
    add_filter('pre_get_posts', static::callable('update_posts_per_page'));

    add_action('init', static::callable('register_post_type'));
    add_action('init', static::callable('register_template'));
    add_action('init', static::callable('register_supports'));

    add_action('init', static::callable('register_save_hooks'));
    add_action('init', static::callable('register_filters'));
    add_action('rest_api_init', static::callable('rest_api_init'));
  }

  /**
   * This is where you can register custom post type.
   */
  public static function register_post_type() {}

  /**
   * This is where you can register this custom post type's templates.
   */
  public static function register_template()
  {
    static::get_object()->template = static::$post_type_template;
  }

  /**
   * This is where you can register post type supports
   */
  public static function register_supports() {}

  static function register_save_hooks() {}

  static function register_filters()
  {
    add_filter('pre_get_posts', static::callable('pre_filter_archive_query'));
  }

  public static function pre_filter_archive_query($query)
  {
    if (static::is_archive_query($query)) {
      if (!empty(static::$posts_per_page)) {
        $query->set('posts_per_page', static::$posts_per_page);
      }

      return static::filter_archive_query($query);
    }
    return $query;
  }

  public static function filter_archive_query($query)
  {
    return $query;
  }

  public static function is_archive_query($query) {
    return !is_admin()
      && $query->is_main_query()
      && (is_post_type_archive(static::$post_slug) || static::$post_slug == 'post' && is_home());
  }

  public static function rest_api_init()
  {
    $rest_fields = static::get_rest_fields();
    if (is_array($rest_fields)) {
      foreach ($rest_fields as $new_field_key => $new_field_configuration) {
        if (is_string($new_field_key) && is_array($new_field_configuration)) {
          register_rest_field(
            static::$post_slug,
            $new_field_key,
            $new_field_configuration
          );
        }
      }
    }
  }

  public static function update_posts_per_page($query)
  {
    if (
      !is_admin()
      && $query->is_main_query()
      && (is_post_type_archive(static::$post_slug) || static::$post_slug == 'post' && is_home())
      && !empty(static::$posts_per_page)
    ) {
      $query->set('posts_per_page', static::$posts_per_page);
    }
  }

  public static function get_rest_fields(): array
  {
    return [
      // See https://developer.wordpress.org/reference/functions/register_rest_field/

      // '<new_field_name>' =>  [ 
      //   'get_callback'    => function( $post_object, $attribute, $request, $object_type ) {},
      //   'update_callback'    => function( $request_data, $response_data, $field_name, $request, $object_type ) {},
      //   'schema'          => [
      //     'description' => __('some description'),
      //     'type'        => '<response type>'
      //    ],
      //  ]
    ];
  }

  static function is_matching_post($post_id)
  {

    $saved_post = Timber::get_post($post_id);
    return !empty($saved_post) && $saved_post->post_type == static::$post_slug;
  }

  static function get_object()
  {
    return get_post_type_object(static::$post_slug);
  }

  static function callable(string $method_name)
  {
    return sprintf('%s::%s', get_called_class(), $method_name);
  }
}
