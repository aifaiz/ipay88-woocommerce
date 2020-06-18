<?php
namespace Faiz;

defined( 'ABSPATH' ) or die( 'nope.. just nope' );

class FaizDriver{
	
	public static function getGatewayURL($currency, $test = false){
		$url = 'https://payment.ipay88.com.my/epayment/entry.asp';
		
		switch($currency):
			case 'PHP':
				$url = 'https://payment.ipay88.com.ph/epayment/entry.asp';
				break;
			case 'MYR':
			default:
				$url = 'https://payment.ipay88.com.my/epayment/entry.asp';
				break;
		endswitch;
		
		if($test === true):
			$url = 'https://validateipay88.faiz';
		endif;
		
		return $url;
	}
	
	public static function getSupportedCurrencies(){
		$currency = [];
		
		$currency = [
			'MYR'=>'Ringgit Malaysia',
			'PHP'=>'Philippine Peso'
		];
		
		return $currency;
	}
}