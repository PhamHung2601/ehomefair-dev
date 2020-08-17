<?php

if ( ! defined( 'ABSPATH' ) )
		exit;

if ( !class_exists( 'MP_Admin_Notifications' ) )
{
	/**
	 * Admin side notification.
	 */
	class MP_Admin_Notifications {
		/**
		 * Constructor.
		 */
		public function __construct() {

			add_action( 'mp_notification_orders', array( $this, 'mp_notification_orders' ) );

			add_action( 'mp_notification_products', array( $this, 'mp_notification_products' ) );

			add_action( 'mp_notification_seller', array( $this, 'mp_notification_sellers' ) );

			add_action( 'mp_notification_orders_all', array( $this, 'mp_notification_orders_all' ) );

			add_action( 'mp_notification_orders_processing', array( $this, 'mp_notification_orders_processing' ) );

			add_action( 'mp_notification_orders_completed', array( $this, 'mp_notification_orders_completed' ) );

			echo '<div class="wrap">';

			echo '<nav class="nav-tab-wrapper">';

			echo '<h1 class="">' . esc_html__( 'Notifications', 'marketplace' ) . '</h1>';

			echo '<p><hr></p>';

			$mp_tabs = array(
				'orders'   => __( 'Orders', 'marketplace' ),
				'products' => __( 'Products', 'marketplace' ),
			);

			$mp_tabs = apply_filters( 'marketplace_get_notification_tabs', $mp_tabs );

			$current_tab = empty( $_GET['tab'] ) ? 'orders' : sanitize_title( $_GET['tab'] );

			$this->id = $current_tab;

			foreach ( $mp_tabs as $name => $label ) {

					echo '<a href="' . esc_url( admin_url( 'admin.php?page=' . $_GET['page'] . '&tab=' . $name ) ) . '" class="nav-tab ' . ( $current_tab == $name ? 'nav-tab-active' : '' ) . '">' . $label . '</a>';

			}

					?>
				</nav>

				<h1 class="screen-reader-text">
					<?php echo esc_html( $mp_tabs[ $current_tab ] ); ?>
				</h1>

				<?php

				do_action( 'mp_notification_' . $current_tab );

				echo '</div>';
		}

		function mp_notification_orders() {
			$this->mp_output_sections();
			$section = ( isset( $_GET['section'] ) ) ? $_GET['section'] : 'all';
			do_action( 'mp_notification_orders_' . $section );
		}

		/**
		 * Product related notifications
		 */
		public function mp_notification_products() {
			global $wpdb;

			$sql = $this->mp_notification_query( 'product', 'all' );

			$table_name = $wpdb->prefix . 'mp_notifications';

			echo '<ul class="mp-notification-list">';
			if ( $sql['data'] ) {

				foreach ( $sql['data'] as $key => $value ) {
					if ( $value['read_flag'] == 0 ) {
						$wpdb->update(
							$table_name, array(
								'read_flag' => '1',
								'timestamp' => $value['timestamp'],
							), array(
								'id' => $value['id'],
							)
						);
					}
					$datetime1 = new DateTime( date( 'F j, Y', strtotime( $value['timestamp'] ) ) );
					$datetime2 = new DateTime( 'now' );
					$interval  = $datetime1->diff( $datetime2 );
					echo '<li class="notification-link" data-id="' . $value['id'] . '">' . $value['content'] . '<strong>' . $interval->days . ' day(s) ago</strong>.</li>';
				}
			} else {
				echo 'No data Found.';
			}
			echo '</ul>';
			echo $sql['count'];

		}

				// sellers related notifications
				function mp_notification_sellers()
				{
						global $wpdb;

						$sql = $this->mp_notification_query('seller', 'all');

						$table_name = $wpdb->prefix.'mp_notifications';

						echo '<ul class="mp-notification-list">';
							if( ! empty( $sql['data'] ) ){
								foreach ($sql['data'] as $key => $value)
								{
										if ( $value['read_flag'] == 0 ) {
											$wpdb->update(
													$table_name,
													array(
															'read_flag'=> '1',
															'timestamp'	=> $value['timestamp'],
													),
													array(
															'type'      => 'seller',
															'read_flag'=> '0'
													)
											);
										}
										$datetime1 = new DateTime(date('F j, Y', strtotime($value['timestamp']) ) );
										$datetime2 = new DateTime('now');
										$interval = $datetime1->diff($datetime2);
										echo '<li class="notification-link" data-id="'.$value['id'].'">'.$value['content'].' <strong>'.$interval->days.' day(s) ago</strong>.</li>';
								}
							}
							else{
								echo 'No data Found.';
							}
						echo '</ul>';

						echo $sql['count'];
				}

				// output sections
				public function mp_output_sections()
				{
						global $current_section;

						$sections = $this->get_sections();

						echo '<ul class="subsubsub">';

						$array_keys = array_keys( $sections );

						$current_section = (isset($_GET['section'])) ? $_GET['section'] : 'all';

						foreach ( $sections as $id => $label ) {
							echo '<li><a href="' . admin_url( 'admin.php?page=' . $_GET['page'] . '&tab=' . $this->id . '&section=' . sanitize_title( $id ) ) . '" class="' . ( $current_section == $id ? 'current' : '' ) . '">' . $label . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
						}

						echo '</ul><br class="clear" />';
				}

				public function get_sections()
				{
					$sections = array(
							'all' => __( 'All', 'marketplace' ),
							'processing' => __( 'Processing', 'marketplace' ),
							'completed' => __( 'Completed', 'marketplace' ),
					);

					return apply_filters( 'marketplace_get_sections_' . $this->id, $sections );
				}

				function mp_notification_query( $type, $keyword)
				{

						global $wpdb;

						$table_name = $wpdb->prefix.'mp_notifications';

						$user_id = get_current_user_id();

						if ($keyword == 'processing') {
								$query = "SELECT * FROM $table_name where type='$type' and author_id='$user_id' and content like '%$keyword%'";
						}
						else if ($keyword == 'complete') {
							$query = "SELECT * FROM $table_name where type='$type' and author_id='$user_id' and content like '%$keyword%'";
						}
						else {
								$query = "SELECT * FROM $table_name where type='$type' and author_id='$user_id'";
						}

						return $this->mp_return_notification($query);

				}

				function mp_return_notification($type_query) {

						global $wpdb;

						$table_name = $wpdb->prefix.'mp_notifications';

						$pagination = '';
						$query           = $type_query;
						$total_query     = "SELECT COUNT(1) FROM (${query}) AS total";
						$total           = $wpdb->get_var( $total_query );
						$items_per_page  = get_option('posts_per_page');
						$page            = isset( $_GET['n-page'] ) ? abs( (int) $_GET['n-page'] ) : 1;
						$offset          = ( $page * $items_per_page ) - $items_per_page;
						$sql          = $wpdb->get_results( $query . " ORDER BY id DESC LIMIT ${offset}, ${items_per_page}" , ARRAY_A);
						$totalPage       = ceil($total / $items_per_page);

						if( $totalPage > 1 )
						{
								$pagination = '<div class="mp-notification-pagination">'.paginate_links(
										array(
												'base'      => add_query_arg( 'n-page', '%#%' ),
												'format'    => '',
												'prev_text' => __('&laquo;'),
												'next_text' => __('&raquo;'),
												'total'     => $totalPage,
												'current'   => $page
										)
								).'</div>';
						}

						return array(
							'data' => $sql,
							'count'=> $pagination
						);
				}

				function mp_notification_orders_all()
				{
						global $wpdb;

						$table_name = $wpdb->prefix.'mp_notifications';

						$sql = $this->mp_notification_query('order', 'all');

						echo '<ul class="mp-notification-list">';
						if( ! empty( $sql['data'] ) ){
								foreach ($sql['data'] as $key => $value)
								{
										if ( $value['read_flag'] == 0 ) {
												$wpdb->update(
														$table_name,
														array(
																'read_flag'=> '1',
																'timestamp'	=> $value['timestamp'],
														),
														array(
																'id'	=> $value['id']
														)
												);
										}
										$datetime1 = new DateTime(date('F j, Y', strtotime($value['timestamp']) ) );
										$datetime2 = new DateTime('now');
										$interval = $datetime1->diff($datetime2);
										echo '</strong><li class="notification-link" data-id="'.$value['id'].'">'.strip_tags($value['content']).' <strong>'.$interval->days.' day(s) ago</strong>.</li>';
								}
							}
							else{
								echo 'No data Found.';
							}
						echo '</ul>';

						echo $sql['count'];
				}

				function mp_notification_orders_processing() {
						global $wpdb;

						$sql = $this->mp_notification_query('order', 'processing');

						echo '<ul class="mp-notification-list">';
						if( ! empty( $sql['data'] )  ){
								foreach ($sql['data'] as $key => $value)
								{
										if (strpos($value['content'], 'Processing') !== false) {
												$datetime1 = new DateTime(date('F j, Y', strtotime($value['timestamp']) ) );
												$datetime2 = new DateTime('now');
												$interval = $datetime1->diff($datetime2);
												echo '</strong><li class="notification-link" data-id="'.$value['id'].'">'.strip_tags($value['content']).' <strong>'.$interval->days.' day(s) ago</strong>.</li>';
										}
								}
							}
							else{
								echo 'No data Found.';
							}
						echo '</ul>';

						echo $sql['count'];
				}

				function mp_notification_orders_completed() {
						global $wpdb;

						$sql = $this->mp_notification_query('order', 'complete');

						echo '<ul class="mp-notification-list">';
						if( ! empty( $sql['data'] ) ){
								foreach ($sql['data'] as $key => $value)
								{
										if (strpos($value['content'], 'Complete') !== false) {
												$datetime1 = new DateTime(date('F j, Y', strtotime($value['timestamp']) ) );
												$datetime2 = new DateTime('now');
												$interval = $datetime1->diff($datetime2);
												echo '</strong><li class="notification-link" data-id="'.$value['id'].'">'.strip_tags($value['content']).' <strong>'.$interval->days.' day(s) ago</strong>.</li>';
										}
								}
							}
							else{
								echo 'No data Found.';
							}
						echo '</ul>';

						echo $sql['count'];
				}

		}

		new MP_Admin_Notifications();

}
