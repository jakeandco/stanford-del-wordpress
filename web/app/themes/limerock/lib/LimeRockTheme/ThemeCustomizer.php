<?php

namespace LimeRockTheme;

use LimeRockTheme\Util;

/**
 * Class ThemeCustomizer
 */
class ThemeCustomizer
{
	public static function init()
	{
		add_action('after_setup_theme', 'LimeRockTheme\ThemeCustomizer::theme_supports');
		add_action('wp_enqueue_scripts', 'LimeRockTheme\ThemeCustomizer::includes');
		add_action('timber/locations', 'LimeRockTheme\ThemeCustomizer::locations');
		add_filter('template_include', 'LimeRockTheme\ThemeCustomizer::add_password_protection', 99);
	}

	public static function theme_supports()
	{
		// Add default posts and comments RSS feed links to head.
		add_theme_support('automatic-feed-links');

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support('title-tag');

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support('post-thumbnails');

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support(
			'html5',
			array(
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
			)
		);

		/*
		 * Enable support for Post Formats.
		 *
		 * See: https://codex.wordpress.org/Post_Formats
		 */
		add_theme_support(
			'post-formats',
			array(
				'aside',
				'image',
				'video',
				'quote',
				'link',
				'gallery',
				'audio',
			)
		);

		add_theme_support('menus');


		add_filter('excerpt_length', function ($length) {
			return Util::array_value(get_fields('site_options'), ['general', 'excerpt', 'max_words']) ?: $length;
		}, 999);
	}

	public static function includes()
	{
		wp_enqueue_style('site-stylesheet', get_template_directory_uri() . '/dist/css/main.css', [], time());
		wp_enqueue_script('site-js', get_template_directory_uri() . '/dist/js/main.js', [], time());
		wp_dequeue_style('wp-block-library');
		wp_dequeue_style('wp-block-library-theme');
	}

	public static function locations($paths)
	{
		$paths['views'] = [
			get_stylesheet_directory() . '/views',
		];
		$paths['partial'] = [get_stylesheet_directory() . '/views/partial'];
		$paths['blocks'] = [get_stylesheet_directory() . '/views/blocks'];
		$paths['parts'] = [get_stylesheet_directory() . '/views/parts'];

		return $paths;
	}

	public static function add_password_protection($template)
	{
		global $post;

		if (!empty($post) && post_password_required($post->ID)) {
			$template = locate_template([
				'password-protected.php',
			]) ?: $template;
		}

		return $template;
	}
}
