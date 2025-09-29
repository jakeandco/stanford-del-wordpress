<?php

namespace LimeRockTheme;

/**
 * Class Taxonomies
 */
class Taxonomies
{
	public static function init()
	{
		add_action('init', 'LimeRockTheme\Taxonomies::register_taxonomies');
	}

	/**
	 * This is where you can register custom taxonomies.
	 */
	public static function register_taxonomies() {}
}
