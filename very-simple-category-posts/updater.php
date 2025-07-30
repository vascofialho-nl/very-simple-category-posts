<?php
// Check if this file is being accessed within the WordPress environment and exit if not.
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }

/**
 * GitHub Plugin Updater
 * Allows automatic updates from a GitHub release.
 */

defined('ABSPATH') || exit;

add_filter('pre_set_site_transient_update_plugins', 'vjfnlccp_check_for_plugin_update');

function vjfnlccp_check_for_plugin_update($transient) {
	if (empty($transient->checked)) {
		return $transient;
	}

	$plugin_slug = plugin_basename(__FILE__);
	$plugin_data = get_plugin_data(__FILE__);
	$current_version = $plugin_data['Version'];
	$remote_version = '1.0.1'; // Update manually or pull from GitHub API if needed

	$update_url = 'https://github.com/vascofmdc/very-simple-category-posts/releases/download/' . $remote_version . '/very-simple-category-posts.zip';

	if (version_compare($current_version, $remote_version, '<')) {
		$transient->response[$plugin_slug] = (object) array(
			'slug'        => 'very-simple-category-posts',
			'plugin'      => $plugin_slug,
			'new_version' => $remote_version,
			'url'         => 'https://github.com/vascofmdc/very-simple-category-posts',
			'package'     => $update_url,
		);
	}

	return $transient;
}
