<?php

/**
 * Render callback to prepare and display a registered block using Timber.
 *
 * @param    array    $block The block block.
 * @param    string   $content The block content.
 * @param    bool     $is_preview Whether or not the block is being rendered for editing preview.
 * @param    int      $post_id The current post being edited or viewed.
 * @param    WP_Block $wp_block The block instance (since WP 5.5).
 * @return   void
 */
function LimeRockTheme_block_render_callback($block, $content = '', $is_preview = false, $post_id = 0, $wp_block = null)
{
	// Create the slug of the block using the name property in the block.json.
	$slug = str_replace('acf/', '', $block['name']);
	$slug = str_replace('limerock/', '', $block['name']);

	$context = Timber\Timber::context();

	// Store block attributes.
	$context['post_id']    = $post_id;
	$context['slug']       = $slug;

	// Store whether the block is being rendered in the editor or on the frontend.
	$context['is_preview'] = $is_preview;

	// Store field values. These are the fields from your ACF field group for the block.
	$context['fields'] = get_fields();
	$context['field_objects'] = get_field_objects();

    if ($slug === 'work-archive') {
        $featured_post = $context['fields']['featured_work'] ?? null;
        $featured_id   = $featured_post ? [$featured_post->ID] : [];
        $paged         = get_query_var('paged') ?: 1;

        $query_args = [
            'post_type'      => ['post', 'project', 'publication'],
            'posts_per_page' => 2,
            'paged'          => $paged,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'post__not_in'   => $featured_id,
            'meta_query'     => [
                'relation' => 'OR',
                [
                    'key'     => 'external_link',
                    'compare' => 'NOT EXISTS',
                ],
                [
                    'key'     => 'external_link',
                    'value'   => '',
                    'compare' => '=',
                ],
            ],
        ];

        $search_query    = sanitize_text_field($_GET['work_search'] ?? '');
        $type_filter     = array_filter((array) ($_GET['type'] ?? []));
        $research_filter = array_filter((array) ($_GET['research_area'] ?? []));
        $sort_filter     = sanitize_text_field($_GET['sort'] ?? '');

        $hide_featured = false;

        // search
        if ($search_query) {
            $query_args['s'] = $search_query;
            $hide_featured    = true;
        }

        // type filter
        if ($type_filter && !in_array('all', $type_filter, true)) {
            $query_args['post_type'] = $type_filter;
            $hide_featured            = true;
        }

        // research area filter
        if ($research_filter && !in_array('all', $research_filter, true)) {
            $query_args['tax_query'] = [
                [
                    'taxonomy' => 'tax-research-area',
                    'field'    => 'slug',
                    'terms'    => $research_filter,
                ],
            ];
            $hide_featured = true;
        }

        // sorting
        $sort_options = [
            'oldest' => ['orderby' => 'date',  'order' => 'ASC'],
            'a_z'    => ['orderby' => 'title', 'order' => 'ASC'],
            'z_a'    => ['orderby' => 'title', 'order' => 'DESC'],
        ];
        if (isset($sort_options[$sort_filter])) {
            $query_args = array_merge($query_args, $sort_options[$sort_filter]);
        }

        // remove featured post when not needed
        if ($hide_featured) {
            unset($query_args['post__not_in']);
        }

        $wp_query = new WP_Query($query_args);
        $context['posts'] = new Timber\PostQuery($wp_query);
        $context['hide_featured'] = $hide_featured;
    }

	if (! empty($block['data']['is_example'])) {
		$context['is_example'] = true;
		$context['fields'] = $block['data'];
	}

	$classes =  [];

	$block['className'] = implode(' ', $classes);

	$context['block']      = $block;
	// Render the block.
	Timber\Timber::render(
		'@blocks/' . $slug . '/' . $slug . '.twig',
		$context
	);
}

add_filter('timber/context', 'add_to_context');

function add_to_context($context) {
    if (!empty($_GET)) $context['request']['get'] = $_GET;
    if (!empty($_POST)) $context['request']['post'] = $_POST;

    return $context;
}
