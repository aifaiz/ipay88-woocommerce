<?php
/*
Plugin Name: ipay88 woocommerce processing
Plugin URI: http://www.aics.my/
Description: Custom ipay88 gateway plugin for woocmmerce
Author: FAiZ
Version: 1.1
*/
defined( 'ABSPATH' ) or die( 'The hell? nope.. just nope' );

$faiz_ipay_path = plugin_dir_path( __FILE__ );

include_once($faiz_ipay_path.'/libs/page-templater.php');

function ipay_gateway(){
    global $faiz_ipay_path;
    include_once($faiz_ipay_path.'libs/faiz-gateway-class.php');
}
add_action('plugins_loaded', 'ipay_gateway');

function add_ipay_gateway( $methods ) {
	$methods[] = 'Faiz_ipay_gateway'; 
	return $methods;
}

add_filter( 'woocommerce_payment_gateways', 'add_ipay_gateway' );

// receive response on load template
function processResponse(){
	$gateway = new Faiz_ipay_gateway();
	$response = $gateway->getResponse();
	if(isset($response['Status'])):
		$gateway->validateResponse($response);
	else:
		echo "somethings wrong with the response";
		exit;
	endif;
}