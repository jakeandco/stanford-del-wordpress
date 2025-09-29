<?php

namespace JakeAndCo\Media;


class Image
{

  /**
   * Function for `wp_get_attachment_image` filter-hook.
   * Removes hardcoded height & width
   *
   * @param string       $html          HTML img element or empty string on failure.
   * @param int          $attachment_id Image attachment ID.
   * @param string|int[] $size          Requested image size. Can be any registered image size name, or an array of width and height values in pixels (in that order).
   * @param bool         $icon          Whether the image should be treated as an icon.
   * @param string[]     $attr          Array of attribute values for the image markup, keyed by attribute name. See wp_get_attachment_image().
   *
   * @return string
   */
  public static function filter_wp_get_attachment_image_strip_dimensions($html, $attachment_id, $size, $icon, $attr)
  {

    $pattern = '/(width|height)="\d+"/i';
    $replacement = '';
    return preg_replace($pattern, $replacement, $html);
  }
}
