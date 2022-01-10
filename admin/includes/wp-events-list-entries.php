<?php

/**
 * Function calls to instantiate and display the content on entries page in dashboard.
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       //allmarketingsolutions.co.uk
 * @since      1.0.449
 *
 * @package    Wp_Events
 * @subpackage Wp_Events/admin/includes
 */

if ( ! function_exists( 'wpe_add_registration_entries_list' ) ) {
	/**
     * Creates instance of class and displays content under registrations tab.
	 *
	 * @since 1.0.449
	 */
	function wpe_add_registration_entries_list() {
    $registrations_list = new Wp_Events_Registrations_list();
    $registrations_list->prepare_items();

    $wpe_settings = get_option( 'wpe_settings' );
?>
    <div id="registrations" class="wrap">
        <?php
        ?>
        <div id="icon-users" class="icon32"></div>
        <?php
        $registrations_list->search_box(__( 'Search', 'wp-events' ), 'wpe-search');
        $registrations_list->views();
        $registrations_list->display(); ?>
    </div>
<?php
}
}

if ( ! function_exists( 'wpe_add_subscriber_entries_list' ) ) {
	/**
     * Creates instance of class and displays content under subscriptions tab.
	 *
	 * @since 1.0.449
	 */
	function wpe_add_subscriber_entries_list() {
    $subscribers_list = new Wp_Events_Subscribers_list();
    $subscribers_list->prepare_items();

    $wpe_settings = get_option( 'wpe_settings' );
?>
    <div id="subscribers" class="wrap">
        <?php
        ?>
        <div id="icon-users" class="icon32"></div>
        <?php
        $subscribers_list->search_box(__( 'Search', 'wp-events' ), 'wpe-search');
        $subscribers_list->views();
        $subscribers_list->display(); ?>
    </div>
<?php
}
}

if ( ! function_exists( 'wpe_admin_entries_tab' ) ) {
	/**
     * Adding Entries Tabs in dashboard in Events > Entries.
     * 
     * @global string $wpe_entries_tab
	 *
	 * @since 1.0.449
	 */
	function wpe_admin_entries_tab() {
    global $wpe_entries_tab;
?>
    <a class="nav-tab <?php echo $wpe_entries_tab === 'registrations' || '' ? 'nav-tab-active' : ''; ?>" href="<?php echo admin_url('edit.php?post_type=wp_events&page=wp_forms_entries&tab=registrations'); ?>"><?php echo __('Registrations', 'wp-events'); ?> </a>
    <a class="nav-tab <?php echo $wpe_entries_tab === 'subscriptions' || '' ? 'nav-tab-active' : ''; ?>" href="<?php echo admin_url('edit.php?post_type=wp_events&page=wp_forms_entries&tab=subscriptions'); ?>"><?php echo __('Subscriptions', 'wp-events'); ?> </a>
<?php
}
}

//Fires when #wpe-list-form is created when entries page is loaded from dashboard.
add_action( 'wp_events_entries_tab', 'wpe_admin_entries_tab' );

if ( ! function_exists( 'wpe_display_entries_table' ) ) {
	/**
     * Function calls to display tables corresponding to tabs.
     * 
     * @global string $wpe_entries_tab
	 *
	 * @since 1.0.449
	 */
	function wpe_display_entries_table() {
    global $wpe_entries_tab;
    switch ( $wpe_entries_tab ) {
        case 'subscriptions':
            wpe_add_subscriber_entries_list();
            break;
        case 'registrations':
        default:
            wpe_add_registration_entries_list();
            break;
        }
    }
}

//Fires when #wpe-list-form is created when entries page is loaded from dashboard.
add_action( 'wp_events_entries_table', 'wpe_display_entries_table' );


if ( ! function_exists( 'wpe_clean_url' ) ) {
	/**
     * Removes certain (passed in array) query parameters from the URL.
	 *
	 * @since 1.0.449
	 */
	function wpe_clean_url() {
?>
    <script>
        var url = jQuery( location ).attr('href');
        var parameters = ["_wp_http_referer", "_wpnonce", "action", "action2"];

        function removeURLParameter( url, parameter ) {
            //prefer to use l.search if you have a location/link object.
            var urlparts = url.split('?');
            if ( urlparts.length >= 2 ) {
                var prefix = encodeURIComponent( parameter ) + '=';
                var pars = urlparts[1].split(/[&;]/g);
                //reverse iteration as may be destructive.
                for ( var i = pars.length; i-- > 0; ) {
                    //idiom for string.startsWith.
                    if ( pars[i].lastIndexOf( prefix, 0 ) !== -1 ) {
                        pars.splice( i, 1 );
                    }
                }
                url = urlparts[0] + '?' + pars.join('&');
                return url;
            } else {
                return url;
            }
        }    
        for ( i = 0; i < parameters.length; i++ ) {
            url = removeURLParameter( url, parameters[i] );
        } 
        window.history.replaceState( {}, document.title, url );
    </script>
<?php
}
}

//Fires when #wpe-list-form is created when entries page is loaded from dashboard.
add_action( 'wp_events_entries_tab', 'wpe_clean_url' );
