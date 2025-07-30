<?php
// Check if this file is being accessed within the WordPress environment and exit if not.
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }

/**
 * GitHub Plugin Updater
 * Allows automatic updates from a GitHub release.
 */

// Define plugin file path
define( 'VJFNLCCP_PLUGIN_FILE', plugin_dir_path( __FILE__ ) . 'very-simple-category-posts.php' );

// Hook into the update check
add_filter( 'pre_set_site_transient_update_plugins', 'vjfnlccp_check_for_plugin_update' );
// Hook into the plugin info API
add_filter( 'plugins_api', 'vjfnlccp_plugin_info', 20, 3 );

function vjfnlccp_check_for_plugin_update( $transient ) {
	if ( empty( $transient->checked ) ) {
		return $transient;
	}

	$plugin_slug     = 'very-simple-category-posts/very-simple-category-posts.php';
	$current_version = '1.0.1'; // Local version in your main plugin file
	$remote_version  = '1.0.2'; // The version available on GitHub

	$update_url = 'https://github.com/vascofialho-nl/very-simple-category-posts/releases/download/' . $remote_version . '/very-simple-category-posts.zip';			

	if ( version_compare( $current_version, $remote_version, '<' ) ) {
		$transient->response[ $plugin_slug ] = (object) array(
			'slug'        => 'very-simple-category-posts',
			'plugin'      => $plugin_slug,
			'new_version' => $remote_version,
			'url'         => 'https://github.com/vascofialho-nl/very-simple-category-posts',
			'package'     => $update_url,
		);
	} 
 
	return $transient;
}

function vjfnlccp_plugin_info( $res, $action, $args ) {
	if ( $action !== 'plugin_information' ) {
		return $res;
	}

	if ( $args->slug !== 'very-simple-category-posts' ) {
		return $res;
	}

	$remote_version = '1.0.1';
	$update_url     = 'https://github.com/vascofialho-nl/very-simple-category-posts/releases/download/' . $remote_version . '/very-simple-category-posts.zip';

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
			'changelog'   => '<p><strong>1.0.1</strong> – Minor update for updater system.</p>',
		),
	);

	return $res;
}
