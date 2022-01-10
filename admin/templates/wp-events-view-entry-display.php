<?php

/**
 * Provide a view/edit entry view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       //allmarketingsolutions.co.uk
 * @since      1.2.0
 *
 * @package    Wp_Events
 * @subpackage Wp_Events/admin/templates
 */
?>
<div class="wpe-header">
    <div class="wpe-header-wrap">
        <img width="50" height="50" src="/wp-content/plugins/wp-events/assets/img/logo.png">
        <h1 class="wpe-header-title">WP Events</h1> 
    </div>
</div>
<div class="wpe-view-entry-wrap">
    <div class="wpe-entry-controls">
        <?php
        /**
         * to display controls after page header
         * 
         * used to hook wpe_prev_next_entry function
         * 
         * @action add_action( 'wpe_entry_controls', 'wpe_prev_next_entry' );
         * @since 1.2.0
         */
        do_action('wpe_entry_controls');
        ?>
    </div>
    <div class="wpe-admin-form-holder-wrap">
        <div class="wpe-admin-form-holder">
            <div class="wpe-register-form-container">
                <?php
                /**
                 * to display entry form (registrations/subscriptions)
                 * 
                 * used to hook wpe_display_entry_form function
                 * 
                 * @action add_action( 'wpe_entry_form', 'wpe_display_entry_form' );
                 * @since 1.2.0
                 */
                do_action( 'wpe_entry_form' );
                ?>
            </div>
        </div>
        <div class="wpe-sidebar">
            <?php
            /**
             * to display sidebar on view entry page
             * 
             * used to hook wpe_get_entry_sidebar function
             * 
             * @action add_action( 'wpe_entry_sidebar', 'wpe_get_entry_sidebar' );
             * @since 1.2.0
             */
            do_action( 'wpe_entry_sidebar' );
            ?>
        </div>
    </div>
</div>