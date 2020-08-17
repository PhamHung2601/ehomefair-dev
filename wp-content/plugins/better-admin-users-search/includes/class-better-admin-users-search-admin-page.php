<?php

if (!class_exists('Better_Admin_Users_Search_Admin_Page')) {
    class Better_Admin_Users_Search_Admin_Page
    {


        private $default_values = array(
            "user_login",
            "user_url",
            "user_email",
            "user_nicename",
            "display_name"
        );

        public function __construct()
        {
            add_action('cmb2_admin_init', array($this, 'add_metabox'));
        }

        public function add_metabox()
        {

            $cmb_options = new_cmb2_box(array(
                'id' => BETTER_ADMIN_USERS_SEARCH_PREFIX . '_settings_page',
                'title' => 'Better Admin Users Search',
                'object_types' => array('options-page'),
                'option_key' => BETTER_ADMIN_USERS_SEARCH_PREFIX . '_options',
                'parent_slug' => 'options-general.php',
                'capability' => 'manage_options',
            ));

            $cmb_options->add_field(array(
                'name' => __('Default search values', 'baus'),
                'desc' => __('Default values used by WordPress to do the search', 'baus'),
                'id' => BETTER_ADMIN_USERS_SEARCH_PREFIX . '_title_default_wp',
                'type' => 'title',
                'on_front' => false,
            ));

            foreach ($this->default_values as $entry) {
                $cmb_options->add_field(array(
                    'name' => esc_html($entry),
                    'type' => 'checkbox',
                    'id' => BETTER_ADMIN_USERS_SEARCH_PREFIX . '_default_value_' . $entry,
                    'description' => sprintf(
                        __('For you, this data is "%s"', 'baus'),
                        wp_get_current_user()->get($entry)
                    )
                ));
            }

            $cmb_options->add_field(array(
                'name' => __('Additionals metas', 'baus'),
                'desc' => __('Add additional user metas to the admin user search', 'baus') . '<br>' . __('Note: Some metas won\'t work because their are not string.', 'baus'),
                'id' => BETTER_ADMIN_USERS_SEARCH_PREFIX . '_title_metas',
                'type' => 'title',
                'on_front' => false,
            ));


            $cmb_options->add_field(array(
                'name' => __('User meta(s)', 'baus'),
                'id' => BETTER_ADMIN_USERS_SEARCH_PREFIX . '_metas',
                'desc' => __('Select metas you want to add to your search.', 'baus'),
                'type' => 'pw_multiselect',
                'options' => $this->get_user_metas(),
            ));


        }

        private function get_user_metas()
        {
            $metas = array();
            global $wpdb;
            $select = "SELECT distinct $wpdb->usermeta.meta_key FROM $wpdb->usermeta";
            $user_metas = $wpdb->get_results($select, ARRAY_A);

            foreach ($user_metas as $meta) {
                $metas[$meta["meta_key"]] = htmlspecialchars($meta["meta_key"]);
            }
            return $metas;
        }

    }
}