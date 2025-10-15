<?php

namespace JakeAndCo;

use LimeRockTheme\Util;

if (!defined('ABSPATH')) {
	exit;
}


class Format
{

	public function __construct()
	{
	}

	/*
	|--------------------------------------------------------------------------
	| Slugify
	|--------------------------------------------------------------------------
	|
	| Convert any string to a slug.
	|
	*/

	public static function to_slug($string)
	{
		return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string) ?? ''));
	}

	/*
	|--------------------------------------------------------------------------
	| Convert Number to Ordinal Word
	|--------------------------------------------------------------------------
	*/

	public static function number_to_ordinal_word($num)
	{

		// Adding support up to 10.
		$words = ['First', 'Second', 'Third', 'Fourth', 'Fifth', 'Sixth', 'Seventh', 'Eighth', 'Ninth', 'Tenth'];

		return Util::array_value($words, $num - 1) ?: $num;
	}


	/*
	|--------------------------------------------------------------------------
	| Phone Number Output
	|--------------------------------------------------------------------------
	|
	| Takes a phone number in varying formats and outputs it in standardized
	| ways for various use cases.
	|
	*/

	public static function format_phone_number($phone_number, $format = 'full')
	{

		// Remove all characters except digits
		$digits = preg_replace('/[^0-9]/', '', $phone_number);

		// Check if the resulting string has exactly 10 digits (U.S. phone numbers)
		if (strlen($digits) !== 10) {
			return 'Invalid phone number';
		}

		// Format the phone number based on the $format parameter
		switch ($format) {
			case 'full':
				return '(' . substr($digits, 0, 3) . ') ' . substr($digits, 3, 3) . '-' . substr($digits, 6, 4);
			case 'dash':
				return substr($digits, 0, 3) . '-' . substr($digits, 3, 3) . '-' . substr($digits, 6, 4);
			case 'plain':
				return $digits;
			default:
				return 'Invalid format';
		}
	}

	/**
	 * Summary of classnames
	 * @param string[] class names
	 * @return string "anchor" | "internal" | "external"
	 */
	public static function classnames($classnames)
	{
		return implode(
			" ",
			array_filter($classnames)
		);
	}

	/**
	 * Summary of attributes
	 * @param string[] attributes map
	 * @return string
	 */
	public static function attributes($attributes = [])
	{
		$class_attr = $attributes['class'];
		$attributes['class'] = is_array($class_attr)
			? static::classnames($class_attr)
			: $class_attr;

		$with_equal = [];

		foreach ($attributes as $key => $value) {
			if (!empty($value)) {
				$with_equal[] = $key . '="' . $value . '"';
			}
		}

		return implode(" ", $with_equal);
	}
}

new Format();
