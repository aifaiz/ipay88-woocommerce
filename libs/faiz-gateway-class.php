<?php
/*
 * Class : Aics_ipay_gateway
 * Author: AiFAiZ
 * website: https://aics.my
 * email: faiz@aics.my
 */
defined( 'ABSPATH' ) or die( 'nope.. just nope' );

class Aics_ipay_gateway extends WC_Payment_Gateway{
    private $merchantID;
    private $merchantKey;
    public $pageID;
	
    public function __construct(){
        $this->id= 'woo_ipay88';
        $this->has_fields = false;
        $this->method_title = 'ipay88 payment processing';
        $this->method_description = 'Process ipay88 payment trough woocommerce. custom plugin made to fit the needs.';
        
        $this->init_form_fields();
        $this->init_settings();
        
        // after init_settins();
        $this->title = 'ipay88 Payment Gateway';
        $this->merchantID = $this->get_option( 'merchantID' );
        $this->merchantKey = $this->get_option('merchantKey');
        $this->pageID = $this->get_option('paymentPageID');
        
        add_action('woocommerce_update_options_payment_gateways_'.$this->id, array(&$this, 'process_admin_options'));
    }
    
    public function init_form_fields(){
		
		$pages = get_pages();
		$poption = [];
		foreach($pages as $page):
			$poption[$page->ID] = $page->post_title;
		endforeach;
		$paymentPageID = array(
							   'title'=> __( 'Choose The Page That Set For Payment Template', 'aics' ),
							   'type'=>'select',
							   'label'=> __('list of wordpress pages to choose as payment page', 'aics'),
							   'default'=>'1',
							   'options'=>$poption);
        $this->form_fields = array(
                                   'enabled' => array(
                                                      'title' => __( 'Enable/Disable', 'aics' ),
                                                      'type' => 'checkbox',
                                                      'label' => __( 'Enable ipay88 gateway', 'aics' ),
                                                      'default' => 'yes'
                                                    ),
                                   'testingPayment' => array(
                                                      'title' => __( 'Testing or Live', 'aics' ),
                                                      'type' => 'checkbox',
                                                      'label' => __( 'Testing payment? (check if testing)', 'aics' ),
                                                      'default' => 'no'
                                                    ),
                                   'merchantID' => array(
                                                          'title' => __( 'Merchant ID', 'aics' ),
                                                          'type' => 'text',
                                                          'default' => '0000000'
                                                        ),
                                   'merchantKey' => array(
                                                          'title' => __( 'Merchant Key', 'aics' ),
                                                          'type' => 'text',
                                                          'default' => '0000000'
                                                        ),
                                   'paymentPageID' => $paymentPageID,
								   'backendURL'=> array(
														'title'=> __('Enable Backend URL', 'aics'),
														'type' => 'select',
														'label'=> __('enable or disable the backend URL feature', 'aics'),
														'default'=>'no',
														'options'=>['yes'=>'Enable', 'no'=>'Disable']
														)
                                   );
    }
    
    public function process_payment( $order_id ){
        global $woocommerce;
        $order          = wc_get_order( $order_id );
        $payment_page = get_permalink($this->pageID).'?oid='.$order_id;
        return array(
			'result'   => 'success',
			'redirect' => $payment_page
		);
    }
    
    // only call this when want to generate form.
    public function preparePayment($order_id){
        $post_url = 'https://www.mobile88.com/ePayment/entry.asp';
        $test_pay = $this->get_option('testingPayment');
        
        $order = wc_get_order( $order_id );
        $amount = $order->get_total();
        
        if($test_pay == 'yes'):
            $amount = '1.00';
        endif;
		
		$amount = number_format((float)$amount, 2, '.', '');
        
        $currency = 'MYR';
		//get_woocommerce_currency_symbol();
		// currently we just need MYR support only
        
        $order_items = $order->get_items();
        $item = '';
        foreach($order_items as $key=>$oi):
            $item_name = $oi['name'];
            if($item != ''):
                $item .= $item_name.', ';
            else:
                $item .= $item_name;
            endif;
        endforeach;
        
        $current_user = wp_get_current_user();
		if(isset($current_user->ID) && !empty($current_user->ID)):
			$user_phone = get_user_meta($current_user->ID, 'billing_phone', true);
			$user_fullname = $current_user->user_firstname.' '.$current_user->user_user_lastname;
			$user_email = $current_user->user_email;
		else:
			$user_phone = $order->get_billing_phone();
			$user_fullname = $order->get_billing_first_name().' '.$order->get_billing_last_name();
			$user_email = $order->get_billing_email();
		endif;
		
        
        $format_amt = $this->formatAmount($amount);
        $the_string = $this->merchantKey.$this->merchantID.$order_id.$format_amt.$currency;
        //echo 'str: <b>'.$the_string.'</b>';
        $the_hash = $this->iPay88_signature($the_string);
        
        $response_url = get_permalink($this->pageID);
		
		$backend_url = $this->get_option('backendURL');
        
        ?>
        <form id="ipaysubmitForm" action="<?php echo $post_url; ?>" method="POST">
            <input type="hidden" name="MerchantCode" value="<?php echo $this->merchantID; ?>">
            <input type="hidden" name="PaymentId" value="">
            <input type="hidden" name="RefNo" value="<?php echo $order_id; ?>">
            <input type="hidden" name="Amount" value="<?php echo $amount; ?>">
            <input type="hidden" name="Currency" value="<?php echo $currency; ?>">
            <input type="hidden" name="ProdDesc" value="<?php echo $item; ?>">
            <input type="hidden" name="UserName" value="<?php echo $user_fullname; ?>">
            <input type="hidden" name="UserEmail" value="<?php echo $user_email; ?>">
            <input type="hidden" name="UserContact" value="<?php echo $user_phone; ?>">
            <input type="hidden" name="Lang" value="UTF-8">
            <input type="hidden" name="Signature" value="<?php echo $the_hash; ?>">
            <input type="hidden" name="ResponseURL" value="<?php echo $response_url; ?>">
			<?php if(isset($backend_url) && $backend_url == 'yes'): ?>
			<input type="hidden" name="BackendURL" value="<?php echo site_url(); ?>">
			<?php endif; ?>
        </form>
        <?php 
    }
    
    public function formatAmount($amt){
        $remove_dot = str_replace('.', '', $amt);
        $remove_comma = str_replace(',', '', $remove_dot);
        return $remove_comma;
    }
    
    public function iPay88_signature($source){
        return base64_encode(hex2bin(sha1($source)));
    }
    
    public function hex2bin($hexSource){
		$bin = '';
        for ($i=0;$i<strlen($hexSource);$i=$i+2){
            $bin .= chr(hexdec(substr($hexSource,$i,2)));
        }
        return $bin;
    }
    
    // solely purpose is to get response after payment done on the payment return url.
    public function getResponse(){
        $MerchantCode = $_POST['MerchantCode'];
        $PaymentId = $_POST['PaymentId'];
        $RefNo = $_POST['RefNo'];
        $Amount = $_POST['Amount'];
        $Currency = $_POST['Currency'];
        $Remark = $_POST['Remark'];
        $TransId = $_POST['TransId'];
        $AuthCode = $_POST['AuthCode'];
        $Status = $_POST['Status'];
        $ErrDesc = $_POST['ErrDesc'];
        $Signature = $_POST['Signature'];
        
        return compact('MerchantCode','PaymentId','RefNo','Amount','Currency','Remark','TransId','AuthCode','Status','ErrDesc','Signature');
    }
	
	public function validateResponse($data){
		global $woocommerce;
		
		if(isset($data['MerchantCode']) && isset($data['PaymentId']) && isset($data['RefNo']) && isset($data['Amount']) && isset($data['Currency']) && isset($data['Signature']) && isset($data['Status'])):
			$mcode = $data['MerchantCode'];
			$payid = $data['PaymentId'];
			$refno = $data['RefNo'];
			$amt_txt = $data['Amount'];
			$cur = $data['Currency'];
			$ret_sign = $data['Signature'];
			$status = $data['Status'];
			
			$amnt = str_replace(',', '', $amt_txt);
			$amnt_final = str_replace('.', '', $amnt);
			$combined = $this->merchantKey.$this->merchantID.$payid.$refno.$amnt_final.$cur.$status;
			$signed = $this->iPay88_signature($combined);
			
			if($status == 1):
				if($signed == $ret_sign):
					//echo 'sign ok';
					$order = new WC_Order( $refno );
					$order->update_status('wc-completed', __( 'Completed', 'woocommerce' ));
					$order->payment_complete();
					$redirect = $order->get_checkout_order_received_url();
					wp_redirect($redirect);
					exit;
				else:
					//echo 'payment failed.';
					$order = new WC_Order( $refno );
					$order->update_status('failed', __( 'Failed', 'woocommerce' ));
					//$woocommerce->cart->empty_cart();
					wp_redirect($this->get_return_url( $order ));
					exit;
				endif;
			elseif($status != 1 && $signed == $ret_sign):
				$order = new WC_Order( $refno );
				$order->update_status('failed', __( 'Failed', 'woocommerce' ));
				//$woocommerce->cart->empty_cart();
				wp_redirect($this->get_return_url( $order ));
				exit;
			endif;
		endif;
	}
	
	public function backendResponse($data){
		global $woocommerce;
		$echo = 'RECEIVEOK';
		
		if(isset($data['MerchantCode']) && isset($data['PaymentId']) && isset($data['RefNo']) && isset($data['Amount']) && isset($data['Currency']) && isset($data['Signature']) && isset($data['Status'])):
			$mcode = $data['MerchantCode'];
			$payid = $data['PaymentId'];
			$refno = $data['RefNo'];
			$amt_txt = $data['Amount'];
			$cur = $data['Currency'];
			$ret_sign = $data['Signature'];
			$status = $data['Status'];
			
			$amnt = str_replace(',', '', $amt_txt);
			$amnt_final = str_replace('.', '', $amnt);
			$combined = $this->merchantKey.$this->merchantID.$payid.$refno.$amnt_final.$cur.$status;
			$signed = $this->iPay88_signature($combined);
			
			if($status == 1):
				if($signed == $ret_sign):
					//echo 'sign ok';
					$order = new WC_Order( $refno );
					$order->update_status('wc-completed', __( 'Completed', 'woocommerce' ));
					$order->payment_complete();
					echo $echo;
					// exit;
				else:
					//echo 'payment failed.';
					$order = new WC_Order( $refno );
					$order->update_status('failed', __( 'Failed', 'woocommerce' ));
					echo $echo;
					// exit;
				endif;
			elseif($status != 1 && $signed == $ret_sign):
				$order = new WC_Order( $refno );
				$order->update_status('failed', __( 'Failed', 'woocommerce' ));
				echo $echo;
				// exit;
			endif;
		endif;
	}
}
