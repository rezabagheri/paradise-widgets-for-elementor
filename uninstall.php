<?php
/**
 * Uninstall handler for Paradise Widgets for Elementor.
 *
 * Removes the plugin's own settings/configuration options. User-created
 * content (FAQ posts, user-profile meta) is intentionally left intact so
 * nothing is lost if the plugin is later re-installed.
 *
 * @package Paradise_Widgets_For_Elementor
 */

// Exit if not called by WordPress during an actual uninstall.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$paradise_ew_options = [
	'paradise_site_info',
	'paradise_ew_settings',
	'paradise_custom_fields',
];

if ( is_multisite() ) {
	// Clean up on every site of the network.
	$paradise_ew_site_ids = get_sites( [ 'fields' => 'ids', 'number' => 0 ] );
	foreach ( $paradise_ew_site_ids as $paradise_ew_site_id ) {
		switch_to_blog( $paradise_ew_site_id );
		foreach ( $paradise_ew_options as $paradise_ew_option ) {
			delete_option( $paradise_ew_option );
		}
		restore_current_blog();
	}
} else {
	foreach ( $paradise_ew_options as $paradise_ew_option ) {
		delete_option( $paradise_ew_option );
	}
}
