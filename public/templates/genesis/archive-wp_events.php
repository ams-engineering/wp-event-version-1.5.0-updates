<?php


add_filter( 'genesis_archive_crumb', 'wpe_custom_archive_crumb' );
if ( ! function_exists( 'wpe_custom_archive_crumb' ) ) {
	/**
     * set breadcrumb to post label
     *
	 * @param $crumb
	 *
	 * @return string
     * @since 1.0448
	 */
	function wpe_custom_archive_crumb( $crumb ) {
		//Set Breadcrumb by Post Label saved in settings
		$settings = get_option( 'wpe_settings' );
		if( isset( $settings['events_post_name'] ) ) {
		    return (string)$settings['events_post_name'];
        }

		return $crumb;
	}
}

add_filter( 'wpseo_breadcrumb_output', 'wpe_yoast_crumb' );
if ( ! function_exists( 'wpe_yoast_crumb' ) ) {
	/**
     * Replace breadcrumb string events with post label
     *
	 * This function's only purpose is to add breadcrumbs compatibility
	 * with Yoast SEO
     *
	 * @param $output
	 *
	 * @return string
     * @since 1.0.448
	 */
	function wpe_yoast_crumb( $output ) {
		$settings = get_option( 'wpe_settings' );
		if ( stripos( $output, 'events' ) ) {
			return str_ireplace( 'events', (string) $settings['events_post_name'], $output );
		}
		return $output;
	}
}

/**
 * Removing Default Genesis Loop
 *
 * @since 1.0.448
 */
remove_action( 'genesis_loop', 'genesis_do_loop' );

$wpe_display_settings = get_option( 'wpe_display_settings' );
$full_width 		  = isset( $wpe_display_settings['fullwidth_archive'] ) ? $wpe_display_settings['fullwidth_archive'] : '';
if( $full_width ) {
	remove_action( 'genesis_sidebar', 'genesis_do_sidebar' );
}
add_action( 'genesis_loop', 'wpe_custom_loop' );

if ( ! function_exists( 'wpe_custom_loop ' ) ) {
	/**
	 * Custom Loop overrides default genesis loop on archive page
	 *
	 * @since 1.0.449
	 */
	function wpe_custom_loop() {

		$wpe_display_settings = get_option( 'wpe_display_settings' );
		$default_view         = $wpe_display_settings['default_view'];
		$default_grid_layout = $wpe_display_settings['grid_layout'];
		switch( $default_view ) {
			case 'list':
				$class = 'wpe-list';
				break;
			case 'grid':
				$class = 'wpe-grid';
				break;
			case 'calendar':
				$class = '';
				break;
		}

		

		?>

        <div class="wpe-event">
			<div class="wpevents-container">
				<?php
				echo wpe_get_archive_page_title();

				/**
				 * Print the archive image if set in
				 * settings->display->archive Image
				 *
				 * @since  1.0.448
				 * @action Wp_Events_Public->wpe_image_on_archive
				 */
				do_action( 'wp_events_archive_image' );

				echo wpe_archive_views();
				$wpe_query = new WP_Query( wpe_get_default_query_args() );
				$count 	   = 0;
				if (!empty($wpe_query->posts)) {
					# code...
					?>
					<div id="wpe-calendar" <?php echo $default_view != 'calendar' ? 'style="display:none"' : ''; ?>></div>
					<div class="wpe-list-grid-view <?php echo $class; ?>" <?php echo $default_view == 'calendar' ? 'style="display:none"' : ''; ?>>
					<?php
				} 
				
					if ( $wpe_query->have_posts() ) {

						while ( $wpe_query->have_posts() ) {
							$wpe_query->the_post();
							$post_id 		 = get_the_ID();
							$hide_in_archive = get_post_meta( $post_id, 'wpevent-hide-archive', true );
							$feat_img 		 = get_the_post_thumbnail_url() != false ? get_the_post_thumbnail_url() : plugins_url() . '/wp-events/assets/img/logo-placeholder.png';

							if ( $hide_in_archive === 'yes' ) {
								continue;
							}
							$count++;
							?>
								<div class="wpe-row wpe-<?php echo $post_id;?> <?php echo $default_grid_layout ?>">
								<div class="wpe-day-date">
								<?php wpe_get_events_day_date_column( $post_id ); ?>
								</div>
								<div class="wpe-col-img">
								<a href="<?php the_permalink(); ?>">
								<img width="300" height="169" src="<?php echo $feat_img; ?>" class="wpe_event_image" alt="<?php echo get_the_title();?>">
								</a>    
								</div>
								<div class="wpe-col-event">
									<div class="wpe-col-inner">
										<?php
										wpe_get_event_date_time( $post_id );

										wpe_get_event_title( $post_id );

										wpe_get_event_category_and_type( $post_id );

										wpe_get_event_address( $post_id );
										?>
										<div class="wpe-archive-buttons">
										<?php
										wpe_get_archive_details();
										
										wpe_get_registration_button( $post_id );
										?>
										</div>
									</div>
								</div>
							</div>
							<hr class="wpe-divider">
							<?php
						}
						wpe_get_pagination_list( $wpe_query->max_num_pages );
					}
					?>
				</div>
            	<?php 
				if ( $count == 0 ) {
					/**
					 * Print the subscriber form if no event is added
                     * or all events are over due
					 *
					 * @since  1.0.448
					 * @action wpe_display_subscribe_form
					 */
					do_action( 'wp_events_subscribe_form' );          // Displays Subscribe Form
				}
				wp_reset_postdata();
				?>
            </div>
        </div>
		<?php
	}
}
genesis();