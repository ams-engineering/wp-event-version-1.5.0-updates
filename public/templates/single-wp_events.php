<?php
/**
 * wp_events Single page
 *
 * @since 1.0.446
 */

/**
 * Redirects to external source if available
 * only admin can see single page
*/
wpe_redirect_to_external_url( get_the_ID() );
get_header();
?>

    <div class="wpe-event">
        <div class="wpe-full-wrap">
            <div class="wpevents-container">
				<?php
				if ( have_posts() ) :
					while ( have_posts() ) : the_post();
						$post_id         = get_the_ID();                                                        // the event ID
						$event_date_time = wpevent_date_time( $post_id );
						$start_date      = isset( $event_date_time['start_date'] ) ? strtotime( $event_date_time['start_date'] ) : 0;
						$start_time      = isset( $event_date_time['start_time'] ) ? strtotime( $event_date_time['start_time'] ) : 0;
						$end_date        = isset( $event_date_time['end_date'] ) ? strtotime( $event_date_time['end_date'] ) : 0;
						$end_time        = isset( $event_date_time['end_time'] ) ? strtotime( $event_date_time['end_time'] ) : 0;
						$end_date_time   = get_post_meta( $post_id, 'wpevent-end-date-time', TRUE );
						$wpe_location 	 = (int) get_post_meta( $post_id, 'wpevent-location', TRUE );
						$location_id 	 = $wpe_location != 0 ? $wpe_location : $post_id;
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
						$seats           = get_post_meta( $post_id, 'wpevent-seats', TRUE );
						$booked_seats 	 = get_booked_seats( $post_id ); //Function defined in wp-events-global-functions.php
						$gmap_url        = get_post_meta( $post_id, 'wpevent-map-url', TRUE );                   // google map URL
						$post_type       = 'wp_events';
						$terms           = wp_get_object_terms( $post_id, 'wpevents-category' );
						$wpe_type        = get_post_meta( $post_id, 'wpevent-type', TRUE );
						$wpe_phone       = get_post_meta( $post_id, 'wpevent-phone', true );
						?>
                        <?php
                        echo apply_filters( 'wpe_single_title', '<h1 class="wpe-single-title">'. get_the_title() .'</h1>' );
                        ?>
                        <div class="wpe-single-content">
                            <span class="wpe-category"><?php
	                            echo apply_filters( 'wpe_single_type', '<span class="wpe-terms"><strong>Type:&nbsp;</strong>' . $wpe_type . '</span>' );
	                            if( !empty( $terms ) ) {
		                            $cat_html  = '';
		                            foreach ( $terms as $term ) {
			                            $cat_html .= '<a href="' . get_term_link( $term->term_id ) . '">' . $term->name . '</a>,&nbsp;';
		                            }
		                            if( $cat_html !== '' ) {
			                            echo apply_filters( 'wpe_single_category', '<span class="wpe-type"><strong>Category:&nbsp;</strong>' . rtrim( $cat_html, ',&nbsp;' ) . '</span>' );
		                            }
	                            }
	                            ?>
                            </span>
                            <span class="wpe-complete-duration">
                                <strong>Date: </strong><?php
								if ( $start_date === $end_date ) {
									echo date( 'F j', $start_date );
								} else {
									echo date( 'F j', $start_date ) . ' - ' . date( 'F j', $end_date );
								} ?>
                            </span>
                            <span class="wpe-duration-date">
                                <strong>Time: </strong><?php
								echo date( 'h:i A', $start_time ) . ' - ' . date( 'h:i A', $end_time ); ?>
                            </span>
                            <?php if( $wpe_phone !== '' ) {?>
                            <span class="wpe-duration-date">
                                <strong>Phone: </strong><?php
								echo "<a href='tel:$wpe_phone'>" . $wpe_phone . "</a>"; ?>
                            </span>
                            <?php  } ?>
                            <span class="wpe-address">
                                <?php
                                $venue_html = '';
                                if( $wpe_type !== 'webinar' ) {
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
                                        echo '<strong>Venue: </strong>' . $venue_html;
                                    }
                                }
                                ?>
                            </span>
	                        <?php echo wpe_display_external_url_to_admin( $post_id );?>
                        </div>
                        <div class="wpe-add-to-calendar">
							<?php
							$integrations = get_option( 'wpe_integration_settings' );
							$map_key 	  = $integrations['gmaps_api'];
							$map_type 	  = $integrations['gmaps_type'];
							if ( $gmap_url !== '' ) {
								if ( $map_key !== '' && $map_type === 'embed_map' ) {
									?>
									<iframe
										width="100%"
										height="375"
										frameborder="0" style="border:0" id="wpe-map-frame" allowfullscreen
										src="<?php echo $gmap_url; ?>">
									</iframe>
									<?php
								}
								if ( $map_type === 'button' ) {
									echo '<a class="wpe-button gmap-button" target="_blank" href="' . $gmap_url . '">Google Map</a>';
								}
							}
							//Replacing Spaces with + symbol to add in Query String
							$e_title         = preg_replace( '/\s+/', '+', get_the_title() );
							$e_date          = date( 'Ymd', $start_date ) . 'T' . date( 'His', $start_time ) . '/' . date( 'Ymd', $end_date ) . 'T' . date( 'His', $end_time );
							$e_description   = preg_replace( '/\s+/', '+', wp_trim_words( get_the_excerpt(), 10, ' ' ) );
							$e_address       = preg_replace( '/\s+/', '+', $wpe_venue . '+' . $wpe_addr . '+' . $wpe_city . '+' . $wpe_country );
							$add_to_calendar = 'https://www.google.com/calendar/event?action=TEMPLATE&amp;text=' . $e_title . '&amp;dates=' . $e_date . '&amp;details=' . $e_description . '&amp;location=' . $e_address . '&amp;trp=false&amp;' . 'sprop=website:' . get_site_url() . '&amp;ctz=' . date( 'e', strtotime( wp_timezone_string() ) );
							$add_to_outlook  = 'https://outlook.office.com/owa/?path=/calendar/action/compose&rru=addevent&startdt=' . date( 'Y-m-d', $start_date ) . 'T' . date( 'H:i:s', $start_time ) . '&enddt=' . date( 'Y-m-d', $end_date ) . 'T' . date( 'H:i:s', $end_time ) . '&subject=' . get_the_title() . '&location=' . $e_address . '&body=' . $e_description;
							?>
                            <ul class="wpe-calendar-ul">
                                <li class="wpe-calendar-list"><a href="javascript:void(0)">+ Calendar</a>
                                    <ul class="wpe-calendar-sublist">
                                        <li><a target="_blank" href="<?php
											echo $add_to_calendar; ?>">Google
                                                Calendar</a></li>
                                        <li><a target="_blank" href="<?php
											echo $add_to_outlook; ?>">Outlook
                                                Calendar</a></li>
                                        <li id="download-ics"><a href="javascript:void(0)">Download ICS File</a></li>
                                        <?php $venue = get_post_meta( $wpe_location, 'wpevent-loc-venue', TRUE );
                                        if ( $venue == "" ) {
                                        	$venue = 'online';
                                        }
                                        $address = get_post_meta( $wpe_location, 'wpevent-loc-address', TRUE );
                                        if ( $address == "" ) {
                                        	$address = 'webinar';
                                        }
                                        ?>
                                        <div class="ics-text" id="get-ics-text">BEGIN:VCALENDAR<?php echo "\n" ?>VERSION:2.0<?php echo "\n" ?>PRODID:-//AMS//NONSGML v1.0//EN<?php echo "\n" ?>CALSCALE:GREGORIAN<?php echo "\n" ?>BEGIN:VEVENT<?php echo "\n" ?>VENUE:<?php echo $wpe_venue; ?><?php echo "\n" ?>DESCRIPTION:<?php echo strip_tags( get_the_excerpt()); ?><?php echo "\n" ?>ADDRESS:<?php echo $wpe_addr; ?><?php echo "\n" ?>DTSTART:<?php echo date('Ymd\THis', get_post_meta( $post_id , 'wpevent-start-date-time', TRUE )); ?><?php echo "\n" ?>DTEND:<?php echo date('Ymd\THis', get_post_meta( $post_id , 'wpevent-end-date-time', TRUE )); ?><?php echo "\n" ?>URL;VALUE=URI:<?php echo get_the_permalink( $post_id ); ?><?php echo "\n" ?>SUMMARY:<?php echo strip_tags( get_the_title()); ?><?php echo "\n" ?>LOCATION:<?php echo $venue_html; ?><?php echo "\n" ?>PHONE:<?php echo get_post_meta( $post_id, 'wpevent-phone', true ); ?><?php echo "\n" ?>DTSTAMP:<?php echo date('Ymd\THis'); ?><?php echo "\n" ?>UID:<?php echo uniqid(); ?><?php echo "\n" ?>END:VEVENT<?php echo "\n" ?>END:VCALENDAR</div>
                                        <div class="filename"><?php echo strip_tags( get_the_title()) .'.ics' ?></div>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                        <div class="wpe-event-thumbnail">
							<?php
							echo get_the_post_thumbnail();                // Event Featured Image
							?>
                        </div>
                        <div class="wpe-description">
                            <?php
                            echo get_the_content();                   // Event Content
                            ?>
                        </div>
						<?php
						$close_event = get_post_meta( $post_id, 'wpevent-close-reg', true );
						if ( $booked_seats < $seats && $close_event !== 'yes' ) {  // booked seats is less than available seats and event is not closed
							if ( $end_date_time < strtotime( current_time( 'mysql' ) ) ) {              //current datetime is greater than event end datetime
								echo __( 'Event is due and cannot be registered at the moment', 'wp-events' );
							} else {
								/**
								 * Prints Registration Form
								 *
								 * @since  1.0.0
								 * @action wpe_registration_form
								 */
								if( empty( $post->post_password ) || !post_password_required() ){
									// do some stuff
									do_action( 'wp_events_registration_form' );                          // Displays Events Registration Form
								}
							}
						} else {
							wpe_get_closed_reg_text();
						}
					endwhile;
				endif;
				?>
            </div>
            <div class="thankyou-popup" style="display:none;">
                <div class="t-y-inner"><span class="close-btn"></span>
                    <p><?php
	                    echo __( 'ThankYou For Registering.', 'wp-events' );
						?>
                    </p>
                </div>
            </div>
        </div>
    </div>
<?php

get_footer();
?>