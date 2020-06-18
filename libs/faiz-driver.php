<?php
namespace Faiz;

defined( 'ABSPATH' ) or die( 'nope.. just nope' );

class FaizDriver{
	
	public static function getGatewayURL($test = false){
		$url = 'https://payment.ipay88.com.my/epayment/entry.asp';
		
		return $url;
	}
}