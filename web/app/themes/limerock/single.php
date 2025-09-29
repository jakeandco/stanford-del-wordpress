<?php

/**
 * The Template for displaying all single posts
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */

use function Env\env;

$context         = Timber::context();
$timber_post     = Timber::get_post();
$context['post'] = $timber_post;

if ($timber_post->post_type == 'styleguide') {
	if (! is_user_logged_in() && env('WP_ENV') == 'production') {
		global $wp_query;
		$wp_query->set_404();
		status_header(404);
		exit;
	}

	$context['styleguide'] = true;
}

if (post_password_required($timber_post->ID)) {
	Timber::render('single-password.twig', $context);
} else {
	Timber::render(array('single-' . $timber_post->ID . '.twig', 'single-' . $timber_post->post_type . '.twig', 'single-' . $timber_post->slug . '.twig', 'single.twig'), $context);
}
