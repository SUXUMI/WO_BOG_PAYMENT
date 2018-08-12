<?php
class WC_Gateway_georgian_card extends WC_Payment_Gateway {
	
    function __construct() {
		
		
		 
			$this->id                 = 'georgiancard';
			$this->has_fields         =  false;
			$this->order_button_text  =  __( 'გადახდა', 'card' );
			$this->method_title       =  __( 'საქართველოს ბანკის გადახდის სისტემა', 'card' );
			$this->method_description =  __( 'მოდული საშუალებას იძლევა ვებ გვერდზე გადახდები მოხდეს Visa/Master/AMEX ბარათებით', 'card' );
			
			 $this->supports          =  array(
				'products'
			);
			$this->title = __( "გადაიხადე Visa/Master/AMEX ბარათებუთ", 'Card' );
			$this->icon = plugin_dir_url(__FILE__).'/visa.png';
 
			$this->init_form_fields();
			$this->init_settings();
		
		foreach ( $this->settings as $setting_key => $value ) 
		{
            $this->$setting_key = $value; 
        }
		
		
		 
			$this->title       = $this->get_option( 'title' );
			$this->description = $this->get_option( 'description' );
			$this->debug       = 'yes' === $this->get_option( 'debug', 'no' );
			$this->cert_path   = $this->get_option( 'cert_path' );
			$this->cert_pass   = $this->get_option( 'cert_pass' );
			$this->ok_slug     = $this->get_option( 'ok_slug' );
			$this->fail_slug   = $this->get_option( 'fail_slug' );
			
			 
			add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
			add_action( 'admin_notices', array( $this, 'do_ssl_check' ) );
			add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'order_details' ) );
			add_action( 'woocommerce_api_redirect_to_payment_form', array( $this, 'redirect_to_payment_form' ) );
			add_action( 'woocommerce_api_' . $this->ok_slug, array( $this, 'return_from_payment_form_ok' ) );
			add_action( 'woocommerce_api_' . $this->fail_slug, array( $this, 'return_from_payment_form_fail' ) );
			add_action( 'woocommerce_api_close_business_day', array( $this, 'close_business_day' ) );
			add_action( 'woocommerce_api_is_wearede', array( $this, 'is_wearede_plugin' ) );
       
	   
        if ( is_admin() )
			{          
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			}
    }


	public function init_form_fields() 
	{
        $this->form_fields = array(
            'enabled' => array(
                'title'     => __( 'ჩართვა / გამორთვა', ''),                
                'type'      => 'checkbox',
                'default'   => 'no',
            ),

            'api_url' => array(
                'title'     => __( 'API URL ', '' ),
                'type'      => 'text',
                'desc_tip'  => __( 'ბმულის მისაღებად მიმართეთ საქ. ბანკს', '' ),
                'default'   => __( 'https://sb3d.georgiancard.ge/payment/start.wsm', '' )
            ),
            'api_merch_id' => array(
                'title'     => __( 'Merchant ID', '' ),
                'type'      => 'text',
                'desc_tip'  => __( 'Merchant ID მისაღებად მიმართეთ საქ. ბანკს', '' ),
            ),
            'api_account_id' => array(
                'title'     => __( 'Account ID', '' ),
                'type'      => 'text',
                'desc_tip'  => __( 'Account ID  მიმართეთ საქ. ბანკს', '' ),
            ),
            'api_page_id' => array(
                'title'     => __( 'Page ID', '' ),
                'type'      => 'text',
                'desc_tip'  => __( 'Page ID  მიმართეთ საქ. ბანკს', '' ),
            ),
                       
            'api_backurl_s' => array(
                'title'     => __( 'Back url "ok"', '' ),
                'type'      => 'text',
                'desc_tip'  => __( 'Back url გადახდის წარმატებით განხორციელების შემთხვევაში', '' ),
            ),

        );
    }
	
	 public function process_payment( $order_id ) {
     global $woocommerce;
		
		
		 
			$this->init_form_fields();
			$this->init_settings();
			
			$order    = wc_get_order( $order_id );
			$currency = $order->get_order_currency() ? $order->get_order_currency() : get_woocommerce_currency();
			$amount   = $order->order_total;
			
			
			   
			$this->api_amount      = $amount * 100;
			$this->api_currency    = '981';
			$this->api_description = sprintf( __( '%s - Order %s', 'card' ), wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), $order->id );
			$this->api_language    = strtoupper( substr( get_bloginfo('language'), 0, -3 ) );

			$this->api_url         = $this->get_option( 'api_url' );
			$this->api_merch_id    = $this->get_option( 'api_merch_id' );
			$this->api_account_id  = $this->get_option( 'api_account_id' );
			$this->api_backurl_s   = $this->get_option( 'api_backurl_s' );
			$this->api_backurl_f   = $this->get_option( 'api_backurl_f' );
			$this->api_page_id     = $this->get_option( 'api_page_id' );
			


       
        $customer_order = new WC_Order( $order_id );
        $customer_order->update_status('on-hold', __( 'Awaiting cheque payment', 'woocommerce' ));
        $backurl_s = urlencode(get_site_url().'/?page_id=935&success=1');
        $backurl_f = urlencode(get_site_url().'/?page_id=936&success=0');
        $amount = (int)$customer_order->order_total;
        $options = array(
            'soap_version'    => SOAP_1_1,
            'exceptions'      => true,
            'trace'           => 1,
            'wdsl_local_copy' => true
        );
		
		$redirect_url = $this->api_url.'?lang=ka&merch_id='.$this->api_merch_id.'&page_id='.$this->api_page_id.'&o.order_id='.$order_id.'&back_url_s='.$backurl_s.'&back_url_f='.$backurl_f;
		 
		return array(
            'result'   => 'success',
            'redirect' => $redirect_url,
        );
        die;
		
    

    }
}