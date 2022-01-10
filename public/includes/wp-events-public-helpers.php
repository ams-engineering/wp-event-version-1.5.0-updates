<?php
/**
 * All helper function used multiple times
 * are defined in this file
 *
 * @since 1.0.448
 */

if ( ! function_exists( 'wpe_get_default_query_args' ) ) {
	/**
	 * Returns default Query args for wp_events list
	 *
	 * this functions will be used in all templates
	 * to get events
	 *
	 * @return array|void
	 * @since 1.0.448
	 */
	function wpe_get_default_query_args() {
		$args = [
			'post_type'      	 => 'wp_events',
			'posts_per_page' 	 => wpe_get_events_per_page(),
			'paged'        		 => wpe_get_events_paged_attribute(),
			'meta_key'       	 => 'wpevent-start-date-time',
			'order'          	 => 'ASC',
			'orderby'        	 => 'meta_value_num',
			'ignore_custom_sort' => true,
			'meta_query'     	 => [
				'date_clause' 	 => [
					'key'     	 => 'wpevent-end-date-time',
					'value'   	 => strtotime( current_time( 'mysql' ) ),
					'type'    	 => 'numeric',
					'compare' 	 => '>',
				],
			],
		];

		return apply_filters( 'wpe_default_query_args', $args );
	}
}


if ( ! function_exists( 'wpe_get_tex_query_args' ) ) {
	/**
	 * Returns default Taxonomy Query args for wp_events category
	 *
	 * this functions will be used in taxonom templates
	 * to get events
	 *
	 * @return array|void
	 * @since 1.0.449
	 */
	function wpe_get_tex_query_args() {
		$args = [
			'post_type'      	 => 'wp_events',
			'posts_per_page' 	 => wpe_get_events_per_page(),
			'paged'          	 => wpe_get_events_paged_attribute(),
			'meta_key'       	 => 'wpevent-start-date-time',
			'order'          	 => 'ASC',
			'orderby'        	 => 'meta_value_num',
			'ignore_custom_sort' => true,
			'tax_query'      	 => [
				[
					'taxonomy'   => 'wpevents-category',
					'field'      => 'ID',
					'terms'      => get_queried_object()->term_id,
				],
			],
			'meta_query'     	 => [
				'date_clause'    => [
					'key'     	 => 'wpevent-end-date-time',
					'value'   	 => strtotime( current_time( 'mysql' ) ),
					'type'    	 => 'numeric',
					'compare' 	 => '>',
				],
			],
		];

		return apply_filters( 'wpe_default_tax_query_args', $args );
	}
}

if ( ! function_exists( 'wpe_get_thankyou_popup' ) ) {
	/**
	 * Returns thankyou pop-up html
	 *
	 * @since 1.0.448
	 */
	function wpe_get_thankyou_popup() {
		?>
        <div class="thankyou-popup" style="display:none;">
            <div class="t-y-inner"><span class="close-btn"></span>
                <p><?php
					echo __( 'ThankYou For Registering.', 'wp-events' );
					?>
                </p>
            </div>
        </div>
		<?php
	}
}

if ( ! function_exists( 'wpe_get_event_address' ) ) {
	/**
	 * Returns event venue output
	 *
	 * returns nothing if event type is webinar
	 *
	 * @param $post_id
	 *
	 * @since 1.0.448
	 */
	function wpe_get_event_address( $post_id ) {

		$wpe_type 		 = get_post_meta( $post_id, 'wpevent-type', TRUE );
		$wpe_location 	 = (int) get_post_meta( $post_id, 'wpevent-location', TRUE );
		$location_id 	 = $wpe_location != 0 ? $wpe_location : $post_id;

		if ( $wpe_type !== 'webinar' && $location_id !== 0 ) {
			$venue_meta 	 = $wpe_location != 0 ? 'wpevent-loc-venue' : 'wpevent-venue';
			$address_meta 	 = $wpe_location != 0 ? 'wpevent-loc-address' : 'wpevent-address';
			$city_meta  	 = $wpe_location != 0 ? 'wpevent-loc-city' : 'wpevent-city';
			$state_meta 	 = $wpe_location != 0 ? 'wpevent-loc-state' : 'wpevent-state';
			$country_meta 	 = $wpe_location != 0 ? 'wpevent-loc-country' : 'wpevent-country';
			$wpe_venue       = get_post_meta( $location_id, $venue_meta, TRUE );
			$wpe_addr        = get_post_meta( $location_id, $address_meta, TRUE );
			$wpe_city        = get_post_meta( $location_id, $city_meta, TRUE );
			$wpe_state       = get_post_meta( $location_id, $state_meta, TRUE );
			$wpe_country     = get_post_meta( $location_id, $country_meta, TRUE );
			$venue_html  = '';
			if ( $wpe_venue !== '' ) {
				$venue_html .= '<span class="wpe-venue">' . $wpe_venue . ',</span>';
			}
			if ( $wpe_addr !== '' ) {
				$venue_html .= '&nbsp;<span class="wpe-addr">' . $wpe_addr . ',</span>';
			}
			if ( $wpe_city !== '' ) {
				$venue_html .= '&nbsp;<span class="wpe-city">' . ucwords( $wpe_city ) . ',</span>';
			}
			if ( $wpe_state !== '' ) {
				$venue_html .= '&nbsp;<span class="wpe-state">' . ucfirst( $wpe_state ) . '</span>';
			}
			if ( $wpe_country !== '' ) {
				$venue_html .= '&nbsp;<span class="wpe-state">' . $wpe_country . '</span>';
			}
			if ( $venue_html !== '' ) {
				echo '<div class="wpe-location"><strong>Venue: </strong>' . $venue_html . '</div>';
			}
		}
	}
}


if ( ! function_exists( 'wpe_get_event_category_and_type' ) ) {
	/**
	 * Generates Event category and type output
	 *
	 * @param $post_id
	 *
	 * @since 1.0.448
	 */
	function wpe_get_event_category_and_type( $post_id ) {
		$terms    = wp_get_object_terms( $post_id, 'wpevents-category' );
		$wpe_type = get_post_meta( $post_id, 'wpevent-type', TRUE );
		?>
        <span class="wpe-category"><?php
			echo apply_filters( 'wpe_single_category_type', '<span class="wpe-type"><strong>Type:&nbsp;</strong>' . $wpe_type . '</span><br>' );
			if ( ! empty( $terms ) ) {
				$cat_html  = '';
				foreach ( $terms as $term ) {
					$cat_html .= '<a href="' . get_term_link( $term->term_id ) . '">' . $term->name . '</a>,&nbsp;';
				}
				if ( $cat_html !== '' ) {
					echo apply_filters( 'wpe_single_category_type', '<span class="wpe-terms"><strong>Category:&nbsp;</strong>' . rtrim( $cat_html, ',&nbsp;' ) . '</span>' );
				}
			}
			?>
        </span>
		<?php
	}
}


if ( ! function_exists( 'wpe_get_event_title' ) ) {
	/**
	 * generates Event title with link
	 *
	 * if external url is added title will be linked
	 * to the external url instead of single page
	 *
	 * @param $post_id
	 *
	 * @since 1.0.448
	 */
	function wpe_get_event_title( $post_id ) {
		$wpe_external_url = get_post_meta( $post_id, 'wpevent-external-url', TRUE );
		$href_link 		  = $wpe_external_url !== '' ? $wpe_external_url : get_the_permalink( $post_id );
		$target 		  = $wpe_external_url !== '' ? '_blank' : '';
	?>
        <div class="wpe-title entry-title">
            <a class="entry-title-link" href="<?php echo $href_link ?>" target="<?php echo $target ?>" 
			title="<?php echo get_the_title(); ?>"><?php echo get_the_title(); ?></a>
        </div>
		<?php
	}
}


if ( ! function_exists( 'wpe_get_event_date_time' ) ) {
	/**
	 * Generate Event Date and time Output
	 *
	 * @param $post_id
	 *
	 * @since 1.0.448
	 */
	function wpe_get_event_date_time( $post_id ) {
		$event_date_time = wpevent_date_time( $post_id );
		$start_date      = isset( $event_date_time['start_date'] ) ? strtotime( $event_date_time['start_date'] ) : 0;
		$start_time      = isset( $event_date_time['start_time'] ) ? strtotime( $event_date_time['start_time'] ) : 0;
		$end_date        = isset( $event_date_time['end_date'] ) ? strtotime( $event_date_time['end_date'] ) : 0;
		$end_time        = isset( $event_date_time['end_time'] ) ? strtotime( $event_date_time['end_time'] ) : 0;
		?>
        <span class="wpe-event-duration-date">
            <strong>Date: </strong><?php
			$start = date( 'F j', $start_date );
			$end   = date( 'F j', $end_date );
			if ( $start === $end ) {
				echo $start;
			} else {
				echo $start . ' - ' . $end;
			}
			?>
        </span>
        <span class="wpe-duration-time">
            <strong>Time: </strong><?php
			echo date( 'h:i A', $start_time ) . ' - ' . date( 'h:i A', $end_time ); ?>
        </span>
		<?php
	}
}

if ( ! function_exists( 'wpe_get_events_per_page' ) ) {
	/**
	 * Returns the total events per page set in
	 * settings->display->No. of Events To Display per Page
	 *
	 * @return int|mixed
     *
	 * @since 1.0.448
	 */
	function wpe_get_events_per_page() {
		$archive_posts_no = get_option( 'wpe_display_settings' );

		return isset( $archive_posts_no['archive_posts'] ) ? $archive_posts_no['archive_posts'] : 12;
	}
}

if ( ! function_exists( 'wpe_get_events_paged_attribute' ) ) {
	/**
     * returns paged attribiute from query vars
     *
     * default is set to 1 if none is set
     *
	 * @return int|mixed
     *
     * @since 1.0.448
	 */
	function wpe_get_events_paged_attribute() {
		return ! empty( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
	}
}

if ( ! function_exists( 'wpe_get_events_day_date_column' ) ) {
	/**
     * generates column output for start date on archive page
     *
	 * @param $post_id
     *
     * @since 1.0.448
	 */
	function wpe_get_events_day_date_column( $post_id ) {
		$event_date_time = wpevent_start_date_time( $post_id );
		$start_date      = isset( $event_date_time['start_date'] ) ? strtotime( $event_date_time['start_date'] ) : 0;
		?>
        <div class="wpe-col-dd">
            <span class="wpe-col-day">
                <?php
                echo date( "D", $start_date );     //event start day ?>
            </span>
            <span class="wpe-col-date">
                 <?php
                 echo date( 'j', $start_date );    //event start date ?>
            </span>
        </div>
		<?php
	}
}

if ( ! function_exists( 'wpe_get_archive_page_title' ) ) {
	/**
     * Generate Archive Page Title Output
     *
     * gets the title from settings->display->Archive Page Title
     * and displayed on archive page
     *
	 * @return string|void
     *
     * @since 1.0.448
	 */
	function wpe_get_archive_page_title() {
		$title         = '';
		$archive_title = get_option( 'wpe_display_settings' );
		$title         = $archive_title['archive_title'] ?? '';
		$title         = apply_filters( 'wpe_archive_page_title_text', $title );
		if ( $title !== '' ) {
			$title = '<h1 class="wpe-template-title">' . esc_attr( $title ) . '</h1>';
		}

		return apply_filters( 'wpe_archive_page_title_html', $title );
	}
}


if ( ! function_exists( 'wpe_get_taxonomy_page_title' ) ) {
	/**
	 * Generate Taxonomy Page Title Output
	 *
	 * gets the title from single_term_title()
	 * and displayed on taxonomy page
	 **
	 * @since 1.0.449
	 */
	function wpe_get_taxonomy_page_title() {
		?>
        <h1 class="wpe-template-title"><?php
			$post_title = get_option( 'wpe_settings' );
			ob_start();
			single_term_title();        //taxonomy title
			$cat_title = ob_get_clean();
			echo $post_title['events_post_name'] . ' Category: ' . ucwords( $cat_title );   //Events Category: cat_name
			?></h1>
		<?php
	}
}


if ( ! function_exists( 'wpe_get_pagination_list' ) ) {
	/**
     * Generates the pagination links fro archive page
     *
	 * @param $max_num_pages
     *
     * @since 1.0.448
	 */
	function wpe_get_pagination_list( $max_num_pages ) {

		$pagination_args = [
			'base'      => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
			'total'     => $max_num_pages,
			'current'   => max( 1, wpe_get_events_paged_attribute() ),
			'format'    => '?paged=%#%',
			'type'      => 'list',
			'prev_next' => TRUE,
			'prev_text' => 'Previous',
			'next_text' => 'Next',
		];
		if ( $pagination_list = paginate_links( apply_filters( 'wpe_pagination_args', $pagination_args ) ) ) {
			echo '<div class="archive-pagination pagination wpe-pagination">' . $pagination_list . '</div>';
		}
	}
}

if ( ! function_exists( 'wpe_get_archive_details' ) ) {
	/**
	 * Generates the content of current post
	 *
	 * @since 1.0.449
	 */
	function wpe_get_archive_details() {
	    $event_content= get_the_content();
	    if( $event_content !=='' ) {
            ?>
            <div class="wpe-archive-description">
            <span title="Click for Details" class="wpe-detail-button"><?php
                echo apply_filters( 'wpe_archive_description_text', __( 'Details', 'wp-events' ) ); ?></span>
                <div class="wpe-archive-content wpe-display-none"><?php
                    echo apply_filters( 'the_content', $event_content ); ?></div>
            </div>
            <?php
        }
	}
}

if ( ! function_exists( 'wpe_redirect_to_external_url' ) ) {
	/**
	 * Redirects to external url if available
	 *
	 * @param $post_id
	 *
	 * @since 1.0.500
	 */
	function wpe_redirect_to_external_url( $post_id ) {
		$wpe_external_url = get_post_meta( $post_id, 'wpevent-external-url', TRUE );
		/**
         * only admin can see single page if external source is added
		*/
		if ( empty( $wpe_external_url ) || current_user_can( 'manage_options' ) ) {
			return;
		}

        wp_redirect( $wpe_external_url );
        exit;
	}
}

if ( ! function_exists( 'wpe_display_external_url_to_admin' ) ) {
	/**
	 * Display External URL to admin when admin visits
	 * single page if external source is added
	 *
	 * @param $post_id
	 *
	 * @return string
	 * @since 1.0.500
	 */
	function wpe_display_external_url_to_admin( $post_id ) {
		$wpe_external_url = get_post_meta( $post_id, 'wpevent-external-url', TRUE );
		if ( !empty( $wpe_external_url ) && current_user_can( 'manage_options' ) ) {
			return '<span class="wpe-type"><strong>External URL:&nbsp;</strong><a href="'. $wpe_external_url .'">'. $wpe_external_url .'</a><small>&nbsp;'. __( 'Click to visit external url (external URL is only visible to administrators all other visitors will be redirected to external URL)', 'wp-events' ) .'</small></span>';
		}
	}
}

if ( ! function_exists( 'wpe_get_registration_button' ) ) {
	/**
	 * Display Registration button on events archive
	 *
	 * @return string
	 * @since 1.1.1
	 */
	function wpe_get_registration_button( $post_id ) {
		$option= get_option('wpe_display_settings');
		if ( isset( $option['reg_button'] ) ) {
			$wpe_external_url = get_post_meta( $post_id, 'wpevent-external-url', TRUE );
			$href_link 		  = $wpe_external_url !== '' ? $wpe_external_url : get_the_permalink( $post_id );
			$target 		  = $wpe_external_url !== '' ? '_blank' : '';
			?>
            <div class="wpe-archive-registration">
                <a class="wpe-reg-button wpe-detail-button" href="<?php echo $href_link ?>" target="<?php echo $target ?>" 
				title="Click to Register"><?php echo $option['button_text']; ?></a>
            </div>
			<?php
		}
	}
}

if ( ! function_exists( 'wpe_get_closed_reg_text' ) ) {
	/**
	 * Display Text when Registrations are Closed.
	 *
	 * @return string
	 * @since 1.1.1
	 */
	function wpe_get_closed_reg_text() {
		$option = get_option( 'wpe_display_settings' );
		$text   = $option['closed_reg'];
		echo '<p style="color:#8b0000">';
		echo $text;
		echo '</p>';
	}
}

if ( ! function_exists( 'wpe_get_seats_dropdown' ) ) {
	/**
	 * Displays seats dropdown in registraion form
	 *
	 * @return string
	 * @since 1.1.1
	 */
	function wpe_get_seats_dropdown() {
		?>
		<select id="event-seats" name="wpe_seats" required>
		<option value="" disabled selected>Select Seats *
		</option>
		<?php
		$booked_seats    = get_booked_seats( get_the_ID() ); //Function defined in wp-events-global-functions.php
		$totalseats      = (int) get_post_meta( get_the_ID(), 'wpevent-seats', TRUE );
		$remaining_seats = $totalseats - $booked_seats;
		$option          = get_option('wpe_display_settings');
		$seats_per_entry = $option['max_seats'] ?? 10;
		$seats_limit 	 = get_post_meta( get_the_ID(), 'wpevent-limit-seats', TRUE );
		if ( isset( $seats_limit ) && $seats_limit !== '' ) {
			$seats_per_entry = (int) $seats_limit;
		}
		for ( $nmber = 1; $nmber <= $totalseats; $nmber ++ ) {
			if ( $nmber > $remaining_seats ) {
				break;
			}
			if ( $nmber <= $seats_per_entry ) {
				echo '<option value="' . $nmber . '">' . $nmber . '</option>';
			}
		}
		?>
		</select>
		<?php
	}
}

if ( ! function_exists( 'wpe_archive_views' ) ) {
	/**
	 * Displays options for views on archive page.
	 *
	 * @return string
	 * @since 1.5.0
	 */
	function wpe_archive_views() {
		$posts = get_posts( wpe_get_default_query_args() );
		$views = '';
		if( $posts ) {
			$wpe_display_settings = get_option( 'wpe_display_settings' );
			$default_view         = $wpe_display_settings['default_view'];
			ob_start();
			?>
			<div class="wpe-select-view">
				<?php 
				if( isset( $wpe_display_settings['list_view'] ) ) {
					?>
					<span id="wpe-select-list" class="<?php echo $default_view === 'list' ? 'wpe-active-view' : '' ?>"><a class="wpe-archive-view" title="List View" href="#list"><span class="dashicons dashicons-list-view"></span></a></span>
					<?php
				} 
				if( isset( $wpe_display_settings['grid_view'] ) ) {
					?>
					<span id="wpe-select-grid" class="<?php echo $default_view === 'grid' ? 'wpe-active-view' : '' ?>"><a class="wpe-archive-view" title="Grid View" href="#grid"><span class="dashicons dashicons-grid-view"></span></a></span>
					<?php
				}
				if( isset( $wpe_display_settings['cal_view'] ) ) {
					?>
					<span id="wpe-select-calendar" class="<?php  echo $default_view === 'calendar' ? 'wpe-active-view' : '' ?>"><a class="wpe-archive-view" title="Calendar View" href="#calendar"><span class="dashicons dashicons-calendar-alt"></span></a></span>
					<?php
				} 
				?>
			</div>
			<?php
			$views = ob_get_clean();
		}

		return apply_filters( 'wpe_archive_views', $views );
	}
}

if( ! function_exists( 'wpe_calendar_data' ) ) {
	/**
	 * Retrieves events data to add to calendar
	 *
	 * @since 1.5.0
	 * @return object
	 */
	function wpe_calendar_data() {

	   	$args = [
		   'post_type'      	 => 'wp_events',
		   'posts_per_page' 	 => -1,
		   'meta_key'       	 => 'wpevent-start-date-time',
		   'order'          	 => 'ASC',
		   'orderby'        	 => 'meta_value_num',
		   'ignore_custom_sort'  => true,
		   'meta_query'     	 => [
			   'date_clause' 	 => [
				   'key'     	 => 'wpevent-end-date-time',
				   'value'   	 => strtotime( current_time( 'mysql' ) ),
				   'type'    	 => 'numeric',
				   'compare' 	 => '>',
			   	],
		   	],
	   	];

	   	$event_data = '';
	   	$wpequery   = get_posts($args) ;
		$wpe_event_data[] = array();

		foreach ( $wpequery as $wpe_event ) {
			$wpe_eventId 	 = $wpe_event->ID;
			$event_date_time = wpevent_date_time( $wpe_eventId );
			$start_date      = $event_date_time['start_date'] ?? '';
			$end_date        = $event_date_time['end_date'] ?? '';
			$start_time      = $event_date_time['start_time'] ?? '';
			$end_time        = $event_date_time['end_time'] ?? '';
			ob_start();
			wpe_get_event_category_and_type( $wpe_eventId );
			$type_cat = ob_get_clean();
			ob_start();
			wpe_get_event_address( $wpe_eventId );
			$venue = ob_get_clean();

			$wpe_event_data[] = array (
				'title'   => get_the_title( $wpe_eventId ),
				'start'   => $start_date,
				'end' 	  => $end_date,
				'url' 	  => get_the_permalink( $wpe_eventId ),
				'time'	  => 'Time: '. date( 'h:i A', strtotime( $start_time ) ) . ' - ' . date( 'h:i A', strtotime( $end_time ) ),
				'venue'	  => $venue,
				'type'	  => $type_cat,
			);
	   }
	   $event_data = json_encode( $wpe_event_data );
	   return $event_data;
	}
}

