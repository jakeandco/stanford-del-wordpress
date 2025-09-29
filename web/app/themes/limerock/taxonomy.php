<?php

/**
 * The template for displaying Archive pages.
 *
 * Used to display archive-type pages if nothing more specific matches a query.
 * For example, puts together date-based pages if no date.php file exists.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since   Timber 0.2
 */
use Timber\Timber;

$templates = array('taxonomy.twig', 'index.twig');

$context = Timber::context();

$term_id = get_queried_object()->term_id;

$term = Timber::get_term($term_id);

if ($term) {
	array_unshift($templates, 'taxonomies/' . $term->taxonomy . '.twig');
	array_unshift($templates, 'taxonomies/' . $term->taxonomy . '-' . $term->slug . '.twig');
}
$fields = get_fields($term);

$context['fields'] = $fields;

$context['posts'] = $term->posts();

Timber::render($templates, $context);
