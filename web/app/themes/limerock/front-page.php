<?php
/**
 * The template for displaying the front page.
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */

$context = Timber::context();
$timber_post     = Timber::get_post();
$context['post'] = $timber_post;
$context['is_front_page'] = true;
Timber::render(['front-page.twig', 'page-' . $timber_post->post_name . '.twig', 'page.twig'], $context);
