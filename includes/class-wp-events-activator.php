<?php

/**
 * Fired during plugin activation
 *
 * @link       //allmarketingsolutions.co.uk
 * @since      1.0
 *
 * @package    Wp_Events
 * @subpackage Wp_Events/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wp_Events
 * @subpackage Wp_Events/includes
 * @author     All Marketing Solutions <btltimes39@gmail.com>
 */
class Wp_Events_Activator {


	/**
	 * private class instance
	 *
	 * @since  2.0
	 * @access private
	 * @var    object $instance private instance of class
	 */
	private static $instance = null;

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		self::$instance = new self();
		delete_option('rewrite_rules');
		Wp_Events_Db_Actions::add_registration_table();
		Wp_Events_Db_Actions::add_subscriber_table();
		Wp_Events_Activator::add_wpe_options();
	}

	private static function add_wpe_options() {
		$wpe_settings = get_option('wpe_settings');
		if( $wpe_settings['events_post_name'] == '' ) {
			$wpe_settings['events_post_name'] = 'Events';
		}
		if( $wpe_settings['events_slug'] == '' ) {
			$wpe_settings['events_slug'] = 'events';
		}
		$wpe_settings['meta_description'] = 'Join us for free seminars for the most up-to-date information on how you can protect your assets during your life and preserve them after your death.';
		update_option( 'wpe_settings', $wpe_settings );

		if( self::$instance === NULL ) {
			self::$instance = new self();
		}

		self::$instance->default_email_settings();
		self::$instance->default_form_settings();
		self::$instance->default_display_settings();
	}

	/**
	 * sets default values for form options
	 *
	 * @access private
	 * @since  1.0.438
	 */

	private function default_form_settings() {
		// email options
		$wpe_form_settings = get_option( 'wpe_forms_settings' );

		$form_defaults = [
			'subscriber_form_title'       		 => 'Subscribe With Us',
			'subscriber_form_description' 		 => 'There are currently no seminars scheduled, but please fill out the form below and we\'ll be sure to notify you of upcoming seminars.',
			'subscriber_form_button'      		 => 'Subscribe',
			'registration_form_button'     		 => 'Submit',
			'consent_checkbox'			  		 => 'I have read & consent to the above.*',
			'disclaimer_checkbox'		  		 => 'I have read & understand your website Disclaimer. *',
			'hearaboutus_options'		  		 => 'An Email I Received, Blog / Facebook, Internet / Search Engine, Landing Pages, Radio and TV, Link from another website, Mailing / Postcard, Newsletter, Newspaper, Other, Referral',
			'subscriber_form_texting_permission' => 'I agree to receive texts at the number provided from [wpe_firm_name]. Frequency may vary and include information on appointments, events, and other marketing messages. Message/data rates may apply. To opt-out, text STOP at any time.',
		];

		$this->wpe_save_default_options( $wpe_form_settings, $form_defaults, 'wpe_forms_settings' );

	}

	private function default_display_settings() {
		// display options
		$wpe_display_settings = get_option( 'wpe_display_settings' );

		$display_defaults = [
			'archive_posts'   => '12',
			'archive_title'   => 'Events',
			'reg_button'	  => 'checked',
			'button_text'	  => 'Register',
			'closed_reg'	  => 'Event Seats Quota is Full',
			'max_seats'	  	  => 10,
		];

		$this->wpe_save_default_options( $wpe_display_settings, $display_defaults, 'wpe_display_settings' );

	}

	/**
	 * sets default values for all email options
	 *
	 * @access private
	 * @since  1.0.438
	 */
	private function default_email_settings() {
		// email options
		$wpe_mail_settings = get_option( 'wpe_mail_settings');


		// email default options array
		$email_defaults = [
			'mail_from'                => get_option( 'admin_email' ),
			'admin_mail'               => get_option( 'admin_email' ),
			'mail_from_name'           => get_current_user(),
			'mail_success_subject'     => 'Thank you for registering with us at [wpe_event_name]',
			'mail_success_message'     => self::$instance->user_email_message(),
			'webinar_success_message'  => self::$instance->user_email_message(),
			'registrant_admin_subject' => 'New booking for [wpe_event_name]',
			'registrant_admin_message' => self::$instance->admin_email_message( 'registrant' ),
			'subscriber_user_subject'  => 'Hey Subscriber!',
			'subscriber_user_message'  => 'Thank you for your interest, You\'re now subscribed for our future events',
			'subscriber_admin_subject' => 'New Subscription',
			'subscriber_admin_message' => self::$instance->admin_email_message( 'subscriber' ),
		];

		$this->wpe_save_default_options( $wpe_mail_settings, $email_defaults, 'wpe_mail_settings' );

	}

	/**
	 * Save default options in options
	 *
	 * checks for the existing options if not set then
	 * add the default value's in options table
	 *
	 * @param $wpe_settings     array   db options array
	 * @param $default_settings array   default options array
	 * @param $option           string  option name
	 *
	 * @since 1.0.438
	 */
	private function wpe_save_default_options( $wpe_settings, $default_settings, $option ) {
		if( $wpe_settings === FALSE ) {
			$wpe_settings = $default_settings;
		} else {
			foreach ( $default_settings as $key => $value ) {
				if ( !isset( $wpe_settings[ $key ] ) ) {
					$wpe_settings[ $key ] = $value;
				}
			}
		}

		update_option( $option, $wpe_settings );
	}

	/**
	 * admin email message for subscriber and registrant
	 *
	 * @param $type string  subscriber|registrant
	 *
	 * @return string
	 * @since 1.0.0
	 */
	private function admin_email_message( $type ) {
		$message = '';
		if ( $type === 'registrant' ) {
			$message = 'This is an auto-generated email confirming receipt of a event reservation made from your website. A confirmation email has also been sent to the registrant, ( [wpe_user_first_name] ) at [wpe_user_email].
			
			Event Details:
			[wpe_event_details]
			User Details:
			[wpe_registration_details]
			The above visitor information has been added to the WordPress Event database. You can access this information by going to your Website WordPress Dashboard.';
		}
		if ( $type === 'subscriber' ) {
			$message = 'Name: [wpe_user_first_name] [wpe_user_last_name]
			Email: [wpe_user_email]
			has been subscribed to your future events.';
		}

		return nl2br( preg_replace( "/\t+/", "", $message ) );     //  removing tabs from string
	}

	/**
	 * returns registrant email message
	 *
	 * @access private
	 * @since  1.0.0
	 */
	private function user_email_message() {
		$message = "Dear [wpe_user_first_name] [wpe_user_last_name],
		Thank you for registering for our upcoming Event. This is an auto-generated email confirming receipt of your registration for our upcoming Event. 
		
		The details of your registration are following.
		[wpe_event_details] 
		[wpe_registration_details]
		If you have any questions, please feel free to contact us at our office number or via email.
		We look forward to seeing you.		
		Sincerely,";

		return nl2br( preg_replace( "/\t+/", "", $message ) );    //  removing tabs from string
	}




}
