<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       //allmarketingsolutions.co.uk
 * @since      1.0.0
 *
 * @package    Wp_Events
 * @subpackage Wp_Events/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wp_Events
 * @subpackage Wp_Events/public
 * @author     All Marketing Solutions <btltimes39@gmail.com>
 */
class Wp_Events_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->include_files_from_directory();
		add_shortcode( 'wpevents', [ $this, 'wpevent_shortcode' ] );
		add_shortcode( 'wpevents_list', [ $this, 'archive_shortcode' ] );
    }
	
	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wp_Events_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_Events_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name.'-public', plugin_dir_url( __FILE__ ) . 'css/wp-events-public.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __DIR__ ) . 'assets/css/wp-events.css', array(), $this->version, 'all' );

		wp_enqueue_style( $this->plugin_name.'-fullcalendarcss', plugin_dir_url( __DIR__ ) . 'assets/fullcalendar/fullcalendar.min.css', array(), $this->version, 'all' );

		wp_enqueue_style( 'dashicons' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wp_Events_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_Events_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( 'jquery-serialize', plugin_dir_url( __DIR__ ) . 'assets/js/jquery.serializejson.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-events-public.js', array( 'jquery', 'jquery-serialize' ), $this->version, false );
		wp_enqueue_script( 'reCAPTCHA-script', 'https://www.google.com/recaptcha/api.js', array( 'jquery', 'jquery-serialize' ), $this->version, false );

        //localizing ajax url
		wp_localize_script(
			$this->plugin_name,
			'wpe_ajaxobject',
			array(
				'ajaxurl'          => admin_url( 'admin-ajax.php' ),
				'captchaType'      => wpe_get_captcha_type(),
				'captchaSiteKey'   => wpe_get_site_key(),
				'captchaSecretKey' => wpe_get_secret_key(),
				'wpeCalendarData'  => wpe_calendar_data(),
				'wpeFullWidth' 	   => wpe_full_width(),
			)
		);
		wp_enqueue_script($this->plugin_name.'-fullcalendar-mo', plugin_dir_url( __DIR__ ) . 'assets/fullcalendar/lib/moment.min.js', array('jquery'), $this->version );
		
		wp_enqueue_script($this->plugin_name.'-fullcalendar-min', plugin_dir_url( __DIR__ ) . 'assets/fullcalendar/fullcalendar.min.js', array('jquery'), $this->version );
	

	}

	/**
	 * Archive Page template for wp_events
	 *
	 * @param $template
	 *
	 * @return string
	 * @since 1.0.448
	 */
	public function wpevents_archive_template( $template ) {

		if ( is_post_type_archive( 'wp_events' ) ) {

			if ( function_exists( 'genesis' ) === TRUE ) {  //if genesis is active load genesis template
				$template_path   = 'wp-events/genesis/archive-wp_events.php';
				$exists_in_theme = locate_template( $template_path );
				if ( $exists_in_theme !== '' ) {
					return $exists_in_theme;
				} else {
					return plugin_dir_path( __FILE__ ) . 'templates/genesis/archive-wp_events.php';
				}
			} else {

				$theme_files     = [
					'archive-wp_events.php',
					'wp-events/archive-wp_events.php',
				];
				$exists_in_theme = locate_template( $theme_files );
				if ( $exists_in_theme !== '' ) {
					return $exists_in_theme;
				} else {
					return plugin_dir_path( __FILE__ ) . 'templates/archive-wp_events.php';
				}

			}
		}

		return $template;
	}

	/**
	 * Single Page template for wp_events
	 *
	 * @since  1.0.2
	 * @param  $single
	 * @return string
	 */
	public function wpevents_single_template( $single ) {
		global $post;

		if ( ( $post->post_type === 'wp_events' ) && file_exists( plugin_dir_path( __FILE__ ) . 'templates/single-wp_events.php' ) ) {
			$theme_files = array('single-wp_events.php', 'wp-events/single-wp_events.php');
			$exists_in_theme = locate_template($theme_files, false);
			if ( $exists_in_theme !== '' ) {
				return $exists_in_theme;
			} else {
				return plugin_dir_path(__FILE__) . 'templates/single-wp_events.php';
			}
		}
		return $single;
	}

	/**
	 * Adding custom classes on pages
	 *
	 * @since  1.0.0
	 * @param  $classes
	 * @return array
	 */
	public function wpe_body_classes( $classes ) {

		if( is_post_type_archive('wp_events' ) ) {
			$classes[] = 'wp-events';
		}
		return $classes;

	}

	/**
	 * include all files from includes folder except classes
	 *
	 * @since 1.0.0
	*/
	private function include_files_from_directory() {
		foreach (glob(plugin_dir_path(__FILE__)."includes/*.php") as $filename) {
			if ( isset( $filename ) ) {
				if( strpos( $filename, 'class' ) ) {
					continue;
				}
				require_once $filename;
			}
		}
	}


	/**
	 * Displays The Image On Archive page if selected in settings
	 *
	 * @since     1.0.0
	 */
	public function wpe_image_on_archive() {
		$display_option = get_option( 'wpe_display_settings' );
		if( isset( $display_option['image_id'] ) ) {
			if ( $image = wp_get_attachment_image_src( $display_option['image_id'], 'full' ) ) {
				echo '<div class="wpe-banner-image"><img width="100%" src="' . $image[0] . '" alt="' . $display_option['archive_title'] . '-banner"/></div>';
			}
		}
	}


	/**
	 * Taxonomy Template for wpevents-category
	 * @param $template
	 *
	 * @return mixed|string
	 * @since 1.0.2
	 */
	function wpevents_taxonomy_template( $template ){
        if ( is_tax( 'wpevents-category' ) ) {
            $template = plugin_dir_path(__FILE__) . 'templates/taxonomy-wpevents-category.php';
        }
        return $template;
    }

	/**
	 * Add WP Events Archive To Page Templates
	 *
	 * displays template in page edit screen
	 * Page Attributes-> Templates -> dropdown
	 *
	 * @param $templates    array   all templates array
	 *
	 * @return mixed
	 * @since 1.0.2
	 */
	function wpevents_themes_page_template( $templates ) {

		$templates['archive-wp_events.php'] = 'WP Events Archive';

		return $templates;
	}

	/**
	 * Returns archive page path when archive template is selected
	 *
	 * @param $template string  template file-name/path
	 *
	 * @return string
	 * @since 1.0.2
	 */
	function wpevents_archive_to_page_template( $template ) {
		$post          = get_post();
		$page_template = get_post_meta( $post->ID, '_wp_page_template', TRUE );
		if ( 'archive-wp_events.php' === basename( $page_template ) ) {
			$theme_files     = [
				'archive-wp_events.php',
				'wp-events/archive-wp_events.php',
			];
			$exists_in_theme = locate_template( $theme_files, FALSE );
			if ( $exists_in_theme !== '' ) {
				return $exists_in_theme;
			} else {
				return plugin_dir_path( __FILE__ ) . 'templates/archive-wp_events.php';
			}
		}

		return $template;
	}

	/**
	 * Set Post per page in pre get post query
	 *
	 * @param $wpe_query
	 *
	 * @since 1.0.2
	 */
	public function wpe_custom_query_post_setup( $wpe_query ) {
		if ( ( is_post_type_archive( 'wp_events' ) || is_tax( 'wpevents-category' ) ) && !is_admin() ) {
			$archive_no_posts = get_option( 'wpe_display_settings' );
			$events_number    = $archive_no_posts['archive_posts'] ?? 12;
			$wpe_query->set( 'posts_per_page', $events_number );
		}
	}

	/**
	 * Shortcode callback
	 *
	 * @param $atts
	 *
	 * @return mixed|void
	 * @since 1.1.01
	 */
	public function wpevent_shortcode( $atts = [] ) {

		$wpe_settings = get_option( 'wpe_settings' );
		if ( isset( $wpe_settings['events_post_name'] ) ) {
			$title = $wpe_settings['events_post_name']; 
		} else {
			$title = 'Upcoming Events';
		}
		// normalize attribute keys, lowercase
		$atts = array_change_key_case( (array) $atts, CASE_LOWER );

		// override default attributes with user attributes
		$wpevent_atts = shortcode_atts(
			[
				'title'         => $title,
				'number'        => 3,
				'location'      => 'true',
				'archive'       => 'true',
				'date'          => 'true',
				'class'		    => '',
				'category'	    => '',
				'button_text'   => __( 'See all ' . $title, 'wp-events' ),
				'type' 		    => '',
				'single-button' => 'false',
			], $atts
		);

		$get_posts = [
			'posts_per_page'	 => $wpevent_atts['number'],
			'post_type' 	     => 'wp_events',
			'meta_key'	         => 'wpevent-start-date-time',
			'order' 	         => 'ASC',
			'orderby'	         => 'meta_value_num',
			'ignore_custom_sort' => true,
			'post_status'    	 => 'publish',
			'meta_query'  	     => array(
				'date_clause'    => array(
					'key'     	 => 'wpevent-end-date-time',
					'value'   	 => strtotime( current_time( 'mysql' ) ),
					'type'    	 => 'numeric',
					'compare' 	 => '>',
				),
			),
		];

		if ( $wpevent_atts['category'] !== '' ) {
			$tax_arr = explode( ',', $wpevent_atts['category'] );
			$get_posts['tax_query'] = [
				[
					'taxonomy' => 'wpevents-category',
					'field'    => 'ID',
					'terms'    => $tax_arr,
				]
			];
		}

		if ( $wpevent_atts['type'] !== '' ) {
			$type_arr = explode( ',', $wpevent_atts['type'] );
			$get_posts['meta_query'] = [
				'relation' => 'AND'
			];
			$get_posts['meta_query']['date_clause'] = [
				'key'     	 => 'wpevent-end-date-time',
				'value'   	 => strtotime( current_time( 'mysql' ) ),
				'type'    	 => 'numeric',
				'compare' 	 => '>',
			];
			$get_posts['meta_query']['type_clause'] = [
				'key'     	 => 'wpevent-type',
				'value'   	 => $type_arr,
				'compare' 	 => 'IN',
			];
		}
		
		$latest_events = get_posts( $get_posts );
		$html          = '';
		$button_html   = '';
		foreach ( $latest_events as $event ) {
			$html             .= '<div class="wpevent-card">';
			$wpe_external_url = get_post_meta( $event->ID, 'wpevent-external-url', TRUE );
			$event_permalink  = get_the_permalink( $event->ID );
			if ( ! empty( $wpe_external_url ) ) {
				$event_permalink = $wpe_external_url;
			}
			$html .= '<p class="wpe-single-event-title"><a href="' . $event_permalink . '">' . $event->post_title . '</a></p>';
			if ( $wpevent_atts['date'] === 'true' ) {
				$html .= $this->get_event_date( $event->ID );
			}
			if ( $wpevent_atts['location'] === 'true' ) {
				$html .= $this->get_event_location( $event->ID );
			}
			if ( $wpevent_atts['single-button'] !== 'false' ) {
				$html .= '<div class="wpe-single-registration">
							<a class="button wpe-reg-button wpe-single-reg" href="' . $event_permalink . '" 
							title="Click to Register">Register</a>
						</div>';
			}
			$html .= '</div>';
		}
		if ( $html === '' ) {
			ob_start();
			if ( $wpevent_atts['title'] !== '' ) {
				echo '<strong class="wpe-main-title" >' . __( $wpevent_atts['title'], 'wp-events' ) . '</strong><div class="wpevent-main">' . $html . '</div>';
			}
			do_action( 'wp_events_subscribe_form' );
			$html = ob_get_clean(); 
			$html = '<div class="wpevents-shortcode-section wpevents-section">' . $html . '</div>';
		} else {
			if ( $wpevent_atts['archive'] === 'true' ) {
				$button_html .= '<div class="wpevent-archive-button"><a class="button" href="' . get_post_type_archive_link( 'wp_events' ) . '"> ' . apply_filters( 'wpevents_shortcode_button', $wpevent_atts['button_text'] ) . ' </a></div>';
			}
			if ( $wpevent_atts['title'] !== '' ) {
				$html = '<strong class="wpe-main-title" >' . __( $wpevent_atts['title'], 'wp-events' ) . '</strong><div class="wpevent-main">' . $html . '</div>';
			}
			if ( $wpevent_atts['class'] !== '' ) {
				$html = '<div class="wpevents-shortcode-section '. $wpevent_atts['class'] .' wpevents-section">' . $html . $button_html . '</div>';
			} else {
				$html = '<div class="wpevents-shortcode-section wpevents-section">' . $html . $button_html . '</div>';
			}
		}

		return apply_filters( 'wpevents_shortcode_html', $html );
	}

	/**
	 * Returns event location by ID
	 *
	 * @param  int  $ID     event ID
	 *
	 * @return string|void
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public function get_event_location( int $ID ) {
		$wpe_type 		 = get_post_meta( $ID, 'wpevent-type', TRUE );
		$wpe_location 	 = (int) get_post_meta( $ID, 'wpevent-location', TRUE );
		$location_id 	 = $wpe_location != 0 ? $wpe_location : $ID;
		if ( $wpe_type === 'webinar' || $location_id === 0 ) {    //webinars are online events
			return '';
		}

		$venue_meta 	 = $wpe_location != 0 ? 'wpevent-loc-venue' : 'wpevent-venue';
		$address_meta 	 = $wpe_location != 0 ? 'wpevent-loc-address' : 'wpevent-address';
		$city_meta  	 = $wpe_location != 0 ? 'wpevent-loc-city' : 'wpevent-city';
		$state_meta 	 = $wpe_location != 0 ? 'wpevent-loc-state' : 'wpevent-state';
		$wpe_venue       = get_post_meta( $location_id, $venue_meta, TRUE );
		$wpe_addr        = get_post_meta( $location_id, $address_meta, TRUE );
		$wpe_city        = get_post_meta( $location_id, $city_meta, TRUE );
		$wpe_state       = get_post_meta( $location_id, $state_meta, TRUE );

		$venue_html = '';
		if ( $wpe_venue !== '' ) {
			$venue_html .= '<span class="wpe-address">' . $wpe_venue . ',</span>';
		}
		if ( $wpe_addr !== '' ) {
			$venue_html .= '&nbsp;<span class="wpe-address">' . $wpe_addr . ',</span>';
		}
		if ( $wpe_city !== '' ) {
			$venue_html .= '&nbsp;<span class="wpe-city">' . ucwords( $wpe_city ) . ',</span>';
		}
		if ( $wpe_state !== '' ) {
			$venue_html .= '&nbsp;<span class="wpe-state">' . ucfirst( $wpe_state ) . '</span>';
		}
		if ( $venue_html !== '' ) {
			$venue_html = '<p class="wpe-venue"><strong>Venue: </strong>' . $venue_html . '</p>';
		}

		return apply_filters( 'wpe_shortcode_location', $venue_html );
	}

	/**
	 * Returns Event Date by event ID
	 *
	 * @param  int  $ID     event ID
	 *
	 * @return string|void
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_event_date( int $ID ) {
		$start_date      = get_post_meta( $ID, 'wpevent-start-date-time', TRUE );
		$end_date        = get_post_meta( $ID, 'wpevent-end-date-time', TRUE );
		$date_format     = apply_filters( 'wpevents_shortcode_date_format', 'F j' );
		$html            = '<span class="wpe-event-date"><strong>Date: </strong>';
		if ( date( $date_format, $start_date) === date( $date_format, $end_date ) ) {
			$html .= date( $date_format, $start_date  );
		} else {
			$html .= date( $date_format, $start_date) . ' - ' . date( $date_format, $end_date );
		}
		$html .= '</span>';

		return apply_filters( 'wpe_shortcode_date', $html );
	}

	/**
	 * archive loop shortcode
	 * 
	 * @param $atts
	 *
	 * @since 1.1.01
	 */
	public function archive_shortcode( $atts = [] ) {

		$wpe_settings = get_option( 'wpe_settings' );
		if ( isset( $wpe_settings['events_post_name'] ) ) {
			$title = $wpe_settings['events_post_name']; 
		} else {
			$title = 'Upcoming Events';
		}

		$wpevent_atts = shortcode_atts(
			[
				'category' => '',
				'title'	   => '',
				'type' 	   => '',
			], $atts
		);
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
		ob_start();
		?>

        <div class="wpe-event">
            <div class="wpe-full-wrap">
                <div class="wpevents-container">
					<?php

					if ( $wpevent_atts['title'] !== '' ) {
						echo '<h1 class="entry-title wpe-template-title">' . esc_attr( $wpevent_atts['title'] ) . '</h1>';
					}

					/**
					 * Print the archive image if set in
					 * settings->display->archive Image
					 *
					 * @since  1.0.449
					 * @action Wp_Events_Public->wpe_image_on_archive
					 */
					do_action( 'wp_events_archive_image' );
					echo wpe_archive_views();

					if (!empty($wpe_query->posts)) {
						# code...
						?>
						<div id="wpe-calendar" <?php echo $default_view != 'calendar' ? 'style="display:none"' : ''; ?>></div>
						<div class="wpe-list-grid-view <?php echo $class; ?>" <?php echo $default_view == 'calendar' ? 'style="display:none"' : ''; ?>>
						<?php
					} 

					$args = wpe_get_default_query_args();

					if ( $wpevent_atts['category'] !== '' ) {
						$tax_arr = explode( ',', $wpevent_atts['category'] );
						$args['tax_query'] = [
							[
								'taxonomy' => 'wpevents-category',
								'field'    => 'ID',
								'terms'    => $tax_arr,
							]
						];
					}

					if ( $wpevent_atts['type'] !== '' ) {
						$type_arr = explode( ',', $wpevent_atts['type'] );
						$args['meta_query'] = [
							'relation' => 'AND'
						];
						$args['meta_query']['date_clause'] = [
							'key'     	 => 'wpevent-end-date-time',
							'value'   	 => strtotime( current_time( 'mysql' ) ),
							'type'    	 => 'numeric',
							'compare' 	 => '>',
						];
						$args['meta_query']['type_clause'] = [
							'key'     	 => 'wpevent-type',
							'value'   	 => $type_arr,
							'compare' 	 => 'IN',
						];
					}

					$wpe_query = new WP_Query( $args );

					if ( $wpe_query->have_posts() ) {
						while ( $wpe_query->have_posts() ) {
							$wpe_query->the_post();
							$post_id 		 = get_the_ID();
							$hide_in_archive = get_post_meta( $post_id, 'wpevent-hide-archive', true );
							$feat_img 		 = get_the_post_thumbnail_url() != false ? get_the_post_thumbnail_url() : plugins_url() . '/wp-events/assets/img/logo-placeholder.png';
							$count 			 = 0;

							if ( $hide_in_archive === 'yes' ) {
								continue;
							}
							$count++;
							?>
                        	<div class="wpe-row wpe-<?php echo $post_id ?> <?php echo $default_grid_layout ?>">
							<div class="wpe-day-date">
								<?php
								wpe_get_events_day_date_column( $post_id ); ?>
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
		return ob_get_clean();
	}

	/**
	 * Adds Meta Description to head
	 * 
	 * 
	 * @since  1.2.5
	 * @access public
	 *
	 */
	public function wpe_meta_description() {
		$post_type = get_post_type();
		$option    = get_option( 'wpe_settings' );
		if ( $post_type === 'wp_events' ) {
			global $post;
			if ( is_singular() ) {
				$des_post = strip_tags( $post->post_content );
				$des_post = strip_shortcodes( $post->post_content );
				$des_post = str_replace( array("\n", "\r", "\t"), ' ', $des_post );
				$des_post = mb_substr( $des_post, 0, 300, 'utf8' );
				$des_post = ( $des_post !== '' ) ? $des_post : $option['meta_description'];
				echo '<meta name="description" content="' . esc_html( $des_post ) . '" />' . "\n";
			} else {
				echo '<meta name="description" content="' . $option['meta_description'] . '" />' . "\n";
			}
		}
	}
}
