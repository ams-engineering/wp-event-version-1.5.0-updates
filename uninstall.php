<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       //allmarketingsolutions.co.uk
 * @since      1.0.0
 *
 * @package    Wp_Events
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Deleting All Options Saved By Plugin
 *
 * @since 1.0.0
*/
if( !function_exists('wpe_delete_all_options') ) {
	function wpe_delete_all_options() {
		$options_arr = [
			'wpe_settings',
			'wpe_maps_settings',
			'wpe_forms_settings',
			'wpe_display_settings',
			'wpe_mail_settings',
		];
		foreach ( $options_arr as $option ) {
			delete_option( $option );
		}
	}
}


/**
 * Dropping Tables Created On Activation
 *
 * @since 1.0.0
*/

if( !function_exists( 'wpe_drop_tables' ) ) {
	function wpe_drop_tables() {
		global $wpdb;

		$sub_table = $wpdb->prefix . 'events_subscribers';
		$reg_table = $wpdb->prefix . 'events_registration';

		$sql   = 'DROP TABLE IF EXISTS '.$sub_table;
		$query = 'DROP TABLE IF EXISTS '.$reg_table;

		$wpdb->query( $sql );
		$wpdb->query( $query );
	}
}

/**
 * Deleting all Events
 *
 * @since 1.0.0
*/

if( !function_exists( 'wpe_delete_all_events' ) ) {
	function wpe_delete_all_events() {
		global $wpdb;

		$posts = get_posts( array(
				'numberposts' => - 1,
				'post_type'   => 'wp_events',
				'post_status' => 'any'
			)
		);

		foreach ( $posts as $post ){
			wp_delete_post( $post->ID, true );
		}
	}
}

/**
 * Deleting all Categories
 *
 * @since 1.1.1
 */
if( !function_exists( 'wpe_delete_all_categories' ) ) {
	function wpe_delete_all_categories() {
		global $wpdb;
		$taxonomy = 'wpevents-category';

		$query = 'SELECT t.name, t.term_id
				FROM ' . $wpdb->terms . ' AS t
				INNER JOIN ' . $wpdb->term_taxonomy . ' AS tt
				ON t.term_id = tt.term_id
				WHERE tt.taxonomy = "' . $taxonomy . '"';

		$terms = $wpdb->get_results( $query );

		foreach ( $terms as $term ) {
			wp_delete_term( $term->term_id, $taxonomy );
		}
	}
}

if ( ! function_exists( 'wpe_is_data_removal_allowed' ) ) {

	/**
	 * checks if allowed to remove all data on plugin uninstall
	 *
	 * @return bool
	 * @since 1.1.10
	 */
	function wpe_is_data_removal_allowed() {
		$general_options = get_option( 'wpe_settings' );
		if ( ! empty( $general_options['remove_on_uninstall'] ) ) {
			return true;
		}

		return false;
	}
}

/**
 * Delete plugin version on uninstall.
 *
 * @since 1.4.0
*/

if( !function_exists( 'wpe_delete_version' ) ) {
	function wpe_delete_version() {
		delete_option( 'WP_EVENTS_VERSION' );
	}
}

/**
 * Driver Function
*/
if ( ! function_exists( 'wpe_uninstall_driver' ) ) {
	function wpe_uninstall_driver() {
		if ( wpe_is_data_removal_allowed() === false ) { //if not allowed return
			wpe_delete_version();
			return;
		}
		wpe_delete_version();
		wpe_delete_all_events();
		wpe_delete_all_categories();
		wpe_delete_all_options();
		wpe_drop_tables();
	}
}


wpe_uninstall_driver();