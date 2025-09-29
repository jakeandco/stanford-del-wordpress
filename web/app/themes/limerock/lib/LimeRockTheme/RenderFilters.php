<?php

namespace LimeRockTheme;

use Timber\Timber;

/**
 * Class RenderFilters
 */
class RenderFilters
{
	public static function init()
	{
		add_action('init', 'LimeRockTheme\RenderFilters::oembed_filters');
		add_filter('timber/post/excerpt/defaults', 'LimeRockTheme\RenderFilters::excerpt_filters', 10, 2);
	}

	public static function excerpt_filters($defaults)
	{
		return array_merge(
			$defaults,
			[
				'read_more' => false
			]
		);
	}

	public static function oembed_filters()
	{
		// self::oembed_filter(
		// 	'brown-panopto',
		// 	'#https://(?:www\.)?brown\.hosted\.panopto\.com/Panopto/Pages/(?:Viewer|Embed)\.aspx\?id=(.*)#i',
		// 	'https://brown.hosted.panopto.com/Panopto/Pages/Embed.aspx?id=%1$s'
		// );

		self::oembed_filter(
			'archiveorg',
			'#https?://(?:www\.)?archive\.org/(?:embed|details)/(.*)#i',
			'https://archive.org/embed/%1$s'
		);
	}

	private static function oembed_filter($name, $filter_matcher, $filter_target)
	{
		wp_embed_register_handler(
			$name,
			$filter_matcher,
			function ($matches, $attr) use ($name, $filter_target) {
				return Timber::compile('@partial/oembed.twig', [
					'embed_class' => $name,
					'embed_url' => sprintf($filter_target, esc_attr($matches[1])),
					'attr' => $attr,
				]);
			}
		);
	}
}
