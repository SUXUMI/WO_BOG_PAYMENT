<?php
/*
Plugin Name: BOG PAYMENT - Georgian Card
Plugin URI:
Description: BOG Payment Gateway
Version: 1.0
Author: Levan Qerdikashvili
Author URI: https://fb.com/levan.qerdikashvili
*/

add_action( 'plugins_loaded', 'georgian_card_init', 0 );
function georgian_card_init()
	{
		include 'georgian_card_class.php';
		add_filter( 'woocommerce_payment_gateways', 'add_georgian_card_gateway' );
		function add_georgian_card_gateway( $methods ) {
		$methods[] = 'WC_Gateway_georgian_card';
		return $methods;
		}
	}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'card_action_links' );


function card_action_links( $links )
	{
		$plugin_links = array(
		 '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout' ) . '">' . __( 'პარამეტრები', 'card' ) . '</a>',
		);
		return array_merge( $plugin_links, $links );
	}

function card_func( $atts )
	{
		if( isset($_GET['success']) ){
			$success = (int)$_GET['success'];
			if( $success == 1 ){
				echo '<p>Thank you, for your payment.</p>';
			} else {
				echo '<p>Payment failed.</p>';
			}
		}
	}
add_shortcode( 'card', 'card_func' );