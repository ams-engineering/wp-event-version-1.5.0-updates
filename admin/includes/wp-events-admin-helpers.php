<?php

/**
 * All helper function used multiple times
 * are defined in this file
 *
 * @since 1.2.0
 */

if ( ! function_exists( 'wpe_sidebar_section' ) ) {
	/**
     * Creates sidebar section for admin pages
     * 
     * @param string$title
     * @param string $body
	 *
	 * @since 1.2.0
	 */
	function wpe_sidebar_section( $title, $body ) {
        ?>
        <div class="wpe-sidebar-section">
            <div class="section-header">
                <h2 class="section-heading"><?php echo $title ?></h2>
                <div class="section-actions">
                    <span class="dashicons dashicons-arrow-up wpe-action-icon"></span>
                </div>
            </div>
            <div class="section-body">
            <?php
                echo $body;
            ?>
            </div>
        </div>
        <?php
    }
}

if ( ! function_exists( 'wpe_sidebar_footer' ) ) {
	/**
     * Creates footer for sections in admin sidebar
     * 
     * @param $link_text
     * @param $button_text
	 *
	 * @since 1.2.0
	 */
	function wpe_sidebar_footer( $link_text, $button_text ) {
        $footer = '<div class="section-footer">
        <div class="publish-actions">
            <div class="trash-action">
                <a href="#" class="wpe-to-trash">'. $link_text .'</a>
            </div>
            <div class="edit-action">
                <button class="wpe-btn wpe-edit-registration">'. $button_text .'
                </button>
            </div>
        </div>
        </div>';
        return $footer;
    }
}

if ( ! function_exists( 'wpe_seat_options' ) ) {
    /**
     * Returns options for seats dropdown
     *
     * @param array $results
     * 
     * @since 1.2.0
     * @return array
     */
    function wpe_seat_options( $results ) {
        $booked_seats    = get_booked_seats( $results[0]->post_id ); //Function defined in wp-events-global-functions.php
        $totalseats      = (int) get_post_meta( $results[0]->post_id, 'wpevent-seats', TRUE );
        $remaining_seats = $totalseats - $booked_seats;
		$option          = get_option('wpe_display_settings');
		$seats_per_entry = $option['max_seats'] ?? 10;
        $options         = array();
        if ( $remaining_seats < $seats_per_entry ) {
            $max_seats = $remaining_seats;
        } else {
            $max_seats = $seats_per_entry;
        }
        if ( $results[0]->wpe_seats > $max_seats ) {
            $max_seats = $results[0]->wpe_seats;
        }
        for ( $number = 1; $number <= $max_seats; $number ++ ) {
            $options[] = $number;
        }

        return $options;
    }
}

if( ! function_exists( 'wpe_is_active_tab' ) ) {
	/**
	 * Compares first two arguments and returns nav-tab-active
	 *
	 * @param  string  $current
	 * @param  string  $compare
	 * @param  bool    $echo
	 *
	 * @return string
	 *
	 * @since 1.1.0
	 */
	function wpe_is_active_tab( string $current, string $compare, bool $echo = FALSE ) {
		if ( $current !== $compare ) {  // return if it's not equal
			return '';
		}

		if ( ! $echo ) {    // return string if echo is set to false
			return 'nav-tab-active';
		}

		echo 'nav-tab-active';
	}
}

if( ! function_exists( 'wpe_get_entry_status' ) ) {
	/**
	 * Returns status of entry in text form
	 *
	 * @param int $status
	 *
	 * @return string
	 *
	 * @since 1.2.0
	 */
	function wpe_get_entry_status( $status ) {
        switch ( $status ) {
            case '-1':
                $text = 'Deleted';
                break;
            case '0':
                $text = 'Trash';
                break;
            case '1':
                $text = 'Active';
                break;
            case '2':
                $text = 'Pending Approval';
                break;
            case '3':
                $text = 'Approved';
                break;
            case '4':
                $text = 'Cancelled';
                break;
        }

        return $text;
	}
} 

if( ! function_exists( 'wpe_get_seminar_message' ) ) {
	/**
	 * Returns seminar email message from settings
	 *
	 * @return string
	 *
	 * @since 1.2.5
	 */
	function wpe_get_seminar_message() {
        $option = get_option('wpe_mail_settings');
        return $option['mail_success_message'];
    }
}

if( ! function_exists( 'wpe_get_webinar_message' ) ) {
	/**
	 * Returns webinar email message from settings
	 *
	 * @return string
	 *
	 * @since 1.2.5
	 */
	function wpe_get_webinar_message() {
        $option = get_option('wpe_mail_settings');
        return $option['webinar_success_message'];
    }
}

if( ! function_exists( 'wpevents_country_drop_down' ) ) {
	/**
     * Country Drop Down Field in Event Fields
     * @param $wp_event_country
     * @param string $name
     *
     * @since 1.0.0
    */
	function wpevents_country_drop_down( $wp_event_country, $name ) {
        $html ='';
        $html .='<select class="wp-event-field wpe-form-control wpe-location-field" id="' . $name . '" name="' . $name . '">';
        //Array containing all country Names
        $countries = array("Select Country", "Afghanistan", "Albania", "Algeria", "American Samoa", "Andorra", "Angola", "Anguilla", "Antarctica", "Antigua and Barbuda", "Argentina", "Armenia", "Aruba", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia and Herzegowina", "Botswana", "Bouvet Island", "Brazil", "British Indian Ocean Territory", "Brunei Darussalam", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Cayman Islands", "Central African Republic", "Chad", "Chile", "China", "Christmas Island", "Cocos (Keeling) Islands", "Colombia", "Comoros", "Congo", "Congo, the Democratic Republic of the", "Cook Islands", "Costa Rica", "Cote d'Ivoire", "Croatia (Hrvatska)", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East Timor", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Falkland Islands (Malvinas)", "Faroe Islands", "Fiji", "Finland", "France", "France Metropolitan", "French Guiana", "French Polynesia", "French Southern Territories", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Gibraltar", "Greece", "Greenland", "Grenada", "Guadeloupe", "Guam", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Heard and Mc Donald Islands", "Holy See (Vatican City State)", "Honduras", "Hong Kong", "Hungary", "Iceland", "India", "Indonesia", "Iran (Islamic Republic of)", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea, Democratic People's Republic of", "Korea, Republic of", "Kuwait", "Kyrgyzstan", "Lao, People's Democratic Republic", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libyan Arab Jamahiriya", "Liechtenstein", "Lithuania", "Luxembourg", "Macau", "Macedonia, The Former Yugoslav Republic of", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Martinique", "Mauritania", "Mauritius", "Mayotte", "Mexico", "Micronesia, Federated States of", "Moldova, Republic of", "Monaco", "Mongolia", "Montserrat", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "Netherlands Antilles", "New Caledonia", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Niue", "Norfolk Island", "Northern Mariana Islands", "Norway", "Oman", "Pakistan", "Palau", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Pitcairn", "Poland", "Portugal", "Puerto Rico", "Qatar", "Reunion", "Romania", "Russian Federation", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Seychelles", "Sierra Leone", "Singapore", "Slovakia (Slovak Republic)", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Georgia and the South Sandwich Islands", "Spain", "Sri Lanka", "St. Helena", "St. Pierre and Miquelon", "Sudan", "Suriname", "Svalbard and Jan Mayen Islands", "Swaziland", "Sweden", "Switzerland", "Syrian Arab Republic", "Taiwan, Province of China", "Tajikistan", "Tanzania, United Republic of", "Thailand", "Togo", "Tokelau", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Turks and Caicos Islands", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "United States Minor Outlying Islands", "Uruguay", "Uzbekistan", "Vanuatu", "Venezuela", "Vietnam", "Virgin Islands (British)", "Virgin Islands (U.S.)", "Wallis and Futuna Islands", "Western Sahara", "Yemen", "Yugoslavia", "Zambia", "Zimbabwe");
        foreach ($countries as $country) {
            if($country===$wp_event_country) {
                $html.='<option selected value="'.$country.'">'.$country.'</option>';
            }
            if( $country === "Select Country" ) {
	            $html .= '<option value="">' . $country . '</option>';
            } else {
	            $html .= '<option value="' . $country . '">' . $country . '</option>';
            }
        }
        $html.='</select>';
        echo  $html;
    }
}

if( ! function_exists( 'wpevents_location_drop_down' ) ) {
	/**
     * Location Drop Down Field in Event Metaboxes
     * 
     * @param $wp_event_location
     * @param string $name
     *
     * @since 1.3.0
    */
	function wpevents_location_drop_down( $wp_event_location, $name ) {
        $html ='';
        $html .='<select class="wp-event-field wpe-form-control wpe-location-field" id="wpevent-location" name="' . $name . '">';
        //Array containing all location names
        $args = array(
            'post_type'      => 'locations',
            'posts_per_page' => -1,
        );
        $location_dropdown = array( 0 => 'Select Location' );
        $locations         = get_posts( $args );
        for( $i = 0; $i < sizeof( $locations ); $i++ ) {
            if ( $locations[ $i ]->post_title === '' ) {
                $locations[ $i ]->post_title = 'Location ' . $locations[ $i ]->ID;
            }
            $location_dropdown[ $locations[ $i ]->ID ] = $locations[ $i ]->post_title;
        }
        foreach( $location_dropdown as $ID => $location ) {
            if( (string) $ID === $wp_event_location ) {
                $html.='<option selected value="'. $ID .'">'. $location .'</option>';
            } else if( $location === "Select Location" ) {
	            $html .= '<option value="xxx">' . $location . '</option>';
            } else {
	            $html .= '<option value="' . $ID . '">' . $location . '</option>';
            }
        }
        $html.='</select>';
        echo  $html;
    }
}

if( ! function_exists( 'wpe_editor' ) ) {
    /**
     * Integrate TinyMCE WP Editor.
     *
     * @param string $content
     * @param string $editor_id
     * @param string $editor_name
     * 
     * @since 1.4.0
     */
    function wpe_editor( $content, $editor_id, $editor_name ) {
        wp_editor( $content, $editor_id, array( 'textarea_name' => $editor_name ) );
    }
}

if( ! function_exists( 'wpe_event_title' ) ) {
    /**
     * Get title dropdown for filters.
     *
     * @since 1.4.3
     * @return string
     */
    function wpe_event_title() {
        $post_title = null;
		$post_id	= null;
		$results 	= null;
		$args = array(
			'post_type' => 'wp_events',
            'posts_per_page' => -1
		);

		$query = new WP_Query( $args );
        if ( $query->have_posts() ) :
            while ( $query->have_posts() ) : $query->the_post();
                $post_id[]	  = get_the_ID();
                $post_title[] = get_the_title();
            endwhile;
        endif;
        wp_reset_postdata();
        $results = wpe_array_combine( $post_title, $post_id );

		// Return null if we found no results.
		if ( ! $results )
		return false;

		// HTML for our select printing post titles as loop.
		$output = '<select name="wpe_titles" id="wpe_titles" class="mdb-select md-form" searchable="Search here..">';

		$output .= '<option value="" selected>Select Event</option>';

		foreach ( $results as $title => $ids ) {
			if ( is_array( $ids ) ) {
				$ids = implode(',', $ids);
			} else {
				$ids = (string) $ids;
			}
			$selected = ( isset( $_GET['wpe_titles'] ) && ( (string) strpos( $ids, $_GET['wpe_titles'] )) !== '' ) ? "selected" : "";
			$output .= '<option value="' . $ids . '" ' . $selected . '>' . $title . '</option>';
		}

		$output .= '</select>'; // end of select element.

		// get the html.
		return $output;
    }
}


if( ! function_exists( 'wpe_array_combine' ) ) {
    /**
     * Creates associative array of post titles and ids.
     *
     * @param string $keys
     * @param int $values
     * 
     * @since 1.4.3
     * @return array
     */
    function wpe_array_combine( $keys, $values ) {
        $result = array();
        if( ! empty( $keys ) ) {
            foreach ( $keys as $i => $k ) {
                $result[ $k ][] = $values[ $i ];
            }
            $callback = function( &$v ) {
                $v = ( count( $v ) == 1 ) ? array_pop( $v ) : $v;
            };
            array_walk( $result, $callback );
        }
        return $result;
    }
}