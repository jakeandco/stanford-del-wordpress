<?php

namespace LimeRockTheme;

use Timber;
use JakeAndCo;

/**
 * Class Menus
 */
class Menus
{
	public static function init()
	{
		add_filter('timber/context', 'LimeRockTheme\Menus::add_to_context');

		add_filter('timber/menu/item_objects', 'LimeRockTheme\Menus::menu_item_objects');
		add_action('init', 'LimeRockTheme\Menus::register_locations');
	}

	public static function register_locations()
	{
		register_nav_menus([
			'main' => __('Main Menu', 'jakeandco-base-theme'),
			'footer' => __('Footer Menu', 'jakeandco-base-theme'),
		]);
	}

	public static function menu_item_objects($menu_items)
	{
		global $post;
		$post_permalink = get_permalink($post);

		return array_map(
			function ($x) use ($post_permalink) {
				if (
					!is_front_page() &&
					empty($x->current_item_ancestor) &&
					str_starts_with($post_permalink, $x->url)
				) {
					$x->current_item_ancestor = true;
				}

				return $x;
			},
			$menu_items
		);
	}

	public static function add_to_context($context)
	{
		$context['menus'] = [
			'main' => Timber\Timber::get_menu('main'),
			'footer' => Timber\Timber::get_menu('footer'),
		];

		return $context;
	}
}
