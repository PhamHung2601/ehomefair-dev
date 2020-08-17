<?php

if (! defined('ABSPATH') ) {
    exit;
}

if (! function_exists('mp_price') ) {
    function mp_price( $value ) 
    {
        if (! function_exists('woocommerce_price') || 'WC_IS_MIS_WC_ACITVE' === false ) {
            return apply_filters('mp_currency_symbol', '&#36;', 'USD') . $value;
        } else {
            return wc_price($value);
        }
    }
}

function product_list() 
{
    global $wp_query;
    $page_no = get_query_var('pagenum');
    $current_page = !empty($page_no) ? intval($page_no) : 1; 
    
    $offset = ($current_page -1) * 10;
    
?> 
<div class="woocommerce-account">
    <?php
    do_action('mp_get_wc_account_menu', 'marketplace');
    ?>
    <div id="main_container" class="woocommerce-MyAccount-content">

    <?php

    global $wpdb, $wp_query;

    $user_id = get_current_user_id();

    $wpmp_pid = '';

    $mainpage = get_query_var('main_page');

    $p_id = get_query_var('pid');

    $action = get_query_var('action');

    if (! empty($p_id) ) {
        $wpmp_pid = $p_id;
    }
    $product_auth = $wpdb->prepare("SELECT post_author from $wpdb->posts where ID = %d", $wpmp_pid);
    $product_auth = $wpdb->get_var($product_auth);

    if (! empty($mainpage) && ! empty($action) && $action == 'delete' ) {

        if (! isset($_GET['_mp_delete_nonce']) || ! wp_verify_nonce($_GET['_mp_delete_nonce'], 'marketplace-product-delete-nonce-action') ) {
            wc_add_notice(__('Security check failed!!!', 'marketplace'), 'error');
            wp_redirect(get_permalink() . get_option('mp_product_list', 'product-list'));
            exit;
        } else {
            if (get_option('mp_product_list', 'product-list') === $mainpage && 'delete' === $action && intval($product_auth) === $user_id ) {
                $delete_product_name = get_the_title($wpmp_pid);

                if (delete_post_meta($wpmp_pid, '_sku') ) {
                    delete_post_meta($wpmp_pid, '_regular_price');
                    delete_post_meta($wpmp_pid, '_sale_price');
                    delete_post_meta($wpmp_pid, '_price');
                    delete_post_meta($wpmp_pid, '_sale_price_dates_from');
                    delete_post_meta($wpmp_pid, '_sale_price_dates_to');
                    delete_post_meta($wpmp_pid, '_downloadable');
                    delete_post_meta($wpmp_pid, '_virtual');

                    $delete_check = wp_delete_post($wpmp_pid);
                    if ($delete_check ) {
                        wc_add_notice($delete_product_name . __(' deleted successfully.', 'marketplace'), 'success');
                        wp_redirect(get_permalink() . get_option('mp_product_list', 'product-list'));
                        exit;
                    }
                }
            }
        }
    }
    
    $total_product_query = $wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_type = 'product' and ( post_status = 'draft' or post_status = 'publish' ) and post_author = '%d' ORDER BY ID DESC", $user_id);
    
    $post_data = $_GET;
    
    if (isset($post_data['wkmp_search']) && !empty($post_data['wkmp_search']) ) {
        $p_search  = strip_tags($post_data['wkmp_search']);
        if (!empty($p_search) && wp_verify_nonce(wp_unslash($post_data['wcqp_nonce_product_search_nonce']), 'wcqp_nonce_product_search')) {
            
            $total_product_query = $wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_type = 'product' and ( post_status = 'draft' or post_status = 'publish' ) and post_author = '%d' AND post_title LIKE %s ORDER BY ID DESC", $user_id, '%'.$p_search.'%');
            
            $product_query = $wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_type = 'product' and ( post_status = 'draft' or post_status = 'publish' ) and post_author = '%d' AND post_title LIKE %s ORDER BY ID DESC", $user_id, '%'.$p_search.'%');
            
        } else {
            
            $product_query = $wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_type = 'product' and ( post_status = 'draft' or post_status = 'publish' ) and post_author = '%d' ORDER BY ID DESC LIMIT %d, 10 ", $user_id, $offset);
        }
    } else {
        
        $product_query = $wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_type = 'product' and ( post_status = 'draft' or post_status = 'publish' ) and post_author = '%d' ORDER BY ID DESC LIMIT %d, 10 ", $user_id, $offset);
    }
    

    $product = $wpdb->get_results($product_query);

    $total_products = $wpdb->get_results($total_product_query);
    $product_count = 0;
                    
    if (!empty($total_products)) {
        $product_count = count($total_products);
    }

    ?>
        <form method="get" id="wkmp-product-list-form">
            <div class="wkmp-table-action-wrap">
                <div class="wkmp-action-div">
                    <input type="text" name="wkmp_search" placeholder="<?php esc_html_e('Search by Product Name', 'marketplace'); ?>">
                    <?php wp_nonce_field('wcqp_nonce_product_search', 'wcqp_nonce_product_search_nonce'); ?>
                    <input type="submit" value="<?php esc_html_e('Search', 'marketplace'); ?>" data-action="search"/>
                </div>
                <div class="wkmp-action-div">
                    <button id="wkmp-bulk-delete" data-action="bulk" class="button"><?php esc_html_e('Delete', 'marketplace'); ?></button>&nbsp;&nbsp;
                    <a href="<?php echo esc_url(get_permalink() . get_option('mp_add_product', 'add-product')); ?>" class="button add-product"><?php esc_html_e('Add Product', 'marketplace'); ?></a>
                </div>
            </div>
            <div class="wkmp-overflowx-auto">

                <table class="productlist">
                    <thead>
                        <tr>
                            <th width="5%"></th>
                            <th width="20%"><?php esc_html_e('Product Name', 'marketplace'); ?></th>
                            <th width="15%"><?php esc_html_e('Image', 'marketplace'); ?></th>
                            <th width="15%"><?php esc_html_e('Stock', 'marketplace'); ?></th>
                            <th width="15%"><?php esc_html_e('Product Status', 'marketplace'); ?></th>
                            <th width="15%"><?php esc_html_e('Price', 'marketplace'); ?></th>
                            <th width="15%"><?php esc_html_e('Action', 'marketplace'); ?></th>
                        </tr>
                    </thead>
    
                    <tbody>
                <?php
                $page_name_query = $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_name = %s and post_type = 'page' ", get_option('wkmp_seller_page_title'));
                
                $page_name = $wpdb->get_var($page_name_query);
                
                $wpmp_obj2 = new MP_Form_Handler();
                
                $translated_strings = array(
                    'instock' => esc_html__('In Stock', 'marketplace'),
                    'outofstock' => esc_html__('Out of Stock', 'marketplace'),
                    'publish' => esc_html__('Publish', 'marketplace'),
                    'draft' => esc_html__('Draft', 'marketplace'),
                );
                
                foreach ( $product as $pro ) {
                    $prod = wc_get_product($pro->ID);
                    $symbol = get_woocommerce_currency_symbol();
                    $product_price = $prod->get_price_html();
                    $product_stock = get_post_meta($pro->ID, '_stock_status', true);
                    $product_stock = !empty($translated_strings[$product_stock]) ? $translated_strings[$product_stock] : $product_stock;
                    $stock_remain = get_post_meta($pro->ID, '_stock', true);
                    $product_image = $wpmp_obj2->get_product_image($pro->ID, '_thumbnail_id');
                
                    if ($prod->is_type('variable') ) {
                        $symbol = get_woocommerce_currency_symbol();
                        $product_price = '';
                
                        if (! empty(get_post_meta($pro->ID, '_min_variation_price', true)) && ! empty(get_post_meta($pro->ID, '_max_variation_price', true)) ) {
                            $product_price = $symbol . get_post_meta($pro->ID, '_min_variation_price', true) . ' - ' . $symbol . get_post_meta($pro->ID, '_max_variation_price', true);
                        }
                    }
                    $product_status = !empty($translated_strings[$pro->post_status]) ? $translated_strings[$pro->post_status] : $pro->post_status;
                ?>
    
                <tr>
                    <td><?php echo '<input type="checkbox" value="' . $pro->ID . '"  /> '; ?></td>
                    <td>
                        <?php if( strtolower( $product_status ) == 'draft' ) {
                            echo esc_attr( $pro->post_title );
                        } else{ ?>
                        <a href="<?php echo get_permalink($pro->ID); ?>"><?php echo $pro->post_title; ?></a>
                        <?php } ?>
                    </td>
                    
                    <td class="wkmp-data-optional" data-name="<?php esc_html_e('Image', 'marketplace'); ?>">
                        <img class="wkmp_productlist_img" alt="<?php echo $pro->post_title; ?>" title="<?php echo $pro->post_title; ?>" src="<?php
                        if ('' !== $product_image ) {
                            echo content_url() . '/uploads/' . $product_image;
                        } else {
                            echo WK_MARKETPLACE . 'assets/images/placeholder.png';
                        }?>" width="50" height="50">
                    </td>

                    <td data-name="<?php esc_html_e('Stock', 'marketplace'); ?>">
                    <?php echo ( isset($product_stock) && ! empty($product_stock) ) ? $product_stock : '-'; ?>
                    </td>

                    
            
                    <td data-name="<?php esc_html_e('Product Status', 'marketplace'); ?>">
                    <?php echo esc_attr( $product_status ); ?>
                    </td>
            
                    <td data-name="<?php esc_html_e('Price', 'marketplace'); ?>">
                    <?php
                    if ('' !== $product_price ) {
                        echo $product_price;
                    } else {
                        echo '-';
                    }
                    ?>
                    </td>
            
                    
                            <td data-name="<?php esc_html_e('Action', 'marketplace'); ?>">
                                <a id="editprod" class="mp-action" href="<?php echo home_url(get_option('wkmp_seller_page_title') . '/product/edit/' . $pro->ID);?>"><?php esc_html_e('edit', 'marketplace'); ?></a>
                                <a id="delprod" class="mp-action" href="<?php echo wp_nonce_url(home_url(get_option('wkmp_seller_page_title') . '/'. get_option('mp_product_list', 'product-list').'/delete/' . $pro->ID), 'marketplace-product-delete-nonce-action', '_mp_delete_nonce'); ?>" class="ask" onclick="return confirm('<?php esc_html_e('Are you sure you want to delete this item?', 'marketplace'); ?>');">
                                <?php esc_html_e('delete', 'marketplace'); ?>
                                </a>
                            </td>
                        </tr>
                <?php
                }
                ?>
                    </tbody>
                </table>
            </div>
        </form>
    <?php
            $maxpage_count = ceil($product_count / 10);
            
    if (1 < $maxpage_count ) : 
    ?>
    <div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
        <?php
        if (1 !== $current_page ) :
        ?>
        <a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button" href="<?php echo esc_url(site_url(get_option('wkmp_seller_page_title').'/'.get_option('mp_product_list', 'product-list').'/page/'.($current_page - 1))); ?>">
            <?php esc_html_e('Previous', 'marketpalce'); ?>
        </a>
        <?php
        endif;
                
        if (intval($maxpage_count) !== $current_page ) :
        ?>
        <a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button" href="<?php echo esc_url(site_url(get_option('wkmp_seller_page_title').'/'.get_option('mp_product_list', 'product-list').'/page/'.($current_page + 1))); ?>">
            <?php _e('Next', 'marketplace'); ?>
                </a>
            <?php 
        endif;
    ?>
    </div>
    <?php
    endif;
        ?>
    </div>
</div>
    <?php
}
