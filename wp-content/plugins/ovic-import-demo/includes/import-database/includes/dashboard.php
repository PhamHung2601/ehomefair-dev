<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // disable direct access
}
if ( ! class_exists( 'Ovic_Import_Database_Dashboard' ) ) {
	class Ovic_Import_Database_Dashboard
	{
		/**
		 * Variable to hold the initialization state.
		 *
		 * @var  boolean
		 */
		protected static $initialized = false;

		public static function initialize()
		{
			// Do nothing if pluggable functions already initialized.
			if ( self::$initialized ) {
				return;
			}
			/*add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );*/
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts' ) );

			// State that initialization completed.
			self::$initialized = true;
		}

		public static function admin_enqueue_scripts( $preflix )
		{
			if ( $preflix == 'ovic-plugins_page_ovic-import' ) {
				wp_enqueue_style( 'import-sample-data', IMPORT_DB_PLUGIN_URL . '/assets/css/style.css' );
				wp_enqueue_script( 'import-sample-data', IMPORT_DB_PLUGIN_URL . '/assets/js/functions.js', array( 'jquery' ), IMPORT_DB_VERSION, true );
				wp_localize_script( 'import-sample-data', 'import_sample_data_ajax_admin', array(
						'ajaxurl'          => admin_url( 'admin-ajax.php' ),
						'security'         => wp_create_nonce( 'import_sample_data_ajax_admin' ),
						'required_plugins' => Ovic_Import_Database_Settings::plugins(),
					)
				);
			}
		}

		public static function admin_menu()
		{
			$args = array(
				'parent_slug' => 'themes.php',
				'page_title'  => esc_html__( 'Sample Data', 'ovic-import' ),
				'menu_title'  => esc_html__( 'Sample Data', 'ovic-import' ),
				'capability'  => 'manage_options',
				'menu_slug'   => 'sample-data',
				'function'    => 'Ovic_Import_Database_Dashboard::dashboard',
			);
			$args = apply_filters( 'import_sample_data_menu_args', $args );
			add_submenu_page( $args['parent_slug'], $args['page_title'], $args['menu_title'], $args['capability'], $args['menu_slug'], $args['function'] );
		}

		public static function dashboard()
		{
			add_thickbox();
			?>
            <div class="import-sample-data-wrap">
                <h2 class="intro-title" style="font-size:2em;line-height:1;margin-bottom:0;margin-top:0;">
					<?php esc_html_e( 'Install Sample Data', 'ovic-import' ); ?>
                    <a rel="noopener noreferrer" title="Click to see changelog" href="#">
                        <sup><?php echo IMPORT_DB_VERSION; ?></sup>
                    </a>
                </h2>
				<?php
				$packages                   = Ovic_Import_Database_Sample_Data::get_sample_packages();
				$import_current_sample_data = get_option( 'import_sample_data_current_sample_data' );
				$ajax_clear                 = add_query_arg(
					array(
						'action'   => 'import_sample_data_clear_temporary',
						'security' => wp_create_nonce( 'import_sample_data_ajax_admin' ),
					),
					admin_url( 'admin-ajax.php' )
				);
				if ( ! class_exists( 'ZipArchive' ) ) {
					?>
                    <div class="error" style="margin: 25px 0 0 0">
                        <p><?php esc_html_e( 'Your host missing class ZipArchive, please enable ZipArchive before import demo.', 'ovic-import' ); ?></p>
                    </div>
					<?php
				}
				if ( ! empty( $packages ) ) : ?>

                    <div class="box-wrap three-col">
						<?php foreach ( $packages as $package ): ?>
							<?php
							if ( $package['id'] == $import_current_sample_data ) {
								$install_class   = 'hidden';
								$uninstall_class = '';
							} else {
								$uninstall_class = 'hidden';
								$install_class   = '';
							}
							$ajax_uninstall = add_query_arg(
								array(
									'action'   => 'import_sample_data_uninstall_sample_data',
									'security' => wp_create_nonce( 'import_sample_data_ajax_admin' ),
									'package'  => $package['id'],
									'step'     => '1',
								),
								admin_url( 'admin-ajax.php' )
							);
							$ajax_install   = add_query_arg(
								array(
									'action'   => 'import_sample_data_install_sample_data',
									'security' => wp_create_nonce( 'import_sample_data_ajax_admin' ),
									'package'  => $package['id'],
									'step'     => '1',
								),
								admin_url( 'admin-ajax.php' )
							);
							?>
                            <div class="col">
                                <div class="box" id="sample-data-<?php echo esc_attr( $package['id'] ); ?>">
                                    <a target="_blank" href="<?php echo esc_url( $package['demo'] ); ?> ">
                                        <img src="<?php echo esc_url( $package['thumbnail'] ) ?>"
                                             alt="<?php echo esc_attr( $package['name'] ); ?>">
                                    </a>
                                    <div class="box-info">
                                        <h5><?php echo esc_html( $package['name'] ); ?></h5>

                                        <div class="bottom">
                                            <a href="<?php echo esc_url( $ajax_uninstall ); ?>"
                                               title="<?php esc_html_e( 'Uninstall Sample Data', 'ovic-import' ); ?>"
                                               class="button button-primary uninstall-sample thickbox <?php echo esc_attr( $uninstall_class ); ?>">
												<?php esc_html_e( 'Uninstall', 'ovic-import' ); ?>
                                            </a>
                                            <a href="<?php echo esc_url( $ajax_install ); ?>"
                                               title="<?php esc_html_e( 'Install Sample Data', 'ovic-import' ); ?>"
                                               class="button button-primary install-sample thickbox <?php echo esc_attr( $install_class ); ?>">
												<?php esc_html_e( 'Install', 'ovic-import' ); ?>
                                            </a>
                                        </div>

                                    </div>
                                </div>
                            </div>
						<?php endforeach; ?>
                    </div>

				<?php else: ?>

                    <div class="error" style="margin: 20px 0;">
                        <p><?php esc_html_e( 'Failed to get available sample data packages.', 'ovic-import' ); ?></p>
                    </div>

				<?php endif; ?>

                <div class="welcome-panel">
                    <h3 style="margin-top: 0;"><?php esc_html_e( 'Export Sample Data', 'ovic-import' ); ?></h3>
                    <form action="" method="post">
                        <div class="input-text-wrap">
                            <p>
                                <label for="backup_filename"> <?php esc_attr_e( 'Name', 'ovic-import' ); ?></label>
                                <input type="text" name="backup_filename" id="backup_filename">
                            </p>
                        </div>

                        <input type="submit" class="button button-primary"
                               value="<?php esc_attr_e( 'Export', 'ovic-import' ); ?>">

                        <a href="<?php echo esc_url( $ajax_clear ); ?>"
                           title="<?php esc_html_e( 'Temp Notice!', 'ovic-import' ); ?>"
                           class="button thickbox">
							<?php esc_html_e( 'Clear Temp', 'ovic-import' ); ?>
                        </a>

						<?php wp_nonce_field( 'export-sample-data-form',
							'_wpnonce',
							true,
							true
						); ?>
                        <input type="hidden" name="import_sample_data_action" value="export-sample-data">
                    </form>
                </div>
            </div>
			<?php
		}
	}
}