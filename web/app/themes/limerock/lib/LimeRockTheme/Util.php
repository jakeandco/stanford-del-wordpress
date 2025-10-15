<?php

namespace LimeRockTheme;

use DirectoryIterator;
use JakeAndCo;

if (!defined('ABSPATH')) {
	exit;
}

// based on original work from the PHP Laravel framework
if (!function_exists('str_contains')) {
	function str_contains($haystack, $needle)
	{
		return $needle !== '' && mb_strpos($haystack, $needle) !== false;
	}
}


/**
 * Class Util
 */
class Util
{
	public static function init() {}

	public static function search_directory(string $search_dir, callable $search_matcher, callable $matching_callback)
	{

		foreach (new DirectoryIterator(realpath(get_stylesheet_directory() . $search_dir)) as $item) {
			try {
				if (!$item->isDot() && call_user_func($search_matcher, $item)) {
					call_user_func($matching_callback, $item);
				}
			} catch (\Throwable $th) {
				static::console_log("Error in \"LimeRockTheme\Util::search_directory\" while acting on \"$item\" file!:", $th->getMessage());
			}
		}
	}

	/*
	|--------------------------------------------------------------------------
	| Get Current URL
	|--------------------------------------------------------------------------
	|
	| Get the current url
	|
	*/
	public static function get_current_url()
	{
		$full_url = (static::array_value($_SERVER, 'HTTPS') === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$current_url = explode("?", $full_url)[0];
		return $current_url;
	}

	/*
	|--------------------------------------------------------------------------
	| Get Current URL
	|--------------------------------------------------------------------------
	|
	| Get the current url
	|
	*/
	public static function is_current_url(string $comparison_url)
	{
		$current_url = static::get_current_url();
		if (str_contains($current_url, '/wp/wp-admin/post.php')) {
			$comparison_post = url_to_postid($comparison_url);
			return str_contains($current_url, 'post=' . $comparison_post);
		}

		return rtrim($current_url ?? '', '/') == rtrim($comparison_url ?? '', '/');
	}


	static public function array_value($args, $key)
	{
		if ($args && is_array($args)) {
			if (is_array($key)) {
				$next_args = $args;
				foreach ($key as $subkey) {
					if (is_array($next_args) && array_key_exists($subkey, $next_args)) {
						$next_args = $next_args[$subkey];
					} else {
						return null;
					}
				}
				return $next_args;
			} else if (
				array_key_exists($key, $args)
			) {
				return $args[$key];
			}
		}
		return null;
	}


	/**
	 * Summary of get_link_type
	 * @param string $url
	 * @return string "anchor" | "internal" | "external"
	 */
	public static function get_link_type($url)
	{
		$site_url = get_site_url();

		// Parse URLs to hostnames and paths
		$link_parts = parse_url($url);
		$site_parts = parse_url($site_url);

		// Check if the URL is a fragment identifier
		if (substr($url, 0, 1) == '#') {
			return 'anchor';
		}

		if (substr($url, 0, 1) == '/') {
			return 'internal';
		}

		// Check if link is on-site
		if (Util::array_value($link_parts, 'host') == $site_parts['host']) {

			// Construct URLs without fragments for comparison
			$link_url_no_frag = $link_parts['scheme'] . '://' . $link_parts['host'] . $link_parts['path'];
			$site_url_no_frag = $site_parts['scheme'] . '://' . $site_parts['host'] . $_SERVER['REQUEST_URI'];

			// Check if link is on-page
			if ($link_url_no_frag == $site_url_no_frag) {
				return 'anchor';
			} else {
				return 'internal';
			}
		} else {

			return 'external';
		}
	}
	public static function console_log(...$args)
	{
?>
		<script>
			console.log(
				<?= join(', ', array_map(fn($arg) => json_encode($arg), $args)) ?>
			);
		</script>
<?php
	}
}
