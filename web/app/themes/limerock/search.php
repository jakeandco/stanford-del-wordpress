<?php
/**
 * Search results page
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since   Timber 0.1
 */

$templates    = array('search.twig', 'archive.twig', 'index.twig');
$context      = Timber::context();
$search_query = get_search_query();

$context['search_title'] = !empty($search_query) ? ("Search results for \"$search_query\"") : 'Search';
$context['posts']        = Timber::get_posts();

Timber::render($templates, $context);
