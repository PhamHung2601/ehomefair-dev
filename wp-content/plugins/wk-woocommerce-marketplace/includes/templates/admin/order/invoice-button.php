<?php

if( ! defined ( 'ABSPATH' ) )

    exit;



// do not show buttons for trashed orders
if ( $order->get_status() == 'trash' ) {
	return;
}

$listing_actions = array(
	 'invoice'		=> array (
	 'name'		=> 'Invoice',
	 'alt'		=> 'Invoice',
	 'url'		=>  wp_nonce_url(admin_url( 'edit.php?page=invoice&order_id=' . base64_encode($order->get_id()) ), 'generate_invoice', 'invoice_nonce')
	)
);

foreach ($listing_actions as $action => $data) {
	?>
	<a href="<?php echo $data['url']; ?>" class="button <?php echo $action; ?>" target="_blank" title="<?php echo $data['alt']; ?>"><?php echo $data['name']; ?>
	</a>
	<?php
}
