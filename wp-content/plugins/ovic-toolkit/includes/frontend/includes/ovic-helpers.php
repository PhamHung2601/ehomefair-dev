<?php
/***
 * Core Name: Theme Helpers
 * Version: 1.0.0
 * Author: Khanh
 */
if ( !defined('ABSPATH') ) {
    exit; // Exit if accessed directly.
}
add_action('ovic_header_vertical', 'ovic_header_vertical', 10, 2);
add_action('ovic_share_button', 'ovic_share_button');
add_action('ovic_set_post_views', 'ovic_set_post_views');
add_action('ovic_get_post_views', 'ovic_get_post_views');
add_action('ovic_search_form', 'ovic_search_form');
add_action('ovic_user_link', 'ovic_user_link');
add_action('ovic_header_language', 'ovic_header_language');
/* AJAX */
add_filter('wcml_multi_currency_ajax_actions', 'ovic_add_action_to_multi_currency_ajax', 10, 1);
/***
 * Template Function
 */
if ( !function_exists('ovic_detected_shortcode') ) {
    function ovic_detected_shortcode( $id, $tab_id = null )
    {
        $post              = get_post($id);
        $content           = preg_replace('/\s+/', ' ', $post->post_content);
        $shortcode_section = '';
        preg_match_all('/\[vc_tta_section(.*?)vc_tta_section\]/', $content, $matches);
        if ( $matches[0] && is_array($matches[0]) && count($matches[0]) > 0 ) {
            foreach ( $matches[0] as $key => $value ) {
                preg_match_all('/tab_id="([^"]+)"/', $matches[0][$key], $matches_ids);
                foreach ( $matches_ids[1] as $matches_id ) {
                    if ( $tab_id == $matches_id ) {
                        $shortcode_section = $value;
                    }
                }
            }
        }

        return $shortcode_section;
    }
}
if ( !function_exists('ovic_add_action_to_multi_currency_ajax') ) {
    function ovic_add_action_to_multi_currency_ajax( $ajax_actions )
    {
        $ajax_actions[] = 'ovic_ajax_tabs'; // Add a AJAX action to the array

        return $ajax_actions;
    }
}
if ( !function_exists('ovic_get_tabs_shortcode') ) {
    function ovic_get_tabs_shortcode()
    {
        $response = array(
            'html'    => '',
            'message' => '',
            'success' => 'no',
        );
        check_ajax_referer('ovic_ajax_frontend', 'security');
        $section_id = isset($_POST['section_id']) ? $_POST['section_id'] : '';
        $id         = isset($_POST['id']) ? $_POST['id'] : '';
        WPBMap::addAllMappedShortcodes();
        $response['html']    = wpb_js_remove_wpautop(ovic_detected_shortcode($id, $section_id));
        $response['success'] = 'ok';
        wp_send_json($response);
        die();
    }
}
if ( !function_exists('ovic_header_language') ) {
    function ovic_header_language()
    {
        $current_language = '';
        $list_language    = '';
        $menu_language    = '';
        $languages        = apply_filters('wpml_active_languages', null, 'skip_missing=0');
        if ( !empty($languages) ) {
            foreach ( $languages as $l ) {
                if ( !$l['active'] ) {
                    $list_language .= '
						<li class="menu-item">
                            <a href="' . esc_url($l['url']) . '">
                                <img src="' . esc_url($l['country_flag_url']) . '" height="12"
                                     alt="' . esc_attr($l['language_code']) . '" width="18"/>
								' . esc_html($l['native_name']) . '
                            </a>
                        </li>';
                } else {
                    $current_language = '
						<a href="' . esc_url($l['url']) . '" data-ovic="ovic-dropdown">
                            <img src="' . esc_url($l['country_flag_url']) . '" height="12"
                                 alt="' . esc_attr($l['language_code']) . '" width="18"/>
							' . esc_html($l['native_name']) . '
                        </a>
                        <span class="toggle-submenu"></span>';
                }
            }
            $menu_language .= '
                 <li class="menu-item ovic-dropdown block-language">
                    ' . $current_language . '
                    <ul class="sub-menu">
                        ' . $list_language . '
                    </ul>
                </li>';
            if ( class_exists('woocommerce_wpml') ) {
                $menu_language .= '<li class="menu-item block-currency">' . do_shortcode('[currency_switcher format="%code%" switcher_style="wcml-dropdown"]') . '</li>';
            }
        }
        echo wp_specialchars_decode($menu_language);
    }
}
if ( !function_exists('ovic_user_link') ) {
    function ovic_user_link()
    {
        $myaccount_link = wp_login_url();
        $currentUser    = wp_get_current_user();
        if ( class_exists('WooCommerce') ) {
            $myaccount_link = get_permalink(get_option('woocommerce_myaccount_page_id'));
        }
        ?>
        <li class="menu-item block-userlink ovic-dropdown">
            <?php if ( is_user_logged_in() ): ?>
                <a data-ovic="ovic-dropdown" class="woo-wishlist-link logged"
                   href="<?php echo esc_url($myaccount_link); ?>">
                    <span class="fa fa-user icon"></span>
                    <span class="text"><?php echo esc_html($currentUser->display_name); ?></span>
                </a>
                <?php if ( function_exists('wc_get_account_menu_items') ): ?>
                    <ul class="sub-menu">
                        <?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
                            <li class="menu-item <?php echo wc_get_account_menu_item_classes($endpoint); ?>">
                                <a href="<?php echo esc_url(wc_get_account_endpoint_url($endpoint)); ?>"><?php echo esc_html($label); ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <ul class="sub-menu">
                        <li class="menu-item">
                            <a href="<?php echo wp_logout_url(get_permalink()); ?>"><?php esc_html_e('Logout',
                                    'ovic-toolkit'); ?></a>
                        </li>
                    </ul>
                <?php endif;
            else: ?>
                <a class="woo-wishlist-link" href="<?php echo esc_url($myaccount_link); ?>">
                    <span class="fa fa-user icon"></span>
                    <span class="text"><?php echo esc_html__('Login', 'ovic-toolkit'); ?></span>
                </a>
            <?php endif; ?>
        </li>
        <?php
    }
}
if ( !function_exists('ovic_search_form') ) {
    function ovic_search_form()
    {
        $selected = '';
        if ( isset($_GET['product_cat']) && $_GET['product_cat'] ) {
            $selected = $_GET['product_cat'];
        }
        $args = array(
            'show_option_none'  => esc_html__('All Categories', 'ovic-toolkit'),
            'taxonomy'          => 'product_cat',
            'class'             => 'category-search-option',
            'hide_empty'        => 1,
            'orderby'           => 'name',
            'order'             => 'ASC',
            'tab_index'         => true,
            'hierarchical'      => true,
            'id'                => rand(),
            'name'              => 'product_cat',
            'value_field'       => 'slug',
            'selected'          => $selected,
            'option_none_value' => '0',
        );
        ?>
        <div class="block-search">
            <form role="search" method="get" action="<?php echo esc_url(home_url('/')) ?>"
                  class="form-search block-search ovic-live-search-form">
                <?php if ( class_exists('WooCommerce') ): ?>
                    <input type="hidden" name="post_type" value="product"/>
                    <input type="hidden" name="taxonomy" value="product_cat">
                    <div class="category">
                        <?php wp_dropdown_categories($args); ?>
                    </div>
                <?php else: ?>
                    <input type="hidden" name="post_type" value="post"/>
                <?php endif; ?>
                <div class="form-content search-box results-search">
                    <div class="inner">
                        <input autocomplete="off" type="text" class="searchfield txt-livesearch input" name="s"
                               value="<?php echo esc_attr(get_search_query()); ?>"
                               placeholder="<?php echo esc_attr__('I&#39;m searching for...', 'ovic-toolkit'); ?>">
                    </div>
                </div>
                <button type="submit" class="btn-submit">
                    <span class="fa fa-search" aria-hidden="true"></span>
                </button>
            </form><!-- block search -->
        </div>
        <?php
    }
}
if ( !function_exists('ovic_header_vertical') ) {
    function ovic_header_vertical( $menu_location, $mobile_active = false )
    {
        global $post;
        /* MAIN THEME OPTIONS */
        $enable_vertical = apply_filters('ovic_get_option', 'ovic_enable_vertical_menu');
        $block_vertical  = apply_filters('ovic_get_option', 'ovic_block_vertical_menu');
        $item_visible    = apply_filters('ovic_get_option', 'ovic_vertical_item_visible', 10);
        if ( $enable_vertical == 1 && has_nav_menu($menu_location) ) : ?>
            <?php
            /* MAIN THEME OPTIONS */
            $vertical_title        = apply_filters('ovic_get_option', 'ovic_vertical_menu_title',
                esc_html__('CATEGORIES', 'ovic-toolkit'));
            $vertical_button_all   = apply_filters('ovic_get_option', 'ovic_vertical_menu_button_all_text',
                esc_html__('All Categories', 'ovic-toolkit'));
            $vertical_button_close = apply_filters('ovic_get_option', 'ovic_vertical_menu_button_close_text',
                esc_html__('Close', 'ovic-toolkit'));
            $block_class           = array( 'vertical-wrapper block-nav-category' );
            $id                    = '';
            $post_type             = '';
            if ( $enable_vertical == 1 ) {
                $block_class[] = 'has-vertical-menu';
            }
            if ( isset($post->ID) ) {
                $id = $post->ID;
            }
            if ( isset($post->post_type) ) {
                $post_type = $post->post_type;
            }
            if ( is_array($block_vertical) && in_array($id, $block_vertical) && $post_type == 'page' ) {
                $block_class[] = 'always-open';
            }
            $locations  = get_nav_menu_locations();
            $menu_id    = $locations[$menu_location];
            $menu_items = wp_get_nav_menu_items($menu_id);
            $count      = 0;
            foreach ( $menu_items as $menu_item ) {
                if ( $menu_item->menu_item_parent == 0 ) {
                    $count++;
                }
            }
            ?>
            <!-- block category -->
            <div data-items="<?php echo esc_attr($item_visible); ?>"
                 class="<?php echo implode(' ', $block_class); ?>">
                <div class="block-title">
                    <span class="before">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                    <span class="text-title"><?php echo esc_html($vertical_title); ?></span>
                </div>
                <div class="block-content verticalmenu-content">
                    <?php
                    wp_nav_menu(array(
                            'menu'            => $menu_location,
                            'theme_location'  => $menu_location,
                            'depth'           => 4,
                            'container'       => '',
                            'container_class' => '',
                            'container_id'    => '',
                            'menu_class'      => 'ovic-nav vertical-menu',
                            'megamenu_layout' => 'vertical',
                            'mobile_enable'   => $mobile_active,
                        )
                    );
                    if ( $count > $item_visible ) : ?>
                        <div class="view-all-category">
                            <a href="#" data-closetext="<?php echo esc_attr($vertical_button_close); ?>"
                               data-alltext="<?php echo esc_attr($vertical_button_all) ?>"
                               class="btn-view-all open-cate"><?php echo esc_html($vertical_button_all) ?></a>
                        </div>
                    <?php endif; ?>
                </div>
            </div><!-- block category -->
        <?php endif;
    }
}
if ( !function_exists('ovic_share_button') ) {
    function ovic_share_button( $post_id )
    {
        $share_image_url = wp_get_attachment_image_url(get_post_thumbnail_id($post_id), 'full');
        $share_link_url  = get_permalink($post_id);
        $share_summary   = get_the_excerpt();
        $twitter         = 'https://twitter.com/share?url=' . $share_link_url . '&text=' . $share_summary;
        $facebook        = 'https://www.facebook.com/sharer.php?u=' . $share_link_url;
        $pinterest       = 'https://pinterest.com/pin/create/button/?url=' . $share_link_url . '&description=' . $share_summary . '&media=' . $share_image_url;
        ?>
        <div class="ovic-share-socials">
            <a class="twitter"
               href="<?php echo esc_url($twitter); ?>"
               title="<?php echo esc_attr__('Twitter', 'ovic-toolkit') ?>"
               onclick='window.open(this.href, "", "menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600");return false;'>
                <figure>
                    <img src="<?php echo OVIC_FRAMEWORK_URI . "assets/images/share-twitter.png" ?>"
                         alt="<?php echo esc_attr__('Twitter', 'ovic-toolkit') ?>">
                </figure>
            </a>
            <a class="facebook"
               href="<?php echo esc_url($facebook); ?>"
               title="<?php echo esc_attr__('Facebook', 'ovic-toolkit') ?>"
               onclick='window.open(this.href, "", "menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600");return false;'>
                <figure>
                    <img src="<?php echo OVIC_FRAMEWORK_URI . "assets/images/share-facebook.png" ?>"
                         alt="<?php echo esc_attr__('Facebook', 'ovic-toolkit') ?>">
                </figure>
            </a>
            <a class="pinterest"
               href="<?php echo esc_url($pinterest); ?>"
               title="<?php echo esc_attr__('Pinterest', 'ovic-toolkit') ?>"
               onclick='window.open(this.href, "", "menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600");return false;'>
                <figure>
                    <img src="<?php echo OVIC_FRAMEWORK_URI . "assets/images/share-pin.png" ?>"
                         alt="<?php echo esc_attr__('Pinterest', 'ovic-toolkit') ?>">
                </figure>
            </a>
        </div>
        <?php
    }
}
if ( !function_exists('ovic_set_post_views') ) {
    function ovic_set_post_views( $postID )
    {
        if ( get_post_type($postID) === 'post' ) {
            $count_key = 'ovic_post_views_count';
            $count     = get_post_meta($postID, $count_key, true);
            if ( $count == '' ) {
                delete_post_meta($postID, $count_key);
                add_post_meta($postID, $count_key, '0');
            } else {
                $count++;
                update_post_meta($postID, $count_key, $count);
            }
        }
    }
}
if ( !function_exists('ovic_get_post_views') ) {
    function ovic_get_post_views( $postID )
    {
        $count_key = 'ovic_post_views_count';
        $count     = get_post_meta($postID, $count_key, true);
        if ( $count == '' ) {
            delete_post_meta($postID, $count_key);
            add_post_meta($postID, $count_key, '0');
            echo '0';
        }
        echo ovic_number_format_short($count);
    }
}
/**
 * @param $n
 *
 * @return string
 * Use to convert large positive numbers in to short form like 1K+, 100K+, 199K+, 1M+, 10M+, 1B+ etc
 */
if ( !function_exists('ovic_number_format_short') ) {
    function ovic_number_format_short( $n )
    {
        if ( $n >= 0 && $n < 1000 ) {
            // 1 - 999
            $n_format = floor($n);
            $suffix   = '';
        } else {
            if ( $n >= 1000 && $n < 1000000 ) {
                // 1k-999k
                $n_format = floor($n / 1000);
                $suffix   = 'K+';
            } else {
                if ( $n >= 1000000 && $n < 1000000000 ) {
                    // 1m-999m
                    $n_format = floor($n / 1000000);
                    $suffix   = 'M+';
                } else {
                    if ( $n >= 1000000000 && $n < 1000000000000 ) {
                        // 1b-999b
                        $n_format = floor($n / 1000000000);
                        $suffix   = 'B+';
                    } else {
                        if ( $n >= 1000000000000 ) {
                            // 1t+
                            $n_format = floor($n / 1000000000000);
                            $suffix   = 'T+';
                        }
                    }
                }
            }
        }

        return !empty($n_format) ? $n_format . $suffix : 0;
    }
}
if ( !function_exists('ovic_generate_class_nav') ) {
    function ovic_generate_class_nav( $prefix, $atts, $count )
    {
        $class = array();
        if ( isset($atts[$prefix . 'ts_items']) && $atts[$prefix . 'ts_items'] >= $count ) {
            $class[] = 'empty_nav_ts';
        }
        if ( isset($atts[$prefix . 'xs_items']) && $atts[$prefix . 'xs_items'] >= $count ) {
            $class[] = 'empty_nav_xs';
        }
        if ( isset($atts[$prefix . 'sm_items']) && $atts[$prefix . 'sm_items'] >= $count ) {
            $class[] = 'empty_nav_sm';
        }
        if ( isset($atts[$prefix . 'md_items']) && $atts[$prefix . 'md_items'] >= $count ) {
            $class[] = 'empty_nav_md';
        }
        if ( isset($atts[$prefix . 'lg_items']) && $atts[$prefix . 'lg_items'] >= $count ) {
            $class[] = 'empty_nav_lg';
        }
        if ( isset($atts[$prefix . 'ls_items']) && $atts[$prefix . 'ls_items'] >= $count ) {
            $class[] = 'empty_nav_ls';
        }

        return implode(' ', $class);
    }
}
/* GET OPTIONS */
if ( !function_exists('ovic_blog_options') ) {
    add_filter('ovic_blog_options', 'ovic_blog_options');
    function ovic_blog_options()
    {
        $blog_options = array();
        $layoutDir    = get_template_directory() . OVIC_BLOG_PATH;
        if ( is_dir($layoutDir) ) {
            $files = scandir($layoutDir);
            if ( $files && is_array($files) ) {
                foreach ( $files as $file ) {
                    if ( $file != '.' && $file != '..' ) {
                        $fileInfo = pathinfo($file);
                        if ( $fileInfo['extension'] == 'php' && $fileInfo['basename'] != 'index.php' ) {
                            $file_data                = get_file_data($layoutDir . $file, array( 'Name' => 'Name' ));
                            $file_name                = str_replace('content-blog-', '', $fileInfo['filename']);
                            $blog_options[$file_name] = array(
                                'title'   => $file_data['Name'],
                                'preview' => get_theme_file_uri(OVIC_BLOG_PATH . 'content-blog-' . $file_name . '.jpg'),
                            );
                        }
                    }
                }
            }
        }

        return $blog_options;
    }
}
if ( !function_exists('ovic_pinmapper_options') ) {
    add_filter('ovic_pinmapper_options', 'ovic_pinmapper_options');
    function ovic_pinmapper_options()
    {
        $args           = array(
            'post_type'      => 'ovic_mapper',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        );
        $pinmap_options = array();
        $posts          = get_posts($args);
        if ( !empty($posts) ) {
            foreach ( $posts as $post ) {
                setup_postdata($post);
                $attachment_id             = get_post_meta($post->ID, 'ovic_mapper_image', true);
                $pinmap_options[$post->ID] = array(
                    'title'   => $post->post_title,
                    'preview' => wp_get_attachment_image_url($attachment_id, 'medium'),
                );
            }
        }
        wp_reset_postdata();

        return $pinmap_options;
    }
}
if ( !function_exists('ovic_social_option') ) {
    add_filter('ovic_social_option', 'ovic_social_option');
    function ovic_social_option()
    {
        $socials     = array();
        $all_socials = apply_filters('ovic_get_option', 'user_all_social');
        if ( !empty($all_socials) ) {
            foreach ( $all_socials as $key => $social ) {
                if ( !empty($social['title_social']) ) {
                    $socials[$key] = $social['title_social'];
                }
            }
        }

        return $socials;
    }
}
if ( !function_exists('ovic_sidebar_options') ) {
    add_filter('ovic_sidebar_options', 'ovic_sidebar_options');
    function ovic_sidebar_options()
    {
        $sidebars = array();
        global $wp_registered_sidebars;
        foreach ( $wp_registered_sidebars as $sidebar ) {
            $sidebars[$sidebar['id']] = $sidebar['name'];
        }

        return $sidebars;
    }
}
if ( !function_exists('ovic_product_options') ) {
    add_filter('ovic_product_options', 'ovic_product_options');
    function ovic_product_options( $allow = 'Theme Option' )
    {
        $layoutDir       = get_template_directory() . OVIC_PRODUCT_PATH;
        $product_options = array();
        if ( is_dir($layoutDir) ) {
            $files = scandir($layoutDir);
            if ( $files && is_array($files) ) {
                foreach ( $files as $file ) {
                    if ( $file != '.' && $file != '..' ) {
                        $fileInfo  = pathinfo($file);
                        $file_data = get_file_data($layoutDir . $file,
                            array(
                                'Name'         => 'Name',
                                'Slug'         => 'Slug',
                                'Theme Option' => 'Theme Option',
                                'Shortcode'    => 'Shortcode',
                            )
                        );
                        $file_name = str_replace('content-product-style-', '', $fileInfo['filename']);
                        if ( $fileInfo['extension'] == 'php' && $fileInfo['basename'] != 'index.php' && $file_data[$allow] == 'true' ) {
                            $product_options[$file_name] = array(
                                'title'   => $file_data['Name'],
                                'preview' => get_theme_file_uri('woocommerce/product-styles/content-product-style-' . $file_name . '.jpg'),
                            );
                        }
                    }
                }
            }
        }

        return $product_options;
    }
}
if ( !function_exists('ovic_attributes_options') ) {
    add_filter('ovic_attributes_options', 'ovic_attributes_options');
    function ovic_attributes_options()
    {
        $attributes     = array();
        $attributes_tax = array();
        if ( function_exists('wc_get_attribute_taxonomies') ) {
            $attributes_tax = wc_get_attribute_taxonomies();
        }
        if ( is_array($attributes_tax) && count($attributes_tax) > 0 ) {
            foreach ( $attributes_tax as $attribute ) {
                $attributes[$attribute->attribute_name] = $attribute->attribute_label;
            }
        }

        return $attributes;
    }
}
if ( !function_exists('ovic_install_widget') ) {
    function ovic_install_widget( $widget )
    {
        register_widget($widget);
    }
}
if ( !function_exists('ovic_install_taxonomy') ) {
    function ovic_install_taxonomy( $taxonomy )
    {
        $labels = array(
            'name'                       => $taxonomy['name'],
            'singular_name'              => $taxonomy['name'],
            'search_items'               => sprintf(esc_html__('Search %s', 'ovic-toolkit'), $taxonomy['name']),
            'popular_items'              => sprintf(esc_html__('Popular %s', 'ovic-toolkit'), $taxonomy['name']),
            'all_items'                  => sprintf(esc_html__('All %s', 'ovic-toolkit'), $taxonomy['name']),
            'parent_item'                => null,
            'parent_item_colon'          => null,
            'edit_item'                  => sprintf(esc_html__('Edit %s', 'ovic-toolkit'), $taxonomy['name']),
            'update_item'                => sprintf(esc_html__('Update %s', 'ovic-toolkit'), $taxonomy['name']),
            'add_new_item'               => sprintf(esc_html__('Add New %s', 'ovic-toolkit'), $taxonomy['name']),
            'new_item_name'              => sprintf(esc_html__('New %s Name', 'ovic-toolkit'), $taxonomy['name']),
            'separate_items_with_commas' => sprintf(esc_html__('Separate %s with commas', 'ovic-toolkit'),
                $taxonomy['name']),
            'add_or_remove_items'        => sprintf(esc_html__('Add or remove %s', 'ovic-toolkit'), $taxonomy['name']),
            'choose_from_most_used'      => sprintf(esc_html__('Choose from the most used %s', 'ovic-toolkit'),
                $taxonomy['name']),
            'not_found'                  => sprintf(esc_html__('No %s found.', 'ovic-toolkit'), $taxonomy['name']),
            'menu_name'                  => $taxonomy['name'],
        );
        $args   = array(
            'hierarchical'          => false,
            'labels'                => $labels,
            'show_ui'               => true,
            'show_admin_column'     => true,
            'update_count_callback' => '_update_post_term_count',
            'query_var'             => true,
            'rewrite'               => array( 'slug' => $taxonomy['slug'] ),
        );
        $args   = apply_filters("ovic_args_taxonomy_{$taxonomy['slug']}", $args);
        register_taxonomy($taxonomy['slug'], $taxonomy['object'], $args);
    }
}
if ( !function_exists('ovic_install_post_type') ) {
    function ovic_install_post_type( $posttype )
    {
        $labels = array(
            'name'               => $posttype['name'],
            'singular_name'      => $posttype['name'],
            'menu_name'          => $posttype['name'],
            'name_admin_bar'     => $posttype['name'],
            'add_new'            => esc_html__('Add New', 'ovic-toolkit'),
            'add_new_item'       => sprintf(esc_html__('Add New %s', 'ovic-toolkit'), $posttype['name']),
            'new_item'           => sprintf(esc_html__('New %s', 'ovic-toolkit'), $posttype['name']),
            'edit_item'          => sprintf(esc_html__('Edit %s', 'ovic-toolkit'), $posttype['name']),
            'view_item'          => sprintf(esc_html__('View %s', 'ovic-toolkit'), $posttype['name']),
            'all_items'          => sprintf(esc_html__('All %s', 'ovic-toolkit'), $posttype['name']),
            'search_items'       => sprintf(esc_html__('Search %s', 'ovic-toolkit'), $posttype['name']),
            'parent_item_colon'  => sprintf(esc_html__('Parent %s:', 'ovic-toolkit'), $posttype['name']),
            'not_found'          => sprintf(esc_html__('No %s found.', 'ovic-toolkit'), $posttype['name']),
            'not_found_in_trash' => sprintf(esc_html__('No %s found in Trash.', 'ovic-toolkit'), $posttype['name']),
        );
        $args   = array(
            'labels'             => $labels,
            'description'        => '',
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => $posttype['slug'] ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 4,
            'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' ),
        );
        $args   = apply_filters("ovic_args_posttype_{$posttype['slug']}", $args);
        register_post_type($posttype['slug'], $args);
    }
}