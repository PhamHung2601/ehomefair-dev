<div class="wrap woocommerce">

	<div class="icon32" id="icon-woocommerce-importer"><br></div>

	<h2 class="nav-tab-wrapper woo-nav-tab-wrapper">

		<a href="<?php echo admin_url('admin.php?page=import_shipment_tracking_csv') ?>" class="nav-tab <?php echo ($tab == 'settings') ? 'nav-tab-active' : ''; ?>">
			<?php _e('Auto Import', 'woocommerce-shipment-tracking'); ?>
		</a>

		<a href="<?php echo admin_url('admin.php?import=import_shipment_tracking_csv') ?>" class="nav-tab <?php echo ($tab == 'manual_importer') ? 'nav-tab-active' : ''; ?>">
			<?php _e('Manual Import', 'woocommerce-shipment-tracking'); ?>
		</a>
	</h2>

	<?php
	switch ($tab) {

		case "settings" :
		$this->admin_settings_page();
		break;
		case "manual_importer" :
		break;
		default :
		$this->admin_settings_page();
		break;

	}
	?>
</div>