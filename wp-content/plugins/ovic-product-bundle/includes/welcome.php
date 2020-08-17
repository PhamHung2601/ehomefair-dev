<?php
/**
 * Ovic Framework setup
 *
 * @author   KHANH
 * @category API
 * @package  Ovic_Plugins_Dashboard
 * @since    1.0.0
 */
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( !class_exists( 'Ovic_Plugins_Dashboard' ) ) {
	class Ovic_Plugins_Dashboard
	{
		public $tabs = array();

		public function __construct()
		{
			$this->set_tabs();
			add_action( 'admin_menu', array( $this, 'admin_menu' ), 5 );
		}

		public function admin_menu()
		{
			if ( current_user_can( 'edit_theme_options' ) ) {
				add_menu_page(
					'Ovic Plugins',
					'Ovic Plugins',
					'manage_options',
					'ovic-plugins',
					array( $this, 'welcome' ),
					' data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6QjA0NzQ1OTREM0YwMTFFNzk5OUNBMzU4RjdCQTg3NTciIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6QjA0NzQ1OTVEM0YwMTFFNzk5OUNBMzU4RjdCQTg3NTciPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDpCMDQ3NDU5MkQzRjAxMUU3OTk5Q0EzNThGN0JBODc1NyIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpCMDQ3NDU5M0QzRjAxMUU3OTk5Q0EzNThGN0JBODc1NyIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PtLIsq8AAADpSURBVHjarJNNCsIwEIWjC0HUi2iXeoAsPYceQzyCdaHgOao78RZiXQkuLB7AhS1KfIGJPAZ/0cIH6Zc3U5ikxhljFE0wAik4CxtxTZ3nlwqYgCtwT/B7M1DVDXzx8kWhZiU19wZTFZgDC+qCFceZSWjQAhfaGD6YS2BAOV/T8jImuXhRHEgoHxuZdhCWgl2QCV3ylvKpFzmJBgUz8hn5Bvm8bD57SrR2tC58gx2JNq374AgOoEe+Q+u9HmLy5RDHXkTqGAdfHGP07iLVBKu+7KTmf1f555+J8Vd7DLagACewFhfp/E2AAQDGb1mBFjYnBAAAAABJRU5ErkJggg==',
					3
				);
			}
		}

		public function set_tabs()
		{
			$tabs       = array(
				'dashboard' => 'Welcome',
				'plugins'   => 'Plugins',
			);
			$tabs       = apply_filters( 'ovic_plugins_registered_dashboard_tabs', $tabs );
			$this->tabs = $tabs;
		}

		public function active_plugin()
		{
			if ( empty( $_GET['magic_token'] ) || wp_verify_nonce( $_GET['magic_token'], 'panel-plugins' ) === false ) {
				echo 'Permission denied';
				die;
			}
			if ( isset( $_GET['plugin_slug'] ) && $_GET['plugin_slug'] != "" ) {
				$plugin_slug = $_GET['plugin_slug'];
				$plugins     = TGM_Plugin_Activation::$instance->plugins;
				foreach ( $plugins as $plugin ) {
					if ( $plugin['slug'] == $plugin_slug ) {
						activate_plugins( $plugin['file_path'] );
						?>
                        <script type="text/javascript">
                            window.location = "admin.php?page=ovic-plugins&tab=plugins";
                        </script>
						<?php
						break;
					}
				}
			}
		}

		public function deactivate_plugin()
		{
			if ( empty( $_GET['magic_token'] ) || wp_verify_nonce( $_GET['magic_token'], 'panel-plugins' ) === false ) {
				echo 'Permission denied';
				die;
			}
			if ( isset( $_GET['plugin_slug'] ) && $_GET['plugin_slug'] != "" ) {
				$plugin_slug = $_GET['plugin_slug'];
				$plugins     = TGM_Plugin_Activation::$instance->plugins;
				foreach ( $plugins as $plugin ) {
					if ( $plugin['slug'] == $plugin_slug ) {
						deactivate_plugins( $plugin['file_path'] );
						?>
                        <script type="text/javascript">
                            window.location = "admin.php?page=ovic-plugins&tab=plugins";
                        </script>
						<?php
						break;
					}
				}
			}
		}

		public function plugins()
		{
		}

		public function dashboard()
		{
			?>
            <div class="dashboard">
                <h1>Welcome to Plugins Ovic</h1>
                <p class="about-text">Thanks for using our theme, we have worked very hard to release a great product
                    and we will do our absolute best to support this theme and fix all the issues. </p>
                <div class="dashboard-intro">
                    <p><a href="<?php echo esc_url( 'https://kutethemes.com' ); ?>" target="_blank">
                            <strong>Contact Us</strong></a> For More Plugins
                        Useful</p>
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
                            <a class="nav-tab <?php if ( $tab == $key ): ?> nav-tab-active<?php endif; ?>"
                               href="admin.php?page=ovic-plugins&tab=<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></a>
						<?php endforeach; ?>
                    </div>
                    <div class="tab-content">
						<?php
						ob_start();
						$this->$tab();
						$content_tab = ob_get_clean();
						$content_tab = apply_filters( 'ovic_plugins_dashboard_tab_content', $content_tab, $tab );
						echo $content_tab;
						?>
                    </div>
                </div>
            </div>
			<?php
		}
	}

	new Ovic_Plugins_Dashboard();
}