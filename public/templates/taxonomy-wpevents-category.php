<?php
/**
 * wp_events archive page
 */

get_header();
?>

<div class="wpe-event">
    <div class="wpe-full-wrap">
        <div class="wpevents-container">
            <?php
            wpe_get_taxonomy_page_title();

            /**
             * Print the archive image if set in
             * settings->display->archive Image
             *
             * @since  1.0.449
             * @action Wp_Events_Public->wpe_image_on_archive
             */
            do_action( 'wp_events_archive_image' );

            $wpe_query = new WP_Query( wpe_get_tex_query_args() );

            if ( $wpe_query->have_posts() ) {
                while( $wpe_query->have_posts() ) {
	                $wpe_query->the_post();
	                $post_id = get_the_ID();

	                ?>
                    <div class="wpe-row">
	                    <?php
	                    wpe_get_events_day_date_column( $post_id ); ?>
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
            } else {
	            /**
	             * Print the subscriber form if no event is added
	             * or all events are over due
	             *
	             * @since  1.0.449
	             * @action wpe_display_subscribe_form
	             */
	            do_action( 'wp_events_subscribe_form' );          // Displays Subscribe Form
            }
            wp_reset_postdata();
            ?>
        </div>
    </div>
</div>
<?php
get_footer();
?>