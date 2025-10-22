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
