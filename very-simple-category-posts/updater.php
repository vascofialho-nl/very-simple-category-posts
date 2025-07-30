<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GitHub Plugin Updater
 * Automatically checks GitHub for new plugin releases and enables updates.
 */

// Define the main plugin file path
define( 'VJFNLCCP_PLUGIN_FILE', plugin_dir_path( __FILE__ ) . 'very-simple-category-posts.php' );

// Hook into update checks
add_filter( 'pre_set_site_transient_update_plugins', 'vjfnlccp_check_for_plugin_update' );
add_filter( 'plugins_api', 'vjfnlccp_plugin_info', 20, 3 );

/**
 * Get current plugin version from plugin header
 */
function vjfnlccp_get_local_version() {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	$data = get_plugin_data( WP_PLUGIN_DIR . '/very-simple-category-posts/very-simple-category-posts.php' );
	return $data['Version'];
}

/**
 * Get latest version tag from GitHub Releases API
 */
function vjfnlccp_get_latest_github_release() {
	$response = wp_remote_get( 'https://api.github.com/repos/vascofialho-nl/very-simple-category-posts/releases/latest', array(
		'headers' => array( 'User-Agent' => 'WordPress/' . get_bloginfo( 'version' ) )
	) );

	if ( is_wp_error( $response ) ) {
		return false;
	}

	$data = json_decode( wp_remote_retrieve_body( $response ) );

	if ( isset( $data->tag_name ) ) {
		return ltrim( $data->tag_name, 'v' ); // Strip 'v' if used like v1.0.2
	}

	return false;
}

/**
 * Add update data to plugin transient
 */
function vjfnlccp_check_for_plugin_update( $transient ) {
	if ( empty( $transient->checked ) ) {
		return $transient;
	}

	$plugin_slug     = 'very-simple-category-posts/very-simple-category-posts.php';
	$current_version = vjfnlccp_get_local_version();
	$remote_version  = vjfnlccp_get_latest_github_release();

	if ( ! $remote_version || version_compare( $current_version, $remote_version, '>=' ) ) {
		return $transient;
	}

	$update_url = 'https://github.com/vascofialho-nl/very-simple-category-posts/releases/download/' . $remote_version . '/very-simple-category-posts.zip';

	$transient->response[ $plugin_slug ] = (object) array(
		'slug'        => 'very-simple-category-posts',
		'plugin'      => $plugin_slug,
		'new_version' => $remote_version,
		'url'         => 'https://github.com/vascofialho-nl/very-simple-category-posts',
		'package'     => $update_url,
	);

	return $transient;
}

/**
 * Provide plugin details for the updater popup
 */
function vjfnlccp_plugin_info( $res, $action, $args ) {
	if ( $action !== 'plugin_information' ) return $res;
	if ( $args->slug !== 'very-simple-category-posts' ) return $res;

	$remote_version = vjfnlccp_get_latest_github_release();
	if ( ! $remote_version ) return $res;

	$update_url = 'https://github.com/vascofialho-nl/very-simple-category-posts/releases/download/' . $remote_version . '/very-simple-category-posts.zip';

	$res = (object) array(
		'name'           => 'Very Simple Category Posts',
		'slug'           => 'very-simple-category-posts',
		'version'        => $remote_version,
		'author'         => '<a href="https://vascofialho.nl">vascofmdc</a>',
		'homepage'       => 'https://github.com/vascofialho-nl/very-simple-category-posts',
		'download_link'  => $update_url,
		'trunk'          => $update_url,
		'sections'       => array(
			'description' => 'Displays all posts in the current category and highlights the current post.',
			'changelog'   => '<p><strong>' . esc_html( $remote_version ) . '</strong> – See GitHub for details.</p>',
		),
	);

	return $res;
}
