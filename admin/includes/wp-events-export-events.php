<?php

/**
 * Exports Events and registrations to a JSON File under Import/Export tab on settings page
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       //allmarketingsolutions.co.uk
 * @since      1.4.2
 *
 * @package    Wp_Events
 * @subpackage Wp_Events/admin/includes
 */

function wp_get_ajax_events() {

	$args = array(
		'post_type'		 => 'wp_events',
		'posts_per_page' => '-1',
		'post_status' => array( 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash' ),
	);

	$event_status = $_POST['postStatus'];
	$include_reg  = $_POST['includeReg'];
	$include_sub  = $_POST['includeSub'];

	if ( $event_status === 'Past' ) {
		$args['meta_query'] = [
			[
				'key'     => 'wpevent-end-date-time',
				'compare' => '<',
				'value'   => strtotime( current_time( 'mysql' ) ),
				'type'    => 'numeric',
			],
		];
	} else if ( $event_status === 'Future' ) {
		$args['meta_query'] = [
			[
				'key'     => 'wpevent-start-date-time',
				'compare' => '>',
				'value'   => strtotime( current_time( 'mysql' ) ),
				'type'    => 'numeric',
			],
		];
	} else if ( $event_status === 'On Going' ) {
		$args['meta_query'] = [
			'relation' => 'AND',
			[
				'key'     => 'wpevent-start-date-time',
				'compare' => '<=',
				'value'   => strtotime( current_time( 'mysql' ) ),
				'type'    => 'numeric',
			],
			[
				'key'     => 'wpevent-end-date-time',
				'compare' => '>=',
				'value'   => strtotime( current_time( 'mysql' ) ),
				'type'    => 'numeric',
			],
		];
	}

	$events = new WP_Query( $args );

	$path 		   = wp_upload_dir();
	$event_content = array();
	$filename 	   = "/events.csv";
	$file 		   = fopen( $path['path'].$filename, 'w');

	// The Loop
	if ( $events->have_posts() ) {
		while ( $events->have_posts() ) : $events->the_post();
			$post_id         = get_the_ID();
            $event_name      = get_the_title( $post_id );                                                        // the event ID
			$event_date_time = wpevent_date_time( $post_id );
			$start_date      = $event_date_time['start_date'];
			$start_time      = $event_date_time['start_time'];
			$end_date        = $event_date_time['end_date'];
			$end_time        = $event_date_time['end_time'];
			$end_date_time   = get_post_meta( $post_id, 'wpevent-end-date-time', TRUE );
			$wpe_venue       = get_post_meta( $post_id, 'wpevent-venue', TRUE );
			$wpe_addr        = get_post_meta( $post_id, 'wpevent-address', TRUE );
			$wpe_city        = get_post_meta( $post_id, 'wpevent-city', TRUE );
			$wpe_state       = get_post_meta( $post_id, 'wpevent-state', TRUE );
			$wpe_country     = get_post_meta( $post_id, 'wpevent-country', TRUE );
			$seats           = get_post_meta( $post_id, 'wpevent-seats', TRUE );
			$booked_seats    = get_booked_seats( $post_id );              //total booked seats
			$gmap_url        = get_post_meta( $post_id, 'wpevent-map-url', TRUE );                   // google map URL
			$post_type       = 'wp_events';
			$terms           = wp_get_object_terms( $post_id, 'wpevents-category' );
			$wpe_type        = get_post_meta( $post_id, 'wpevent-type', TRUE );
			$wpe_phone       = get_post_meta( $post_id, 'wpevent-phone', TRUE );

			$event_content[] = array (
				'Post ID' 		 => $post_id,
                'Event Name'     => $event_name,
				'Start Date' 	 => $start_date,
				'End Date' 		 => $end_date,
				'Start Time' 	 => $start_time,
				'End Time' 		 => $end_time,
				'Venue' 		 => $wpe_venue,
				'Address' 		 => $wpe_addr,
				'City' 			 => $wpe_city,
				'State' 		 => $wpe_state,
				'Country' 		 => $wpe_country,
				'Total Seats' 	 => $seats,
				'Booked Seats' 	 => $booked_seats,
				'Google Map URL' => $gmap_url,
				'Events Type'	 => $wpe_type,
				'Event Status'   => get_post_status( $post_id ),
				'Phone'			 => $wpe_phone,
			);

		endwhile;

		$keys = array_keys( $event_content[0] );

		fputcsv( $file, $keys );
		foreach ( $event_content as $key => $event_info ) {
			fputcsv( $file, $event_info );
		}

	} else {
		$event_content = array (
			'Post ID' 		 => '',
			'Event Name'     => '',
			'Start Date' 	 => '',
			'End Date' 		 => '',
			'Start Time' 	 => '',
			'End Time' 		 => '',
			'Venue' 		 => '',
			'Address' 		 => '',
			'City' 			 => '',
			'State' 		 => '',
			'Country' 		 => '',
			'Total Seats' 	 => '',
			'Booked Seats' 	 => '',
			'Google Map URL' => '',
			'Events Type'	 => '',
			'Event Status'   => '',
			'Phone'			 => '',
		);
		$keys = array_keys( $event_content );
		fputcsv( $file, $keys );
	} 
	fclose( $file );
	$fileUrl = $path['url'].$filename;		
	wpe_send_ajax_response( $fileUrl );
}

add_action('wp_ajax_wp_get_ajax_events', 'wp_get_ajax_events');
add_action('wp_ajax_nopriv_wp_get_ajax_events', 'wp_get_ajax_events');

/**
* Fetches Subscribers for WP Events from Database
*
* @since 1.0.446
*
* @return array 
*
*/
function get_wpe_subscribers() {

	global $wpdb;
	$table_name = $wpdb->prefix . 'events_subscribers';
	$results    = $wpdb->get_results('SELECT * FROM '. $table_name);

	foreach ( $results as $result ) {

		$status = $result->wpe_status;

		switch( $status ) {
			case 0:
				$entry_status = 'Trash';
				break;
			case 1:
				$entry_status = 'Active';
				break;
			case -1:
				$entry_status = 'Deleted Permanently';
				break;
			default:
				$entry_status = 'Active';
		}

		$data[] = [
			'Id'                  => esc_attr( $result->id ),
			'First Name' 		  => esc_attr( $result->subscriber_firstname ),
			'Last Name'  		  => esc_attr( $result->subscriber_lastname ),
			'Email'     		  => esc_attr( $result->subscriber_email ),
			'Phone'     		  => esc_attr( $result->subscriber_phone ),
			'Texting Permission'  => esc_attr( $result->subscriber_texting_permission ),
			'Time'                => esc_attr( $result->time_generated ),
			'Status'              => $entry_status,
		];
	
	}

	return $data;
}

/**
 * Export Registrations
 */

add_action('wp_ajax_wpe_event_entries', 'wpe_event_entries_export');
add_action('wp_ajax_nopriv_wpe_event_entries', 'wpe_event_entries_export');

function wpe_event_entries_export() {

	$event_startDate = $_POST['Startdate'];
	$event_endDate   = $_POST['Enddate'];
	$wpeevent  		 = $_POST['wpeeventid'];
	$path 			 = wp_upload_dir();
	$entries 		 = array();
	$file 			 = fopen( $path['path']."/events-entries.csv", 'w');

	$args = array(
		'post_type'		 => 'wp_events',
		'posts_per_page' => '-1',
		'post_status' => array( 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash' ),
	);

	if( $wpeevent ) {
		$args['post__in'] = explode( ",", $wpeevent );
	}

	if( $event_startDate !== '' && $event_endDate !== '' ) {
		$args['meta_query'] = [
			'relation' => 'AND',
			[
				'key'     => 'wpevent-end-date-time',
				'compare' => '<=',
				'value'   => strtotime( $event_endDate  . '23:59:59' ),
				'type'    => 'numeric',
			],
			[
				'key'     => 'wpevent-start-date-time',
				'compare' => '>=',
				'value'   => strtotime( $event_startDate ),
				'type'    => 'numeric',
			],
		];
	} else if ( $event_startDate !== '' ) {
		$args['meta_query'] = [
			[
				'key'     => 'wpevent-start-date-time',
				'compare' => '>=',
				'value'   => strtotime( $event_startDate ),
				'type'    => 'numeric',
			],
		];

	} else if ( $event_endDate !== '' ) {
		$args['meta_query'] = [
			[
				'key'     => 'wpevent-end-date-time',
				'compare' => '<=',
				'value'   => strtotime( $event_endDate  . '23:59:59' ),
				'type'    => 'numeric',
			],
		];
	}

	$events = new WP_Query( $args );

	if ( $events->have_posts() ) {
	
		while ( $events->have_posts() ) : $events->the_post();
			$postID = get_the_ID();

			global $wpdb;
			$table_name = $wpdb->prefix . 'events_registration';
			$results    = $wpdb->get_results('SELECT * FROM '. $table_name .' WHERE post_id = '. $postID );

			foreach ( $results as $result ) {

				$status = $result->wpe_status;

				switch( $status ) {
					case 0:
						$entry_status = 'Trash';
						break;
					case -1:
						$entry_status = 'Deleted Permanently';
						break;
					case 1:
						$entry_status = 'Active';
						break;
					case 2:
						$entry_status = 'Pending';
						break;
					case 3:
						$entry_status = 'Approved';
						break;
					case 4:
						$entry_status = 'Cancelled';
						break;
					default:
						$entry_status = 'Active';
				}
				
				$data[] = array(
					'Id'          => esc_attr( $result->ID ),
					'Event ID'	  => esc_attr( $result->post_id ),
					'First Name'  => esc_attr( $result->first_name ),
					'Last Name'   => esc_attr( $result->last_name ),
					'Email'       => esc_attr( $result->email ),
					'Phone'       => esc_attr( $result->phone ),
					'Event Name'  => get_the_title( esc_attr( $result->post_id ) ),
					'Event Type'  => get_post_meta( esc_attr( $result->post_id ), 'wpevent-type', true ),
					'Time'        => esc_attr( $result->time_generated ),
					'Seats'  	  => esc_attr( $result->wpe_seats ),
					'Status'  	  => esc_attr( $entry_status ),
				);

			}
			
		endwhile;

		$keys = array_keys( $data[0] );

		fputcsv( $file, $keys );
		foreach ( $data as $key => $entries ) {
			fputcsv( $file, $entries );
		}

	} else {
		fputcsv( $file, 'No Entries Found!' );
	} 

	fclose( $file );
	$fileUrl = $path['url'].'/events-entries.csv';
	wpe_send_ajax_response( $fileUrl );
}

/**
 * 
 *  Export Subscriptions
 */
add_action('wp_ajax_wpe_export_subscription', 'wpe_export_subscription');
add_action('wp_ajax_nopriv_wpe_export_subscription', 'wpe_export_subscription');

/**
 * Export Subscriptions to CSV file.
 * 
 * @since 1.4.3
 */
function wpe_export_subscription() {

	$subscribers   = get_wpe_subscribers();
	$path 		   = wp_upload_dir();
	$file 		   = fopen( $path['path']."/wpe-subscribers.csv", 'w');

	$keys = array_keys( $subscribers[0] );

	fputcsv( $file, $keys );
	foreach ( $subscribers as $key => $entries ) {
		fputcsv( $file, $entries );
	}
	fclose( $file );
	$fileUrl = $path['url'].'/wpe-subscribers.csv';		
	wpe_send_ajax_response( $fileUrl );
}

/**
 * 
 *  Delete File
 */
add_action('wp_ajax_wpe_delete_file', 'wpe_delete_file');
add_action('wp_ajax_nopriv_wpe_delete_file', 'wpe_delete_file');

/**
 * Delete CSV file after download.
 * 
 * @since 1.4.3
 */
function wpe_delete_file() {
	$file 		 = $_POST['url'];
	$path 		 = wp_upload_dir();
	$file_folder = explode('wp-content', $file);
	$base_path   = explode('wp-content', $path['path']);
	$file_path   = $base_path[0] . 'wp-content' . $file_folder[1];
	unlink( $file_path );

	wpe_send_ajax_response( 1 );
}