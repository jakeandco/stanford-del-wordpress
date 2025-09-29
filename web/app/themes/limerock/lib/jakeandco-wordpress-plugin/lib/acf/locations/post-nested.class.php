<?php

if (!defined('ABSPATH') || !class_exists('ACF_Location')) {
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

class Post_Nested_Location extends ACF_Location
{
  function initialize()
  {
    $this->name = 'post-parent-location';
    $this->label = __('Post Nested Level');
  }

  public static function get_operators($rule)
  {
    return [
      '==' => __('is equal to', 'acf'),
      '!=' => __('is not equal to', 'acf'),
      '>' => __('is greater than', 'acf'),
      '<' => __('is less than', 'acf'),
      '>=' => __('is greater than or equal to', 'acf'),
      '<=' => __('is less than or equal to', 'acf'),
    ];
  }

  public function get_values($rule)
  {
    return array(
      'all' => 'All',
      '0' => 'First-level',
      '1' => 'Second-level',
      '2' => 'Third-level'
    );
  }

  public function compare_to_rule($value, $rule)
  {
    // Allow "all" to match any value.
    if ($rule['value'] === 'all') {
      return true;
    }

    $rule_val = intval($rule['value']);

    $result = ($value == $rule_val);

    // Reverse result for "!=" operator.
    if ($rule['operator'] === '!=') {
      return !$result;
    }

    if ($rule['operator'] === '>') {
      return $value > $rule_val;
    }

    if ($rule['operator'] === '<') {
      return $value < $rule_val;
    }

    if ($rule['operator'] === '>=') {
      return $value >= $rule_val;
    }

    if ($rule['operator'] === '<=') {
      return $value <= $rule_val;
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

    $ancestors = get_post_ancestors($post);
    $level = count($ancestors);

    $result = $this->compare_to_rule($level, $rule);

    return $result;
  }
}
