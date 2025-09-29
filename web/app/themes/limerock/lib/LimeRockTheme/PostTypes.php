<?php

namespace LimeRockTheme;
use LimeRockTheme;

require_once 'PostType/PostTypeClass.php';

/**
 * Class PostTypes
 */
class PostTypes
{
	public static function init()
	{
		$post_types = [];

		LimeRockTheme\Util::search_directory(
			'/lib/LimeRockTheme/PostType',
			fn($item) => $item->isFile() && $item->getExtension() == 'php' && $item->getBaseName('.php') !== 'PostTypeClass',
			function ($item) use (&$post_types) {
				$post_types[] = $item->getBaseName('.php');

				$fileName = "PostType/" . $item->getFileName();

				require_once $fileName;
			}
		);

		foreach ($post_types as $post_type_name) {
			$class_path = "LimeRockTheme\\PostType\\$post_type_name";
			$class_path::init();
		}
	}
}
