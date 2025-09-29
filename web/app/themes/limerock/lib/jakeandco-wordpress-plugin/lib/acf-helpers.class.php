<?php

namespace JakeAndCo;

use ACF;


if (!defined('ABSPATH') || !function_exists('acf_is_block_editor')) {
  exit;
}

require_once dirname(__DIR__) . '/lib/acf/locations/post-nested.class.php';
require_once dirname(__DIR__) . '/lib/acf/locations/post-parent-children.class.php';

class ACF_Helpers
{
  public static function register_custom_locations()
  {
    if (function_exists('acf_register_location_type')) {
      acf_register_location_type('Post_Parent_Children_location');
      acf_register_location_type('Post_Nested_Location');
    }
  }
  public static function block_render_callback($block, $content = '', $is_preview = false)
  {
    // When in the editor mode, a consistent wrapping div must be maintained.
    // If there is no consistent wrapper, the block may error if all fields are empty
    // or if the block does not render any visible content.
    //
    // This is because the React renderer that the block editor & ACF preview views
    // are built on requires a way to track when changes happen to a component.
    //
    // The ACF preview renderer does not seem to be correctly set up to prevent errors
    // when the HTML structure of a block dramatically changes, such as in our use case
    // where we switch in a block preview image when fields are empty.
    if (acf_is_block_editor() || $is_preview) {
      self::template_block_wrapper($block, $content, $is_preview);
    } else {
      self::block_render_callback_content($block, $content, $is_preview);
    }
  }

  public static function block_render_callback_content($block, $content = '', $is_preview = false)
  {
    // Blocks to exclude and always show template.
    $exclude_blocks = [];

    // Field types to check for content.
    $check_field_types = [
      'text',
      'link',
      'wysiwyg',
      'textarea',
      'relationship',
      'taxonomy',
      'post_object',
      'url',
      'email',
      'image',
      'file',
      'gallery',
      'oembed',
      'repeater',
      'group'
    ];

    // Get the fields and template paths.
    $fields = get_fields();
    $block_path = $block['path'];
    $template = $block['render_template'];
    $template_path = $block_path . '/' . $template;

    // If this is not the preview mode, or it's an excluded block, render the block normally.
    if (in_array($block['name'], $exclude_blocks) || !$is_preview) {
      include $template_path;
      return;
    }

    // Check if fields of the above field types are empty.
    $is_empty = true;
    foreach ($fields as $key => $field) {
      $field_object = get_field_object($key);

      if (in_array($field_object['type'], $check_field_types)) {
        // If the field type is 'group', iterate over its subfields
        if ($field_object['type'] == 'group') {
          foreach ($field_object['sub_fields'] as $sub_field) {
            if (in_array($sub_field['type'], $check_field_types) && !empty($field[$sub_field['name']])) {
              $is_empty = false;
              break 2; // Break this and the parent loop.
            }
          }
        } elseif (!empty($field)) {
          $is_empty = false;
          break;
        }
      }
    }

    // If the block has content or it is not in preview mode, render the block normally
    if (!$is_empty || !$is_preview) {

      include $template_path;
    } else {
      self::template_block_preview($block);
    }
  }

  private static function template_block_wrapper($block, $content = '', $is_preview = false)
  {
    if (!empty($block)) : ?>
      <div class='jakeandco__block__callback-wrapper' id='editor-wrapper-<?= $block[' id']; ?>'>
        <?= self::block_render_callback_content($block, $content, $is_preview); ?>
      </div>
    <?php endif;
  }

  private static function template_block_preview($block)
  {
    if (!empty($block)) :
      $preview_image = $block['path'] . '/preview.png';
    ?>
      <div class="jakeandco__block__preview">
        <div class="jakeandco__block__preview__container">
          <?php if (file_exists($preview_image)) :
            $preview_image_url = str_replace(get_template_directory(), get_template_directory_uri(), $preview_image);
          ?>
            <img src="<?= esc_url($preview_image_url) ?>" class="jakeandco__block__preview__image" style="max-width: 100%; height: auto;" />
          <?php else : ?>
            <p class="jakeandco__block__preview__text">Enter content for your <?= $block['title'] ?> block.</p>
          <?php endif; ?>
        </div>
      </div>
<?php endif;
  }
}

new ACF();
