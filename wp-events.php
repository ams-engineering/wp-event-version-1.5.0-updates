<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              //allmarketingsolutions.co.uk
 * @since             1.0.0
 * @package           Wp_Events
 *
 * @wordpress-plugin
 * Plugin Name:       WP Events
 * Plugin URI:        wp-events
 * Description:       The only WordPress plugin to create, manage and update hassle free events
 * Version:           1.5.0
 * Requires at least: 5
 * Requires PHP:      7
 * Author:            All Marketing Solutions
 * Author URI:        //allmarketingsolutions.co.uk
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-events
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'WP_EVENTS_VERSION', '1.5.0' );

/**
 * File that contains functions related to database operations
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-events-db-actions.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp-events-activator.php
 */
function activate_wp_events() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-events-activator.php';
	Wp_Events_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wp-events-deactivator.php
 */
function deactivate_wp_events() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-events-deactivator.php';
	Wp_Events_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wp_events' );
register_deactivation_hook( __FILE__, 'deactivate_wp_events' );


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-events.php';
require plugin_dir_path( __FILE__ ) . 'includes/wp-events-global-functions.php';
require plugin_dir_path( __FILE__ ) . 'includes/wp-events-constants.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wp_events() {

	$plugin = new Wp_Events();
	$plugin->run();

}
run_wp_events();
