<?php

/**
 * File that contains functions related to database operations
 *
 * @link       //allmarketingsolutions.co.uk
 * @since      1.1.1
 *
 * @package    Wp_Events
 * @subpackage Wp_Events/includes
 */

/**
 * File that contains functions related to database operations
 *
 * @since      1.1.1
 * @package    Wp_Events
 * @subpackage Wp_Events/includes
 * @author     All Marketing Solutions <btltimes39@gmail.com>
 */
class Wp_Events_Db_Actions {

	/**
	 * Adding Subscriber Table
	 *
	 * @since 1.1.1
	*/
	public static function add_subscriber_table() {

		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$table_name = $wpdb->prefix . 'events_subscribers';

		$sql = "CREATE TABLE IF NOT EXISTS " .$table_name. "(
                    id BIGINT PRIMARY KEY AUTO_INCREMENT NOT NULL,
                    subscriber_firstname VARCHAR(60) NOT NULL,
                    subscriber_lastname VARCHAR(60) NOT NULL,
                    subscriber_email VARCHAR(80) NOT NULL,
                    time_generated DATETIME NOT NULL,
					wpe_status INT(1) NOT NULL DEFAULT 1,
					subscriber_phone VARCHAR(80) NOT NULL,
					subscriber_texting_permission INT(1) NOT NULL DEFAULT 0
                )" .$charset_collate. ";";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		dbDelta($sql);

	}

	/**
	 * Adding Registration Table
	 *
	 * @since 1.1.1
	*/
	public static function add_registration_table() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$table_name = $wpdb->prefix . 'events_registration';
		$post_table_name = $wpdb->prefix .'posts (ID)';
		$sql = "CREATE TABLE IF NOT EXISTS " . $table_name. " (
                    ID BIGINT PRIMARY KEY AUTO_INCREMENT NOT NULL,
                    post_id BIGINT(20) UNSIGNED,
                    first_name VARCHAR(80) NOT NULL,
                    last_name VARCHAR(80) NOT NULL,
                    addres_one VARCHAR(240) NOT NULL,
                    addres_two VARCHAR(240),
                    city VARCHAR(80) NOT NULL,
                    state VARCHAR(80) NOT NULL,
                    zip VARCHAR(80) NOT NULL,
                    phone VARCHAR(80) NOT NULL,
                    email VARCHAR(320) NOT NULL,
                    fax VARCHAR(80),
                    business_name VARCHAR(80),
                    hear_about_us VARCHAR(80) NOT NULL,
                    time_generated DATETIME NOT NULL,
					wpe_seats VARCHAR(80) NOT NULL,
					guests VARCHAR(255),
					wpe_status INT(1) NOT NULL DEFAULT 1
                )" .$charset_collate. ";";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		dbDelta( $sql );
	}

	/**
	 * Checks if a table exists in the database
	 *
	 * @param string $table
	 *
	 * @since 1.1.1
	 * @return bool
	 */
	public static function wpe_table_exists( $table ) {
		global $wpdb;
		$table_name = $wpdb->prefix . $table;

		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
			return false;
		}

		return true;
	}

	/**
	 * Retrieves data for single registration from database
	 * 
	 * @since 1.2.0
	 * @return array
	 */
	public static function wpe_get_registration_data( $entry_id = null, $status = null, $format = 'OBJECT' ) {
		global $wpdb;
		$append_id	   = '';
		$append_status = '';
		$table_name	   = 'events_registration';
		if ( isset( $entry_id ) && $entry_id !== '' ) {
			$append_id = "WHERE ID = ". $entry_id;
		}
		if ( isset( $status ) && $status !== '' ) {
			$append_status = "WHERE wpe_status in (". $status . ")";
		}
		$sql     = "SELECT * FROM {$wpdb->prefix}$table_name " . $append_id . $append_status;
		$results = $wpdb->get_results( $sql, $format );
		return $results;
	}

	/**
	 * Retrieves data for single subscription from database
	 * 
	 * @since 1.2.0
	 * @return array
	 */
	 public static function wpe_get_subscription_data( $entry_id = null, $status = null ) {
		global $wpdb;
		$append_id	   = '';
		$append_status = '';
		$table_name	   = 'events_subscribers';
		if ( isset( $entry_id ) && $entry_id !== '' ) {
			$append_id = "WHERE ID = ". $entry_id;
		}
		if ( isset( $status ) && $status !== '' ) {
			$append_status = "WHERE wpe_status in (". $status . ")";
		}
		$sql     = "SELECT * FROM {$wpdb->prefix}$table_name " . $append_id . $append_status;
		$results = $wpdb->get_results( $sql, OBJECT );
		return $results;
	}

	/**
	 * Retrieves ID of event corresponding to the entry
	 * 
	 * @since 1.2.0
	 * @return array
	 */
	public static function wpe_get_event_id( $entry_id ) {
		global $wpdb;
		$table_name      = 'events_registration';
		$sql             = "SELECT post_id FROM {$wpdb->prefix}$table_name WHERE ID = " . $entry_id;
		$event_id        = $wpdb->get_var( $sql );
		return $event_id;
	}

	/**
	 * Updates record in database when an entry is edited
	 * 
	 * @since 1.2.0
	 * @return array
	 */
	public static function wpe_update_entry() {

		$current_user		   = wp_get_current_user();
		$user				   = $current_user->display_name;
		$_REQUEST['edited-by'] = $user;
		/**
		 * wpe_request_log global function created in includes/wp-events-global-functions.php
		 */
		wpe_request_log( $_REQUEST );

		$form_data = $_POST['formData'];

		/**
		 * wpe_decode_array global function created in includes/wp-events-global-functions.php
		*/
		$form_data = wpe_decode_array( $form_data );
		$referrer  = $form_data['_wp_http_referer'];

		global $wpdb;

		// If Nonce Is Not Verified Then Return
		if ( ! wp_verify_nonce( $form_data['wpe_entry_form_nonce'], 'wp_event_entry_form' ) ) {
			$response = '0000';
			wpe_send_ajax_response( $response );
		}

		if ( strpos( $referrer, 'registrations' ) !== false ) {
			$table_name	  = 'events_registration';
			$id			  = 'ID';
			$updated_data = array(
				'first_name'	 => isset( $form_data['wpe_first_name'] ) ? sanitize_text_field( $form_data['wpe_first_name'] ) : '',
				'last_name'		 => isset( $form_data['wpe_last_name'] ) ? sanitize_text_field( $form_data['wpe_last_name'] ) : '',
				'addres_one'	 => isset( $form_data['wpe_address'] ) ? sanitize_text_field( $form_data['wpe_address'] ) : '',
				'addres_two'	 => isset( $form_data['wpe_address_2'] ) ? sanitize_text_field( $form_data['wpe_address_2'] ) : '',
				'city'			 => isset( $form_data['wpe_city'] ) ? sanitize_text_field( $form_data['wpe_city'] ) : '',
				'state'			 => isset( $form_data['wpe_state'] ) ? sanitize_text_field( $form_data['wpe_state'] ) : '',
				'zip'			 => isset( $form_data['wpe_zip'] ) ? sanitize_text_field( $form_data['wpe_zip'] ) : '',
				'phone'			 => isset( $form_data['wpe_phone'] ) ? sanitize_text_field( $form_data['wpe_phone'] ) : '',
				'email'			 => isset( $form_data['wpe_email'] ) ? sanitize_text_field( $form_data['wpe_email'] ) : '',
				'fax'			 => isset( $form_data['wpe_fax'] ) ? sanitize_text_field( $form_data['wpe_fax'] ) : '',
				'business_name'	 => isset( $form_data['wpe_business_name'] ) ? sanitize_text_field( $form_data['wpe_business_name'] ) : '',
				'hear_about_us'	 => isset( $form_data['hear_about_us'] ) ? sanitize_text_field( $form_data['hear_about_us'] ) : '',
				'wpe_seats'		 => isset( $form_data['wpe_seats'] ) ? sanitize_text_field( $form_data['wpe_seats'] ) : '',
				'guests'		 => isset( $form_data['guests'] ) ? sanitize_text_field( $form_data['guests'] ) : ''
			);
		} else {
			$table_name	  = 'events_subscribers';
			$id			  = 'id';
			$text_perm 	  = $_POST['permissions'];
			$updated_data = array(
				'subscriber_firstname' 			   => isset( $form_data['wpe_first_name'] ) ? sanitize_text_field( $form_data['wpe_first_name'] ) : '',
				'subscriber_lastname'  			   => isset( $form_data['wpe_last_name'] ) ? sanitize_text_field( $form_data['wpe_last_name'] ) : '',
				'subscriber_email'	   			   => isset( $form_data['wpe_email'] ) ? sanitize_text_field( $form_data['wpe_email'] ) : '',
				'subscriber_phone'	   			   => isset( $form_data['wpe_phone'] ) ? sanitize_text_field( $form_data['wpe_phone'] ) : '',
				'subscriber_texting_permission'	   => $text_perm === 'true' ? 1 : 0,
			);
		}

		$entry_id = isset( $form_data['entry'] ) ? $form_data['entry'] : '';
		$result = $wpdb->update(
			"{$wpdb->prefix}$table_name",
			$updated_data,
			[ $id => $entry_id ],
			'%s',
			'%d'
		);
		
		$response = 'Record Updated Successfully!';

		wpe_send_ajax_response( $response );
	}

	// /**
    //  * Removes entries from display when related events are deleted.
    //  * 
    //  * @global object $wpdb instantiation of the wpdb class.
    //  *
    //  * @param $post_id id of the event being deleted.
    //  *
    //  * @since 1.1.1
    //  */
    // public function wpe_remove_trash_event_entries( $post_id ) {
    //     global $wpdb;

    //     $table_name = 'events_registration';

    //     return $wpdb->update(
    //         "{$wpdb->prefix}$table_name",
    //         ['wpe_status' => -1],
    //         ['post_id' => $post_id],
    //         '%d',
    //         '%d'
    //     );
    // }
}