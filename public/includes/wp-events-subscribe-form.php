<?php
/**
 * Wp events Subscribe form
*/
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Displays Subscriber Form when No event exists
 *
 * @since 1.0.0
 */
if( !function_exists( 'wpe_display_subscribe_form' ) ) {
	function wpe_display_subscribe_form() {
		/**
		 * Fires before Subscriber Form
		 *
		 * @since  1.0.0
		 * @action wpe_before_subscribe_form
		 */
		do_action( 'wp_event_before_subscribe_form' );

		$form_options               = get_option( 'wpe_forms_settings' );
        $captcha_options            = get_option( 'wpe_reCAPTCHA_settings' );
		$labels                     = isset( $form_options['subscriber_form_labels'] );
		$form_title                 = isset( $form_options['subscriber_form_title'] ) ? $form_options['subscriber_form_title'] : '';
		$form_description           = isset( $form_options['subscriber_form_description'] ) ? $form_options['subscriber_form_description'] : '';
		$form_button                = isset( $form_options['subscriber_form_button'] ) ? $form_options['subscriber_form_button'] : 'Subscribe';
        $form_textin_permission     = isset( $form_options['subscriber_form_texting_permission'] ) ? $form_options['subscriber_form_texting_permission'] : 'I agree to receive texts at the number provided from [wpe_firm_name]. Frequency may vary and include information on appointments, events, and other marketing messages. Message/data rates may apply. To opt-out, text STOP at any time.';
        $hide_phone_number          = isset( $form_options['subscriber_enable_phone_number'] );
        $hide_texting_permission    = isset( $form_options['subscriber_enable_texting_permission'] );
        ?>
        <div class="wpe-form-holder">
            <div class="wpe-subscribe-form-container">
                <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" class="wpe-subscribe-form" id="wpe-subscribe-form" autocomplete="off">
                    <?php wp_nonce_field('wp_events_subscribe_form','wpe_subscribe_form');
                    if( $form_title != '' ) {
                        ?>
                        <h2 class="wpe-h2"><?php echo apply_filters( 'wpe_subscribe_form_heading',  __( $form_title, 'wp-events' ) ); ?></h2>
	                    <?php
                    }
                    if( $form_description != '' ) {
                        ?>
                        <p class="wpe-form-description"><?php echo apply_filters( 'wpe_subscribe_form_description',  __( $form_description, 'wp-events' ) ); ?></p>
	                    <?php
                    }?>
                    <div class="wpe-name-box wpe-col-2">
                        <div class="wpe-form-control">
                            <?php if( $labels ) { echo'<label for="wpe_username">First Name*</label>';}?>
                            <input type="text" name="wpe_first_name" id="wpe_firstname" <?php if( !$labels ) {?>placeholder="First Name*"<?php }?> required>
                            <small>Error Message</small>
                        </div>
                        <div class="wpe-form-control">
                            <?php if( $labels ) { echo'<label for="wpe_username">Last Name*</label>';}?>
                            <input type="text" name="wpe_last_name" id="wpe_lastname" <?php if( !$labels ) {?>placeholder="Last Name*"<?php }?> required>
                            <small>Error Message</small>
                        </div>
                    </div>
                    <div class="wpe-col-2">
                        <div class="wpe-form-control">
                            <?php if( $labels ) { echo'<label for="wpe_email">Email*</label>';}?>
                            <input type="email" name="wpe_email" id="wpe_email" <?php if( !$labels ) {?>placeholder="Email*"<?php }?> required>
                            <small>Error Message</small>
                        </div>
                        <?php 
                        if( $hide_phone_number ) { 
                        ?>
                        <div class="wpe-form-control">
                            <?php if( $labels ) { echo'<label for="wpe_phone">Cell Phone Number*</label>';}?>
                            <input type="text" title="(123) 111-1234" name="wpe_phone" id="wpe_phone" <?php if( !$labels ) {?>placeholder="Cell Phone Number"<?php }?>>
                            <small>Error Message</small>
                        </div>
                        <?php } ?>
                    </div>
                   <?php
                    if( $hide_texting_permission ) { 
                    ?>
                    <div class="wpe-form-control wpe-field-container wpe-full-width wpe-texting-permission">
                        <input  type="checkbox" name="wpe_texting_permission" id="wpe_texting_permission" value="1">
                        <label for="wpe_texting_permission"> <?php echo do_shortcode( $form_textin_permission ); ?></label>
                        <small>Error Message</small>
                    </div>
                    <?php
                    } 
                    ?>
                    <input type="hidden" name="action" value="subscribe_form">
                    <?php
                    $site_key = isset( $captcha_options['reCAPTCHA_site_key'] ) ? $captcha_options['reCAPTCHA_site_key'] : '';
                    if( $site_key !== '' ) {
                        ?>
                        <div class="wpe-form-control wpe-field-container wpe-full-width">
                        <div class="g-recaptcha" data-expired-callback="CaptchaExpired" data-sitekey="<?php echo $site_key ?>" <?php if ( $captcha_options['reCAPTCHA_type'] === 'invisible' ) { echo 'data-size="invisible"'; } ?> ></div>
                        <small class="recaptcha-error">Error Message</small>
                        </div>
                        <?php
                    } else {
                        ?>
                        <span class="g-recaptcha">Captcha not found.</span>
                        <?php
                    }
                    ?>
                    <button id="wpe-button" class="button wpe-button"><?php echo apply_filters( 'wpe_subscribe_form_button_text',  __( $form_button, 'wp-events' ) ); ?></button>
                    <div class="wpe-button-loader"></div>
                </form>
            </div>
        </div>
        <?php
		/**
		 * Fires after Subscriber Form
		 *
		 * @since 1.0.0
		 * @action wpe_after_subscribe_form
		 */
        do_action('wp_event_after_subscribe_form');
    }
}

add_action( 'wp_events_subscribe_form', 'wpe_display_subscribe_form' );


/**
 * Before subscriber form Area       Displays HtML or text before subscriber form
 *
 * @since 1.0.2
*/
if( !function_exists( 'wpe_before_subscribe_form' ) ) {
	function wpe_before_subscribe_form() {
		$before_form_message = get_option( 'wpe_forms_settings' );
		if ( isset( $before_form_message['before_subscriber_form_message'] ) && $before_form_message['before_subscriber_form_message'] !== '' ) {
			$html = '<div class="before-subscribe-form"><p>' . $before_form_message['before_subscriber_form_message'] . '</p></div>';
			echo $html;
		}
	}
}

add_action( 'wp_event_before_subscribe_form', 'wpe_before_subscribe_form' );

/**
 * After subscriber Form     Displays HTML or text after subscriber form
 *
 * @since 1.0.2
*/

if( !function_exists( 'wpe_after_subscribe_form' ) ) {
	function wpe_after_subscribe_form() {
		$after_form_message = get_option( 'wpe_forms_settings' );
		if ( isset($after_form_message['after_subscriber_form_message']) && $after_form_message['after_subscriber_form_message'] !== '' ) {
			$html = '<div class="after-subscribe-form"><p>' . $after_form_message['after_subscriber_form_message'] . '</p></div>';
			echo $html;
		}
	}
}

add_action( 'wp_event_after_subscribe_form', 'wpe_after_subscribe_form' );
