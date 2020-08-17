<?php

if (!class_exists('Better_Admin_Users_Search')) {
    class Better_Admin_Users_Search
    {

        private $plugin_name;
        private $version;

        public function __construct()
        {
            if (defined('BETTER_ADMIN_USERS_SEARCH_VERSION')) {
                $this->version = BETTER_ADMIN_USERS_SEARCH_VERSION;
            } else {
                $this->version = '1.0.0';
            }
            $this->plugin_name = 'better-admin-users-search';

            $this->load_dependencies();
            $this->create_admin_page();
            $this->users_search();
        }


        private function load_dependencies()
        {
            require_once plugin_dir_path(dirname(__FILE__)) . 'vendor/autoload.php';
            require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-better-admin-users-search-admin-page.php';
            require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-better-admin-users-search-hook.php';

        }

        private function create_admin_page()
        {
            new Better_Admin_Users_Search_Admin_Page();
        }

        private function users_search()
        {
            new Better_Admin_Users_Search_Hook();
        }

    }
}
