<?php

if (!defined('ABSPATH')) {
  exit;
}

/*
|--------------------------------------------------------------------------
| ACF Configurations
|--------------------------------------------------------------------------
|
| Various ACF-related customizations and functionality.
|
*/

class Post_Parent_Children_Location extends ACF_Location
{
  function initialize()
  {
    $this->name = 'post-parent-children-location';
    $this->label = __('Post Parents/Children');
  }

  public static function get_operators($rule)
  {
    return [
      '==' => __('has', 'acf'),
      '!=' => __('does not have', 'acf'),
    ];
  }

  public function get_values($rule)
  {
    return array(
      'children' => 'Children',
      'parents' => 'Parents',
    );
  }

  public function compare_to_rule($value, $rule)
  {
    $result = $value[$rule['value']];

    // Reverse result for "!=" operator.
    if ($rule['operator'] === '!=') {
      return !$result;
    }


    return $result;
  }

  public function match($rule, $screen, $field_group)
  {
    // Check screen args for "post_id" which will exist when editing a post.
    // Return false for all other edit screens.
    if (isset($screen['post_id'])) {
      $post_id = $screen['post_id'];
    } else {
      return false;
    }

    // Load the post object for this edit screen.
    $post = get_post($post_id);
    if (!$post) {
      return false;
    }
    $children = get_children(['post_parent' => $post_id, 'post_type' => $post->post_type]);
    $parents = get_post_ancestors($post);

    $result = $this->compare_to_rule([
      'children' => !empty($children),
      'parents' => !empty($parents)
    ], $rule);

    return $result;
  }
}
