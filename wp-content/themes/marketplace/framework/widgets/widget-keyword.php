<?php
if (!defined('ABSPATH')) {
    exit;
}
/**
 * Ovic Keyword
 *
 * Displays Keyword widget.
 *
 * @author   Khanh
 * @category Widgets
 * @package  Ovic/Widgets
 * @version  1.0.0
 * @extends  OVIC_Widget
 */
if (class_exists('OVIC_Widget')) {
    if (!class_exists('Keyword_Widget')) {
        class Keyword_Widget extends OVIC_Widget
        {
            /**
             * Constructor.
             */
            public function __construct()
            {
                $array_settings = apply_filters('ovic_filter_settings_widget_keyword',
                    array(
                        'title' => array(
                            'type' => 'text',
                            'title' => esc_html__('Title', 'marketplace'),
                        ),
                        'multi_key' => array(
                            'type' => 'group',
                            'title' => esc_html__('List Keywords', 'marketplace'),
                            'button_title' => esc_html__('Add New Key', 'marketplace'),
                            'accordion_title' => esc_html__('Key Settings', 'marketplace'),
                            'fields' => array(
                                array(
                                    'id' => 'title_key',
                                    'type' => 'text',
                                    'title' => esc_html__('Text', 'marketplace'),
                                ),
                                array(
                                    'id' => 'link_key',
                                    'type' => 'text',
                                    'title' => esc_html__('Link', 'marketplace'),
                                ),
                            ),
                        ),
                    )
                );
                $this->widget_cssclass = 'widget-keywords';
                $this->widget_description = esc_html__('Display the customer Keywords.', 'marketplace');
                $this->widget_id = 'widget_keywords';
                $this->widget_name = esc_html__('Ovic: Keywords', 'marketplace');
                $this->settings = $array_settings;
                parent::__construct();
            }

            /**
             * Output widget.
             *
             * @see WP_Widget
             *
             * @param array $args
             * @param array $instance
             */
            public function widget($args, $instance)
            {
                $this->widget_start($args, $instance);
                ob_start();
                ?>
                <ul class="keywords">
                    <?php
                    foreach ($instance['multi_key'] as $item) :
                        $url = $item['link_key'] != '' ? $item['link_key'] : '#';
                        if ($item['title_key'] != '') : ?>
                            <li class="keyword">
                                <a href="<?php echo esc_url($url); ?>"><?php echo esc_html($item['title_key']) ?></a>
                            </li>
                        <?php endif;
                    endforeach;
                    ?>
                </ul>
                <?php
                echo apply_filters('ovic_filter_widget_keyword', ob_get_clean(), $instance);
                $this->widget_end($args);
            }
        }
    }
    /**
     * Register Widgets.
     *
     * @since 2.3.0
     */
    function Keyword_Widget()
    {
        register_widget('Keyword_Widget');
    }

    add_action('widgets_init', 'Keyword_Widget');
}