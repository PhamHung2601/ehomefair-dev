<?php
/**
 * Marketplace Admin Advance Settings Class.
 *
 * @author   webkul
 * @category Admin
 * @package  webkul/Admin/settings
 * @version  4.7.1
 */

if (! defined('ABSPATH') ) {
    exit;
}

$endpoints_array = array(
    array(
        'slug'  => esc_attr('dashboard'),
        'title' => esc_html__('Dashboard', 'marketplace'),
    ),
    array(
        'slug'  => esc_attr('product-list'),
        'title' => esc_html__('Product List', 'marketplace'),
    ),
    array(
        'slug'  => esc_attr('add-product'),
        'title' => esc_html__('Add Product', 'marketplace'),
    ),
    array(
        'slug'  => esc_attr('order-history'),
        'title' => esc_html__('Order History', 'marketplace'),
    ),
    array(
        'slug'  => esc_attr('transaction'),
        'title' => esc_html__('Transaction', 'marketplace'),
    ),
    array(
        'slug'  => esc_attr('shipping'),
        'title' => esc_html__('Shipping', 'marketplace'),
    ),
    array(
        'slug'  => esc_attr('profile'),
        'title' => esc_html__('Seller profile', 'marketplace'),
    ),
    array(
        'slug'  => esc_attr('notification'),
        'title' => esc_html__('Notification', 'marketplace'),
    ),
    array(
        'slug'  => esc_attr('shop-follower'),
        'title' => esc_html__('Shop Follower', 'marketplace'),
    ),
    array(
        'slug'  => esc_attr('to'),
        'title' => esc_html__('Ask to Admin', 'marketplace'),
    ),
    array(
        'slug'  => esc_attr( 'seller-product' ),
        'title' => esc_html__( 'Product from Seller', 'marketplace' ), 
    ),
    array( 
        'slug'  => esc_attr( 'store' ),
        'title' => esc_html__( 'Recent Product from Seller', 'marketplace' ),
    ),
);

?>

<?php settings_errors(); ?>
<h1><?php echo esc_html__('Marketplace Account endpoints', 'marketplace'); ?></h1>

<p>
<?php
echo esc_html__('Endpoints are appended to your page URLs to handle specific actions on the accounts pages. They should be unique.', 'marketplace');
?>
</p>
<form method="post"  action="options.php">

<?php settings_fields('marketplace-seller-advanced-setting-group'); ?>

<?php foreach ($endpoints_array as $key => $value) {
    $name = str_replace('-', '_', $value['slug']);
    ?>
    <fieldset class="mp-fieldset">
        <legend><?php echo esc_html(wc_strtoupper($value['title'])); ?></legend>
        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row">
                        <label for=""><?php echo esc_html__('Endpoint', 'marketplace'); ?></label>
                    </th>
                    <td class="forminp">
                        <?php echo wc_help_tip(esc_html__('Endpoint for the "my Account → ' . $value['title'] . '" page.', 'marketplace')); ?>
                        <input type="text" class="regular-text mp-endpoints-text" name="mp_<?php echo esc_attr($name);?>" value="<?php echo esc_attr(get_option('mp_' . $name, $value['slug'])); ?>" required>
                    </td>
                </tr>
                </tr>
                    <th scope="row">
                        <label for=""><?php echo esc_html__('Title', 'marketplace'); ?></label>
                    </th>
                    <td class="forminp">
                        <?php echo wc_help_tip(esc_html__('Title for the "my Account → ' . $value['title'] . '" page.', 'marketplace')); ?>
                        <input type="text" class="regular-text" name="mp_<?php echo esc_attr($name);?>_name" value="<?php echo esc_attr(get_option('mp_' . $name . '_name', esc_attr($value['title']))); ?>" required>
                    </td>
                </tr>
            </tbody>
        </table>
    </fieldset>
    <?php
}
submit_button();
?>
</form>