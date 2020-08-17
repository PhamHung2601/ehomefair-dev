<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Marketplace Dashboard Template
 */

if ( ! class_exists( 'MP_Report_Dashboard' ) ) {

	class MP_Report_Dashboard extends WC_Admin_Report {

		public function __construct() {

		}

		function mp_dashboard_page() {
			?>
			<div class="woocommerce-account">

				<?php	do_action( 'mp_get_wc_account_menu', 'marketplace' ); ?>

				<div class="woocommerce-MyAccount-content">

					<div class="mp-dashboard-wrapper">
						<?php

						$this->mp_dashboard_summary_section();

						$this->mp_dashboard_sale_order_history();

						$this->mp_dashboard_order_product_section();

						$this->mp_top_billing_country();

						$this->mp_dashboard_recent_order_section();

						?>
					</div>

				</div>

			</div>

			<?php
		}

		public function getOrderId($sel_id) {

			global $wpdb;

			$sql = "SELECT DISTINCT woi.order_item_id

					FROM {$wpdb->prefix}woocommerce_order_itemmeta woi

					JOIN {$wpdb->prefix}woocommerce_order_items woitems ON woitems.order_item_id = woi.order_item_id

					JOIN {$wpdb->prefix}posts post ON woi.meta_value = post.ID

					WHERE ( woi.meta_key ='_product_id' OR woi.meta_key ='_variation_id' )

					AND post.ID = woi.meta_value

					AND post.post_author ='" . $sel_id . "'

					";

			$result = $wpdb->get_results( $sql );

			$ID = array();

			foreach ( $result as $res ) {

				$ID[] = $res->order_item_id;

			}

			return implode( ',', $ID );

		}

		/**
		 * Store summary section function.
		 */
		public function mp_dashboard_summary_section() {
			$data          = $this->mp_get_sale_stats();
			$total_payout  = isset( $data->paid_amount ) ? $data->paid_amount : 0;
			$total_sales   = isset( $data->seller_total_ammount ) ? $data->seller_total_ammount : 0;
			$total_refund   = isset( $data->total_refunded_amount ) ? $data->total_refunded_amount : 0;
			$remaining_amt = round( $total_sales - $total_payout );
			?>
			<div class="mp-store-summary">
				<div class="mp-store-summary-section life-time-sale">
						<div class="summary-stats">
							<h2>
								<sup><?php echo get_woocommerce_currency_symbol(); ?></sup>
								<?php
								if ( $total_sales >= 1000 ) {
									echo number_format( $total_sales / 1000, 2 ) . 'K';
								} else {
									echo number_format( $total_sales, 2 );
								}
								?>
							</h2>
							<p><?php echo esc_html__( 'Life Time Sale', 'marketplace' ); ?></p>
						</div>
						<div class="summary-icon"><span><?php echo get_woocommerce_currency_symbol(); ?></span></div>
				</div>
				<div class="mp-store-summary-section total-payout">
					<div class="summary-stats">
							<h2><sup><?php echo get_woocommerce_currency_symbol(); ?></sup>
								<?php
								if ( $total_payout >= 1000 ) {
									echo number_format( $total_payout / 1000, 2 ) . 'K';
								} else {
									echo number_format( $total_payout, 2 );
								}
								?>
							</h2>
							<p><?php echo esc_html__( 'Total Payout', 'marketplace' ); ?></p>
						</div>
						<div class="summary-icon payout"></div>
				</div>
				<div class="mp-store-summary-section remaining-amount">
					<div class="summary-stats">
							<h2><sup><?php echo get_woocommerce_currency_symbol(); ?></sup>
								<?php
								if ( $remaining_amt >= 1000 ) {
									echo number_format( $remaining_amt / 1000, 2 ) . 'K';
								} else {
									echo number_format( $remaining_amt, 2 );
								}
								?>
							</h2>
							<p><?php echo esc_html__( 'Remaining Amount', 'marketplace' ); ?></p>
						</div>
						<div class="summary-icon remaining"></div>
				</div>
				<div class="mp-store-summary-section life-time-sale">
					<div class="summary-stats">
						<h2>
							<sup><?php echo get_woocommerce_currency_symbol(); ?></sup>
							<?php
							if ( $total_refund >= 1000 ) {
								echo number_format( $total_refund / 1000, 2 ) . 'K';
							} else {
								echo number_format( $total_refund, 2 );
							}
							?>
						</h2>
						<p><?php echo esc_html__( 'Refunded Amount', 'marketplace' ); ?></p>
					</div>
					<div class="summary-icon"><span><?php echo get_woocommerce_currency_symbol(); ?></span></div>
				</div>
			</div>
			<?php
		}

		private function round_chart_totals( $amount ) {
			if ( is_array( $amount ) ) {
				return array( $amount[0], wc_format_decimal( $amount[1], wc_get_price_decimals() ) );
			} else {
				return wc_format_decimal( $amount, wc_get_price_decimals() );
			}
		}

		/**
		 * Sale Order History
		 */
		private function mp_dashboard_sale_order_history() {
			global $wpdb, $wp_locale;

			$order_items = array();

			$postid = $this->get_order_item_id();

			$time = isset( $_GET['sort'] ) ? $_GET['sort'] : 'year';

			$sort_array = array(
				'year',
				'month',
				'7day',
				'last_month',
			);

			if ( ! in_array( $time, $sort_array, true ) ) {
				$time = 'year';
			}

			$this->calculate_current_range( $time );

			if ( $postid['order_item_id'] ) {

				$order_ID = $postid['order_id'];

				$ID = $postid['order_item_id'];

				$query = "SELECT posts.post_date as post_date, sum(meta.meta_value) as total_sales, count(*) as count FROM {$wpdb->prefix}posts AS posts join {$wpdb->prefix}woocommerce_order_items as items on items.order_id = posts.ID join {$wpdb->prefix}woocommerce_order_itemmeta as meta on meta.order_item_id = items.order_item_id WHERE posts.ID IN ($order_ID) and meta.meta_key='_line_total' and meta.order_item_id in($ID) and posts.post_type 	IN ( 'shop_order' )	AND 	posts.post_status 	IN ( 'wc-completed','wc-processing','wc-on-hold','wc-refunded')	AND posts.post_date >= '" . date( 'Y-m-d H:i:s', $this->start_date ) . "'	AND posts.post_date < '" . date( 'Y-m-d H:i:s', strtotime( '+1 DAY', $this->end_date ) ) . "' GROUP BY " . $this->group_by_query;

				$data = $wpdb->get_results( $query );

				$amount = array( 'order_amounts' => $this->prepare_chart_data( $data, 'post_date', 'total_sales', $this->chart_interval, $this->start_date, $this->chart_groupby ) );

				$count = array( 'order_count' => $this->prepare_chart_data( $data, 'post_date', 'count', $this->chart_interval, $this->start_date, $this->chart_groupby ) );

				switch ( $time ) {
					case 'year':
						$labels = array(
							__( 'Jan', 'marketplace' ),
							__( 'Feb', 'marketplace' ),
							__( 'Mar', 'marketplace' ),
							__( 'Apr', 'marketplace' ),
							__( 'May', 'marketplace' ),
							__( 'Jun', 'marketplace' ),
							__( 'Jul', 'marketplace' ),
							__( 'Aug', 'marketplace' ),
							__( 'Sep', 'marketplace' ),
							__( 'Oct', 'marketplace' ),
							__( 'Nov', 'marketplace' ),
							__( 'Dec', 'marketplace' ),
						);
						break;

					case 'month':
						foreach ( $amount['order_amounts'] as $key => $value ) {
							$labels[] = date( 'd M', substr( $key, 0, -3 ) );
						}
						break;

					case '7day':
						foreach ( $amount['order_amounts'] as $key => $value ) {
							$labels[] = date( 'd M', substr( $key, 0, -3 ) );
						}
						break;

					case 'last_month':
						foreach ( $amount['order_amounts'] as $key => $value ) {
							$labels[] = date( 'd M', substr( $key, 0, -3 ) );
						}
						break;

					default:
						break;
				}

				$order_amount = json_encode(
					array(
						'order_amount' => array_map( array( $this, 'round_chart_totals' ), array_values( $amount['order_amounts'] ) ),
						'labels'       => $labels,
						'count'        => array_map( array( $this, 'round_chart_totals' ), array_values( $count['order_count'] ) ),
					)
				);

			} else {
				$order_amount = '';
			}

			if ( $order_amount ) {
				?>

				<div class="mp-store-sale-order-history-section">

					<div class="header">
						<p><?php echo __( 'Sale Order History', 'marketplace' ); ?></h2>
						<div class="select-interval">
							<form method="get">
								<?php if ( is_admin() ) {
									echo '<input type="hidden" name="page" value="seller" />';
								}  ?>

								<select id="mp-update-sale-order" name="sort" onchange='this.form.submit()'>
									<option value="year" <?php echo $time == 'year' ? 'selected' : ''; ?>><?php echo __( 'This Year', 'marketplace' ); ?></option>
									<option value="month" <?php echo $time == 'month' ? 'selected' : ''; ?>><?php echo __( 'This Month', 'marketplace' ); ?></option>
									<option value="last_month" <?php echo $time == 'last_month' ? 'selected' : ''; ?>><?php echo __( 'Last Month', 'marketplace' ); ?></option>
									<option value="7day" <?php echo $time == '7day' ? 'selected' : ''; ?>><?php echo __( 'Last 7 Days', 'marketplace' ); ?></option>
								</select>
							</form>
						</div>
					</div>

					<canvas id="sale-order-history" style="width: 100%;"></canvas>

				</div>
				<script>
				var order_data = jQuery.parseJSON( '<?php echo $order_amount; ?>' );
				if ( order_data ) {
					lineChart(order_data)
				}
				function lineChart($order_amount) {

					var data = order_data.order_amount
					var count = order_data.count
					var label = order_data.labels
					$labels = new Array()
					$sales = new Array()
					$count = new Array()
					jQuery.each( data, function(i) {
						$labels.push(label[i])
						$sales.push(data[i][1])
					} )

					jQuery.each( count, function(i) {
						$count.push(parseInt(count[i][1]))
					} )

					var data = {
						labels : $labels,
						datasets : [
							{
								label: 'Sale',
								borderColor : '#673AB7',
								backgroundColor : '#673AB7',
								data : $sales,
								fill: false,
								yAxisID: 'y-axis-1'
							},
							{
								label: 'Order',
								borderColor : '#96588A',
								backgroundColor : '#96588A',
								data : $count,
								fill: false,
								yAxisID: 'y-axis-2'
							}
						]
					}

					var ctx = document.getElementById("sale-order-history").getContext("2d");
					new Chart(ctx, {
						type: 'line',
						data: data,
						stacked: false,
						options: {
							responsive: true,
							scales: {
								yAxes: [{
									type: 'linear',
									display: true,
									position: "left",
									id: 'y-axis-1',
									gridLines: {
										drawOnChartArea: false,
									},
									ticks: {
										callback: function(label, index, labels) {
											if ( label >= 1000 ) {
												return label/1000+'K';
											} else {
												if (Math.floor(label) === label) {
														return label;
												}
											}
										}
									}
								}, {
									type: 'linear',
									display: true,
									position: "right",
									id: 'y-axis-2',
									ticks: {
										callback: function(label, index, labels) {
											if ( label >= 1000 ) {
												return label/1000+'k';
											} else {
												if (Math.floor(label) === label) {
														return label;
												}
											}
										},
									},
									gridLines: {
										drawOnChartArea: false,
									},
								}
							],
									xAxes: [
										{
											gridLines: {
												display: false
											},
										}
									]
							}
						}
					})
				}
				</script>
				<?php
			}
		}

		/**
		 * Order status and product selling status
		 */
		private function mp_dashboard_order_product_section() {
			global $wpdb;

			$postid = $this->get_order_item_id();

			$order_ids = $postid['order_id'];

			$total_products = $this->mp_get_total_products_count() ? $this->mp_get_total_products_count() : 0;

			$total_orders = $this->mp_get_total_order_count() ? $this->mp_get_total_order_count() : 0;

			$order_items = $this->mp_top_3_product();

			foreach ( explode( ',', $order_ids ) as $key => $value ) {
				$status[] = get_post_field( 'post_status', $value );
			}

			$status = wp_json_encode(
				array(
					'status' => array_count_values( $status ),
				)
			);

			$user_id = get_current_user_id();

			$args = array(
				'post_type'      => 'product',
				'author'         => $user_id,
				'meta_key'       => 'total_sales',
				'orderby'        => 'meta_value_num',
				'order'          => 'ASC',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			);

			$loop = new WP_Query( $args );

			?>
			<div class="mp-store-order-product-section">

				<div class="mp-store-order-status-section">

					<div class="section-header">
						<div class="summary-stats">
							<h2><?php echo $total_orders; ?></h2>
							<p><?php echo esc_html__( 'Total Orders', 'marketplace' ); ?></p>
						</div>
						<div class="summary-icon order-icon"></div>
					</div>

					<div class="section-body" style="min-height: 280px;">
						<canvas id="mp-order-status-chart" style="width: 100%; height: 550px; position: absolute;"></canvas>
						<script>
						var statusVar = jQuery.parseJSON('<?php echo $status; ?>')
						var statusArr = new Array()
						var $labels = [
										'wc-completed',
										'wc-pending',
										'wc-processing',
										'wc-on-hold',
										'wc-cancelled',
										'wc-refunded',
										'wc-failed'
								]
						jQuery.each($labels, function(i) {
							if (statusVar.status[$labels[i]]) {
								statusArr.push(statusVar.status[$labels[i]])
							} else {
								statusArr.push(0)
							}
						})

						var data = {
								datasets: [{
									data: statusArr,
									backgroundColor: [
										'rgba(142, 36, 170,1)',
										'rgba(142, 36, 170,1)',
										'rgba(171, 70, 188,1)',
										'rgba(186, 104, 200,1)',
										'rgba(186, 104, 200,1)',
										'rgba(206, 147, 216,1)',
										'rgba(245, 232, 246,1)'
									],
								}],

								// These labels appear in the legend and in the tooltips when hovering different arcs
								labels: [
									'<?php echo esc_html__( 'Completed', 'marketplace' ); ?>',
									'<?php echo esc_html__( 'Pending', 'marketplace' ); ?>',
									'<?php echo esc_html__( 'Processing', 'marketplace' ); ?>',
									'<?php echo esc_html__( 'OnHold', 'marketplace' ); ?>',
									'<?php echo esc_html__( 'Cancelled', 'marketplace' ); ?>',
									'<?php echo esc_html__( 'Refunded', 'marketplace' ); ?>',
									'<?php echo esc_html__( 'Failed', 'marketplace' ); ?>'
								]
						};
						var options = {
							legend: {
								display: true,
								position: 'right',
								verticalAlign: "center",
								labels: {
									boxWidth: 20,
									padding: 15
								}
							},
							responsive: true
						};
						var ctx = document.getElementById("mp-order-status-chart").getContext('2d');

						var myDoughnutChart = new Chart(ctx, {
								type: 'doughnut',
								data: data,
								options: options
						});
						</script>
					</div>

				</div>

				<div class="mp-store-product-status-section">

					<div class="section-header">
						<div class="summary-stats">
							<h2><?php echo $total_products; ?></h2>
							<p><?php echo __('Total Products', 'marketplace'); ?></p>
						</div>
						<div class="summary-icon cubes"></div>
					</div>

					<div class="section-body">
						<p><?php echo __( 'Top Selling Product', 'marketplace' ); ?></p>
						<div class="product-list">
							<?php foreach ($order_items as $key => $value): ?>
								<a href="<?php echo get_permalink( $value->ID ); ?>"><?php echo $value->ItemName; ?></a>
								<p><?php echo $value->Sales . ' ' . __('Sale', 'marketplace'); ?></p>
							<?php endforeach; ?>
						</div>
					</div>

					<?php if ( $loop->have_posts() ) : ?>
							<div class="section-footer">
								<?php
								while ( $loop->have_posts() ) : $loop->the_post(); ?>

								<a id="id-<?php the_id(); ?>" href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
								<?php the_title(); ?></a>

								<p><?php echo get_post_meta( get_the_ID(), 'total_sales', true ); ?> <?php echo esc_html__( 'Least Sale', 'marketplace' ); ?></p>

								<?php endwhile; ?>

								<?php wp_reset_query(); ?>
							</div>
					<?php endif; ?>

				</div>
			</div>
			<?php
		}

		function mp_top_3_product() {

			global $wpdb, $sql, $Limit;

			$Limit = 3;

			$user_id = get_current_user_id();

			$sql = "SELECT wois.order_item_name AS  'ItemName', post.ID, SUM( woi.meta_value ) AS  'Sales', SUM( woi6.meta_value ) AS  'Total'
				FROM {$wpdb->prefix}woocommerce_order_items AS wois
				LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS woi ON woi.order_item_id = wois.order_item_id
				LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS woi6 ON woi6.order_item_id = wois.order_item_id
				LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS woi_auther ON woi_auther.order_item_id = wois.order_item_id
				JOIN {$wpdb->prefix}posts post ON post.ID = woi_auther.meta_value
				WHERE post.post_author='" . $user_id . "' and post.post_status = 'publish' and woi.meta_key ='_qty'
				AND woi6.meta_key ='_line_total'
				AND woi_auther.meta_key =  '_product_id'
				GROUP BY wois.order_item_name
				ORDER BY Sales DESC
				LIMIT 3";

			$order_items = $wpdb->get_results( $sql );

			return $order_items;

		}

		/**
		 *  Get total product count
		 */
		function mp_get_total_products_count() {
			global $wpdb, $sql, $Limit;

			$user_id = get_current_user_id();

			$sql = "SELECT COUNT(*) AS 'product_count' FROM {$wpdb->prefix}posts as posts WHERE  post_type='product' AND post_author='" . $user_id . "'  AND post_status = 'publish'";

			return $wpdb->get_var( $sql );

		}

		/**
		 *  Get total order count
		 */
		function mp_get_total_order_count() {
			global $wpdb;

			$postid = $this->get_order_item_id();

			if( $postid['order_item_id'] ) {
				$sql = " SELECT count(*) AS 'total_order_count' FROM {$wpdb->prefix}woocommerce_order_items as item join {$wpdb->prefix}posts as post on item.order_id=post.ID WHERE  post.post_type='shop_order' AND item.order_item_id in ($postid[order_item_id]) AND item.order_id=post.ID";

				if($wpdb->get_var($sql)==0)
				{
					$total_order=0;
				}

				else{
					$total_order=$wpdb->get_var($sql);
				}

				return $total_order;
			}
		}


		/**
		 * Store recent orders section.
		 */
		public function mp_dashboard_recent_order_section() {
			
			global $wpdb;

			$order_items = array();

			$per_page = 10;

			$postid = $this->get_order_item_id();

			$user_id = get_current_user_id();

			require WK_MARKETPLACE_DIR . 'includes/class-mp-commission.php';

			$commission = new MP_Commission();

			$order_ids = !empty( $postid['order_id'] ) ? explode( ',', $postid['order_id'] ) : array();

			rsort( $order_ids );

			$order_items = array();

			foreach ( $order_ids as $key => $order_id ) {

				$seller_order_data = $commission->get_seller_final_order_info( $order_id, $user_id );

				$order_items[] = array(
					'OrderID' => $order_id,
					'ItemCount' => $seller_order_data['quantity'],
					'OrderCurrency' => get_post_meta( $order_id, '_order_currency', true ),
					'OrderTotal' => $seller_order_data['total_seller_amount'],
					'OrderDate' => get_post_field( 'post_date', $order_id ),
					'BillingEmail' => get_post_meta( $order_id, '_billing_email', true ),
					'FirstName' => get_post_meta( $order_id, '_billing_first_name', true ),
				);

			}


			$page_name = $wpdb->get_var( "SELECT post_name FROM $wpdb->posts WHERE post_name ='" . get_option( 'wkmp_seller_page_title' ) . "'" );

			?>
			<div class="mp-store-recent-orders">

				<?php
				if ( ! is_admin() ) :
					$order_url = site_url( '/' ) . $page_name . '/' . get_option( 'mp_order_history', 'order-history' );
				else :
					$order_url = admin_url( 'admin.php?page=order-history' );
				endif;
				?>

				<h4><?php _e( 'Recent Orders', 'marketplace' ); ?><a href="<?php echo $order_url; ?>"><?php echo __( 'View All', 'marketplace' ); ?></a></h4>

				<table class="recentOrders">

					<thead style="background : #e9e9e9;">

						<tr>
							<th><?php echo _e("Order ID", "marketplace"); ?></th>

							<th><?php echo _e("Order Date", "marketplace"); ?></th>

							<th><?php echo _e("Billing Email", "marketplace"); ?></th>

							<th><?php echo _e("First Name", "marketplace"); ?></th>

							<th><?php echo _e("Item Count", "marketplace"); ?></th>

							<th><?php echo _e("Amount", "marketplace"); ?></th>
						</tr>

					</thead>

					<tbody>
						<?php

						if (count($order_items)>0) :
							foreach ($order_items as $key => $order_item) {
								if ($key%2 == 1) {
									$alternate = "alternate ";
								} else {
									$alternate = "";
								}
								?>
								<tr class="<?php echo $alternate . "row_" . $key; ?>">
									<td><?php echo $order_item['OrderID']; ?></td>

									<td><?php echo $order_item['OrderDate']; ?></td>

									<td><?php echo $order_item['BillingEmail']; ?></td>

									<td><?php echo $order_item['FirstName']; ?></td>

									<td><?php echo $order_item['ItemCount']; ?></td>

									<td><?php echo wc_price( $order_item['OrderTotal'], array( 'currency' => $order_item['OrderCurrency'] ) ); ?></td>
								</tr>
								<?php
							}
						else :
							echo '<tr><td colspan=6><p>';
							echo esc_html__( 'No orders found.', 'marketplace' );
							echo '</p></td></tr>';
						endif;

						?>

					<tbody>

				</table>

			</div>
		<?php
		}

		/**
		 * Get order item id.
		 */
		public function get_order_item_id() {
			global $wpdb;

			$user_id = get_current_user_id();

			$sql = "Select woitems.order_item_id,woitems.order_id,post.post_type from {$wpdb->prefix}woocommerce_order_itemmeta woi join {$wpdb->prefix}woocommerce_order_items woitems on woitems.order_item_id=woi.order_item_id join {$wpdb->prefix}posts as post on woi.meta_value=post.ID where woi.meta_key='_product_id' and post.ID=woi.meta_value and post.post_author='" . $user_id . "' GROUP BY order_id";

			$result = $wpdb->get_results( $sql );

			$ID = array();

			$order_ID = array();

			foreach ( $result as $res ) {
				$order_post_type = $wpdb->get_var( $wpdb->prepare( "SELECT post_type FROM {$wpdb->prefix}posts WHERE ID=%d", $res->order_id ) );
				if( $order_post_type != 'shop_order_refund' ) {
					$ID[] = $res->order_item_id;
	
					$order_ID[] = $res->order_id;
				}
			}
			$ID       = implode( ',', $ID );
			$order_ID = implode( ',', $order_ID );

			return array(
				'order_item_id' => $ID,
				'order_id'      => $order_ID,
			);

		}

		function mp_top_billing_country() {

			global $wpdb;

			$order_items = array();

			$per_page = 10;

			$postid = $this->get_order_item_id();

			if ( $postid['order_item_id'] ) {

				$sql = "SELECT sum(woi.meta_value) AS 'Total',postmeta.meta_value AS 'BillingCountry',Count(*) AS 'OrderCount' FROM {$wpdb->prefix}woocommerce_order_itemmeta woi
				left join {$wpdb->prefix}woocommerce_order_items wois on woi.order_item_id=wois.order_item_id
				left join {$wpdb->prefix}postmeta as postmeta on postmeta.post_id=wois.order_id
				WHERE  woi.meta_key='_line_total' AND wois.order_item_id in(" . $postid['order_item_id'] . ") AND  postmeta.meta_key='_billing_country'
				GROUP BY  postmeta.meta_value
				Order By OrderCount DESC
				LIMIT {$per_page}";

				$order_items = $wpdb->get_results($sql);

			}

			?>
			<script>

				function googleChartLoadCb() {
					var topBilling = new google.visualization.DataTable();

					topBilling.addColumn('string', 'country');

					topBilling.addColumn('number', '<?php echo esc_html__('Total-Amount', 'marketplace'); ?>');

					topBilling.addColumn('number', '<?php echo esc_html__('OrderTotalCount', 'marketplace'); ?>')
					topBilling.addRows( <?php echo count( $order_items ); ?>);

					drawRegionsMap(topBilling);

			   }

			   function drawRegionsMap(topBilling) {
					var options = {};
					var chart = new google.visualization.GeoChart(document.getElementById('top_billing_country'));
					chart.draw(topBilling, options);
					go();
					window.addEventListener('resize', go);
					function go(){
						chart.draw(topBilling, options);
					}
				}

			</script>

			<?php

			$i = 0;

			if ( ! empty( $order_items ) ) :
				$country = new WC_Countries();

				foreach ( $order_items as $key ) {
					?>
					<script>
						topBilling.setValue( <?php echo $i; ?>, 0, '<?php echo $country->countries[ $key->BillingCountry ]; ?>' );

						topBilling.setValue( <?php echo $i; ?>, 1, '<?php echo $key->Total; ?>' );

						topBilling.setValue( <?php echo $i; ?>, 2, '<?php echo $key->OrderCount; ?>' );
					</script>

					<?php
					$i++;
				}

			endif;

			?>
			<div class="mp-store-top-billing-country">
				<h4><?php esc_html_e( 'Top Billing Countries', 'marketplace' ); ?></h4>
				<div id="top_billing_country" style="overflow:hidden"></div>
			</div>
			<?php

		}

		/**
		 * Get sales stat.
		 */
		private function mp_get_sale_stats() {
			global $wpdb;

			$user_id = get_current_user_id();

			$result = $wpdb->get_row( $wpdb->prepare( "SELECT seller_total_ammount, paid_amount, total_refunded_amount from {$wpdb->prefix}mpcommision where seller_id = '%d'", $user_id ) );

			return $result;
		}

	}

}
