<?php
/**
 * wp_events archive page
 *
 * @since 1.0.2
 */
get_header();
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
    <div class="wpe-full-wrap">
        <div class="wpevents-container">
            <?php
            echo wpe_get_archive_page_title();

            /**
             * Print the archive image if set in
             * settings->display->archive Image
             *
             * @since  1.0.449
             * @action Wp_Events_Public->wpe_image_on_archive
             */
            do_action( 'wp_events_archive_image' );

            echo wpe_archive_views();
            $wpe_query = new WP_Query( wpe_get_default_query_args() );
            $count 	   = 0;
//echo '<pre>'; print_r($wpe_query->posts); echo '</pre>';
          //  var_dump($wpe_query->posts , 'ID');
            // var_dump();
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
                        $post_id         = get_the_ID();
                        $hide_in_archive = get_post_meta( $post_id, 'wpevent-hide-archive', true );
                        $feat_img 		 = get_the_post_thumbnail_url() != false ? get_the_post_thumbnail_url() : plugins_url() . '/wp-events/assets/img/logo-placeholder.png';

                        if ( $hide_in_archive === 'yes' ) {
                            continue;
                        }
                        $count++;
                        ?>
                            <div class="wpe-row wpe-<?php echo $post_id ?> <?php echo $default_grid_layout ?>">
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