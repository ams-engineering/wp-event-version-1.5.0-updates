<?php

/**
 * Registration Entries List
 *
 * @package    Wp_Events/admin
 * @subpackage Wp_Events/admin/includes
 * @since      1.0.500
 */

// If this file is called directly, abort.
if ( !defined('WPINC') ) {
	die;
}

/**
 * Includes WP_List_Table class from wordpress core.
 */
if ( !class_exists('WP_List_Table') ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * New class that extends WP_List_Table, displays form entries
 * and handles user actions.
 *
 * @since 1.0.449
 * 
 * @see WP_List_Table
 */
class Wp_Events_Registrations_list extends WP_List_Table {

	/**
	 * Name of the table in database.
	 * 
	 * @since 1.0.449
	 * @var string $table_name
	 */
	protected $table_name = 'events_registration';

	/**
	 * Class Constructor.
	 * 
	 * Calls constructor from the parent class.
	 *
	 * @since 1.0.449
	 */
	public function __construct() {
		parent::__construct([
			'singular' => __( 'Registrations', 'wp-events' ),
			//singular name of the listed records
			'plural'   => __( 'Registrations', 'wp-events' ),
			//plural name of the listed records
			'ajax'     => FALSE
			//should this table support ajax?
		]);
	}

	/**
	 * Message to display when no items are available.
	 * 
	 * @since 1.0.449
	 */
	public function no_items() {
		_e( 'No Registrations available.', 'wp-events' );
	}

	/**
	 * Define what data to show on each column of the table.
	 *
	 * @param  Array   $item         Data.
	 * @param  String  $column_name  - Current column name.
	 *
	 * @since 1.0.449
	 * @return Mixed
	 */
	public function column_default( $item, $column_name ) {

		switch ( $column_name ) {
			case 'first_name':
			case 'last_name':
			case 'email':
				return $item[ $column_name ];
			case 'event':
				return '<a href="' . get_the_permalink( $item['post_id'] ) . '">' . get_the_title( $item['post_id'] ) . '</a>';
			case 'event_type':
				return get_post_meta( esc_attr( $item['post_id'] ), 'wpevent-type', TRUE );
			case 'time':
				return $item['time_generated'];
			case 'wpe_seat':
				return $item['wpe_seats'];
			case 'ID':
				return $item['ID'];
			default:
				return print_r( $item, TRUE );
		}
	}

	/**
	 * Render the bulk edit checkbox.
	 *
	 * @param  array  $item
	 *
	 * @since 1.0.449
	 * @return string
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />',
			$item['ID']
		);
	}

	/**
	 * Returns an associative array containing the bulk action.
	 *
	 * @return array
	 * @since 1.1.0
	 */
	public function get_bulk_actions() {
		$bulk_actions['bulk-delete'] = __( 'Move to Trash', 'wp-events' );

		$display = isset( $_GET['display'] ) ? $_GET['display'] : 'all';

		switch( $display ) {
			case 'pending':
				$bulk_actions['approve-entry']	  = __( 'Approve', 'wp-events' );
				$bulk_actions['cancel-entry']	  = __( 'Cancel', 'wp-events' );
				break;
			case 'trash':
				unset( $bulk_actions['bulk-delete'] );
				$bulk_actions['permanent-delete'] = __( 'Delete Permanently', 'wp-events' );
				$bulk_actions['restore']		  = __( 'Restore', 'wp-events' );
				break;
		}

		return $bulk_actions;
	}

	/**
	 * Outputs HTML to display bulk actions and filters.
	 * 
	 * @since 1.0.449
	 * @param $which
	 */
	public function display_tablenav( $which ) {

?>
		<input type='hidden' name='_wpnonce' value='<?php echo wp_create_nonce('wp_events_entries'); ?>' />
		<div class="tablenav 
		<?php echo esc_attr( $which ); ?>">
		<?php if ( $this->has_items() ) : ?>
		<div class="alignleft actions bulkactions">
		<?php $this->bulk_actions( $which ); ?>
		</div>
		<?php
			endif;
			$this->extra_tablenav( $which );
			$this->pagination( $which );
		?>
		<br class="clear" />
		</div>
<?php
	}

	/**
	 * Handles single row actions of deletion and restore.
	 *
	 * @param $item
	 *
	 * @return string
	 * @since 1.1.0
	 */
	function column_ID( $item ) {
		
		$display	   = isset( $_GET["display"] ) ? $_GET["display"] : 'all';
		$event_options = get_option('wpe_events_settings');

		if ( isset( $event_options['approve_registrations'] ) ) { //only for pending approval entries
			if( $display === 'pending' || $item['wpe_status'] == WPE_PENDING || $display === 'cancelled' || $item['wpe_status'] == WPE_CANCELLED ) {
				$actions['approve_entry'] = sprintf(
						'<a href="edit.php?post_type=wp_events&page=%s&display=%s&action=%s&bulk-delete[0]=%s&action2=approve-entry&_wpnonce=%s">' . __( 'Approve', 'wp-events' ) . '</a>',
						$_REQUEST['page'],
						$display,
						'approve-entry',
						$item['ID'],
						wp_create_nonce( 'wp_events_entries' ) );
			}
			if( $display === 'pending' || $item['wpe_status'] == WPE_PENDING || $display === 'approved' || $item['wpe_status'] == WPE_APPROVED ) {
				$actions['cancel_entry'] = sprintf(
						'<a href="edit.php?post_type=wp_events&page=%s&display=%s&action=%s&bulk-delete[0]=%s&action2=cancel-entry&_wpnonce=%s">' . __( 'Cancel', 'wp-events' ) . '</a>',
						$_REQUEST['page'],
						$display,
						'cancel-entry',
						$item['ID'],
						wp_create_nonce( 'wp_events_entries' ) );
			}
		}

		$eventID = '';

		//if there is more than one event with same name
		if( isset( $_GET['wpe_titles'] ) && $_GET['wpe_titles'] !== '' ) {
			$event_ids = explode( ',', $_GET['wpe_titles'] );
			for( $i = 0; $i < sizeof( $event_ids ); $i++ ) {
				if( $event_ids[$i] == $item['post_id'] ) {
					$eventID = '&event=' . $event_ids[$i];
				}
			}
		}

		$actions['view_entry'] = sprintf(
			'<a href="edit.php?post_type=wp_events&page=wpe_view_entry'. $eventID .'&entry=%s&tab=registrations&display='. $display .'">' . __( 'View', 'wp-events' ) . '</a>',
			$item['ID'] );


		if ( $display === 'trash' ) {  //only for trash entries
				$actions['delete_permanent'] = sprintf(
				'<a href="edit.php?post_type=wp_events&page=%s&display=trash&action=%s&bulk-delete[0]=%s&action2=permanent-delete&_wpnonce=%s" onclick="return confirm(\'Are you sure you want to delete item(s)?\');">' . __( 'Delete Permanently', 'wp-events' ) . '</a>',
				$_REQUEST['page'],
				'permanent-delete',
				$item['ID'],
				wp_create_nonce( 'wp_events_entries' ) );
			$actions['restore'] 		 = sprintf(
				'<a href="edit.php?post_type=wp_events&page=%s&display=trash&action=%s&bulk-delete[0]=%s&action2=restore&_wpnonce=%s">' . __( 'Restore', 'wp-events' ) . '</a>',
				$_REQUEST['page'],
				'restore',
				$item['ID'],
				wp_create_nonce( 'wp_events_entries' ) );
		} else {
            $actions['delete'] = sprintf(
                    '<a href="edit.php?post_type=wp_events&page=%s&display=%s&action=%s&bulk-delete[0]=%s&action2=bulk-delete&_wpnonce=%s">' . __( 'Move To Trash', 'wp-events' ) . '</a>',
                    $_REQUEST['page'],
					$display,
                    'bulk-delete',
                    $item['ID'],
                    wp_create_nonce( 'wp_events_entries' ) );
		}

		return sprintf(
			'%1$s %2$s',
			$item['ID'],
			$this->row_actions( $actions )
        );
	}

	/**
	 * Handles data query and filter, sorting, and pagination.
	 * 
	 * @since 1.0.449
	 */
	public function prepare_items()	{

		if ( ! Wp_Events_Db_Actions::wpe_table_exists( $this->table_name ) ) {
			Wp_Events_Db_Actions::add_registration_table();
		}

		$this->wpe_remove_deleted_event_entries();

		$search_key = isset( $_REQUEST['s'] ) ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';

		$this->_column_headers = [
			$this->get_columns(),
			[], // hidden columns
			$this->get_sortable_columns(),
			$this->get_primary_column_name(),
		];

		//Delete all trash entries when empty trash button is clicked.
		if ( isset( $_GET['emptytrash'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'wp_events_entries' ) ) {
			$this->wpe_empty_trash();
		}

		/** Process bulk action */
		$this->process_bulk_action();

		$per_page      = $this->get_per_page();
		$current_page  = $this->get_pagenum();

		//Create SQL string for filter dropdowns
		$filter_string = '';

		$filter_posts_array = $this->get_filter_string();

		if ( isset( $filter_posts_array ) && ! empty( $filter_posts_array ) ) {
			$postID_string = implode(', ', $filter_posts_array);
			$filter_string = ' AND post_id in (' . $postID_string .')';
		} elseif( $filter_posts_array === [] ) {
			$filter_string = ' AND post_id in (-1)';
		}

		$total_items   = $this->record_count( $filter_string );

		if ($search_key) {
			$table_data  = $this->get_event_registrations( -1, $current_page, $filter_string );
			$table_data  = $this->filter_table_data( $table_data, $search_key );
			$total_items = count( $table_data );
			$per_page    = $total_items;
		} else {
			$table_data = $this->get_event_registrations( $per_page, $current_page, $filter_string );
		}

		$this->set_pagination_args([
			'total_items' => $total_items,
			'per_page'    => $per_page,
		]);


		$this->items = $table_data;
	}

	/**
	 * Gets a list of columns.
	 *
	 * The format is:
	 * - `'internal-name' => 'Title'`
	 *
	 * @since 1.0.449
	 *
	 * @return array
	 */
	public function get_columns() {
		return [
			'cb'         => '<input type="checkbox">',
			'ID'         => __('Id', 'wp-events'),
			'first_name' => __('First Name', 'wp-events'),
			'last_name'  => __('Last Name', 'wp-events'),
			'email'      => __('Email', 'wp-events'),
			'event'      => __('Event', 'wp-events'),
			'event_type' => __('Type', 'wp-events'),
			'wpe_seat'   => __('Seats', 'wp-events'),
			'time'       => __('Time', 'wp-events'),
		];
	}

	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return [
			'ID'       => ['id', TRUE],
			'time'     => ['time_generated', TRUE],
			'wpe_seat' => ['wpe_seats', TRUE],
		];
	}

	/**
	 * Process bulk actions of deletion, restore, approve, cancel entries.
	 * 
	 * @since 1.2.0
	 */
	public function process_bulk_action() {

		$bulkaction	   = $this->current_action();
		$event_options = get_option('wpe_events_settings');

		switch ( $bulkaction ) {
			case 'bulk-delete':
				$this->wpe_process_bulk_action( WPE_TRASHED, ' item(s) moved to the Trash.' );
				break;
			case 'permanent-delete':
				$this->wpe_process_bulk_action( WPE_DELETED, ' item(s) permanently deleted.' );
				break;
			case 'cancel-entry':
				$this->wpe_process_bulk_action( WPE_CANCELLED,  ' item(s) Cancelled.' );
				$this->wpe_approve_cancel_notification( WPE_CANCELLED );
				break;
			case 'approve-entry':
				$this->wpe_process_bulk_action( WPE_APPROVED, ' item(s) Approved.' );
				$this->wpe_approve_cancel_notification( WPE_APPROVED );
				break;
			case 'restore':
				if ( isset( $event_options['approve_registrations'] ) ) {
					$this->wpe_process_bulk_action( WPE_PENDING, ' item(s) restored from the Trash.' );
				} else {
					$this->wpe_process_bulk_action( WPE_ACTIVE, ' item(s) restored from the Trash.' );
				}
		}
	}

	/**
	 * Trash/Delete, Approve/Cancel a customer record.
	 * 
	 * @global object $wpdb instantiation of the wpdb class.
	 *
	 * @param  int  $id  customer ID.
	 * @param int $val status of registration.
	 * 
	 * @since 1.2.0
	 *
	 * @return bool|int
	 */
	public function update_registration_status( $id, $val ) {
		global $wpdb;

		return $wpdb->update(
			"{$wpdb->prefix}$this->table_name",
			['wpe_status' => $val],
			['id' => $id],
			'%d',
			'%d'
		);
	}

	/**
	 * Returns per page items from screen options.
	 * 
	 * @since 1.0.449
	 * 
	 * @return int 
	 */
	public function get_per_page() {
		// get the current user ID
		$user = get_current_user_id();
		// get the current admin screen
		$screen = get_current_screen();
		// retrieve the "per_page" option
		$screen_option = $screen->get_option( 'per_page', 'option' );
		// retrieve the value of the option stored for the current user
		$per_page = get_user_meta( $user, $screen_option, TRUE );
		if ( empty( $per_page ) || $per_page < 1 ) {
			// get the default value if none is set
			$per_page = $screen->get_option( 'per_page', 'default' );
		}

		return $per_page;
	}

	/**
	 * Returns the count of records (to display on screen) in the database.
	 * 
	 * @global object $wpdb instantiation of the wpdb class.
	 *
	 * @return null|string
	 */
	public function record_count( $filter_string ) {
		global $wpdb;

		$display_tab = isset( $_GET['display'] ) ? $_GET['display'] : 'all';

		switch ( $display_tab ) {
			case 'trash':
				$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}$this->table_name WHERE wpe_status = ". WPE_TRASHED . $filter_string;
				break;
			case 'approved':
				$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}$this->table_name WHERE wpe_status = ". WPE_APPROVED . $filter_string;
				break;
			case 'cancelled':
				$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}$this->table_name WHERE wpe_status = ". WPE_CANCELLED . $filter_string;
				break;
			case 'pending':
				$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}$this->table_name WHERE wpe_status = ". WPE_PENDING . $filter_string;
				break;
			default:
				$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}$this->table_name WHERE wpe_status in (" . WPE_ACTIVE . ", " . WPE_PENDING . ", " . WPE_APPROVED . ", " . WPE_CANCELLED . ")" . $filter_string;
		}

		return $wpdb->get_var( $sql );
	}

	/**
	 * Retrieve customer’s data from the database.
	 * 
	 * @global object $wpdb instantiation of the wpdb class.
	 *
	 * @param  int  $per_page from screen options.
	 * @param  int  $page_number
	 * @param string $filter_string from title and category filters.
	 *
	 * @return mixed
     *
     * @since 1.1.0
	 */
	public function get_event_registrations( $per_page = 5, $page_number = 1, $filter_string ) {

		global $wpdb;

		$display_tab = isset( $_GET["display"] ) ? $_GET["display"] : 'all';

		switch ( $display_tab ) {
			case 'trash':
				$sql = "SELECT * FROM {$wpdb->prefix}$this->table_name WHERE wpe_status = ". WPE_TRASHED . $filter_string;
				break;
			case 'approved':
				$sql = "SELECT * FROM {$wpdb->prefix}$this->table_name WHERE wpe_status = ". WPE_APPROVED . $filter_string;
				break;
			case 'cancelled':
				$sql = "SELECT * FROM {$wpdb->prefix}$this->table_name WHERE wpe_status = ". WPE_CANCELLED . $filter_string;
				break;
			case 'pending':
			$sql = "SELECT * FROM {$wpdb->prefix}$this->table_name WHERE wpe_status = ". WPE_PENDING . $filter_string;
				break;
			default:
			$sql = "SELECT * FROM {$wpdb->prefix}$this->table_name WHERE wpe_status in (" . WPE_ACTIVE . ", " . WPE_PENDING . ", " . WPE_APPROVED . ", " . WPE_CANCELLED . ")" . $filter_string;
		}
		
		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
			$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' DESC';
		} else {
		    $sql .= ' ORDER BY time_generated DESC';
        }

		if ( $per_page !== -1 ) {
			$sql .= " LIMIT $per_page";

			$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;
		}

		$results = $wpdb->get_results( $sql, ARRAY_A );

		return $results;
	}

	/**
	 * Filters the table data based on search key.
	 *
	 * @param array $table_data
	 * @param string $search_key
	 * 
	 * @since 1.0.449
	 * 
	 * @return bool
	 */
	public function filter_table_data( $table_data, $search_key ) {
		return array_values( array_filter( $table_data, function ( $row ) use ( $search_key ) {
			foreach ( $row as $row_val ) {
				if ( stripos( $row_val, $search_key ) !== FALSE ) {
					return TRUE;
				}
			}
		}));
	}

	/**
	 * Displays the list of views (all, trash) available on this table.
	 *
	 * @since 1.0.449
	 */
	public function views() {

		$wpe_current_display = isset( $_GET['display'] ) ? $_GET['display'] : 'all';

		$views = [
			'all'	    => '<a '. wpe_is_current( $wpe_current_display, 'all' ) .'href="edit.php?post_type=wp_events&page=wp_forms_entries&display=all">' . __( 'All', 'wp-events' ) . '</a>',
		];

		$event_options = get_option('wpe_events_settings');
		$event 		   = '';
		if ( isset( $_GET['wpe_titles'] ) ) {
			$number = isset( $_GET['posts_page'] ) ? $_GET['posts_page'] : '1';
			$event  = '&posts_page='. $number .'&wpe_titles='. $_GET['wpe_titles'];
		}

		if ( isset( $event_options['approve_registrations'] ) ) {
			$views['pending']   = '<a '. wpe_is_current( $wpe_current_display, 'pending' ) .' href="edit.php?post_type=wp_events&page=wp_forms_entries&display=pending'. $event .'">' . __( 'Pending Approval', 'wp-events' ) . '</a>';
			$views['approved']  = '<a '. wpe_is_current( $wpe_current_display, 'approved' ) .' href="edit.php?post_type=wp_events&page=wp_forms_entries&display=approved'. $event .'">' . __( 'Approved', 'wp-events' ) . '</a>';
			$views['cancelled'] = '<a '. wpe_is_current( $wpe_current_display, 'cancelled' ) .' href="edit.php?post_type=wp_events&page=wp_forms_entries&display=cancelled'. $event .'">' . __( 'Cancelled', 'wp-events' ) . '</a>';
		}

		$views['trash'] = '<a '. wpe_is_current( $wpe_current_display, 'trash' ) .' href="edit.php?post_type=wp_events&page=wp_forms_entries&display=trash'. $event .'">' . __( 'Trash', 'wp-events' ) . '</a>';

		/**
		 * Filters the list of available list table views.
		 *
		 * The dynamic portion of the hook name, `$this->screen->id`, refers
		 * to the ID of the current screen.
		 *
		 * @since 1.0.449
		 *
		 * @param string[] $views An array of available list table views.
		 */
		$views = apply_filters( "views_{$this->screen->id}", $views );

		if ( empty( $views ) ) {
			return;
		}

		echo "<ul class='subsubsub'>\n";
		foreach ( $views as $class => $view ) {
			$views[ $class ] = "\t<li class='$class'>$view";
		}
		echo implode( " |</li>\n", $views ) . "</li>\n";
		echo '</ul>';
	}

	/**
	 * Extra controls to be displayed between bulk actions and pagination.
	 * 
	 * Displays filters for event titles and categories.
	 *
	 * @since 1.0.449
	 *
	 * @param string $which
	 */
	protected function extra_tablenav( $which ) {
		if ( $which == "top" ) {
			$post_title_list = $this->output_titles_list();

			$this->empty_trash_button();

			if ( $post_title_list ) {
				echo '<div class="alignleft actions bulkactions">' .
				$post_title_list .
				$this->output_categories_list() .
				$this->output_dates_filter();
				?>
					<input type="submit" id="filter-entries" class="button action" value="Filter">
					</div>
				<?php
			}
		}
	}

	/**
	 * Outputs HTML for event title dropdown filter.
	 * 
	 * @since 1.1.0
	 * @return string
	 */
	public function output_titles_list() {
		$post_title = null;
		$post_id	= null;
		$results 	= null;
		$args = array(
			'post_type' => 'wp_events'
		);

		$query = new WP_Query( $args );
		if ( $query->have_posts() ) :
			while ( $query->have_posts() ) : $query->the_post();
				$post_id[]	  = get_the_ID();
				$post_title[] = get_the_title();
			endwhile;
		endif;
		wp_reset_postdata();
		if (isset( $post_title ) && isset( $post_id ) ) {
			$results = wpe_array_combine( $post_title, $post_id );
		}

			// Return null if we found no results.
		if ( ! $results )
		return false;

		// HTML for our select printing post titles as loop.
		$output = '<select name="wpe_titles" id="wpe_titles" class="mdb-select md-form" searchable="Search here..">';

		$output .= '<option value="-1" selected>All Events</option>';

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

	/**
	 * Outputs HTML for categories dropdown filter.
	 * 
	 * @global object $wpdb instantiation of the wpdb class.
	 * 
	 * @since 1.0.449
	 * @return string
	 */
	public function output_categories_list() {
		$args = array(
			'taxonomy' => 'wpevents-category',
			'orderby' => 'name',
			'order'   => 'ASC'
		);

		$cats = get_categories( $args );

		if ( !$cats ) {
			return false;
		}

		// HTML for our select printing post categories as loop.
		$output = '<select name="wpe_categories" id="wpe_categories" class="mdb-select md-form" searchable="Search here..">';

		$output .= '<option value="-1">All Categories</option>';


		foreach ( $cats as $cat ) {
			$cat = (array) $cat;
			$selected = ( isset( $_GET['wpe_categories'] ) && $_GET['wpe_categories'] == $cat['slug']) ? "selected" : "";
			$output .= '<option value="' . $cat['slug'] . '" ' . $selected . '>' . $cat['name'] . '</option>';
		}

		$output .= '</select>'; // end of select element.

		// get the html.
		return $output;
	}

	/**
	 * Returns array of post IDs matching category filter.
	 *
	 * @since 1.0.449
	 * @return array
	 */
	public function get_categories_posts() {
		$postID = null;
		if ( isset($_GET['wpe_categories'] ) && $_GET['wpe_categories'] != -1 ) {
			$args = array(
				'post_type' => 'wp_events',
				'tax_query' => array(
					array(
						'taxonomy' => 'wpevents-category',
						'field'    => 'slug',
						'terms'    => $_GET['wpe_categories'],
					),
				),
			);
			$query = new WP_Query( $args );
			if ( $query->have_posts() ) :
				while ( $query->have_posts() ) : $query->the_post();
					$postID[] = (string) get_the_ID();
				endwhile;
			endif;
			wp_reset_postdata();
			return $postID;
		}
	}

	/**
	 * Outputs HTML for start date and end date filter.
	 * 
	 * @since 1.1.0
	 * @return string
	 */
	public function output_dates_filter() {
		?>
			<input id="wpe-filter-start-date" autocomplete="off" class="wp-event-datepicker" type="text" name="wpe-filter-start-date" placeholder="Filter by start date" value="<?php echo isset( $_GET['wpe-filter-start-date'] ) ? $_GET['wpe-filter-start-date'] : '' ;?>"/>
			<input id="wpe-filter-end-date" autocomplete="off" class="wp-event-datepicker" type="text" name="wpe-filter-end-date" placeholder="Filter by end date" value="<?php echo isset( $_GET['wpe-filter-end-date'] ) ? $_GET['wpe-filter-end-date'] : '' ;?>"/>
		<?php
	}

	/**
	 * Returns array of post IDs matching dates filters.
	 *
	 * @since 1.1.12
	 * @return array
	 */
	public function get_date_filter_posts() {
		$post_ID = array();
		$args    = array(
			'post_type'      => 'wp_events',
			'posts_per_page' => '-1',
			'post_status'    => array( 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash' ),
		);

		if ( isset( $_GET['wpe-filter-start-date'] ) && $_GET['wpe-filter-start-date'] !== '' && isset( $_GET['wpe-filter-end-date'] ) && $_GET['wpe-filter-end-date'] !== '' ) {
			$args['meta_query'] = [
				'relation' => 'AND',
				[
					'key'     => 'wpevent-end-date-time',
					'compare' => '<=',
					'value'   => strtotime( $_GET['wpe-filter-end-date'] . '23:59:59' ),
					'type'    => 'numeric',
				],
				[
					'key'     => 'wpevent-start-date-time',
					'compare' => '>=',
					'value'   => strtotime( $_GET['wpe-filter-start-date'] ),
					'type'    => 'numeric',
				],
			];
		} else if ( isset( $_GET['wpe-filter-start-date'] ) && $_GET['wpe-filter-start-date'] !== '' ) {
			$args['meta_query'] = [
				[
					'key'     => 'wpevent-start-date-time',
					'compare' => '>=',
					'value'   => strtotime( $_GET['wpe-filter-start-date'] ),
					'type'    => 'numeric',
				],
			];
		} else if ( isset( $_GET['wpe-filter-end-date'] ) && $_GET['wpe-filter-end-date'] !== '' ) {
			$args['meta_query'] = [
				[
					'key'     => 'wpevent-end-date-time',
					'compare' => '<=',
					'value'   => strtotime( $_GET['wpe-filter-end-date'] . '23:59:59' ),
					'type'    => 'numeric',
				],
			];
		}
		$query = new WP_Query( $args );
			if ( $query->have_posts() ) :
				while ( $query->have_posts() ) : $query->the_post();
					$post_ID[] = (string) get_the_ID();
				endwhile;
			endif;
			wp_reset_postdata();
			return $post_ID;
	}

	/**
	 * Returns array of post IDs from all filters combined.
	 *
	 * @since 1.1.0
	 * @return array
	 */
	public function get_filter_string() {

		$category_filter = $this->get_categories_posts();
		$dates_filter 	 = $this->get_date_filter_posts();
		$filter			 = array();

		if ( isset( $category_filter ) && isset( $dates_filter ) ) {
			$filter = array_intersect( $category_filter, $dates_filter );
		} elseif ( isset( $category_filter ) ) {
			$filter = $category_filter;
		} elseif ( isset( $dates_filter ) ) {
			$filter = $dates_filter;
		}

		if ( isset( $_GET['wpe_titles'] ) && $_GET['wpe_titles'] != -1 ) {
			$title_filter 	  = $_GET['wpe_titles'];
			$title_filter_arr = explode( ",", $title_filter );
			if ( isset( $filter ) ) {
				$filter = array_intersect( $title_filter_arr, $filter );
			} else {
				$filter = $title_filter_arr;
			}
		}
		
		return $filter;
	}

	/**
	 * Generates custom admin notices on deletion/restore actions
	 * 
	 * @global string $pagenow 
	 *
	 * @param $message
	 * @since 1.1.0
	 */
	public function wpe_admin_notice( $message ) {
		global $pagenow;
		if ( $pagenow == 'edit.php' && isset( $_REQUEST['page'] ) && 'wp_forms_entries' === $_REQUEST['page'] ) {
			 echo '<div class="notice notice-success is-dismissible">
				 <p>'. $message .'</p>
			 </div>';
		}
	}

	/**
	 * Displays empty trash button if user is on trash tab and there are
	 * entries in the trash.
	 * 
	 * @since 1.1.0
	 */
	public function empty_trash_button() {

		if ( isset( $_GET['display'] ) && $_GET['display'] == 'trash' ) {
			echo '<input type="submit" name="emptytrash" class="button action" value="Empty Trash" 
				  onclick="return confirm(\'Are you sure you want to empty the Trash?\');" />';
		}
	}

	/**
	 * Permanently delete all entries from trash tab.
	 * 
	 * @global object $wpdb instantiation of the wpdb class.
	 * 
	 * @since 1.1.0
	 *
	 * @return bool|int
	 */
	public function wpe_empty_trash() {
		global $wpdb;

		return $wpdb->update(
			"{$wpdb->prefix}$this->table_name",
			[ 'wpe_status' => WPE_DELETED ],
			[ 'wpe_status' => WPE_TRASHED ],
			'%d',
			'%d'
		);
	}

	/**
	 * process bulk actions of delete, restore, approve, cancel
	 *
	 * @param int $entry_status
	 * @param string $message
	 *
	 * @since 1.2.0
	 */
	public function wpe_process_bulk_action( $entry_status, $message ) {

		// In our file that handles the request, verify the nonce.
		$nonce = esc_attr( $_REQUEST['_wpnonce'] );

		if ( ! wp_verify_nonce( $nonce, 'wp_events_entries' ) ) {
			die('Go get a life script kiddies');
		} else {
			$delete_arr = $_GET['bulk-delete'];
			if ( is_array( $delete_arr ) ) {
				foreach ( $delete_arr as $id ) {
					$this->update_registration_status( (int) $id, $entry_status );
				}
				$no_of_posts = sizeof( $delete_arr );
				$message = $no_of_posts . __( $message, 'wp-events' );
				$this->wpe_admin_notice( $message );
			}
		}
	}

	/**
     * Removes entries from display for deleted events.
     * 
     * @global object $wpdb instantiation of the wpdb class.
     *
     * @since 1.2.0
	 * @return bool
     */
	public function wpe_remove_deleted_event_entries(): bool {
        global $wpdb;
		// $table_name	   = 'events_registration';
		$sql 	 	   = "SELECT post_id FROM {$wpdb->prefix}$this->table_name";
		$results 	   = $wpdb->get_results( $sql, ARRAY_A );
		$deleted_event = [];

		$args = array(
			'numberposts' => -1,
			'post_type'   => 'wp_events',
			'post_status' => array( 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash' ),
			'fields'	  => 'ids',
		);

		$events = query_posts( $args );
		
		for( $i = 0; $i < sizeof( $results ); $i++ ) {
			foreach( $results[$i] as $postid => $id ) {
				if( !in_array( $id, $events ) ) {
					$deleted_event[] = (int) $id;
				}
			}
		}

		$deleted_event = array_unique( $deleted_event );
		$deleted_event = array_values( $deleted_event );

		if( !empty( $deleted_event ) ){
			for( $j = 0; $j < sizeof( $deleted_event ); $j++ ){
				$result = $wpdb->update(
					"{$wpdb->prefix}$this->table_name",
					['wpe_status' => -1],
					['post_id' => $deleted_event[$j] ],
					'%d',
					'%d'
				);
			}
			return $result;
		}
		return true;
	}

	/**
	 * Send notification to registrant if entry is approved/cancelled
	 *
	 * @param int $entry_status
	 * 
	 * @since 1.2.0
	 */
	public function wpe_approve_cancel_notification( $entry_status ) {
		$status		    = $entry_status == WPE_CANCELLED ? 'cancelled.' : 'approved.';
		$append_message = $entry_status == WPE_APPROVED ? 'We look forward to seeing you' : '';
		// In our file that handles the request, verify the nonce.
		$nonce = esc_attr( $_REQUEST['_wpnonce'] );

		if ( ! wp_verify_nonce( $nonce, 'wp_events_entries' ) ) {
			die('Go get a life script kiddies');
		} else {
			$delete_arr   = $_GET['bulk-delete'];
			$mail_options = get_option('wpe_mail_settings');
			$firm_info 	  = get_option('wpe_firm_settings');
			$from_name    = $firm_info['mail_from_name'];
			$from_email   = $mail_options['mail_from'];
			$headers[]    = 'Content-Type: text/html;';
			$headers[]    = "from: $from_name <$from_email>";
			if ( is_array( $delete_arr ) ) {
				foreach ( $delete_arr as $id ) {
					$data	  = Wp_Events_Db_Actions::wpe_get_registration_data( $id, '', 'ARRAY_A' );
					Wpe_Shortcodes::set_form_data( $data[0] );
					$to       = $data[0]['email'];
					$subject  = 'Your registration for '. get_the_title( $data[0]['post_id'] ) .' is '. $status;
					$message  = 'Dear '. $data[0]['first_name'] .' '. $data[0]['last_name'] .',<br />
					Thank you for registering for our upcoming Event. This is an auto-generated email to inform you that your registration for our upcoming Event is '. $status .'<br />
					<br />
					The details of your registration are following.<br />
					[wpe_event_details] <br />
					[wpe_registration_details]<br />
					If you have any questions, please feel free to contact us at our office number or via email.<br />
					'. $append_message .'.<br />
					Sincerely,';
					$message  = do_shortcode( $message, TRUE );
					wp_mail( $to, $subject, $message, $headers );
				}
			}
		}
	}
}
