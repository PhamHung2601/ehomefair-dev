<?php
if ( !class_exists( 'Ovic_Dashboard' ) ) {
	class  Ovic_Dashboard
	{
		public $tabs = array();
		public $theme_name;
		public $market;

		public function __construct()
		{
			/* CHECK MARKET */
			$file_stylesheet = trailingslashit( get_template_directory() ) . 'style.css';
			$theme_info      = get_file_data( $file_stylesheet, array( 'market' => 'Market' ) );
			$this->market    = ( isset( $theme_info['market'] ) ) ? $theme_info['market'] : '';
			/* ACTION */
			$this->set_tabs();
			$this->theme_name = wp_get_theme()->get( 'Name' );
			add_action( 'admin_menu', array( $this, 'admin_menu' ), 5 );
			add_action( 'admin_enqueue_scripts', array( $this, 'dashboard_admin_scripts' ) );
		}

		public function admin_menu()
		{
			if ( current_user_can( 'edit_theme_options' ) ) {
				add_menu_page( __( 'Ovic Panel', 'ovic-toolkit' ), __( 'Ovic Panel', 'ovic-toolkit' ), 'manage_options', 'ovic-dashboard', array( $this, 'welcome' ), OVIC_TOOLKIT_PLUGIN_URL . '/assets/images/icon-menu.png', 2 );
				add_submenu_page( 'ovic-dashboard', 'Dashboard', 'Dashboard', 'manage_options', 'ovic-dashboard', array( $this, 'welcome' ) );
			}
		}

		public function dashboard_admin_scripts( $preflix )
		{
			if ( $preflix == 'toplevel_page_ovic-dashboard' ) {
				wp_enqueue_style(
					'ovic-addon-dashboard',
					OVIC_TOOLKIT_PLUGIN_URL . 'assets/css/dashboard.css',
					array(),
					OVIC_TOOLKIT_VERSION
				);
			}
		}

		public function set_tabs()
		{
			$tabs = array(
				'dashboard' => esc_html__( 'Welcome', 'ovic-toolkit' ),
			);
			if ( $this->market != 'Templatemonster' ) {
				$tabs['our_theme'] = esc_html__( 'View Our Theme', 'ovic-toolkit' );
			}
			if ( file_exists( get_template_directory() . '/changelog.txt' ) ) {
				$tabs['changelog'] = esc_html__( 'Changelog', 'ovic-toolkit' );
			}
			if ( $this->market != 'Templatemonster' && ( !class_exists( 'Ovic_Import_Demo' ) || !class_exists( 'Import_Sample_Data' ) ) ) {
				$tabs['ovic_import'] = esc_html__( 'Import Data', 'ovic-toolkit' );
			}
			$tabs       = apply_filters( 'ovic_registered_dashboard_tabs', $tabs );
			$this->tabs = $tabs;
		}

		public function changelog()
		{
			if ( file_exists( get_template_directory() . '/changelog.txt' ) ) {
                $changelog = wp_remote_get(get_theme_file_uri('/changelog.txt'));
                if ( is_array( $changelog ) && ! is_wp_error( $changelog ) ) {
                    echo '<pre class="changelog">';
                    print_r( $changelog['body'] );
                    echo '</pre>';
                }
			}
		}

		function ovic_import()
		{
			do_action( 'importer_page_content' );
		}

		public function our_theme()
		{
			$token           = 'sntVqHmrHVU5FGEkESRFHdE45rJs9AIg';
			$themeforest_api = add_query_arg(
				array(
					'site'           => 'themeforest.net',
					'page'           => '1',
					'username'       => 'kutethemes',
					'sort_by'        => 'sales',
					'sort_direction' => 'desc',
					'page_size'      => '25',
					'term'           => 'wordpress',
				),
				'https://api.envato.com/v1/discovery/search/search/item'
			);
			$api_key         = 'ovic_dashboard_our_themes_' . md5( $themeforest_api );
			$items           = get_transient( $api_key );
			if ( $items === false ) {
				$response = wp_remote_get( $themeforest_api, array(
						'headers' => array( 'Authorization' => 'Bearer ' . $token ),
					)
				);
				if ( !is_wp_error( $response ) ) {
					$data    = json_decode( $response['body'], true );
					$matches = isset( $data['matches'] ) ? $data['matches'] : array();
					foreach ( $matches as $match ) {
						$items[] = array(
							'id'              => $match['id'],
							'previews'        => $match['previews']['landscape_preview']['landscape_url'],
							'url'             => $match['url'],
							'rating'          => $match['rating']['rating'],
							'number_of_sales' => $match['number_of_sales'],
							'name'            => $match['name'],
						);
					}
					set_transient( $api_key, $items, 12 * HOUR_IN_SECONDS );
				}
			}
			if ( isset( $items ) && !empty( $items ) ) {
				/**
				 * affiliates
				 * CDN: https://cdn.staticaly.com/wp/p/:plugin_name/:version/:file
				 */
				include OVIC_TOOLKIT_PLUGIN_DIR . 'includes/admin/affiliates.php';
				$affiliates = ovic_link_affiliates();
				?>
                <div class="rp-row plugin-tabs">
					<?php
					foreach ( $items as $key => $item ) {
						$url = !empty( $affiliates[$item['id']] ) ? $affiliates[$item['id']] : $item['url'];
						?>
                        <div class="rp-col">
                            <div class="plugin theme-item">
                                <div class="thumb">
                                    <a target="_blank" href="<?php echo esc_url( $url ); ?>">
                                        <img src="<?php echo esc_url( $item['previews'] ) ?>"
                                             alt="envato">
                                    </a>
                                </div>
                                <div class="meta">
									<?php
									$percent = $item['rating'] / 5 * 100;
									?>
                                    <div class="star-rating">
                                        <span style="width:<?php echo esc_attr( $percent ); ?>%"></span>
                                    </div>
                                    <strong class="sale">
										<?php echo $item['number_of_sales'] . ' Sales'; ?>
                                    </strong>
                                </div>
                                <h4 class="name">
                                    <a target="_blank" href="<?php echo esc_url( $url ); ?>">
										<?php echo '' . $item['name']; ?>
                                    </a>
                                </h4>
                            </div>
                        </div>
						<?php
					}
					?>
                    <div class="rp-col">
                        <div class="plugin theme-item">
                            <a target="_blank" class="view-all"
                               href="https://themeforest.net/user/kutethemes/portfolio"><?php esc_html_e( 'View All Our Themes', 'ovic-toolkit' ); ?></a>
                        </div>
                    </div>
                </div>
				<?php
			}
		}

		public function dashboard()
		{
			$theme = wp_get_theme();
			$image = get_theme_file_uri( '/screenshot.jpg' );
			if ( !file_exists( get_template_directory() . '/screenshot.jpg' ) )
				$image = get_theme_file_uri( '/screenshot.png' );
			?>
            <div class="dashboard">
                <h1>Welcome to <?php echo ucfirst( esc_html( $theme->get( 'Name' ) ) ); ?> -
                    Version <?php echo $theme->get( 'Version' ); ?></h1>
                <p class="about-text">Thanks for using our theme, we have worked very hard to release a great product
                    and we will do our absolute best to support this theme and fix all the issues. </p>
                <div class="dashboard-intro">
                    <div class="image">
                        <img src="<?php echo esc_url( $image ); ?>"
                             alt="<?php echo esc_attr( $theme->get( 'Name' ) ); ?>">
                    </div>
                    <div class="intro">
                        <p class="text">
                            <strong><?php echo ucfirst( esc_html( $theme->get( 'Name' ) ) ); ?></strong> is a
                            modern, clean
                            and professional WooCommerce Wordpress Theme, It
                            is fully responsive, it looks stunning on all types of screens and devices.</p>
						<?php $this->support(); ?>
                    </div>
                </div>
            </div>
			<?php
		}

		public function welcome()
		{
			$tab = 'dashboard';
			if ( isset( $_GET['tab'] ) ) {
				$tab = $_GET['tab'];
			}
			?>
            <div class="ovic-wrap">
                <div id="tabs-container" role="tabpanel">
                    <div class="nav-tab-wrapper">
						<?php foreach ( $this->tabs as $key => $value ): ?>
							<?php
							$url = add_query_arg(
								array(
									'page' => 'ovic-dashboard',
									'tab'  => $key,
								),
								'admin.php'
							);
							?>
                            <a class="nav-tab <?php if ( $tab == $key ): ?> nav-tab-active<?php endif; ?>"
                               href="<?php echo esc_url( $url ); ?>">
								<?php echo esc_html( $value ); ?>
                            </a>
						<?php endforeach; ?>
                    </div>
                    <div class="tab-content">
						<?php
						ob_start();
						$this->$tab();
						$content_tab = ob_get_clean();
						$content_tab = apply_filters( 'ovic_dashboard_tab_content', $content_tab, $tab );
						echo $content_tab;
						?>
                    </div>
                </div>
            </div>
			<?php
		}

		public function support()
		{
			$my_theme = wp_get_theme();
			if ( $this->market == '' ) {
				$link_doc = '//help.kutethemes.com/docs/' . $my_theme->get( 'TextDomain' );
				$link_sp  = '//kutethemes.com/supports/';
			} else {
				$link_doc = '//' . $my_theme->get( 'TextDomain' ) . '.kutethemes.net/documentation/';
				$link_sp  = '//support.kutethemes.net/support-system';
			}
			if ( $this->market == 'Templatemonster' )
				return;
			ob_start();
			?>
            <div class="rp-row support-tabs">
                <div class="rp-col">
                    <div class="support-item">
                        <h3><?php esc_html_e( 'Documentation', 'ovic-toolkit' ); ?></h3>
                        <p><?php esc_html_e( 'Here is our user guide for ' . ucfirst( esc_html( $this->theme_name ) ) . ', including basic setup steps, as well as ' . ucfirst( esc_html( $this->theme_name ) ) . ' features and elements for your reference.', 'ovic-toolkit' ); ?></p>
                        <a target="_blank"
                           href="<?php echo esc_url( $link_doc ); ?>"
                           class="button button-primary"><?php esc_html_e( 'Read Documentation', 'ovic-toolkit' ); ?></a>
                    </div>
                </div>
                <div class="rp-col closed">
                    <div class="support-item">
                        <h3><?php esc_html_e( 'Video Tutorials', 'ovic-toolkit' ); ?></h3>
                        <p class="coming-soon"><?php esc_html_e( 'Video tutorials is the great way to show you how to setup ' . ucfirst( esc_html( $this->theme_name ) ) . ' theme, make sure that the feature works as it\'s designed.', 'ovic-toolkit' ); ?></p>
                        <a href="#"
                           class="button button-primary disabled"><?php esc_html_e( 'See Video', 'ovic-toolkit' ); ?></a>
                    </div>
                </div>
                <div class="rp-col">
                    <div class="support-item">
                        <h3><?php esc_html_e( 'Forum', 'ovic-toolkit' ); ?></h3>
                        <p><?php esc_html_e( 'Can\'t find the solution on documentation? We\'re here to help, even on weekend. Just click here to start chatting with us!', 'ovic-toolkit' ); ?></p>
                        <a target="_blank" href="<?php echo esc_url( $link_sp ); ?>"
                           class="button button-primary"><?php esc_html_e( 'Request Support', 'ovic-toolkit' ); ?></a>
                    </div>
                </div>
            </div>
			<?php
			$content = ob_get_clean();
			echo apply_filters( 'ovic_dashboard_support_tab_content', $content );
		}
	}

	new Ovic_Dashboard();
}