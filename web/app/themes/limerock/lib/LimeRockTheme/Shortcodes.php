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

    add_shortcode('button', "LimeRockTheme\Shortcodes::shortcode_button");
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

  public static function shortcode_button($attributes)
  {
    return Timber\Timber::compile('views/shortcodes/button.twig', Timber\Timber::context(
      [
        'shortcode_attrs' => [
          'href' => esc_url(Util::array_value($attributes, 'href')) ?: '#',
          'type' => esc_attr(Util::array_value($attributes, 'type')) ?: 'primary',
          'target' => esc_attr(Util::array_value($attributes, 'target')) ?: 'self',
          'label' => esc_attr(Util::array_value($attributes, 'label')) ?: '',
          'aria-label' => esc_attr(Util::array_value($attributes, 'aria-label')) ?: '',
          'title' => wp_kses_post(Util::array_value($attributes, 'title')) ?: '',
        ]
      ]
    ));
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
