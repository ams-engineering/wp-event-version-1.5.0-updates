<?php

/**
 * Subscriptions Entries List
 *
 * @package    Wp_Events/admin
 * @subpackage Wp_Events/admin/includes
 * @since      1.0.449
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
 * @since 1.0.0
 * 
 * @see WP_List_Table
 */
class Wp_Events_Subscribers_list extends WP_List_Table {

	/**
	 * Name of the table in database.
	 * 
	 * @since 1.0.449
	 * @var string $table_name
	 */
	protected $table_name = 'events_subscribers';

	/**
	 * Class Constructor.
	 * 
	 * Calls constructor from the parent class.
	 *
	 * @since 1.0.449
	 */
	public function __construct()
	{
		parent::__construct([
			'singular' => __( 'Subscribers', 'wp-events' ),
			//singular name of the listed records
			'plural'   => __( 'Subscribers', 'wp-events' ),
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
		_e( 'No Subscribers available.', 'wp-events' );
	}

	/**
	 * Define what data to show on each column of the table.
	 *
	 * @param  Array   $item         Data
	 * @param  String  $column_name  - Current column name
	 *
	 * @since 1.0.449
	 * @return Mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'subscriber_firstname':
			case 'subscriber_lastname':
			case 'subscriber_email':
			case 'subscriber_phone':
			case 'subscriber_texting_permission':
				return $item[ $column_name ];
			case 'time':
				return $item['time_generated'];
			case 'id':
				return $item['id'];
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
			$item['id']
		);
	}

	/**
	 * Returns an associative array containing the bulk action.
	 *
	 * @return array
	 * @since 1.1.0
	 */
	public function get_bulk_actions() {
		if ( isset( $_GET["display"] ) && $_GET["display"] === 'trash' ) {
			return [
				'permanent-delete' => __( 'Delete Permanently', 'wp-events' ),
				'restore'          => __( 'Restore', 'wp-events' ),
			];
		}

		return [
			'bulk-delete' => __( 'Move to Trash', 'wp-events' ),
		];
	}

	/**
	 * Outputs HTML to display bulk actions and filters.
	 * 
	 * @since 1.0.449
	 * @param $which
	 */
	public function display_tablenav( $which ) {
?>
		<input type='hidden' name='_wpnonce' value='<?php echo wp_create_nonce( 'wp_events_entries' ); ?>' />
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
	 * @since 1.0.449
	 * @return string
	 */
	function column_ID( $item ) {

		$display = isset( $_GET["display"] ) ? $_GET["display"] : 'all';

		$actions = [ 'view_entry' => sprintf(
			'<a href="edit.php?post_type=wp_events&page=wpe_view_entry&entry=%s&tab=subscriptions&display='. $display .'">' . __( 'View', 'wp-events' ) . '</a>',
			$item['id'] ),
		];

		if ( $display === 'trash' ) {  //only for trash entries
			$actions['delete_permanent'] = sprintf(
					'<a href="edit.php?post_type=wp_events&page=%s&tab=subscriptions&display=trash&action=%s&bulk-delete[0]=%s&action2=permanent-delete&_wpnonce=%s" onclick="return confirm(\'Are you sure you want to delete item(s)?\');">' . __( 'Delete Permanently', 'wp-events' ) . '</a>',
					$_REQUEST['page'],
					'permanent-delete',
					$item['id'],
					wp_create_nonce( 'wp_events_entries' ) );
			$actions['restore'] = sprintf(
					'<a href="edit.php?post_type=wp_events&page=%s&tab=subscriptions&display=trash&action=%s&bulk-delete[0]=%s&action2=restore&_wpnonce=%s">' . __( 'Restore', 'wp-events' ) . '</a>',
					$_REQUEST['page'],
					'restore',
					$item['id'],
					wp_create_nonce( 'wp_events_entries' ) );
		} else {
			$actions['delete'] = sprintf(
					'<a href="edit.php?post_type=wp_events&page=%s&tab=subscriptions&action=%s&bulk-delete[0]=%s&action2=bulk-delete&_wpnonce=%s">' . __( 'Move To Trash', 'wp-events' ) . '</a>',
					$_REQUEST['page'],
					'bulk-delete',
					$item['id'],
					wp_create_nonce( 'wp_events_entries' ) );
		}

		return sprintf(
			'%1$s %2$s',
			$item['id'],
			$this->row_actions( $actions )
        );
	}

	/**
	 * Handles data query and filter, sorting, and pagination.
	 * 
	 * @since 1.0.449
	 */
	public function prepare_items() {

		if ( ! Wp_Events_Db_Actions::wpe_table_exists( $this->table_name ) ) {
			Wp_Events_Db_Actions::add_subscriber_table();
		}

		$search_key = isset( $_REQUEST['s'] ) ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';

		$this->_column_headers = [
			$this->get_columns(),
			[], // hidden columns
			$this->get_sortable_columns(),
			$this->get_primary_column_name(),
		];

		/** Process bulk action */
		$this->process_bulk_action();

		//Delete all trash entries when empty trash button is clicked.
		if ( isset( $_GET['emptytrash'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'wp_events_entries' ) ) {
			$this->wpe_empty_trash();
		}


		$per_page     = $this->get_per_page();
		$current_page = $this->get_pagenum();
		$total_items  = $this->record_count();

		if ( $search_key ) {
			$table_data  = $this->get_event_subscribers( -1, $current_page );
			$table_data  = $this->filter_table_data( $table_data, $search_key );
			$total_items = count( $table_data );
			$per_page    = $total_items;
		} else {
			$table_data = $this->get_event_subscribers( $per_page, $current_page );
		}

		$this->set_pagination_args([
			'total_items' => $total_items,
			//WE have to calculate the total number of items
			'per_page'    => $per_page,
			//WE have to determine how many items to show on a page
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
			'cb'         					=> '<input type="checkbox">',
			'id'         					=> __('Id', 'wp-events'),
			'subscriber_firstname' 			=> __('First Name', 'wp-events'),
			'subscriber_lastname'  			=> __('Last Name', 'wp-events'),
			'subscriber_email'      		=> __('Email', 'wp-events'),
			'subscriber_phone'      		=> __('Cell Phone', 'wp-events'),
			'subscriber_texting_permission'	=> __('Texting Permission', 'wp-events'),
			'time'       					=> __('Time', 'wp-events'),
		];
	}

	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return [
			'id'       => ['id', TRUE],
			'time'     => ['time_generated', TRUE],
		];
	}

	/**
	 * Process bulk actions of deletion and restore entries.
	 * 
	 * @since 1.0.449
	 */
	public function process_bulk_action() {

		//Detect when a bulk action is being triggered...
		if ( 'bulk-delete' === $this->current_action() ) {

			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'wp_events_entries' ) ) {
				die( 'Go get a life script kiddies' );
			} else {
				$delete_arr = $_GET['bulk-delete'];
				if ( is_array( $delete_arr ) ) {
					foreach ( $delete_arr as $id ) {
						$this->delete_restore_entry( (int) $id, WPE_TRASHED );
					}
					$no_of_posts = sizeof( $delete_arr );
					$message = $no_of_posts . __( ' item(s) moved to the Trash.', 'wp-events' );
					$this->wpe_admin_notice( $message );
				}
			}
		}

		if ( 'permanent-delete' === $this->current_action() ) {

			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'wp_events_entries') ) {
				die( 'Go get a life script kiddies' );
			} else {
				$delete_arr = $_GET['bulk-delete'];
				if ( is_array( $delete_arr ) ) {
					foreach ( $delete_arr as $id ) {
						$this->delete_restore_entry( (int) $id, WPE_DELETED );
					}
					$no_of_posts = sizeof( $delete_arr );
					$message = $no_of_posts . __( ' item(s) permanently deleted.', 'wp-events' );
					$this->wpe_admin_notice( $message );
				}
			}
		}

		if ( 'restore' === $this->current_action() ) {

			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'wp_events_entries' ) ) {
				die( 'Go get a life script kiddies' );
			} else {
				$delete_arr = $_GET['bulk-delete'];
				if ( is_array( $delete_arr ) ) {
					foreach ( $delete_arr as $id ) {
						$this->delete_restore_entry( (int) $id, WPE_ACTIVE );
					}
					$no_of_posts = sizeof( $delete_arr );
					$message = $no_of_posts . __( ' item(s) restored from the Trash.', 'wp-events' );
					$this->wpe_admin_notice( $message );
				}
			}
		}
	}

	/**
	 * Trash/Delete a customer record.
	 * 
	 * @global object $wpdb instantiation of the wpdb class.
	 *
	 * @param  int  $id  customer ID.
	 * @param int $val value to update in status column.
	 * 
	 * @since 1.0.449
	 *
	 * @return bool|int
	 */
	public function delete_restore_entry( $id, $val ) {
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
	 * @return null|string
	 * @global object $wpdb instantiation of the wpdb class.
	 *
	 * @since 1.1.0
	 */
	public function record_count() {
		global $wpdb;

		if ( isset ( $_GET["display"] ) && $_GET["display"] === 'trash' ) {
			$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}$this->table_name WHERE wpe_status = ". WPE_TRASHED;
		} else {
			$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}$this->table_name WHERE wpe_status = ". WPE_ACTIVE;
		}

		return $wpdb->get_var( $sql );
	}

	/**
	 * Retrieve subscriber's data from the database.
	 *
	 * @param  int    $per_page  from screen options.
	 * @param  int    $page_number
	 *
	 * @return mixed
	 * @global object $wpdb      instantiation of the wpdb class.
	 *
	 * @since 1.1.0
	 */
	public function get_event_subscribers( $per_page = 5, $page_number = 1 ) {

		global $wpdb;

		if ( isset ( $_GET["display"] ) && $_GET["display"] === 'trash' ) {
			$sql = "SELECT * FROM {$wpdb->prefix}$this->table_name WHERE wpe_status = ". WPE_TRASHED;
		} else {
			$sql = "SELECT * FROM {$wpdb->prefix}$this->table_name WHERE wpe_status = ". WPE_ACTIVE;
		}

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
			$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
		} else {
			$sql .= ' ORDER BY time_generated DESC';
		}

		if ( -1 !== $per_page ) {
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

		$views = array(
			'all' 	=> '<a '. wpe_is_current( $wpe_current_display, 'all' ) .' href="edit.php?post_type=wp_events&page=wp_forms_entries&tab=subscriptions&display=all">All</a>',
			'trash' => '<a '. wpe_is_current( $wpe_current_display, 'trash' ) .' href="edit.php?post_type=wp_events&page=wp_forms_entries&tab=subscriptions&display=trash">Trash</a>'
		);

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

		$this->screen->render_screen_reader_content( 'heading_views' );

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
	 * Displays empty trash button
	 *
	 * @since 1.1.0
	 *
	 * @param string $which
	 */
	protected function extra_tablenav( $which ) {
		if ( $which == "top" ) {
			$this->empty_trash_button();
		}
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
}
