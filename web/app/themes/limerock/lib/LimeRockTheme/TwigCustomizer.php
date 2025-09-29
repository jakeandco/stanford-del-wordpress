<?php

namespace LimeRockTheme;

use LimeRockTheme\Util;
use DateTimeImmutable;
use Twig;
use Timber;

/**
 * Class TwigCustomizer
 */
class TwigCustomizer
{
	public static function init()
	{
		add_filter('timber/twig', 'LimeRockTheme\TwigCustomizer::add_to_twig');
		add_filter('timber/twig/environment/options', 'LimeRockTheme\TwigCustomizer::update_twig_environment_options');
	}


	/**
	 * This is where you can add your own functions to twig.
	 *
	 * @param Twig\Environment $twig get extension.
	 */
	public static function add_to_twig($twig)
	{
		/**
		 * Required when you want to use Twig’s template_from_string.
		 * @link https://twig.symfony.com/doc/3.x/functions/template_from_string.html
		 */
		// $twig->addExtension( new Twig\Extension\StringLoaderExtension() );

		$twig->addExtension(new Twig\Extra\Intl\IntlExtension());
		$twig->addExtension(new Twig\Extra\Html\HtmlExtension());
		$twig->addFilter(new Twig\TwigFilter('console_log', fn(...$args) => Util::console_log(...$args)));

		$twig->addFilter(new Twig\TwigFilter(
			'datetime',
			function($datetime, $to_format = 'Y.m.d', $from_format = 'd/m/Y') {
				if (Util::array_value($datetime, 'prefix') == 'acf') {
					$from_format = Util::array_value($datetime, 'return_format');
					$datetime = Util::array_value($datetime, 'value');
				}

				$formatter = DateTimeImmutable::createFromFormat($from_format, $datetime);;
				return $formatter->format($to_format);
			}
		));

		$twig->addFunction(new Twig\TwigFunction('get_post_type_archive', function ($post_type) {
			if (!empty($post_type) && post_type_exists($post_type)) {
				$object        = get_post_type_object($post_type);

				$archive_link  = get_post_type_archive_link($post_type);
				$archive_title = $object->labels->menu_name;
				$archive_path  = wp_make_link_relative($archive_link);


				$archive_post_id = url_to_postid($archive_link);
				if (!empty($archive_post_id)) {
					$archive_post  = Timber\Timber::get_post($archive_post_id);
					$archive_post_id  = $archive_post->id;

					$archive_title = $archive_post->title;
					$archive_link  = $archive_post->link;
					$archive_path  = $archive_post->path;
				}

				return [
					'post_id' => $archive_post_id,
					'path'    => $archive_path,
					'link'    => $archive_link,
					'title'   => $archive_title,
				];
			}
		}));

		$twig->addFunction(new Twig\TwigFunction('global_context', function ($context_key = null) {
			$ctx = Timber\Timber::context();
			if ($context_key) {
				return Util::array_value($ctx, is_string($context_key) ? explode('.', $context_key) : $context_key);
			}
			return $ctx;
		}));

		return $twig;
	}

	/**
	 * Updates Twig environment options.
	 *
	 * @link https://twig.symfony.com/doc/2.x/api.html#environment-options
	 *
	 * \@param array $options An array of environment options.
	 *
	 * @return array
	 */
	public static function update_twig_environment_options($options)
	{
		return $options;
	}
}
