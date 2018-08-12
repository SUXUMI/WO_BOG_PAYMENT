<?php
require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-config.php' );
if ( get_option('db_version') != $wp_db_version ) {
	wp_redirect(admin_url('upgrade.php?_wp_http_referer=' . urlencode(stripslashes($_SERVER['REQUEST_URI']))));
	exit;
}
global $wpdb;
header("Content-type: text/xml");
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
add_action( 'woocommerce_thankyou', 'my_change_status_function' );

function status_function( $order_id ) {

    $order = new WC_Order( $order_id );
    $order->update_status( 'completed' );

}
echo status_function($_GET["o_order_id"]);
echo '
		<register-payment-response>
  <result>
    <code>1</code>
    <desc>OK</desc>
  </result>
</register-payment-response>';
