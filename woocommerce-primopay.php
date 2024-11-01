<?php
/*
Plugin Name: WooCommerce - PrimoPay Gateway for WooCommerce
Plugin URI: http://primopay.com.au
Description: PrimoPay gateway for WooCommerce.
Version: 1.3
Author: PrimoPay
Author URI: http://primopay.com.au
 

*/
 
add_action('plugins_loaded', 'woocommerce_primopay_init', 0);


 
function woocommerce_primopay_init() {
 
	if ( !class_exists( 'WC_Payment_Gateway' ) ) return;
 
	/**
 	 * Localisation
	 */
	load_plugin_textdomain('wc-gateway-name', false, dirname( plugin_basename( __FILE__ ) ) . '/languages');
	
	

 	// Add the Gateway to WooCommerce
	function woocommerce_add_primopay_gateway($methods) {
		$methods[] = 'WC_Primopay';
		return $methods;		
	}	
	
	add_filter('woocommerce_payment_gateways', 'woocommerce_add_primopay_gateway' );    
	
	
	
	
	
	class WC_Primopay extends WC_Payment_Gateway {
		
		function __construct(){
			$this->id = 'primopay';
			//$this->icon = "http://www.primopay.com.au/wp-content/uploads/2013/09/primopayemaillogo1.jpg";
			$this->has_fields = true;
			$this->method_title = "PrimoPay";
			$this->method_description	= "Australian payment gateway using CTEL technology.";
			
			$this->init_form_fields();
			$this->init_settings();
			
			$this->enabled = $this->get_option( 'enabled' );
			$this->title = $this->get_option( 'title' );
			$this->description = $this->get_option( 'description' );
			$this->mode = $this->get_option( 'mode' );
			$this->location = $this->get_option( 'location' );
            $this->ctelid = $this->get_option('ctelid');
            $this->username = $this->get_option('username');
            $this->password = $this->get_option('password');
			
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

		}
		

		
		function init_form_fields(){
			$this->form_fields = array(
				'enabled' => array(
					'title' => __( 'Enable/Disable', 'woocommerce' ),
					'type' => 'checkbox',
					'label' => __( 'Enable PrimoPay', 'woocommerce' ),
					'default' => 'yes'
				),
				'title' => array(
					'title' => __( 'Title', 'woocommerce' ),
					'type' => 'text',
					'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
					'default' => __( 'Pay via Credit Card (PrimoPay)', 'woocommerce' ),
					'desc_tip'      => true,
				),
				'mode' => array(
					'title' 		=> __( 'Mode', 'woocommerce' ),
					'type' 			=> 'select',
					'default'		=> 'test',
					'description' 	=> __( 'Set this to live when you are ready to accept real payments.', 'woocommerce' ),
					'options'		=> Array('test'=>'Test mode','live'=>'Live Mode'),
					'desc_tip'      => true,
				),
				'location' => array(
					'title' 		=> __( 'Servers to use first', 'woocommerce' ),
					'type' 			=> 'select',
					'default'		=> 'east',
					'description' 	=> __( 'Set this to your website server\'s closet city', 'woocommerce' ),
					'options'		=> Array('east'=>'Sydney','west'=>'Adelaide'),
					'desc_tip'      => true,
				),
				'ctelid' => array(
					'title' => __( '6 digit account ID', 'woocommerce' ),
					'type' => 'text',
					'default' => __( '', 'woocommerce' ),
				),
				'username' => array(
					'title' => __( 'Transaction username', 'woocommerce' ),
					'type' => 'text',
					'default' => __( '', 'woocommerce' ),
				),
				'password' => array(
					'title' => __( 'Transaction Password', 'woocommerce' ),
					'type' => 'password',
					'default' => __( '', 'woocommerce' ),
				),
			);
			
		}
		
		public function admin_options() {
    	?>
    	<h3><?php _e( 'PrimoPay', 'woocommerce' ); ?></h3>
    	<p><?php _e('Allows payments from customers using PrimoPay.', 'woocommerce' ); ?></p>
    	<table class="form-table">
    	<?php
    		// Generate the HTML For the settings form.
    		$this->generate_settings_html();
    	?>
		</table><!--/.form-table-->
    	<?php
    }
    
    
		
		function payment_fields(){
			$this->payment_fields = array(
				'cardname' => array(
					'title' => __( 'Name on Card', 'woocommerce' ),
					'type' => 'text',
					'description' => __( 'The name on the credit card', 'woocommerce' ),
					'default' => __( '', 'woocommerce' ),
					'desc_tip'      => true,
				),
				'cardnumber' => array(
					'title' => __( 'Card Number', 'woocommerce' ),
					'type' => 'text',
					'default' => __( '', 'woocommerce' ),
				),	
							
				);
				
	
				$cardname = sanitize_text_field($_POST['cardname']);
				$cardnum = sanitize_text_field($_POST['cardnum']);
				$expmonth = sanitize_text_field($_POST['expmonth']);
				$expyear = sanitize_text_field($_POST['expyear']);
				$cvv = sanitize_text_field($_POST['cvv']);
				
				// Cacl the expiry month
				for($i=1; $i<=12; $i++){
					$monthname = date("F",mktime(0, 0, 0, $i, 10));
					
					$sel = "";
					if($expmonth == $i) $sel = "selected";
					
					$expmonth .= "<option value=$i $sel>$monthname</option>";
				}
				$expmonth = "<select name=expmonth class='primpay expmonth' >$expmonth</select>";
				
				for($i=0; $i<=8; $i++){
					$year = date("Y")+$i;
					
					$sel = "";
					if($expyear == $i) $sel = "selected";
					
					$expyear .= "<option value=$year $sel>$year</option>";
				}
				$expyear = "<select name=expyear class='primpay expyear' >$expyear</select>";
				
				echo "<script language='javascript' type='text/javascript'>
								function removeSpaces(string) {
								 return string.split(' ').join('');
								}
								</script>";
				
				echo "<br><label for='cardname'>Cardname</label><input class='primopay cardname' type=input name='cardname' value='$cardname'>";
				echo "<br><label for='cardnum'>Card Number</label><input class='primopay cardnum'  type=input name='cardnum' value='$cardnum' onkeyup='this.value=removeSpaces(this.value);' maxlength=16>";
				echo "<br>Expiry: $expmonth $expyear";
				echo "<br><label for='cvv'>CVV</label><input class='primopay css'  type=input name='cvv' value='$cvv'>";
			
			
		}
		
		function validate_fields(){
            global $woocommerce;

            $valid = true;

            $cardname = sanitize_text_field($_POST['cardname']);
            $cardnum = sanitize_text_field($_POST['cardnum']);
            $expmonth = str_pad(sanitize_text_field($_POST['expmonth']),2,"0",STR_PAD_LEFT);
            $expyear = sanitize_text_field($_POST['expyear']);
            $cvv = sanitize_text_field($_POST['cvv']);

            if(strlen($cardname)<2){
                $error_message = "Card name must be entered";
                wc_add_notice( sprintf( __('Credit Card Details Error: ', 'woothemes') . $error_message ),  'error' );
                $valid = false;
            }

            if(strlen($cardnum)!=16){
                $error_message = "Card number must be 16 digits";
                wc_add_notice( sprintf( __('Credit Card Details Error: ', 'woothemes') . $error_message ),  'error' );
                $valid = false;
            }

            $now = date("Ym");
            if(($expyear.$expmonth)<$now){
                $error_message = "Expiry date ".($expyear.$expmonth)." must be in the future";
                wc_add_notice( sprintf( __('Credit Card Details Error: ', 'woothemes') . $error_message ),  'error' );
                $valid = false;
            }

            if(strlen($cvv)!=3){
                $error_message = "CVV must be 3 digits";
                wc_add_notice( sprintf( __('Credit Card Details Error: ', 'woothemes') . $error_message ),  'error' );
                $valid = false;
            }
			
			
			return $valid;
		}
        
        private function get_servers(){
            if($this->mode == 'test'){
                // Test servers
                if($this->location=="east"){
                    // Show east test servers
                    $servers[1]='https://ccdemo.gw02.ctel.com.au/txn.asmx';
                    $servers[2]='https://ccdemo.gw01.ctel.com.au/txn.asmx';
                } else {
                    // Show west test servers
                    $servers[1]='https://ccdemo.gw01.ctel.com.au/txn.asmx';
                    $servers[2]='https://ccdemo.gw02.ctel.com.au/txn.asmx';  
                }
            
            } else if ($this->mode == 'live'){
                // Live servers
                if($this->location=="east"){
                    // Show east live servers
                    $servers[1]='https://cc.gw02.ctel.com.au/txn.asmx';
                    $servers[2]='https://cc.gw01.ctel.com.au/txn.asmx';   
                } else {
                    // Show west live servers
                    $servers[1]='https://cc.gw01.ctel.com.au/txn.asmx';
                    $servers[2]='https://cc.gw02.ctel.com.au/txn.asmx';    
                }                
                
            }   else {
                // fatal error - mode should be something. 
                echo "PrimoPay mode not set. Must be 'test' or 'live'. Please adjust in shop configuration";
                die();
            }

            return $servers;
            
        }
		
		
		function process_payment( $order_id ) {
			global $woocommerce;
			$order = new WC_Order( $order_id );
			
			$cardname = sanitize_text_field($_POST['cardname']);
			$cardnum = sanitize_text_field($_POST['cardnum']);
			$expmonth = str_pad(sanitize_text_field($_POST['expmonth']),2,"0",STR_PAD_LEFT);
			$expyear = substr(sanitize_text_field($_POST['expyear']),-2);            
            
			$cvv = sanitize_text_field($_POST['cvv']);	

			// Process payment here.
            $servers = $this->get_servers();
            
            // Get the order toal expressedi n cents
            $amount = $order->get_total()*100;
            
            $params->Merchant = $this->ctelid;
            $params->OperatorUsername = $this->username; 
            $params->OperatorPassword = $this->password; 
            $params->CtelTechnology = "PRM_WP0001";
            $params->TxnType = "Payment";
            $params->CustomerReference = $order->get_order_number();
            $params->CardNumber = $cardnum;
            $params->CardExpiry = $expmonth.$expyear; 
            $params->CardCVV = $cvv;
            $params->CardHolderName = $cardname;
            $params->Amount = $amount;



            $wsdl_loc = plugins_url()."/woocommerce-primopay/txn.wsdl";
            
            // for testing only
            //$wsdl_loc = "http://localhost/woo/wp-content/plugins/woocommerce-primopay/txn.wsdl";  
            //print_r($params);        
            
            // Attempt transaction via primary server   
            $txnClient = new SoapClient($wsdl_loc,array("location" => $servers[1], "connection_timeout" => 100, "exceptions" => true, "trace"=> true ));          
            $pprst = $this->PerformCCTxn( $txnClient, $params );

            if($pprst['success']==false){
                // Attempt on second server
                $txnClient2 = new SoapClient($wsdl_loc,array("location" => $servers[2], "connection_timeout" => 100, "exceptions" => true, "trace"=> true ));
                $pprst = $this->PerformCCTxn( $txnClient2, $params );
            }    
		
		
			if($pprst['success']==true){	
			// Return thankyou redirect
				
				// USe for bank errors
				//$order->update_status('on-hold', __( 'Awaiting cheque payment', 'woocommerce' ));
				$order->payment_complete();
                
                $order->add_order_note( __('Bank Reference for payment:'.$pprst['receipt'], 'woothemes') );
				
				
				// Remove cart
				$woocommerce->cart->empty_cart();			
			
				return array(
					'result' => 'success',
					'redirect' => $this->get_return_url( $order )
				);
				
			} else {
                //$order->cancel_order();
                
                $error_message = "The bank said: ".$pprst['reponsecode']." ".$pprst['reponsedesc'];
                
                $order->add_order_note( __($error_message, 'woothemes') );

                wc_add_notice( sprintf( __('Payment error: ' . $error_message , 'woothemes') ),  'error' );
				return;				
			}
			
			
			
			
		}
        
        // Perform credit card transaction by calling web service method, and output results.
        private function PerformCCTxn( $txnClient, $params ) {
            // Make web service method call to perform credit card transaction
            $txnResult = $txnClient->PerformCreditCardTxn($params);   
            // Process transaction results
            return $this->ProcessCCTxnResults($txnResult);
        }
        
        // Process credit card transaction results by outputting results
        private function ProcessCCTxnResults($txnResult) {

            if ( $txnResult->PerformCreditCardTxnResult->Successful == true )  {
                $primopayrst['success']=true;
            } else  {            
                $primopayrst['success']=false;  
            }
            
            if ( isset($txnResult->PerformCreditCardTxnResult->Receipt) ){            
                $primopayrst['receipt'] = $txnResult->PerformCreditCardTxnResult->Receipt;  
            }

            $primopayrst['reponsecode'] = $txnResult->PerformCreditCardTxnResult->ResponseCode;  

            $primopayrst['reponsedesc'] = $txnResult->PerformCreditCardTxnResult->ResponseDescription; ; 

            if ( isset($txnResult->PerformCreditCardTxnResult->BankReferenceNumber) )   {
                $primopayrst['bankref'] = $txnResult->PerformCreditCardTxnResult->BankReferenceNumber;  
            }

            $primopayrst['banksettdate'] = $txnResult->PerformCreditCardTxnResult->BankSettlementDate;  

            if ( isset($txnResult->PerformCreditCardTxnResult->AuthorisationNumber) ) {
                $primopayrst['auth'] = $txnResult->PerformCreditCardTxnResult->AuthorisationNumber;  
            }

            if ( isset($txnResult->PerformCreditCardTxnResult->PartialCardNumber) ) {
                $primopayrst['partialcard'] = $txnResult->PerformCreditCardTxnResult->PartialCardNumber;  
            } else {
                $primopayrst['partialcard'] = 0;
            }                   
            return $primopayrst;
        }
		    
	}
	

} 