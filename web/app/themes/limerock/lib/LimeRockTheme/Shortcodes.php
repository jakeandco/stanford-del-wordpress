<?php

namespace LimeRockTheme;

use Timber;

if (!defined('ABSPATH')) {
  exit;
}

class Shortcodes
{

  public static function init()
  {

    add_shortcode('year', "LimeRockTheme\Shortcodes::shortcode_current_year");
    add_shortcode('footnote', "LimeRockTheme\Shortcodes::shortcode_footnote");
  }

  /*
  |--------------------------------------------------------------------------
  | Output Current Year
  |--------------------------------------------------------------------------
  */
  public static function shortcode_current_year()
  {
    return date('Y');
  }

  /*
  |--------------------------------------------------------------------------
  | Output Footnote Link
  |--------------------------------------------------------------------------
  */
  public static function shortcode_footnote($attributes)
  {
    $index = isset($attributes['id']) ? intval($attributes['id']) : '';
    $post              = get_post();
    $footnotes              = get_field('page_footnotes', $post);
    if (is_array($footnotes) && is_int($index)) {
      $zeroed_index = $index - 1;
      if (key_exists($zeroed_index, $footnotes)) {
        $context           = Timber\Timber::context();
        $context['footnote'] = $footnotes[$zeroed_index];
        $context['footnote_index'] = $index;
        return Timber\Timber::compile('views/shortcodes/footnote.twig', $context);
      }
    }
  }
}
