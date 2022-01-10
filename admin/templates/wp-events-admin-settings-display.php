<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       //allmarketingsolutions.co.uk
 * @since      1.0.0
 *
 * @package    Wp_Events
 * @subpackage Wp_Events/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<?php
    settings_errors();
    global $wpe_active_tab;
    $wpe_active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general'; ?>
    <h1>WP Events Settings</h1>
    <form method="post" action="options.php">
    <h2 class="nav-tab-wrapper">
        <?php
        do_action( 'wp_events_settings_tab' );
        ?>
    </h2>
    <div class="wpe-settings-content wrap">
        <?php
        do_action( 'wp_events_settings_content' );
        ?>
    </div>
        <?php submit_button();?>
    </form>
