<?php
/**
 * WP Events Registration Form
 *
 * This file mainly consists of HTML
*/
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Displays Registration Form on single page
 *
 * @since 1.0.0
 */
if( !function_exists( 'wpe_registration_form' ) ) {
    function wpe_registration_form() {

        /**
         * Fires Before Registration Form
         *
         * @since 1.0.0
         * @action wpe_before_registration_form
        */
        do_action('wp_event_before_registration_form');

	    $form_options    = get_option( 'wpe_forms_settings' );
	    $captcha_options = get_option( 'wpe_reCAPTCHA_settings' );
	    $labels          = isset( $form_options['form_labels'] );
	    $form_button     = isset( $form_options['registration_form_button'] ) ? $form_options['registration_form_button'] : 'Submit';
        $addrees1        = isset( $form_options['form_address1'] );
        $addrees2        = isset( $form_options['form_address2'] );
        $city            = isset( $form_options['form_city'] );
        $state           = isset( $form_options['form_state'] );
        $zip             = isset( $form_options['form_zip'] );
        $fax             = isset( $form_options['form_fax'] );
        $businessName    = isset( $form_options['form_businessName'] );
        $hearAbout       = isset( $form_options['form_hear_about'] );
        ?>
        <div class="wpe-main-form-holder">
            <div class="wpe-register-form-container">
                <form method="post" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>" class="wpe-register-form" id="wpe-register-form" autocomplete="off">
                <div class="wpe-col-2 wpe-field">
                  <div class="wpe-form-control wpe-field-container wpe-left-half">
                        <?php if( $labels) { echo'<label for="wpe_first_name">First Name *</label>';}?>
                        <input class="wpe-field" type="text" name="wpe_first_name" id="wpe_first_name" <?php if( !$labels ) {?>placeholder="First Name *" <?php } ?> required>
                        <small>Error Message</small>
                   </div>
                   <div class="wpe-form-control wpe-field-container wpe-right-half">
                        <?php if( $labels) { echo'<label for="wpe_last_name">Last Name *</label>';}?>
                        <input class="wpe-field" type="text" name="wpe_last_name" id="wpe_last_name" <?php if( !$labels ) {?>placeholder="Last Name *" <?php } ?>required>
                        <small>Error Message</small>
                   </div>
                </div>
                <div class="wpe-col-2 wpe-field">
                <?php if ( ! $addrees1 ) { ?>
                    <div class="wpe-form-control wpe-field-container wpe-left-half">
                        <?php if( $labels) { echo'<label for="wpe_address">Address *</label>';}?>
                        <input class="wpe-field" type="text" name="wpe_address" id="wpe_address" <?php if( !$labels ) {?>placeholder="Address *" <?php } ?>required>
                        <small>Error Message</small>
                    </div>
                    <?php }   if ( ! $addrees2 ) {?>
                    <div class="wpe-form-control wpe-field-container wpe-right-half">
                        <?php if( $labels) { echo'<label for="wpe_address_2">Address 2</label>';}?>
                        <input class="wpe-field" type="text" name="wpe_address_2" id="wpe_address_2" <?php if( !$labels ) {?>placeholder="Address 2" <?php } ?>>
                        <small>Error Message</small>
                    </div>
                    <?php  } ?>
                </div>
                <div class="wpe-col-3 wpe-field">
                <?php if ( ! $city ) { ?>
                    <div class="wpe-form-control wpe-field-container wpe-left-third">
                        <?php if( $labels) { echo'<label for="wpe_city">City *</label>';}?>
                        <input class="wpe-field" type="text" name="wpe_city" id="wpe_city" <?php if( !$labels ) {?>placeholder="City *" <?php } ?>required>
                        <small>Error Message</small>
                    </div>
                    <?php } if ( ! $state ) { ?>
                    <div class="wpe-form-control wpe-field-container wpe-middle-third">
                        <?php if( $labels) { echo'<label for="wpe_state">State *</label>';}?>
                        <input class="wpe-field" type="text" name="wpe_state" id="wpe_state" <?php if( !$labels ) {?>placeholder="State *" <?php } ?>required>
                        <small>Error Message</small>
                    </div>
                    <?php } if ( ! $zip ) { ?>
                    <div class="wpe-form-control wpe-field-container wpe-right-third">
                        <?php if( $labels) { echo'<label for="wpe_zip">Zip</label>';}?>
                        <input class="wpe-field" type="number" name="wpe_zip" id="wpe_zip" <?php if( !$labels ) {?>placeholder="Zip *" <?php } ?>required>
                        <small>Error Message</small>
                    </div>
                    <?php }?>
                </div>
                <div class="wpe-col-3 wpe-field">
                    <div class="wpe-form-control wpe-field-container wpe-left-third">
                        <?php if ( $labels) { echo'<label for="wpe_phone">Phone *</label>';}?>
                        <input class="wpe-field" type="text" pattern="^(\([0-9]{3}\) |[0-9]{3}-)[0-9]{3}-[0-9]{4}$" title="(123) 111-1234" name="wpe_phone" id="wpe_phone" <?php if( !$labels ) {?>placeholder="Phone*: (XXX) XXX-XXXX" <?php } ?>required>
                        <small>Error Message</small>
                    </div>
                    <div class="wpe-form-control wpe-field-container wpe-middle-third">
                        <?php if( $labels) { echo'<label for="wpe_email">Email *</label>';}?>
                        <input class="wpe-field" type="email" name="wpe_email" id="wpe_email" <?php if( !$labels ) {?>placeholder="Email *" <?php } ?> required>
                        <small>Error Message</small>
                    </div>
                    <?php if ( ! $fax ) { ?>
                    <div class="wpe-form-control wpe-field-container wpe-right-third">
                        <?php if( $labels) { echo'<label for="wpe_fax">Fax</label>';}?>
                        <input class="wpe-field" type="number" name="wpe_fax" id="wpe_fax" <?php if( !$labels ) {?>placeholder="Fax"<?php } ?>>
                        <small>Error Message</small>
                    </div>
                    <?php } ?>
                </div>
                <?php if ( ! $businessName ) { ?>
                <div class="wpe-col-full wpe-field">
                    <div class="wpe-form-control wpe-field-container wpe-full-width">
                        <?php if( $labels) { echo'<label for="wpe_business_name">Business Name</label>';}?>
                        <input class="wpe-field" type="text" name="wpe_business_name" id="wpe_business_name" <?php if( !$labels ) {?>placeholder="Business Name" <?php } ?>>
                        <small>Error Message</small>
                    </div>
                </div>
                <?php } ?>
                <div class="wpe-col-full wpe-field">
                    <?php
                     if ( ! $hearAbout ) {
                    //display dropdown for hear about us
                    wpe_get_dropdown( 'hear_about_us', 'How did you hear about us?', wpe_get_hearaboutus_options() );
                }
                    $option = get_option('wpe_settings');
                    if( isset( $option['privacy_policy'] ) && $option['privacy_policy'] !== '' ) {?>
                    <div class="wpe-form-control wpe-field-container wpe-full-width">
                        <label for="wpe_privacy_policy">Privacy Policy</label>
                        <textarea class="wpe-field" name="wpe_settings[privacy_policy]" id="wpe_privacy_policy" readonly><?php
                            echo trim($option['privacy_policy']);  ?></textarea>
                        <small>Error Message</small>
                    </div>
                    <?php } ?>
                </div>
                <?php
                $consent_box    = isset( $form_options['consent_checkbox'] ) ? $form_options['consent_checkbox'] : 'I have read & consent to the above.*';
                $disclaimer_box = isset( $form_options['disclaimer_checkbox'] ) ? $form_options['disclaimer_checkbox'] : 'I have read & understand your website Disclaimer.*';
                ?>
                <div class="wpe-col-full wpe-field">
                    <div class="wpe-form-control wpe-field-container wpe-full-width">
                        <input  type="checkbox" name="wpe_consent_box" id="wpe_consent_box" value="I have read &amp; consent to the above." required>
                        <label for="wpe_consent_box"><?php echo $consent_box ?></label>
                        <small>Error Message</small>
                    </div>
                    <div class="wpe-form-control wpe-field-container wpe-full-width">
                        <input  type="checkbox" name="wpe_disclaimer_box" id="wpe_disclaimer_box" value="I have read &amp; understand your website Disclaimer." required>
                        <label for="wpe_disclaimer_box"> <?php echo $disclaimer_box ?></label>
                        <small>Error Message</small>
                    </div>
                </div>
                <div class="wpe-col-full wpe-field">
                    <div class="wpe-form-control wpe-field-container wpe-full-width">
	                    <?php
	                    if ( $labels ) {
		                    ?>
                            <label for="event-seats">Seats *</label>
		                    <?php
	                    }
                        wpe_get_seats_dropdown();
	                    ?>
                        <small>Error Message</small>
                    </div>
                </div>
                <div class="guest-info wpe-form-control wpe-field-container wpe-full-width">
                    <div style="display: none" class="wpe-col-2 wpe-field guest-box">
                        <div class="wpe-form-control wpe-field-container wpe-left-half">
                            <?php if( $labels) { echo'<label>Guest First Name*</label>';}?>
                            <input class="wpe-field wpe-guest-field" type="text" name="wpe_guest_first_name[]" <?php if( !$labels ) {?>placeholder="Guest First Name*" <?php } ?>>
                            <small>Error Message</small>
                        </div>
                        <div class="wpe-form-control wpe-field-container wpe-right-half">
                            <?php if( $labels) { echo'<label>Guest Last name*</label>';}?>
                            <input class="wpe-field wpe-guest-field" type="text" name="wpe_guest_last_name[]" <?php if( !$labels ) {?>placeholder="Guest Last name*" <?php } ?>>
                            <small>Error Message</small>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="action" value="registration_form">
                <input type="hidden" name="post" value="<?php echo get_the_ID(); ?>">
                <?php wp_nonce_field('wp_event_registration_form','wpe_register_form_nonce');
                $site_key = isset( $captcha_options['reCAPTCHA_site_key'] ) ? $captcha_options['reCAPTCHA_site_key'] : '';
                if( $site_key !== '' ) {
                    ?>
                <div class="form-flex">
                    <div class="wpe-form-control wpe-field-container wpe-full-width">
                        <div class="g-recaptcha" data-expired-callback="CaptchaExpired" data-sitekey="<?php echo $site_key ?>" <?php if ( $captcha_options['reCAPTCHA_type'] === 'invisible' ) { echo 'data-size="invisible"'; } ?> ></div>
                        <small class="recaptcha-error">Error Message</small>
                    </div>
                </div>
                        <?php
                    } else {
                        ?>
                        <span class="g-recaptcha">Captcha not found.</span>
                        <?php
                    }
                    ?>
                    <div class="wpe-form-control wpe-field-container wpe-submit-button">
                        <button id="wpe-button" class="button wpe-button"><?php echo apply_filters( 'wpe_registration_form_button_text',  __( $form_button, 'wp-events' ) ); ?> </button>
                    </div>
                    <div class="wpe-button-loader"></div>
                </form>
            </div>
        </div>

        <?php
        /**
         * Fires after Registration Form
         *
         * @since 1.0.0
         * @action wpe_after_registration_form
         */
        do_action('wp_event_after_registration_form');
    }
}

add_action('wp_events_registration_form', 'wpe_registration_form');


/**
 * Before Registration Form     Displays HTML or text before registration form
 *
 * @since 1.0.2
*/
if( !function_exists( 'wpe_before_registration_form' ) ) {
	function wpe_before_registration_form() {
		$before_form_message = get_option( 'wpe_forms_settings' );
		if ( isset( $before_form_message['before_registration_form_message'] ) && $before_form_message['before_registration_form_message'] !== '' ) {
			$html = '<div class="before-registration-form"><p>' . $before_form_message['before_registration_form_message'] . '</p></div>';
			echo $html;
		}
	}
}

add_action( 'wp_event_before_registration_form', 'wpe_before_registration_form' );


/**
 * After Registration Form     Displays HTML or text after registration form
 *
 * @since 1.0.2
 */
if( !function_exists( 'wpe_after_registration_form' ) ) {
	function wpe_after_registration_form() {
		$after_form_message = get_option( 'wpe_forms_settings' );
		if ( isset( $after_form_message['after_registration_form_message'] ) && $after_form_message['after_registration_form_message'] !== '' ) {
			$html = '<div class="after-registration-form"><p>' . $after_form_message['after_registration_form_message'] . '</p></div>';
			echo $html;
		}
	}
}

add_action( 'wp_event_after_registration_form', 'wpe_after_registration_form' );
