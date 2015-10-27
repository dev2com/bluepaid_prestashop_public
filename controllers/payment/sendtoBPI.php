<?php 
require_once(dirname(__FILE__).'./../../../../config/config.inc.php');
/** Call init.php to initialize context */
require_once(dirname(__FILE__).'/../../../../init.php');
require_once(dirname(__FILE__).'/../../bluepaid.php');
ini_set('default_charset', 'UTF-8');

/** Check PS_VERSION */
if (version_compare(_PS_VERSION_, '1.4', '<'))
	return;
	
if (Configuration::get('BPI_MERCHID'))
{
	$url =  'https://paiement-securise.bluepaid.com/in.php';
}
else{
	die( "erreur configuration" );
}

	
	
$used_currency = false;
if (version_compare(_PS_VERSION_, '1.6', '<'))
{
	global $cart, $cookie;
	$amount = $cart->getOrderTotal(true);
	$amount_total=$amount;
	$cart_id = $cart->id;
	$currency = new Currency(intval($cart->id_currency));
	$used_currency = $currency->iso_code;
	$customer = new Customer (intval($cart->id_customer));
	$iso_code_lang = Language::getIsoById( (int)$cookie->id_lang );
}
else
{	
	$context = Context::getContext();
	$cookie = $context->cookie;
	$iso_code_lang = $context->language->iso_code;
	
	$amount = $context->cart->getOrderTotal(true, Cart::BOTH);
	$amount_total = $amount;
	$cart_id = $context->cart->id;
	$currency = $context->cart->id_currency;
	$result_currency = Currency::getCurrency($currency);
	$used_currency = $result_currency['iso_code'];
	$customer = $context->customer;
}

	
$order_filename = "index.php?controller=order&step=3";
if (version_compare(_PS_VERSION_, '1.6', '<'))$order_filename = "order.php?step=3";

if (!isset($cookie->bpi_payment) ||  $cookie->bpi_payment === false)
	Tools::redirect('order.php');
	
if ((!Configuration::get('BPI_MERCHID') ||  Configuration::get('BPI_MERCHID') === '') || (!$amount || $amount <=0))
	Tools::redirect('order.php');
	
unset($cookie->bpi_payment);
	
$bluepaid = new Bluepaid();


$params = array();
$params['id_boutique'] = Configuration::get('BPI_MERCHID');
$params['id_client'] = $cart_id;
$params['montant'] = $amount;
$params['devise'] = $used_currency;
$params['langue'] = strtoupper($iso_code_lang);
$params['divers'] = $customer->secure_key;
$params['email_client'] = $customer->email;
$params['url_retour_bo'] = 'paiement-securise.bluepaid.com/services/bp_return_customer.php';
$params['url_retour_ok'] = htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/bluepaid/controllers/front/payment_return.php';
$params['url_retour_stop'] = htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.$order_filename;
$params['HvTag'] = 'D2C_ps_bpi.bluepaid.com.php';
$params['pvD2CBPI'] = 'BPD2C_PS';
$params['pvD2CBPI_v'] = $bluepaid->version;

//$params['set_secure_return'] = 'true';
//$params['set_secure_conf'] = 'true';

$url_params = http_build_query($params);

$bluepaid->redirectForVersion($url.'?'.$url_params);

