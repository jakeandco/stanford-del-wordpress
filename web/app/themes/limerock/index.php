<?php

/**
 * The main template file
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since   Timber 0.1
 */

$context          = Timber\Timber::context();
$templates        = array('index.twig');

if (is_home()) {
	$context['posts'] = Timber\Timber::get_posts([
		'post_type' => 'post',
		'post_status' => 'publish',
		'paged' => get_query_var('paged'),
	]);
	array_unshift($templates, 'home.twig');
}
if (is_front_page()) {
	array_unshift($templates, 'front-page.twig');
}

Timber\Timber::render($templates, $context);
