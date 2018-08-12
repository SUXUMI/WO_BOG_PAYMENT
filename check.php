<?php
require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-config.php' );
if ( get_option('db_version') != $wp_db_version ) {
	wp_redirect(admin_url('upgrade.php?_wp_http_referer=' . urlencode(stripslashes($_SERVER['REQUEST_URI']))));
	exit;
}

  global $wpdb;
header("Content-type: text/xml");
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
$error=0;	
$api_merch_id = get_option('api_merch_id');
$api_account_id = get_option('api_account_id');

	if($_GET["merch_id"]!=""){
			$order = new WC_Order( $_GET["o_order_id"]);
			$items = $order->get_items();
			$order_meta = get_post_meta($_GET["o_order_id"]); 			
			$order_user_id=$order_meta["_customer_user"][0];
			
			if(!empty($items)){
				$amount=0;
				foreach ( $items as $item ) {
					#qty
					$name=$name.'; '.$item['name'];
					$amount=$amount+$item['line_total'];
				}
				$name=substr($name,1,125);
				$amount=$amount*100;
				
 

 
echo '
<payment-avail-response>
<result>
<code>1</code>
</result>
<merchant-trx>'.$_GET["o_order_id"].'</merchant-trx>
<purchase>
<shortDesc>Order: '.$_GET["o_order_id"].'</shortDesc>
<longDesc>'.$name.'</longDesc>
<account-amount>
<amount>'.$amount.'</amount>
<currency>981</currency>
<exponent>2</exponent>
</account-amount>
</purchase>

</payment-avail-response>
 
';

			}else{
				$error=2;	
			}
		}else{
			$error=1;	
		}
		
		
		
		if($error==1){
			echo '
			<payment-avail-response>
			  <result>
				<code>2</code> <!-- რეზულტატის კოდი: მაღაზიას არ შეუძია  გადახდის მიღება -->
				<desc>Unable to accept this payment</desc>
			  </result>
			</payment-avail-response>

			';
		}
