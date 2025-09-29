<?php

namespace LimeRockTheme;

use ACFComposer;
use LimeRockTheme;

/**
 * Class Blocks
 */
class Blocks
{
	public static $added_block_types = [];

	public static function init()
	{
		add_action('init', 'LimeRockTheme\Blocks::register_acf_blocks');
		add_filter('allowed_block_types_all', 'LimeRockTheme\Blocks::filter_allowed_block_types');
	}

	public static function register_acf_blocks()
	{
		LimeRockTheme\Util::search_directory(
			'/views/blocks',
			fn($item) => $item->isDir() && file_exists($item->getPathname() . '/block.json'),
			function ($item) {
				// Register the block given the directory name within the blocks
				// directory.
				$registration_response = register_block_type($item->getPathname());

				if (file_exists($item->getPathname() . '/acf-composed.json')) {
					ACFComposer\ACFComposer::registerFieldGroup(
						json_decode(
							file_get_contents($item->getPathname() . '/acf-composed.json'),
							true
						)
					);
				}

				if ($registration_response) {
					static::$added_block_types = array_merge(static::$added_block_types, [$registration_response->name]);
				}
			}
		);
	}

	public static function filter_allowed_block_types()
	{
		return array_values(
			array_filter(
				static::$added_block_types,
				fn($block_name) => $block_name !== 'limerock/example-block'
			)
		);
	}
}
