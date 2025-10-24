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
        $paged = get_query_var('paged') ?: 1;

        $result = limerock_get_work_query_args([
            'featured_post' => $featured_post,
            'paged'         => $paged,
            'search'   => $_GET['search'] ?? '',
            'type'          => $_GET['type'] ?? [],
            'research_area' => $_GET['research_area'] ?? [],
            'sort'          => $_GET['sort'] ?? '',
        ]);

        $wp_query = new WP_Query($result['query_args']);
        $context['posts'] = new Timber\PostQuery($wp_query);
        $context['hide_featured'] = $result['hide_featured'];
        $context['ajax_url'] = esc_url(get_permalink());
        $context['research_terms'] = get_research_terms_for_work();
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

function limerock_get_work_query_args($args = []) {
    $featured_post = $args['featured_post'] ?? null;
    $paged         = $args['paged'] ?? 1;

    $featured_id = $featured_post ? [$featured_post->ID] : [];

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
                'key'     => 'is_external_link',
                'compare' => 'NOT EXISTS',
            ],
            [
                'key'     => 'is_external_link',
                'value'   => '0',
                'compare' => '=',
            ],
        ],
    ];

    $search_query    = sanitize_text_field($args['search'] ?? '');
    $type_filter     = array_filter((array) ($args['type'] ?? []));
    $research_filter = array_filter((array) ($args['research_area'] ?? []));
    $sort_filter     = sanitize_text_field($args['sort'] ?? '');

    $hide_featured = false;

    // Apply search
    if ($search_query) {
        $query_args['s'] = $search_query;
        $hide_featured = true;
    }

    // Apply type filter
    if ($type_filter) {
        $query_args['post_type'] = $type_filter;
        $hide_featured = true;
    }

    // Apply research area filter
    if ($research_filter) {
        $query_args['tax_query'] = [
            [
                'taxonomy' => 'tax-research-area',
                'field'    => 'slug',
                'terms'    => $research_filter,
            ],
        ];
        $hide_featured = true;
    }

    // Apply sorting
    $sort_options = [
        'oldest' => ['orderby' => 'date',  'order' => 'ASC'],
        'a_z'    => ['orderby' => 'title', 'order' => 'ASC'],
        'z_a'    => ['orderby' => 'title', 'order' => 'DESC'],
    ];
    if (isset($sort_options[$sort_filter])) {
        $query_args = array_merge($query_args, $sort_options[$sort_filter]);
        $hide_featured = true; // Hide featured if not "newest" default sorting
    }

    // Remove featured if needed
    if ($hide_featured) {
        unset($query_args['post__not_in']);
    }

    return ['query_args' => $query_args, 'hide_featured' => $hide_featured];
}

function get_research_terms_for_work() {
    $posts = get_posts([
        'post_type'      => ['post', 'project', 'publication'],
        'fields'         => 'ids',
        'posts_per_page' => -1,
    ]);

    if (empty($posts)) {
        return [];
    }

    return get_terms([
        'taxonomy'   => 'tax-research-area',
        'hide_empty' => true,
        'object_ids' => $posts,
    ]);
}


add_filter('timber/context', 'add_to_context');

function add_to_context($context) {
    if (!empty($_GET)) $context['request']['get'] = $_GET;
    if (!empty($_POST)) $context['request']['post'] = $_POST;

    return $context;
}

add_filter('timber/twig', function($twig) {
    $twig->addFilter(new \Twig\TwigFilter('file_get_contents_raw', function($url) {
        $uploads = wp_upload_dir();
        $baseurl = $uploads['baseurl'];   // URL до папки uploads
        $basedir = $uploads['basedir'];   // Фізичний шлях до папки uploads

        $relative_path = str_replace($baseurl, '', $url); // /2025/10/research-area-icon-1.svg
        $path = $basedir . $relative_path;

        return file_exists($path) ? file_get_contents($path) : '';
    }));
    return $twig;
});
