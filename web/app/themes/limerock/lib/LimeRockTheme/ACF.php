<?php

namespace LimeRockTheme;

use ACFComposer\ACFComposer;
use JakeAndCo;
use LimeRockTheme;

/**
 * Class ACF
 */

class ACF
{
	static $disallowed_field_names = [
		'footnotes',
		'description',
		'keywords',
	];

	public static function init()
	{
		add_action('init', 'LimeRockTheme\ACF::register_acf_types');

		JakeAndCo\ACF_Helpers::register_custom_locations();

		add_action('admin_head', function () {
			remove_submenu_page('edit.php?post_type=acf-field-group', 'edit.php?post_type=acf-ui-options-page');
		});

		add_filter('acf/prepare_field', 'LimeRockTheme\ACF::add_video_selection_to_image_fields');
		add_filter('acf/pre_format_value', 'LimeRockTheme\ACF::remove_wpautop', 10, 5);
		add_filter('acf/validate_save_post', 'LimeRockTheme\ACF::validate_save_post');
	}

	public static function validate_save_post()
	{
		if (is_array($_POST) && is_array($_POST['acf'])) {
			foreach ($_POST['acf'] as $key => $value) {
				$field_object = get_field_object($key);

				if (is_array($field_object) && array_key_exists('name', $field_object)) {
					$field_name = $field_object['name'];

					if (in_array($field_name, static::$disallowed_field_names)) {
						acf_add_validation_error(
							"acf[$key]",
							"The field name \"$field_name\" is reserved in WordPress. Please choose a different name."
						);
					}
				}
			}
		}
		return false;
	}

	public static function register_acf_types()
	{
		$definitionsFolderPath = '/lib/acf-composer';
		$compositionFilterFolders = ['fields'];
		$groupFolders = ['post-types', 'taxonomies', 'options', 'general', 'templates'];

		foreach ($compositionFilterFolders as $compositionFilterType) {
			LimeRockTheme\Util::search_directory(
				implode('/', [$definitionsFolderPath, $compositionFilterType]),
				fn($item) => $item->isFile() && $item->getExtension() == 'json',

				function ($item) use ($compositionFilterType) {
					$baseName = $item->getBasename('.json');
					$fieldContents = json_decode(
						file_get_contents($item->getPathname()),
						true
					);

					$base_filtername = "LimeRockTheme/ACF/$compositionFilterType/$baseName";
					add_filter($base_filtername, function ($field) use ($fieldContents) {
						return $fieldContents;
					});
				}
			);
		}

		foreach ($groupFolders as $groupType) {
			LimeRockTheme\Util::search_directory(
				implode('/', [$definitionsFolderPath, $groupType]),
				fn($item) => $item->isFile() && $item->getExtension() == 'json',
				function ($item) {
					$fieldContents = json_decode(
						file_get_contents($item->getPathname()),
						true
					);

					if (!empty($fieldContents['fields'])) {
						ACFComposer::registerFieldGroup($fieldContents);
					}
				}
			);
		}
	}
	public static function is_custom_option($field, $option, $value)
	{
		return is_array($field) && array_key_exists("limerock_option_$option", $field) && $field["limerock_option_$option"] == $value;
	}

	public static function is_composed_type($field, $type)
	{
		return is_array($field) && array_key_exists('limerock_base_type', $field) && $field['limerock_base_type'] == $type;
	}

	public static function remove_wpautop($skip_format, $value, $post_id, $field, $escape_html)
	{
		if (static::is_custom_option($field, 'autop', false)) {
			remove_filter('acf_the_content', 'wpautop');
			add_filter('acf/format_value', 'LimeRockTheme\ACF::cleanup_remove_wpautop', 100, 4);
		}
		return $skip_format;
	}

	public static function cleanup_remove_wpautop($value, $post_id, $field, $escape_html)
	{
		if (static::is_custom_option($field, 'autop', false)) {
			add_filter('acf_the_content', 'wpautop');
			remove_filter('acf/format_value', 'LimeRockTheme\ACF::cleanup_remove_wpautop');
		}

		return $value;
	}

	public static function add_video_selection_to_image_fields($field)
	{
		if (static::is_composed_type($field, 'image')) {
			add_filter('wp_get_attachment_image_src', 'LimeRockTheme\ACF::support_videos_in_images', 10, 4);
			add_filter('acf/render_field', 'LimeRockTheme\ACF::cleanup_video_selection_filters', 100);
		}
		return $field;
	}

	public static function support_videos_in_images($image, $attachment_id, $size, $icon)
	{
		if (!$image) {
			$metadata = wp_get_attachment_metadata($attachment_id);

			if (str_contains($metadata['mime_type'], 'video')) {
				$width = $metadata['width'];
				$height = $metadata['height'];
				$title = str_replace(' ', '_', get_the_title($attachment_id));
				$fontsize = max(24, $height / 10);

				return [
					implode('', [
						"https://place-hold.it/",
						$width,
						"x",
						$height,
						"/f1f1f1/&text=",
						rawurlencode($title),
						"&fontsize=",
						$fontsize
					]),
					$width,
					$height,
					false
				];
			}
		}
		return $image;
	}


	public static function cleanup_video_selection_filters($field)
	{
		if (static::is_composed_type($field, 'image')) {
			remove_filter('wp_get_attachment_image_src', 'LimeRockTheme\ACF::wp_get_attachment_image_src', 10);
			remove_filter('acf/render_field', 'LimeRockTheme\ACF::cleanup_video_selection_filters', 100);
		}

		return $field;
	}
}
