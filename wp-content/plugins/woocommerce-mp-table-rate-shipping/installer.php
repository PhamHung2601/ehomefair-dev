<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

$wpdb->hide_errors();

$collate = '';

if ( $wpdb->has_cap( 'collation' ) ) {
	$collate = $wpdb->get_charset_collate();
}

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

// Table for storing table rates themselves. shipping_method_id is an individual table of rates applied to a zone
$sql = "
CREATE TABLE {$wpdb->prefix}woocommerce_table_rate_shipping (
shipping_id bigint(20) NOT NULL auto_increment,
seller_id bigint(20) NOT NULL,
shipping_label longtext NULL,
shipping_zone varchar(200) NULL,
shipping_basis varchar(200) NOT NULL,
shipping_min varchar(200) NOT NULL,
shipping_max varchar(200) NOT NULL,
shipping_cost bigint(20) NOT NULL,
PRIMARY KEY  (shipping_id)
) $collate;
";
dbDelta( $sql );
