<?php


if ( ! function_exists( 'wpe_update_driver' ) ) {
	/**
	 * Driver function for database updates
	 *
	 * driver function handles all operations
	 * performed after plugin update
	 *
	 * @since 1.2.0
	 */
	function wpe_update_driver() {
		if ( ( wpe_add_status_column( 'events_registration' ) === true  &&
		       wpe_add_status_column( 'events_subscribers' ) === true ) ||
			  ( wpe_add_subscriber_phone_column( 'events_subscribers' ) === true &&
			   wpe_add_subscriber_texting_permission_column( 'events_subscribers' ) === true ) ||
		       wpe_set_display_settings_defaults() === true || 
			   wpe_set_form_settings_defaults() === true ||
			   wpe_set_mail_settings_defaults() === true ||
			   wpe_set_firm_settings_defaults() === true ||
			   wpe_set_general_settings_defaults() === true ||
			   wpe_set_integration_settings_defaults() === true
 		) {
			update_option( 'WP_EVENTS_VERSION', WP_EVENTS_VERSION );
		}
	}
}

add_action( 'plugins_loaded', 'wpe_update_driver' );

if ( ! function_exists( 'wpe_add_status_column' ) ) {
	/**
	 * Add status column in WP table
	 *
	 * @param  string  $table_name
	 *
	 * @return bool
	 * @global object  $wpdb instance of the wpdb class.
	 *
	 */
	function wpe_add_status_column( string $table_name ) : bool {
		global $wpdb;
		$table_name          = $wpdb->prefix . $table_name;
		$wpe_current_version = get_option( 'WP_EVENTS_VERSION' );

		//only for backward compatibility with versions less than 1.1.0
		if ( version_compare( $wpe_current_version, '1.1.0', '<' ) && $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name ) {
			$row = $wpdb->get_results( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
            WHERE table_name = $table_name AND column_name = 'wpe_status'" );

			if ( empty( $row ) ) {
				$wpdb->query( "ALTER TABLE $table_name ADD wpe_status INT(1) NOT NULL DEFAULT 1" );

				return TRUE;
			}

		}

		return FALSE;
	}
}

if ( ! function_exists( 'wpe_add_subscriber_phone_column' ) ) {
	/**
	 * Add subscriber phone column in WP table
	 *
	 * @param  string  $table_name
	 *
	 * @return bool
	 * @global object  $wpdb instance of the wpdb class.
	 *
	 */
	function wpe_add_subscriber_phone_column( string $table_name ) {
		global $wpdb;
		$table_name          = $wpdb->prefix . $table_name;
		$wpe_current_version = get_option( 'WP_EVENTS_VERSION' );

		//compatibility with latest Version
		if ( version_compare( $wpe_current_version, '1.4.0', '<' ) && $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name ) {
			$row = $wpdb->get_results( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
            WHERE table_name = $table_name AND column_name = 'subscriber_phone'" );
			if ( empty( $row ) ) {
				$wpdb->query( "ALTER TABLE $table_name ADD subscriber_phone VARCHAR(80) NOT NULL" );

				return TRUE;
			}
		}
		return FALSE;
	}

}

if ( ! function_exists( 'wpe_add_subscriber_texting_permission_column' ) ) {
	/**
	 * Add texting permission column in WP table
	 *
	 * @param  string  $table_name
	 *
	 * @return bool
	 * @global object  $wpdb instance of the wpdb class.
	 *
	 */
	function wpe_add_subscriber_texting_permission_column( string $table_name ) {
		global $wpdb;
		$table_name          = $wpdb->prefix . $table_name;
		$wpe_current_version = get_option( 'WP_EVENTS_VERSION' );

		//compatibility with latest Version
		if ( version_compare( $wpe_current_version, '1.4.0', '<' ) && $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name ) {
			$row = $wpdb->get_results( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
            WHERE table_name = $table_name AND column_name = 'subscriber_texting_permission'" );

			if ( empty( $row ) ) {
				$wpdb->query( "ALTER TABLE $table_name ADD subscriber_texting_permission INT(1) NOT NULL DEFAULT 0" );

				return TRUE;
			}
		}
		return FALSE;
	}
}


if( ! function_exists( 'wpe_set_display_settings_defaults' ) ) {
	/**
	 * Adds new settings fields default values when plugin
	 * is updated
	 *
	 * @return bool
	 * @since 1.1.10
	*/
	function wpe_set_display_settings_defaults(): bool {
		//only for version 1.1.10
		if ( version_compare( get_option( 'WP_EVENTS_VERSION' ), '1.5.0' ) !== -1 ) {
			return FALSE;
		}

		// display options
		$wpe_display_settings = get_option( 'wpe_display_settings' );

		$default_settings = [
			'list_view' 	  => 'yes',
			'grid_view' 	  => 'yes',
			'cal_view' 		  => 'yes',
			'default_view' 	  => 'list',
			'reg_button' 	  => 'l_true',
			'button_text'	  => 'Register',
		];

		$wpe_settings = [];
		if( $wpe_display_settings === FALSE ) {
			$wpe_settings = $default_settings;
		} else {
			foreach ( $default_settings as $key => $value ) {
				if ( !isset( $wpe_settings[ $key ] ) ) {
					$wpe_settings[ $key ] = $value;
				}
			}
		}

		return update_option( 'wpe_display_settings', $wpe_settings );
	}
}

if( ! function_exists( 'wpe_set_form_settings_defaults' ) ) {
	/**
	 * Adds new settings fields default values when plugin
	 * is updated
	 *
	 * @return bool
	 * @since 1.4.0
	*/
	function wpe_set_form_settings_defaults(): bool {
		//only for version 1.4.0
		if ( version_compare( get_option( 'WP_EVENTS_VERSION' ), '1.4.0' ) !== -1 ) {
			return FALSE;
		}

		// form options
		$wpe_form_settings = get_option( 'wpe_form_settings' );

		$default_settings = [
			'subscriber_form_texting_permission'  => 'I agree to receive texts at the number provided from [wpe_firm_name]. Frequency may vary and include information on appointments, events, and other marketing messages. Message/data rates may apply. To opt-out, text STOP at any time.',
		];

		$wpe_settings = [];
		if( $wpe_form_settings === FALSE ) {
			$wpe_settings = $default_settings;
		} else {
			foreach ( $default_settings as $key => $value ) {
				if ( !isset( $wpe_settings[ $key ] ) ) {
					$wpe_settings[ $key ] = $value;
				}
			}
		}

		return update_option( 'wpe_form_settings', $wpe_settings );
	}
}

if( ! function_exists( 'wpe_set_mail_settings_defaults' ) ) {
	/**
	 * Adds new settings fields default values when plugin
	 * is updated
	 *
	 * @return bool
	 * @since 1.2.5
	*/
	function wpe_set_mail_settings_defaults(): bool {
		//only for version 1.2.5
		if ( version_compare( get_option( 'WP_EVENTS_VERSION' ), '1.2.5' ) !== -1 ) {
			return FALSE;
		}

		// form options
		$wpe_mail_settings = get_option( 'wpe_mail_settings' );
		$user_email_message = "Hi [wpe_user_first_name], <br><br>

		We’re thrilled that you’ll be joining us for our upcoming virtual estate planning webinar. We are looking forward to teaching you simple legal strategies that you can use right now to protect your assets and family.<br><br>
		
		Here are the presentation details to add to your calendar:
		<span style='text-align: center;'>[wpe_event_date_time]</span>
		
		<p style='text-align: center;'><b>You will receive another confirmation email with your individual link directly from Zoom within the next 24 hours.</b></p><br>
		
		In this online seminar, you will learn how to avoid common estate planning mistakes, as well as:<br>
<ul>
		<li>Discover the advantages and disadvantages of Wills and Trusts.
		<li>How to <b>keep your affairs private</b> and your loved ones out of court if you become disabled and can’t speak for yourself. (Hint: Not all Powers of Attorney are valid!)
		<li>Steps can you take now to help your family avoid the expensive and time-consuming probate process when you’re gone.
		<li>How to <b>create a long-term care plan <i>before</i> it’s needed</b> so you can stay as independent as possible and in complete control of your decisions as you age.
		<li>Why <b>owning assets jointly or putting property in a child’s name is likely a huge mistake</b>—and how to accomplish your goals in a more secure way.
		<li>Easy strategies to <b>protect your children’s inheritance</b> if they get divorced, sued or file bankruptcy or your surviving spouse remarries. 
		<li>How to <b>qualify and use Medicaid to pay for nursing home expenses</b> which can average <$0,000> monthly.
		<li>How <b>parents (and grandparents!) of children with special needs can plan</b> for a lifetime of care without jeopardizing eligibility for benefits like Medicaid or Supplemental Security Income (SSI).
</ul>
		
		<br>If you have any questions about this webinar, please do not hesitate to contact us. <b><a style='color: blue' href='https://support.zoom.us/hc/en-us?flash_digest=fdab14cb185123b2c9d9911616a7771c82e53b5b'>Click here</a></b> for help with Zoom.<br>
		Best Regards,<br><br>

		<span style='text-transform: uppercase;'>[wpe_owner_name]</span><br>
		<b>[wpe_firm_name]</b><br>
		<b>[wpe_site_url]</b><br><br>
		P.S. – If you are unable to join this presentation for any reason, please reply to this email or call me at <b>[wpe_firm_phone]</b>, at least 24 hours in advance of this webinar. Doing so will allow us to open your spot to others who may be on our waitlist.<br>
		P.P.S. – As a reminder, your Participant Access Link is unique to the email address you registered with. Do not forward or share this link with others as it may prevent you from accessing the presentation. If you have friends or family who would like to listen to this presentation that are in our area, please send them to <b>[wpe_event_link] </b>where they can easily register online for any spaces that may be left.";

		$user_seminar_message = "Dear [wpe_user_first_name] [wpe_user_last_name],<br><br>

		Thank you for registering for our upcoming estate planning workshop! Below are the details of your registration.<br><br>

		We’re excited to share with you the common misconceptions about Wills and Trusts and why today’s families need to plan more than ever before. We promise to provide you with easy to understand information, answer your questions and make sure you walk away with the knowledge you need to make the best decisions for you and your loved ones.<br><br>
		
		We’ll be in touch by phone to confirm your reservation and share more details about the workshop.<br><br>

		[wpe_event_details]
		
		Seats Confirmed: [wpe_event_seats]<br><br>

		If you have any questions, please feel free to contact us at <span style='color: blue'>[wpe_firm_phone]</span> or via email at <b>[wpe_notification_email]</b>.<br><br>

		We look forward to seeing you soon!<br><br>

		Sincerely,<br><br>

		<b>[wpe_firm_name]</b><br>
		<b>[wpe_notification_email]</b>";

		$default_settings = [
			'webinar_success_message'  => $user_email_message,
			'mail_success_message'     => $user_seminar_message,
			'mail_from'                => $wpe_mail_settings['mail_from'],
			'mail_success_subject'     => 'Thank you for registering!',
			'registrant_admin_subject' => $wpe_mail_settings['registrant_admin_subject'],
			'registrant_admin_message' => $wpe_mail_settings['registrant_admin_message'],
			'subscriber_user_subject'  => $wpe_mail_settings['subscriber_user_subject'],
			'subscriber_user_message'  => $wpe_mail_settings['subscriber_user_message'],
			'subscriber_admin_subject' => $wpe_mail_settings['subscriber_admin_subject'],
			'subscriber_admin_message' => $wpe_mail_settings['subscriber_admin_message'],
		];

		$wpe_settings = [];
		if( $wpe_mail_settings === FALSE ) {
			$wpe_settings = $default_settings;
		} else {
			foreach ( $default_settings as $key => $value ) {
				if ( ! isset( $wpe_settings[ $key ] ) || $wpe_settings[ $key ] !== '' ) {
					$wpe_settings[ $key ] = $value;
				}
			}
		}

		return update_option( 'wpe_mail_settings', $wpe_settings );
	}
}

if( ! function_exists( 'wpe_set_firm_settings_defaults' ) ) {
	/**
	 * Adds new settings fields default values when plugin
	 * is updated
	 *
	 * @return bool
	 * @since 1.2.5
	*/
	function wpe_set_firm_settings_defaults(): bool {
		//only for version 1.2.5
		if ( version_compare( get_option( 'WP_EVENTS_VERSION' ), '1.2.5' ) !== -1 ) {
			return FALSE;
		}

		// from options
		$wpe_firm_settings = get_option( 'wpe_firm_settings' );
		$wpe_mail_settings = get_option( 'wpe_mail_settings' );

		$default_settings = [
			'admin_mail'               => $wpe_mail_settings['admin_mail'] ?? $wpe_firm_settings['admin_mail'],
			'mail_from_name'           => $wpe_mail_settings['mail_from_name'] ?? $wpe_firm_settings['mail_from_name'],
			'owner_name' 			   => '',
			'firm_phone'			   => '(XXX) XXX-XXXX',
		];

		$wpe_settings = [];
		if( $wpe_firm_settings === FALSE ) {
			$wpe_settings = $default_settings;
		} else {
			foreach ( $default_settings as $key => $value ) {
				if ( !isset( $wpe_settings[ $key ] ) ) {
					$wpe_settings[ $key ] = $value;
				}
			}
		}

		return update_option( 'wpe_firm_settings', $wpe_settings );
	}
}

if( ! function_exists( 'wpe_set_general_settings_defaults' ) ) {
	/**
	 * Adds new settings fields default values when plugin
	 * is updated
	 *
	 * @return bool
	 * @since 1.2.5
	*/
	function wpe_set_general_settings_defaults(): bool {
		//only for version 1.2.5
		if ( version_compare( get_option( 'WP_EVENTS_VERSION' ), '1.2.5' ) !== -1 ) {
			return FALSE;
		}

		// from options
		$wpe_general_settings = get_option( 'wpe_settings' );

		$default_settings = [
			'meta_description' => 'Join us for free seminars for the most up-to-date information on how you can protect your assets during your life and preserve them after your death.',
		];

		$wpe_settings = [];
		if( $wpe_general_settings === FALSE ) {
			$wpe_settings = $default_settings;
		} else {
			foreach ( $default_settings as $key => $value ) {
				if ( !isset( $wpe_settings[ $key ] ) ) {
					$wpe_settings[ $key ] = $value;
				}
			}
		}

		return update_option( 'wpe_settings', $wpe_settings );
	}
}

if( ! function_exists( 'wpe_set_integration_settings_defaults' ) ) {
	/**
	 * Adds new settings fields default values when plugin
	 * is updated
	 *
	 * @return bool
	 * @since 1.3.0
	*/
	function wpe_set_integration_settings_defaults(): bool {
		//only for version 1.3.0
		if ( version_compare( get_option( 'WP_EVENTS_VERSION' ), '1.3.0' ) !== -1 ) {
			return FALSE;
		}

		// form options
		$wpe_integration_settings = get_option( 'wpe_integration_settings' );

		$default_settings = [
			'gmaps_api'	    => '',
			'gmaps_type' 	=> 'button',
		];

		$wpe_settings = [];
		if( $wpe_integration_settings === FALSE ) {
			$wpe_settings = $default_settings;
		} else {
			foreach ( $default_settings as $key => $value ) {
				if ( !isset( $wpe_settings[ $key ] ) ) {
					$wpe_settings[ $key ] = $value;
				}
			}
		}

		return update_option( 'wpe_integration_settings', $wpe_settings );
	}
}
