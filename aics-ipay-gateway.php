<?php
/*
Plugin Name: AiCS ipay88 Woocommerce
Plugin URI: http://www.aics.my/
Description: AiCS Malaysian ipay88 gateway plugin for woocommerce
Author: AiCS
Version: 1.0.3
*/
defined( 'ABSPATH' ) or die( 'nope.. just nope' );

$aics_ipay_path = plugin_dir_path( __FILE__ );
$aics_ipay_uri = plugins_url( __FILE__ );

define( 'AICS_IPAY88_PATH', $aics_ipay_path );
define( 'AICS_IPAY88_URI', $aics_ipay_uri);

include_once(AICS_IPAY88_PATH.'/libs/page-templater.php');

if(!function_exists('aics_ipay_gateway')):
function aics_ipay_gateway(){
    include_once(AICS_IPAY88_PATH.'libs/faiz-gateway-class.php');
}
add_action('plugins_loaded', 'aics_ipay_gateway');
endif;

if(!function_exists('aics_add_ipay_gateway')):
function aics_add_ipay_gateway( $methods ) {
	$methods[] = 'Aics_ipay_gateway'; 
	return $methods;
}
add_filter( 'woocommerce_payment_gateways', 'aics_add_ipay_gateway' );
endif;

// receive response on load template
if(!function_exists('aics_processResponse')):
function aics_processResponse(){
	$gateway = new Aics_ipay_gateway();
	$response = $gateway->getResponse();
	
	/*
	if(isset($_REQUEST['Status'])):
		aics_debug($_REQUEST);
		exit;
	endif;
	*/
	 
	if(isset($response['Status'])):
		$gateway->validateResponse($response);
	endif;
	if(isset($_GET['testpay']) && !empty($_GET['testpay'])):
		$gateway->validateResponse($response);
	endif;
}
endif;
// include extra css and js on the payment page template
if(!function_exists('aics_include_extra_css_js')):
function aics_include_extra_css_js(){
	$ipay_gateway = new Aics_ipay_gateway();
	if(isset($ipay_gateway->pageID) && !empty($ipay_gateway->pageID)):
		if(is_page($ipay_gateway->pageID)):
			wp_register_style('aics-bs-style', plugins_url('inc/css/bootstrap.min.css'));
			wp_register_script('aics-bs-scrpt', plugins_url('inc/js/bootstrap.min.js'), ['jquery'],'3.3.7', true);
			wp_enqueue_style('aics-bs-style');
			wp_enqueue_script('aics-bs-scrpt');
		endif;
	endif;
}
add_action('wp_enqueue_scripts', 'aics_include_extra_css_js');
endif;

if(!function_exists('aics_payment_footer_js')):
function aics_payment_footer_js(){
	?>
	<script>
        jQuery(document).ready(function(){
            jQuery('#ipaysubmitForm').submit();
        });
        </script>
	<?php 
}
add_action('ipay_template','aics_payment_footer_js');
endif;

if(!function_exists('aics_debug')):
	function aics_debug($var){
		echo'<pre>'.print_r($var,true).'</pre>';
	}
endif;