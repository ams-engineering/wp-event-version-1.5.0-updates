<?php
/**
 * This class wil handle all the POST request for formms
 *
 * @package wp-events/public
 * @subpackage wp-events/public/includes
 */

/**
 * If this file is called directly, abort.
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Wp_Form_Request
{

    /**
     * Subscribe Form Post Handling
     *
     * @since 1.1.1
     */
	public function wpe_subscribe_form() {

		/**
		 * wpe_request_log global function created in includes/wp-events-global-functions.php
		 */
		wpe_request_log( $_REQUEST );

		$form_data = isset( $_POST['form_data'] ) ? $_POST['form_data'] : [];
		$page_slug = get_option( 'wpe_settings' );

		/**
		 * wpe_decode_array global function created in includes/wp-events-global-functions.php
		 */
		$form_data = wpe_decode_array( $form_data );
		/**
		 * settings current form data to shortcodes $form_data attr
		 */
		Wpe_Shortcodes::set_form_data( $form_data );

		$referer   = isset( $form_data['_wp_http_referer'] ) ? $form_data['_wp_http_referer'] : '';
		$arr	   = explode('/' , $referer);
		$subdomain = $arr[1];
		$slug	   = array_slice( $arr, 2 );
		$slug	   = implode( '/', $slug );

		if ( $form_data === [] ) {
			wpe_send_ajax_response( [ 'url' => $referer . '?validation-error' ] );
		}

		if ( $referer !== '' ) {
			if ( strpos( get_site_url(), $subdomain ) > 0 ) { //if it is a subdomain
				$referer = get_site_url() . '/' . $slug . '?thankyou';
			} else {
				$referer = get_site_url() . $referer . '?thankyou' ;     //will redirect to the page where the request came from
			}
		} else {
			$referer = get_site_url() . '/' . $page_slug['events_slug'] . '?thankyou';
		}

		if ( ! wp_verify_nonce( $form_data['wpe_subscribe_form'], 'wp_events_subscribe_form' ) ) {
			wpe_send_ajax_response( [ 'url' => $referer . '?validation-error' ] );
		}
		$subscribe_firstname 			  = isset( $form_data['wpe_first_name'] ) ? sanitize_text_field( $form_data['wpe_first_name'] ) : '';
		$subscribe_lastname  			  = isset( $form_data['wpe_last_name'] ) ? sanitize_text_field( $form_data['wpe_last_name'] ) : '';
		$subscriber_email    			  = isset( $form_data['wpe_email'] ) ? sanitize_text_field( $form_data['wpe_email'] ) : '';
		$subscriber_phone    			  = isset( $form_data['wpe_phone'] ) ? sanitize_text_field( $form_data['wpe_phone'] ) : '';
		$subscriber_texting_permission    = isset( $form_data['wpe_texting_permission'] ) ?  $form_data['wpe_texting_permission']  : '';
		$subscriber_email    			  = filter_var( $subscriber_email, FILTER_VALIDATE_EMAIL );

		$table = 'events_subscribers';
		$data  = [
			'subscriber_firstname' 			  => $subscribe_firstname,
			'subscriber_lastname'   		  => $subscribe_lastname,
			'subscriber_email'     			  => $subscriber_email,
			'time_generated'       			  => date( "Y-m-d h:i:s" ),
			'wpe_status'					  => WPE_ACTIVE,
			'subscriber_phone'     			  => $subscriber_phone,
			'subscriber_texting_permission'   => $subscriber_texting_permission,
		];

        $format = [
            '%s', '%s', '%s', '%s', '%d', '%s', '%s',
        ];

		if ( ! Wp_Events_Db_Actions::wpe_table_exists( $table ) ) {
			Wp_Events_Db_Actions::add_subscriber_table();
		}

        //submitting to database
	    if ( $this->add_entry_to_db( $table, $data, $format ) ) {

	        //getting mail settings
	        $mail_options = get_option( 'wpe_mail_settings' );
			//Get Firm information
	        $firm_info = get_option( 'wpe_firm_settings' );
			// subscriber details
	        $subscriber_subject = do_shortcode( $mail_options['subscriber_user_subject'], TRUE );
	        $subscriber_message = do_shortcode( $mail_options['subscriber_user_message'], TRUE );

	        // admin details
	        $admin_subject = do_shortcode( $mail_options['subscriber_admin_subject'], TRUE );
	        $admin_message = do_shortcode( $mail_options['subscriber_admin_message'], TRUE );

	        // header information
	        $from_name  = $firm_info['mail_from_name'];
	        $from_email = $mail_options['mail_from'];
	        $headers[]  = 'Content-Type: text/html;';
	        $headers[]  = "from :$from_name <$from_email>";

	        //send email to user
	        wp_mail( $subscriber_email, $subscriber_subject, $subscriber_message, $headers );

	        //send email top admin
	        wp_mail( $firm_info['admin_mail'], $admin_subject, $admin_message, $headers );

		    /**
		     * Fires after submission is completed
		     * @since 1.2.0
		    */
		    do_action( 'wpe_after_subscriber_form_submission', $form_data );

		    //redirect
	        $on_subscriber_form_success = get_option( 'wpe_forms_settings' );
	        if ( ! empty( $on_subscriber_form_success['subsc_form_success'] ) && $on_subscriber_form_success['subsc_form_success'] !== '#' ) { //redirect page to URL saved in settings
		        wpe_send_ajax_response( [ 'url' => $on_subscriber_form_success['subsc_form_success'] ] );
	        } else {
	        	wpe_send_ajax_response( [ 'url' => $referer ] );
	        }
        }
    }

	/**
	 * Registration Form Post Handling
	 *
	 * @since 1.0.441
	 */
	public function wpe_registration_form() {

		/**
		 * wpe_request_log global function created in includes/wp-events-global-functions.php
		*/
		wpe_request_log( $_REQUEST );

		$form_data = isset( $_POST['form_data'] ) ? $_POST['form_data'] : [];

		$page_slug    = get_option( 'wpe_settings' );
		$redirect_url = get_site_url() . '/' . $page_slug['events_slug'];

		/**
		 * wpe_decode_array global function created in includes/wp-events-global-functions.php
		*/
		$form_data = wpe_decode_array( $form_data );

		/**
		 * settings current form data to shortcodes $form_data attr
		*/
		Wpe_Shortcodes::set_form_data( $form_data );

		if ( $form_data === [] ) {
			wpe_send_ajax_response( [ 'url '=> get_site_url() . '/' . $page_slug['events_slug'] ] );
		}

		$post_id         = isset( $form_data['post'] ) ? (int)$form_data['post'] : '';

		// If Nonce Is Not Verified Then Return
		if ( ! wp_verify_nonce( $form_data['wpe_register_form_nonce'], 'wp_event_registration_form' ) ) {
			wpe_send_ajax_response( ['url' => get_the_permalink( $post_id ) . '?validation-error'] );
		}

		//Sanitizing All Text Fields
		$wpe_first_name  = isset( $form_data['wpe_first_name'] ) ? sanitize_text_field( $form_data['wpe_first_name'] ) : '';
		$wpe_last_name   = isset( $form_data['wpe_last_name'] ) ? sanitize_text_field( $form_data['wpe_last_name'] ) : '';
		$wpe_address_one = isset( $form_data['wpe_address'] ) ? sanitize_text_field( $form_data['wpe_address'] ) : '';
		$wpe_address_two = isset( $form_data['wpe_address_2'] ) ? sanitize_text_field( $form_data['wpe_address_2'] ) : '';
		$wpe_wpe_city    = isset( $form_data['wpe_city'] ) ? sanitize_text_field( $form_data['wpe_city'] ) : '';
		$wpe_state       = isset( $form_data['wpe_state'] ) ? sanitize_text_field( $form_data['wpe_state'] ) : '';
		$wpe_zip         = isset( $form_data['wpe_zip'] ) ? sanitize_text_field( $form_data['wpe_zip'] ) : '';
		$wpe_phone       = isset( $form_data['wpe_phone'] ) ? sanitize_text_field( $form_data['wpe_phone'] ) : '';
		$wpe_email       = isset( $form_data['wpe_email'] ) ? sanitize_text_field( $form_data['wpe_email'] ) : '';
		$wpe_email       = filter_var( $wpe_email, FILTER_VALIDATE_EMAIL );
		$wpe_fax         = isset( $form_data['wpe_fax'] ) ? sanitize_text_field( $form_data['wpe_fax'] ) : '';
		$wpe_business    = isset( $form_data['wpe_business_name'] ) ? sanitize_text_field( $form_data['wpe_business_name'] ) : '';
		$hear_about_us   = isset( $form_data['hear_about_us'] ) ? sanitize_text_field( $form_data['hear_about_us'] ) : '';
		$wpe_seats       = isset( $form_data['wpe_seats'] ) ? sanitize_text_field( $form_data['wpe_seats'] ) : '';
		$wpe_status		 = WPE_ACTIVE;

		$event_options   = get_option( 'wpe_events_settings' );
		if ( isset( $event_options['approve_registrations'] ) ) {
			$wpe_status  = WPE_PENDING;
		}

		$table = 'events_registration'; //table Name

		// Row data
		$data = [
			'post_id'        => $post_id,
			'first_name'     => $wpe_first_name,
			'last_name'      => $wpe_last_name,
			'addres_one'     => $wpe_address_one,
			'addres_two'     => $wpe_address_two,
			'city'           => $wpe_wpe_city,
			'state'          => $wpe_state,
			'zip'            => $wpe_zip,
			'phone'          => $wpe_phone,
			'email'          => $wpe_email,
			'fax'            => $wpe_fax,
			'business_name'  => $wpe_business,
			'hear_about_us'  => $hear_about_us,
			'time_generated' => date( "Y-m-d h:i:s" ),
			'wpe_seats'      => $wpe_seats,
		];


		// Data Format
		$format = [
			'%d',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%d',
		];


		$guest_names = $this->get_guest_information( $form_data['wpe_guest_first_name'], $form_data['wpe_guest_last_name'] );
		if ( $guest_names !== FALSE ) {
			$data['guests'] = $guest_names;
		} else {
			$data['guests'] = '';
		}

		$data['wpe_status'] = $wpe_status;

		if ( ! Wp_Events_Db_Actions::wpe_table_exists( $table ) ) {
			Wp_Events_Db_Actions::add_registration_table();
		}

		$totalseats		 = (int) get_post_meta( $post_id, 'wpevent-seats', TRUE );
		$booked_seats	 = get_booked_seats( $post_id ); //Function defined in wp-events-global-functions.php
		$remaining_seats = $totalseats - $booked_seats;

		if ( $remaining_seats <= 0 ) {
			wpe_send_ajax_response( [ 'url' => get_the_permalink( $post_id ) . '?registration-failed' ] );
		}
		//if submission is successful
		if ( $this->add_entry_to_db( $table, $data, $format ) ) {

			/**
			 * Fires after submission is completed
			 * @since 1.2.0
			 */
			do_action( 'wpe_after_registration_form_submission', $form_data );

			$type = get_post_meta( $post_id, 'wpevent-type', true );
			$on_success = get_option( 'wpe_forms_settings' );

			if ( $type === 'webinar' && ! empty( $on_success['form_success_webinar'] ) ) {
				$this->send_mail_to_user_and_admin( $data, $type );
				wpe_send_ajax_response( [ 'url' => $on_success['form_success_webinar'] ] );
			} else if ( ! empty( $on_success['form_success'] ) ) { //redirect page to URL saved in settings
				$this->send_mail_to_user_and_admin( $data, $type );
				wpe_send_ajax_response( [ 'url' => $on_success['form_success'] ] );
			} else {
				$this->send_mail_to_user_and_admin( $data, $type );
				wpe_send_ajax_response( [ 'url' => get_the_permalink( $post_id ) . '?thankyou' ] );
			}
		}
		wpe_send_ajax_response( [ 'url' => get_the_permalink( $post_id ) . '?registration-failed' ] );

	}


	/**
	 * Add Data to DataBase Tables
	 *
	 * @param  string      $table
	 * @param  array       $data
	 * @param  array|null  $format
	 *
	 * @return int
	 * @since 1.0.0
	 */
	private function add_entry_to_db(string $table, array $data, array $format = null): int
	{
		global $wpdb;
		return $wpdb->insert($wpdb->prefix . $table, $data, $format);
	}

	/**
	 * send email to user on form submission
	 *
	 * @param $data
	 * @since 1.0.0
	 */
	private function send_mail_to_user_and_admin( $data, $type )
	{
		$mail_options 				= get_option('wpe_mail_settings');
		$firm_info 					= get_option('wpe_firm_settings');
		$event_option 				= get_option('wpe_events_settings');
		$from_name    				= $firm_info['mail_from_name'];
		$from_email   				= $mail_options['mail_from'];
		$enable_webinar_confimation = $mail_options['enable_webinar_conformation'];	

		$headers[]  = 'Content-Type: text/html;';
		$headers[]  = "from :$from_name <$from_email>";

		/**
		 * Adding Custom message from post meta OR options
		 */
		$user_subject  = do_shortcode( $mail_options['mail_success_subject'], TRUE );
		$user_message  = get_confirmation_message( $data['post_id'], $mail_options, $type );
		$user_message  = do_shortcode( $user_message, TRUE );

		if ( isset( $event_option['approve_registrations'] ) ) {
			$user_subject = str_replace('confirmed', 
										'received', 
										$user_subject );
			$user_message = str_replace('We look forward to seeing you.', 
										'You will shortly receive another email confirming status of your registration.', 
										$user_message );
		}

		$admin_subject = do_shortcode( $mail_options['registrant_admin_subject'], TRUE );
		$admin_message = do_shortcode( $mail_options['registrant_admin_message'], TRUE );

		//send email to user
		switch ( $type ) {
			case 'webinar':
					if( $enable_webinar_confimation ) {
						wp_mail( $data['email'], $user_subject, $user_message, $headers );
					}
				break;
			case 'seminar':
						wp_mail( $data['email'], $user_subject, $user_message, $headers );
				break;	
		}

		/**
		 * get notification user from post meta
		*/
		$notified_user = get_post_meta( $data['post_id'], 'wpevent-email-notification', TRUE );
		if( $notified_user !=='' && $notified_user !== $firm_info['admin_mail'] ) { // if not empty and not identical to the one in mail options send to notified user
			wp_mail( $notified_user, $admin_subject, $admin_message, $headers );
		} else {
			wp_mail( $firm_info['admin_mail'], $admin_subject, $admin_message, $headers );
		}
	}

	/**
	 *  gets guest information and returns guest names
	 *
	 * @param $guest_first_names
	 * @param $guest_last_names
	 *
	 * @return false|string
	 */
	private function get_guest_information( $guest_first_names, $guest_last_names ) {
		$guest_names	  = isset( $guest_first_names ) ? $guest_first_names : false ;
		$guest_last_names = isset( $guest_last_names ) ? $guest_last_names : false ;

		if( $guest_names === false && $guest_last_names === false ) {     // returns false if  empty
			return false;
		}
		for( $i=0; $i < count( $guest_last_names ); $i++ ) {
			$guest_names[$i] = sanitize_text_field( $guest_names[$i] ). ' ' .sanitize_text_field( $guest_last_names[$i] );
		}
		return implode( ',',$guest_names );
	}

	/**
	 * Handles request for reCAPTCHA validation
	 *
	 * @since 1.2.0
	 */
	public function wpe_verify_captcha() {
		if( isset( $_POST['captchaResponse'] ) ) {
			$option	    = get_option( 'wpe_reCAPTCHA_settings' );
			$secret_key = isset( $option['reCAPTCHA_secret_key'] ) ? $option['reCAPTCHA_secret_key'] : '';
			$response   = $_POST['captchaResponse'];
			$ip	   	    = $_SERVER['SERVER_ADDR']; //server Ip
			//Build up the url
			$url   	    = 'https://www.google.com/recaptcha/api/siteverify';
			$full_url 	= $url . '?secret=' . $secret_key . '&response=' . $response . '&remoteip='. $ip;
			
			//Get the response back decode the json
			$data = json_decode( file_get_contents( $full_url ) );
			//Return true or false, based on users input
			if( isset( $data->success ) && $data->success == true ) {
			wpe_send_ajax_response('success');
			}
		}
		wpe_send_ajax_response('error');
	}

	/**
	 * Updates archive views option when view changed
	 * on front end.
	 *
	 * @since 1.5.0
	 */
	public function wpe_update_default_view() {
		$clicked_view 	  = $_POST['archiveView'];
		$display_settings = get_option( 'wpe_display_settings' );
		switch( $clicked_view ) {
			case '#grid':
				$display_settings['default_view'] = 'grid';
				break;
			case '#calendar':
				$display_settings['default_view'] = 'calendar';
				break;
			case '#list':
				$display_settings['default_view'] = 'list';
				break;
		}
		update_option( 'wpe_display_settings', $display_settings );
		wpe_send_ajax_response( 1 );
	}
}