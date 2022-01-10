<?php

/**
 * Function calls to instantiate and display the registration form.
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       //allmarketingsolutions.co.uk
 * @since      1.2.0
 *
 * @package    Wp_Events
 * @subpackage Wp_Events/admin/includes
 */

if ( ! function_exists( 'wpe_add_entry_fields' ) ) {
	/**
     * Creates fields for the registration form
	 *
	 * @since 1.2.0
	 */
	function wpe_add_entry_fields() {
        if ( isset( $_GET['entry'] ) && $_GET['entry'] !== '' ) {
            $entry_id    = $_GET['entry'];
            $seats       = [];
            $form_fields = [];
            $guest_class = array('guest-div');
            if ( isset( $_GET['tab'] ) && $_GET['tab'] === 'registrations' ) {
                $results  = Wp_Events_Db_Actions::wpe_get_registration_data( $entry_id );
                $seats = wpe_seat_options( $results );
                if ( $results[0]->wpe_seats >= 2 ) {
                    $guest_class[] = 'wpe-show';
                } else {
                    $guest_class[] = 'wpe-hidden';
                }

                $form_fields = array(
                    'wpe_first_name'    => array(
                        'label' 	    => 'First Name',
                        'value'         => $results[0]->first_name,
                        'required'      => true,
                    ),
                    'wpe_last_name'     => array(
                        'label' 	    => 'Last Name',
                        'value'         => $results[0]->last_name,
                        'required'      => true,
                    ),
                    'wpe_address'       => array(
                        'label' 	    => 'Address',
                        'value'         => $results[0]->addres_one,
                    ),
                    'wpe_address_2'     => array(
                        'label' 	    => 'Address2',
                        'value'         => $results[0]->addres_two,
                    ),
                    'wpe_city'          => array(
                        'label'	        => 'City',
                        'value'         => $results[0]->city,
                    ),
                    'wpe_state'         => array(
                        'label' 	    => 'State',
                        'value'         => $results[0]->state,
                    ),
                    'wpe_zip'           => array(
                        'label' 	    => 'Zip',
                        'value'         => $results[0]->zip,
                    ),
                    'wpe_phone'         => array(
                        'label' 	    => 'Phone',
                        'value'         => $results[0]->phone,
                        'required'      => true,
                    ),
                    'wpe_email'         => array(
                        'label'	        => 'Email',
                        'value'         => $results[0]->email,
                    ),
                    'wpe_fax'           => array(
                        'label'	        => 'Fax',
                        'value'         => $results[0]->fax,
                    ),
                    'wpe_business_name' => array(
                        'label'	        => 'Business Name',
                        'value'         => $results[0]->business_name,
                    ),
                    'hear_about_us'     => array(
                        'label'         => 'How did you hear about us?',
                        'type'	        => 'select',
                        'value'         => $results[0]->hear_about_us,
                        'required'      => true,
                        'options'       => wpe_get_hearaboutus_options(),
                    ),
                    'wpe_seats'         => array(
                        'label'	        => 'Seats',
                        'type'	        => 'select',
                        'value'         => $results[0]->wpe_seats,
                        'required'      => true,
                        'options'       => $seats,
                    ),
                    'guests'            => array(
                        'label'         => 'Guests',
                        'value'         => $results[0]->guests,
                        'guest-class'   => $guest_class,
                    ),
                    'action'            => array(
                        'type'	        => 'hidden',
                        'value'         => 'registration_form',
                    ),
                    'post' => array(
                        'type'	        => 'hidden',
                        'value'         => $results[0]->post_id,
                    ),
                    'entry' => array(
                        'type'	        => 'hidden',
                        'value'         => $results[0]->ID,
                    ),
                );
            }
        
            

            /**
             * to customize fields displayed on view entry page
             * 
             * used to hook wpe_add_subscribers_fields function
             * 
             * @param array $form_fields
             * 
             * @filter add_filter( 'wpe_add_entry_fields', 'wpe_add_subscribers_fields' );
             * @since 1.2.0
             */
            $form_fields = apply_filters( 'wpe_add_entry_fields', $form_fields );

            return $form_fields;
        }

        return;

    }
}

if ( ! function_exists( 'wpe_add_subscribers_fields' ) ) {
	/**
     * Creates fields for the subscribers form
	 *
	 * @since 1.2.0
	 */
	function wpe_add_subscribers_fields() {
        if ( isset( $_GET['entry'] ) && $_GET['entry'] !== '' ) {
            $entry_id = $_GET['entry'];
            $results  = Wp_Events_Db_Actions::wpe_get_subscription_data( $entry_id );

            $form_fields = array(
                'wpe_first_name'    => array(
                    'label' 	    => 'First Name',
                    'value'         => $results[0]->subscriber_firstname,
                    'required'      => true,
                ),
                'wpe_last_name'     => array(
                    'label'	        => 'Last Name',
                    'value'         => $results[0]->subscriber_lastname,
                    'required'      => true,
                ),
                'wpe_email'         => array(
                    'label'	        => 'Email',
                    'value'         => $results[0]->subscriber_email,
                    'required'      => true,
                ),
                'wpe_phone'         => array(
                    'label'	        => 'Cell Phone',
                    'value'         => $results[0]->subscriber_phone,
                ),
                'wpe_texting'       => array(
                    'type'          => 'checkbox',
                    'disabled'      => true,
                    'label'	        => 'Texting Permissions',
                    'value'         => $results[0]->subscriber_texting_permission,
                ),
                'action' => array(
                    'type'  	    => 'hidden',
                    'value'         => 'subscribe_form',
                ),
                'entry' => array(
                    'type'  	    => 'hidden',
                    'value'         => $results[0]->id,
                ),
            );

            return $form_fields;
        }
        return;
    }
}

if( isset( $_GET['tab'] ) && $_GET['tab'] == 'subscriptions' ) {
    $form_fields = add_filter( 'wpe_add_entry_fields', 'wpe_add_subscribers_fields' );
}

if ( ! function_exists( 'wpe_display_entry_form' ) ) {
	/**
     * Displays fields for the registration form
	 *
	 * @since 1.2.0
	 */
	function wpe_display_entry_form() {
        if ( isset( $_GET['entry'] ) && $_GET['entry'] !== '' ) {
            if ( isset( $_GET['tab'] ) && $_GET['tab'] === 'registrations' ) {
                $event_id = Wp_Events_Db_Actions::wpe_get_event_id( $_GET['entry'] );
                $entry_title = 'Event: ' . get_the_title( $event_id );
            } else  {
                $entry_title = 'Entry # ' . $_GET['entry'];
            }
            $form_fields = wpe_add_entry_fields();
            ?>
            <span class="wpe-entry-header">
            <span class="wpe-entry-title"><?php echo $entry_title ?></span>
            <!-- <span class="wpe-show-empty">
            <input type="checkbox" id="wpe-show-empty" name="wpe-show-empty" value="1">
            <label for="wpe-show-empty">Show Empty Fields</label></span> -->
            </span>
            <form method="post" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>" class="wpe-register-form disabledform wpe-edit-entry-form" id="wpe-edit-entry-form">
            <?php

            foreach ( $form_fields as $key => $field ) {
                wpe_form_field( $key, $field );
            }
            wp_nonce_field('wp_event_entry_form','wpe_entry_form_nonce');
            ?>
            </form>
            <?php
        } else echo 'Entry Not Found!';
    }
}

add_action( 'wpe_entry_form', 'wpe_display_entry_form' );

if ( ! function_exists( 'wpe_get_entry_sidebar' ) ) {
	/**
     * Creates sidebar for view registration page
	 *
	 * @since 1.2.0
	 */
	function wpe_get_entry_sidebar() {
        if ( isset( $_GET['entry'] ) && $_GET['entry'] !== '' ) {
            $tab       = isset( $_GET['tab'] ) ? $_GET['tab'] : '';
            $event_url = '';
            $info      = wpe_get_entry_info();
            $footer    = wpe_sidebar_footer( 'Move To Trash', 'Edit' );

            if ( $tab === 'registrations' ) {
                $event_url = '<span>Event URL: '. $info['event-url'] .' </span>';
            }

            if ( $info['entry-status'] === 'Trash' ) {
                $footer = wpe_sidebar_footer( 'Restore', 'Edit' );
            }
            $entry_info =  '<span>Entry ID: '. $info['entry-id'] .'</span>
                            <span>Submitted On: '. $info['submitted-on'] .'</span>
                            <span class="wpe-entry-status">Entry Status: '. $info['entry-status'] .' </span>
                            '. $event_url .''
                            . $footer;

            $notification_body = '<input type="checkbox" id="wpe-entry-notification" name="wpe-entry-notification" value="1">
            <label for="wpe-entry-notification">Admin Notification</label><br>
            <button title="Resend Registrant Notification" class="wpe-btn" id="resend-btn">Resend</button>';

            wpe_sidebar_section( 'Entry', $entry_info );
            if ( $tab === 'registrations' ) {
                wpe_sidebar_section( 'Notifications', $notification_body );
            }
        } else {
            $error_info = 'Entry Not Found!';
            wpe_sidebar_section( 'Entry', $error_info );
        }
    }
}

add_action( 'wpe_entry_sidebar', 'wpe_get_entry_sidebar' );

if ( ! function_exists( 'wpe_prev_next_entry' ) ) {
	/**
     * Displays controls to switch to next or previous entry
	 *
	 * @since 1.2.0
	 */
	function wpe_prev_next_entry() {
        if ( isset( $_GET['entry'] ) && $_GET['entry'] !== '' ) {
            $entry_id = $_GET['entry'];
            $tab      = isset( $_GET['tab'] ) ? $_GET['tab'] : '';
            $display  = isset( $_GET["display"] ) ? $_GET["display"] : 'all';
            if ( $tab == 'registrations' ) {
                switch ( $display ) {
                    case 'pending':
                        $data = Wp_Events_Db_Actions::wpe_get_registration_data( '', WPE_PENDING );
                        break;
                    case 'approved':
                        $data = Wp_Events_Db_Actions::wpe_get_registration_data( '', WPE_APPROVED );
                        break;
                    case 'cancelled':
                        $data = Wp_Events_Db_Actions::wpe_get_registration_data( '', WPE_CANCELLED );
                        break;
                    case 'trash':
                        $data = Wp_Events_Db_Actions::wpe_get_registration_data( '', WPE_TRASHED );
                        break;
                    default:
                    $data = Wp_Events_Db_Actions::wpe_get_registration_data( '', WPE_ACTIVE . ', ' . WPE_APPROVED . ', ' . WPE_CANCELLED . ', ' . WPE_PENDING );
                }
                $id = 'ID';
            } else {
                if ( $display === 'trash' ) {
                    $data = Wp_Events_Db_Actions::wpe_get_subscription_data( '', WPE_TRASHED );
                } else {
                    $data = Wp_Events_Db_Actions::wpe_get_subscription_data( '', WPE_ACTIVE );
                }
                $id = 'id';
            }
        }
        
        $entry_number = '1';
        $eventID      = '';

        foreach( $data as $key => $entry_data ) {
            if( $entry_data->wpe_status == WPE_DELETED ) {
                unset( $data[ $key ] );
            }
            if( isset( $_GET['event'] ) && $_GET['event'] !== '' ) {
                $eventID = '&event='.$_GET['event'];
                if( $entry_data->post_id !== $_GET['event'] ) {
                    unset( $data[ $key ] );
                }
            }
        }

        $new_data     = array_values( $data );
        $entry_number = 1;

        foreach( $new_data as $key => $entry_data ) {
            if( $entry_id === $entry_data->$id ) {
                $index = $key;
                $entry_number = (int) $index + 1;
            }
        }

        $size         = sizeof( $new_data );
        $href_before  = '#';
        $href_after   = '#';

        $new_data     = array_values( $new_data );

        if( $size > 1 ) {
            $before      = $index > 0 ? $new_data[ $index - 1 ] : "";
            $after       = ( $index + 1 ) < count( $new_data ) ? $new_data[ $index + 1 ] : "";
            if ( $before != "" ) {
                $href_before = 'edit.php?post_type=wp_events&page=wpe_view_entry'. $eventID .'&entry=' . $before->$id . '&tab=' . $tab . '&display=' . $display;
            }
            if ( $after != "" ) {
                $href_after = 'edit.php?post_type=wp_events&page=wpe_view_entry'. $eventID .'&entry=' . $after->$id . '&tab=' . $tab . '&display=' . $display;
            }
        }
        
        ?>
        <span class="wpe-switch-entry">Entry <?php echo (string) $entry_number ?> of <?php echo $size; ?>
        <a id="wpe-entry-previous" href="<?php echo $href_before ?>" title="Previous"><span class="dashicons dashicons-arrow-left-alt"></span></a>
        <a id="wpe-entry-next" href="<?php echo $href_after ?>" title="Next"><span class="dashicons dashicons-arrow-right-alt"></span></a>
        </span>
        <?php
    }
}

add_action( 'wpe_entry_controls', 'wpe_prev_next_entry' );

if ( ! function_exists( 'wpe_get_entry_info' ) ) {
	/**
     * Retrieves and returns entry info displayed in sidebar
	 *
     * @return array
	 * @since 1.2.0
	 */
	function wpe_get_entry_info() {
        if ( isset( $_GET['entry'] ) && $_GET['entry'] !== '' ) {
            $entry_id = $_GET['entry'];
            $tab      = isset( $_GET['tab'] ) ? $_GET['tab'] : '';
            if ( $tab == 'registrations' ) {
                $data = Wp_Events_Db_Actions::wpe_get_registration_data( $entry_id );
            } else {
                $data = Wp_Events_Db_Actions::wpe_get_subscription_data( $entry_id );
            }
        }
        $info = [
            'entry-id'     => $entry_id,
            'submitted-on' => $data[0]->time_generated,
            'entry-status' => wpe_get_entry_status( $data[0]->wpe_status ),
        ];

        if ( $tab === 'registrations' ) {
            $info['event-url'] = '<a href="'. get_the_permalink( $data[0]->post_id ) .'">'. get_the_permalink( $data[0]->post_id ) .'</a>';
        }

        return $info;
    }
}

if ( ! function_exists( 'wpe_go_back_link' ) ) {
	/**
     * Creates url for the go back button.
	 *
	 * @since 1.2.0
	 */
	function wpe_go_back_link() {
        if ( isset( $_GET['posts_page'] ) && $_GET['posts_page'] > 0 ) {
            $status = isset( $_GET['event_status'] ) ? $_GET['event_status'] : '';
            if ( $status !== '' ) {
                $status = '&event_status='. $status;
            }
            $post_status = isset( $_GET['post_status'] ) ? $_GET['post_status'] : '';
            if ( $post_status !== '' ) {
                $post_status = '&post_status='. $post_status;
            }
            echo '<span class="go-back-link"><a class="button" href="edit.php?post_type=wp_events&paged='. $_GET['posts_page'] . $status . $post_status .'" title="Go back"><span class="dashicons dashicons-arrow-left-alt"></span>Go Back</a></span>';
        }
    }
}
