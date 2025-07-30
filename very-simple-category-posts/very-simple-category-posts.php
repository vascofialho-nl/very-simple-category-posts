<?php
// Check if this file is being accessed within the WordPress environment and exit if not.
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }

/*
	Plugin Name: Very Simple Category Posts
	Plugin URI: http://www.vascofialho.nl
	Description: Adds a shortcode that displays all posts in the current category and highlights the current post.
	Author: vascofmdc
	Version: 1.0.0
	Author URI: http://www.vascofialho.nl
	License: GPL-2.0-or-later
	License URI: https://www.gnu.org/licenses/gpl-2.0.html
	Text Domain: very-simple-category-posts
*/


// Include the GitHub-based plugin updater
require_once plugin_dir_path(__FILE__) . 'updater.php';

/**
 * Registers the [current_category_posts] shortcode.
 * Outputs all posts in the current category, with the current post highlighted.
 */
function vjfnlccp_posts_in_current_category_shortcode() {
	if (!is_single()) {
		return '';
	}

	global $post;
	$current_post_id = $post->ID;

	$categories = get_the_category($current_post_id);
	if (empty($categories)) {
		return 'No categories found.';
	}

	$category_id = $categories[0]->term_id;

	$args = array(
		'category__in'   => array($category_id),
		'post__not_in'   => array(),
		'posts_per_page' => -1,
		'orderby'        => 'date',
		'order'          => 'DESC',
	);

	$query = new WP_Query($args);

	if (!$query->have_posts()) {
		return 'No posts found in this category.';
	}

	add_action('wp_footer', 'vjfnlccp_enqueue_inline_styles');

	$output = '<ul class="vjfnlccp-category-post-list">';
	while ($query->have_posts()) {
		$query->the_post();
		$loop_post_id = get_the_ID();
		$current_class = ($loop_post_id === $current_post_id) ? ' class="current-post"' : '';
		$output .= '<li' . $current_class . '><a href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
	}
	wp_reset_postdata();

	$output .= '</ul>';
	return $output;
}
add_shortcode('current_category_posts', 'vjfnlccp_posts_in_current_category_shortcode');

/**
 * Outputs inline CSS for the shortcode output.
 */
function vjfnlccp_enqueue_inline_styles() {
	if (!is_single()) {
		return;
	}

	echo '
	<style>
		.vjfnlccp-category-post-list 				 { padding-left: 0; }
		.vjfnlccp-category-post-list li 			 { list-style: none; }
		.vjfnlccp-category-post-list li a:hover, 			
		.vjfnlccp-category-post-list .current-post a { font-weight: bold; }
	</style>';
}

/**
 * Adds a submenu page under "Settings" to display the readme.txt contents.
 */
function vjfnlccp_add_readme_menu() {
	add_submenu_page(
		'options-general.php',
		'Very Simple Category Posts - Info',
		'Category Posts Info',
		'manage_options',
		'vjfnlccp-readme',
		'vjfnlccp_display_readme_page'
	);
}
add_action('admin_menu', 'vjfnlccp_add_readme_menu');

/**
 * Displays the contents of readme.txt in a readable format.
 */
function vjfnlccp_display_readme_page() {
	$readme_path = plugin_dir_path(__FILE__) . 'readme.txt';

	echo '<div class="wrap"><h1>Very Simple Category Posts – Info</h1><pre style="background: #fff; padding: 1em; border: 1px solid #ccc; overflow: auto;">';

	if (file_exists($readme_path)) {
		echo esc_html(file_get_contents($readme_path));
	} else {
		echo 'readme.txt not found.';
	}

	echo '</pre></div>';
}

