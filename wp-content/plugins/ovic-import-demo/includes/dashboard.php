<?php
/**
 * Ovic Import Dashboard
 *
 * @author   KHANH
 * @category API
 * @package  Ovic_Import_Dashboard
 * @since    1.0.0
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!class_exists('Ovic_Import_Dashboard')) {
    class Ovic_Import_Dashboard
    {
        public function __construct()
        {
            add_action('admin_menu', array($this, 'admin_menu'), 10);
            add_action('plugins_loaded', array($this, 'includes'));
        }

        public function admin_menu()
        {
            if (current_user_can('edit_theme_options')) {
                add_submenu_page(
                    'ovic-plugins',
                    'Ovic Import Demo',
                    'Ovic Import Demo',
                    'manage_options',
                    'ovic-import',
                    array(
                        $this, 'options_setting'
                    )
                );
            }
        }

        function includes()
        {
            require_once OVIC_IMPORT_PLUGIN_DIR.'/includes/welcome.php';
        }

        public function options_setting()
        {
            $tab  = 'import_content';
            $tabs = array(
                'import_content'  => 'Import Content',
                'import_database' => 'Import Database',
            );
            if (isset($_GET['tab'])) {
                $tab = $_GET['tab'];
            }
            ?>
            <div class="ovic-demo-wrap">
                <div id="tabs-container" role="tabpanel">
                    <h1 style="margin: 20px 0;font-size: 40px;line-height: 1;" class="intro-title">
                        <?php esc_html_e('Import Panel KuteThemes', 'ovic-import'); ?>
                        <a rel="noopener noreferrer" title="Click to see changelog" href="#">
                            <sup><?php echo OVIC_IMPORT_VERSION; ?></sup>
                        </a>
                    </h1>
                    <div class="nav-tab-wrapper">
                        <?php foreach ($tabs as $function => $value): ?>
                            <a class="nav-tab <?php if ($tab == $function): ?> nav-tab-active<?php endif; ?>"
                               href="<?php echo esc_url('admin.php?page=ovic-import&tab='.$function) ?>"
                               data-tab=".<?php echo esc_attr($function); ?>">
                                <?php echo esc_html($value); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <div class="tab-content-wrapper">
                        <?php foreach ($tabs as $function => $name): ?>
                            <?php
                            $class = 'tab-content '.$function;
                            if ($tab == $function) {
                                $class .= ' active';
                            }
                            ?>
                            <div class="<?php echo esc_attr($class); ?>">
                                <div class="wp-clearfix ovic-demo-detail">
                                    <form action="" class="check_post_action" method="POST">
                                        <div class="alert-tool"></div>
                                        <?php $this->$function(); ?>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php
        }

        public function import_content()
        {
            do_action('ovic_importer_page_content');
        }

        public function import_database()
        {
            Ovic_Import_Database_Dashboard::dashboard();
        }
    }

    new Ovic_Import_Dashboard();
}