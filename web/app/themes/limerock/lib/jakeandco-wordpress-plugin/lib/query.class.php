<?php

namespace JakeAndCo;

use LimeRockTheme\Util;

if (!defined('ABSPATH')) {
  exit;
}

class Query
{
  static public function build_outer_query(array $query_array, string $outer_relation = 'AND')
  {
    $filtered_query = array_filter($query_array, function ($part) {
      return !empty($part);
    });

    $outer_query = [];

    if (count($filtered_query) > 1) {
      $outer_query['relation'] = $outer_relation;
    }

    foreach ($filtered_query as $query_part) {
      $type = Util::array_value($query_part, 'type');
      if ($type == 'tax' || $type == 'meta') {
        $outer_query[] = self::build_query_part(
          $query_part['type'],
          $query_part['key'],
          $query_part['values'],
          $query_part['params']
        );
      } else {
        $outer_query[] = $query_part;
      }
    }

    return $outer_query;
  }

  static public function build_query_part(string $type, string $key, $values, $params = [])
  {

    $relation = Util::array_value($params, 'relation') ?: 'OR';
    $compare = Util::array_value($params, 'compare') ?: 'LIKE';
    $is_acf_relation = Util::array_value($params, 'is_acf_relation');


    $query_part = [];
    if (empty($key) || empty($values)) {
      return $query_part;
    }

    if (!is_array($values)) {
      $values = [$values];
    }


    if ($type === 'tax') {
      $query_part = [
        'taxonomy' => $key,
        'terms' => $values
      ];
    } elseif ($type === 'meta') {
      $meta_values = [];

      foreach ($values as $value) {
        $meta_values[] = [
          'key' => $key,
          'value' => $is_acf_relation ? ('"' . $value . '"') : $value,
          'compare' => $compare,
        ];
      }

      if (count($meta_values) > 1) {
        $query_part = ['relation' => $relation] + $meta_values;
      } else {
        $query_part = $meta_values[0];
      }
    }

    return $query_part;
  }
}
