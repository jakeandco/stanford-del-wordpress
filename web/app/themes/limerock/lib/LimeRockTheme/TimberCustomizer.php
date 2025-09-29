<?php

namespace LimeRockTheme;

use Timber\Timber;
use Timber\URLHelper;

/**
 * Class TimberCustomizer
 */
class TimberCustomizer
{
	public static function init()
	{
		add_filter('timber/context', 'LimeRockTheme\TimberCustomizer::add_to_context');
	}

	/**
	 * Attempts to build a rough replica of JavaScript's window.location object.
	 */
	private static function buildCurrentPageContext()
	{
		$urlHelper = new URLHelper();

		$current_url = $urlHelper->get_current_url();
		$scheme = $urlHelper->get_scheme();
		$host = $urlHelper->get_host();
		$port = parse_url($current_url, PHP_URL_PORT) ?: ($scheme === 'https' ? 443 : 80);
		$pathname = $urlHelper->get_rel_url($current_url);
		$query = parse_url($current_url, PHP_URL_QUERY);

		return [
			'protocol' => $scheme . ':', // Add colon to match JavaScript's window.location
			'host' => $host,             // Hostname with port (if applicable)
			'hostname' => $host,         // Hostname without port
			'port' => (string) $port,    // Port as a string
			'pathname' => $pathname,     // Pathname of the URL
			'search' => $query ? '?' . $query : '', // Query string prefixed with "?"
			'origin' => $scheme . '://' . $host . ($port && !in_array($port, [80, 443]) ? ':' . $port : ''), // Origin (protocol + host + optional port)
			'url' => $current_url,       // Full URL
		];
	}

	/**
	 * This is where you add some context
	 *
	 * @param string $context context['this'] Being the Twig's {{ this }}.
	 */
	public static function add_to_context($context)
	{
		$context['site']  = WordpressSite::$instance;
		$context['UrlHelper'] = new URLHelper();
		$context['current_page'] = self::buildCurrentPageContext();
		$context['archives']  = [
		];

		$context['options']  = [
			'footer' => get_fields('footer_options'),
			'site' => get_fields('site_options'),
		];

		return $context;
	}
}
