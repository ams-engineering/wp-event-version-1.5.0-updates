<?php
/**
 * This class wil handle all the admin POST requests for forms
 *
 * @package wp-events/admin
 * @subpackage wp-events/admin/includes
 */

/**
 * If this file is called directly, abort.
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Wp_Admin_Request {

    /**
     * handles ajax request for resend notification
     * when entry is edited
     *
     * @since 1.2.0
     */
    public function wpe_resend_notification() {

		$formData          = isset( $_POST['formData'] ) ? $_POST['formData'] : [];
        $tab               = isset( $_POST['displayTab'] ) ? $_POST['displayTab'] : 'registrations';
        $admin_noification = $_POST['adminNoti'];
        $mail_options      = get_option('wpe_mail_settings');
        $firm_info         = get_option('wpe_firm_settings');
        $from_name         = $firm_info['mail_from_name'];
		$from_email        = $mail_options['mail_from'];
		$headers[]         = 'Content-Type: text/html;';
		$headers[]         = "from: $from_name <$from_email>";

        Wpe_Shortcodes::set_form_data( $formData );

        if( !isset( $formData ) || $formData == '' ) {
            wpe_send_ajax_response( 0 );
        }


        if( $admin_noification === 'true' ) {
            $admin_subject = 'Entry Edited for '.do_shortcode("[wpe_event_name]");
            $admin_message = 'This is an auto-generated email confirming change in an event reservation made from your website. A notification email has also been resent to the registrant, ( [wpe_user_first_name] ) at [wpe_user_email].<br />
            <br />
            Event Details:<br />
            [wpe_event_details]<br />
            User Details:<br />
            [wpe_registration_details]<br />
            The above visitor information has been added to the WordPress Event database. You can access this information by going to your Website WordPress Dashboard.';
            $admin_message = do_shortcode( $admin_message, TRUE );
            wp_mail( $firm_info['admin_mail'], $admin_subject, $admin_message, $headers );
        }

        $to       = $formData['wpe_email'];
        $subject  = 'Your registration for '. do_shortcode("[wpe_event_name]") .' is Edited';
        $message  = 'Dear [wpe_user_first_name] [wpe_user_last_name],<br />
        Thank you for registering for our upcoming Event. This is an auto-generated email confirming change of details for your registration for our upcoming Event. <br />
        <br />
        The new details of your registration are following.<br />
        [wpe_event_details] <br />
        [wpe_registration_details]<br />
        If you have any questions, please feel free to contact us at our office number or via email.<br />
        We look forward to seeing you.<br />
        Sincerely,';
        $message  = do_shortcode( $message, TRUE );
        wp_mail( $to, $subject, $message, $headers );

        wpe_send_ajax_response( 1 );

	}

    /**
     * handles ajax request for move to trash button
     * on view entry page
     *
     * @since 1.2.0
     */
    public function wpe_trash_restore() {

        $button_text = $_POST['text'];
        $entry_id    = $_POST['entryID'];
        $tab         = isset( $_POST['displayTab'] ) ? $_POST['displayTab'] : 'registrations';
        $val         = WPE_TRASHED;
        global $wpdb;

        if( $button_text !== 'Move To Trash' && $button_text !== 'Restore' ) {
            wpe_send_ajax_response( 0 );
        }

        if( $tab === 'registrations' ) {
            $table_name = 'events_registration';
            if( $button_text === 'Restore' ) {
                $val = WPE_PENDING;
            }
        } else {
            $table_name = 'events_subscribers';
            if( $button_text === 'Restore' ) {
                $val = WPE_ACTIVE;
            }
        }

		$result = $wpdb->update(
			"{$wpdb->prefix}$table_name",
			['wpe_status' => $val],
			['id' => $entry_id],
			'%d',
			'%d'
		);

        wpe_send_ajax_response( 1 );
	}

    /**
     * Handles ajax request when approval checkbox is checked/unchecked
     *
     * @since 1.2.0
     */
    public function wpe_update_entry_status() {
        global $wpdb;
        $table_name = 'events_registration';

        if( $_POST['checkbox'] === 'true' ) {
            $update = $wpdb->update(
                "{$wpdb->prefix}$table_name",
                ['wpe_status' => WPE_PENDING],
                ['wpe_status' => WPE_ACTIVE],
                '%d',
                '%d'
            );

            wpe_send_ajax_response( 1 );

        } else {
            $update = $wpdb->update(
                "{$wpdb->prefix}$table_name",
                ['wpe_status' => WPE_ACTIVE],
                ['wpe_status' => WPE_APPROVED],
                '%d',
                '%d'
            );
            $update = $wpdb->update(
                "{$wpdb->prefix}$table_name",
                ['wpe_status' => WPE_ACTIVE],
                ['wpe_status' => WPE_PENDING],
                '%d',
                '%d'
            );
            $update2 = $wpdb->update(
                "{$wpdb->prefix}$table_name",
                ['wpe_status' => WPE_TRASH],
                ['wpe_status' => WPE_CANCELLED],
                '%d',
                '%d'
            );
            wpe_send_ajax_response( 1 );
        }
    }

    /**
     * Ajax request for updating location data on edit event page
     *
     * @since 1.3.0
     */
    public function wpe_update_location() {
        $postID           = $_POST['locationID'];
        $eventID          = $_POST['eventID'];
        $options          = get_option( 'wpe_integration_settings' );
        $maps_key         = $options['gmaps_api'];
        $maps_type        = $options['gmaps_type'];
        $pattern          = array( ' ', '-', '&' );
        $wpeObj           = new stdClass();
        if ( $postID === 'xxx' ) {
            $wpeObj->map_url  = '';
        } else {
            $wpeObj->map_url  = get_post_meta( $eventID, 'wpevent-map-url', true ) ? get_post_meta( $eventID, 'wpevent-map-url', true ) : '';
        }
        $wpeObj->venue    = get_post_meta( $postID, 'wpevent-loc-venue', true ) ? get_post_meta( $postID, 'wpevent-loc-venue', true ) : '';
        $venue            = str_replace( $pattern, '+', $wpeObj->venue) ?? '';
        $wpeObj->address  = get_post_meta( $postID, 'wpevent-loc-address', true ) ? get_post_meta( $postID, 'wpevent-loc-address', true ) : '';
        $address          = str_replace( $pattern, '+', $wpeObj->address) ?? '';
        $wpeObj->country  = get_post_meta( $postID, 'wpevent-loc-country', true ) ? get_post_meta( $postID, 'wpevent-loc-country', true ) : '';
        $wpeObj->city     = get_post_meta( $postID, 'wpevent-loc-city', true ) ? get_post_meta( $postID, 'wpevent-loc-city', true ) : '';
        $city             = str_replace( $pattern, '+', $wpeObj->city) ?? '';
        $wpeObj->state    = get_post_meta( $postID, 'wpevent-loc-state', true ) ? get_post_meta( $postID, 'wpevent-loc-state', true ) : '';
        $state            = str_replace( $pattern, '+', $wpeObj->state) ?? '';
        $wpeObj->zip      = get_post_meta( $postID, 'wpevent-loc-zip', true ) ? get_post_meta( $postID, 'wpevent-loc-zip', true ) : '';
        if( $maps_type === 'embed_map' && ( $venue !== '' || $address !== '' || $city !== '' || $state !== '' ) && $maps_key !== '' ) {
            $wpeObj->map_url  = "https://www.google.com/maps/embed/v1/place?key=" . $maps_key . " &q=" . $venue . '+' . $address . "," . $city . '+' . $state;
        }
        if( $maps_type === 'button' && ( $venue !== '' || $address !== '' || $city !== '' || $state !== '' ) ) {
            $wpeObj->map_url  = "https://www.google.com/maps/place?q=" . $venue . '+' . $address . "," . $city . '+' . $state;
        }
        $location_obj     = json_encode( $wpeObj );
        wpe_send_ajax_response( $location_obj );
    }

    /**
     * Ajax request for creating new location in location CPT
     *
     * @since 1.3.0
     */
    public function wpe_create_location() {
        $location_data = $_POST['location'];
        $postTitle     = stripcslashes( $location_data['venue'] );
        if ( $location_data['venue'] === '' || $location_data['address'] === '' 
        || $location_data['country'] === '' || $location_data['city'] === ''
        || $location_data['state'] === '' || $location_data['zip'] === '' ) {
            wpe_send_ajax_response( 'Please fill all fields!' );
        }
        $post_arr = array(
            'post_title'   => $location_data['venue'],
            'post_type'    => 'locations',
            'post_status'  => 'publish',
            'meta_input'   => array(
                'wpevent-loc-venue'   => $location_data['venue'],
                'wpevent-loc-address' => $location_data['address'],
                'wpevent-loc-country' => $location_data['country'],
                'wpevent-loc-city'    => $location_data['city'],
                'wpevent-loc-state'   => $location_data['state'],
                'wpevent-loc-zip'     => $location_data['zip'],
            ),
        );

        $args = array(
            'post_type'      => 'locations',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        );

        $locations = get_posts( $args );
        for( $i = 0; $i < sizeof( $locations ); $i++ ) {
            if ( $locations[ $i ]->post_title === $postTitle ) {
                wpe_send_ajax_response( 'Location Already Exists!' );
            }
        }

        $post_id = wp_insert_post( $post_arr );
        wpe_send_ajax_response( $post_id );
    }

    /**
     * Ajax request for uploading CSV
     * and importing the events.
     *
     * @since 1.5.0
     */
    public function wpe_upload_file() {  
        $target_dir  = wp_upload_dir();
        $target_file = $target_dir['url'] . '/' . basename( $_FILES["fileUpload"]["name"] );
        $uploadOk    = 1;
        $FileType    = strtolower( pathinfo( $target_file, PATHINFO_EXTENSION ) );

        if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax-nonce' ) ) {
            //$uploadOk = 0;
                die ( 'Busted!');
        }

        if ( ! wp_doing_ajax() ) {
            die();
        }

        // Check file size
        if ( $_FILES["fileUpload"]["size"] > 200000 ) {
            $uploadOk = 2;
        }

        // Allow certain file formats
        if( $FileType != "csv" ) {
            $uploadOk = 3;
        }

        // Check if $uploadOk is set to 0 by an error
        if ( $uploadOk != 1 ) {
            wpe_send_ajax_response( $uploadOk );
        // if everything is ok, try to upload file
        } else {
            if( isset( $_FILES['fileUpload'] ) ) {
                $upload   = wp_upload_bits( $_FILES["fileUpload"]["name"], null, file_get_contents( $_FILES["fileUpload"]["tmp_name"] ) );
                $uploadOk = $this->wpe_importFile( $upload );
            } else {
                $uploadOk = 4;
            }
        }
        wpe_send_ajax_response( $uploadOk );
    }

    /**
     * Imports events to CPT from uploaded file.
     *
     * @param array $upload
     * @since 1.5.0
     */
    public function wpe_importFile( $upload ) {
        if( $upload['error'] == false ) {
            $file          = fopen( $upload['url'], "r" );
            $file_contents = fgetcsv( $file );
            if ( $file_contents[1] == 'Event Name' && $file_contents[2] == 'Start Date' && $file_contents[3] == 'End Date'
            && $file_contents[4] == 'Start Time' && $file_contents[5] == 'End Time' && $file_contents[6] == 'Venue' && $file_contents[7] == 'Address'
            && $file_contents[8] == 'City' && $file_contents[9] == 'State' && $file_contents[10] == 'Country'
            && $file_contents[11] == 'Total Seats' && $file_contents[14] == 'Events Type' && $file_contents[16] == 'Phone' ) {
                while( ! feof( $file ) ) {
                    $file_contents = fgetcsv( $file );
                    $my_post = array(
                        'post_title'              => $file_contents[1],
                        'post_status'             => 'publish',
                        'post_type'               => 'wp_events',
                        'meta_input'              => array(
                        'wpevent-type'            => $file_contents[14],
                        'wpevent-start-date-time' => strtotime( $file_contents[2] . ' ' . $file_contents[4] ),
                        'wpevent-end-date-time'   => strtotime( $file_contents[3] . ' ' . $file_contents[5] ),
                        'wpevent-venue'           => $file_contents[6],
                        'wpevent-address'         => $file_contents[7],
                        'wpevent-city'            => $file_contents[8],
                        'wpevent-state'           => $file_contents[9],
                        'wpevent-country'         => $file_contents[10],
                        'wpevent-seats'           => $file_contents[11],
                        'wpevent-phone'           => $file_contents[16],
                        )
                    );
                    
                    // Insert the post into the database
                    $post_id = wp_insert_post( $my_post );
                    if( $post_id ){
                        $uploadOk = $upload['url'];
                    } else {
                        $uploadOk = 5;
                    }
                }
            } else {
                $uploadOk = 6;
            }

            fclose( $file ); 
        }
        return $uploadOk;
    }
}