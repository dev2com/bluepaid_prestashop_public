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
	$url =  'https://paiement-securise.bluepaid.com/ABOv2/in.php';
}
else{
	die( "erreur configuration" );
}
	
	
/**
 * Check currency used
 */
$used_currency = false;
if (version_compare(_PS_VERSION_, '1.6', '<'))
{
	global $cart, $cookie;
	$amount_total=$cart->getOrderTotal(true);
	$cart_id = $cart->id;
	$currency = new Currency(intval($cart->id_currency));
	$used_currency = $currency->iso_code;
	$customer = new Customer(intval($cart->id_customer));
	$iso_code_lang = Language::getIsoById( (int)$cookie->id_lang );
}
else
{	
	$context = Context::getContext();
	$cookie = $context->cookie;
	$iso_code_lang = $context->language->iso_code;
	
	$amount_total=$context->cart->getOrderTotal(true, Cart::BOTH);
	$cart_id = $context->cart->id;
	$currency = $context->cart->id_currency;
	$result_currency = Currency::getCurrency($currency);
	$used_currency = $result_currency['iso_code'];
	$customer = $context->customer;
}

$order_filename = "index.php?controller=order&step=3";
if (version_compare(_PS_VERSION_, '1.6', '<'))$order_filename = "order.php?step=3";


if (!isset($cookie->bpi_payment) || $cookie->bpi_payment === false)
	Tools::redirect('order.php');
	
if ((!Configuration::get('BPI_MERCHID') ||  Configuration::get('BPI_MERCHID') === '') || (!$amount_total || $amount_total <=0))
	Tools::redirect('order.php');
	
unset($cookie->bpi_payment);

	
$bluepaid = new Bluepaid();

// ----- NUMBER OF OCUR ------
$max_occur = $bluepaid->_get_maxOccur();
if (!(int)$max_occur > 0)
{
	die( "erreur configuration" );
}
//
// ----- Construct amount ------
//
$initamount = false;
$amount = false;
$amount_ocu = false;
$amount = $amount_total;
// ----- INITIAL AMOUNT ------
$bpi_xpay_initamount = Configuration::get('BPI_XPAY_INITAMOUNT');

$check = false;
if($bpi_xpay_initamount && $bpi_xpay_initamount>0){
	$initamount=0;
	$initamount=$amount_total*$bpi_xpay_initamount/100;
	$amount = $amount_total-$initamount;
	$amount_ocu = round((float)$amount/((float)$max_occur-1), 2);
	//Check amount integrity
	$check = $amount_ocu*($max_occur-1)+$initamount;
}	
else
{
	$amount_ocu = round((float)$amount/(float)$max_occur, 2);
	//Check amount integrity
	$check = $amount_ocu*$max_occur+$initamount;
}

$delta = (float)$amount_total - (float)$check;
if (ceil(round($delta, 2)) > 0);
{
	if ($initamount)$initamount+=round($delta, 2);
	else
	{
		$initamount = $amount_ocu+round($delta, 2);
	}
}

	
$params = array();
$params['ID_BOUTIQUE'] = Configuration::get('BPI_MERCHID');
$params['ID_CLIENT'] = $cart_id;	


if($initamount){
	$params['MONTANT_INITIAL'] = number_format($initamount, 2, '.', '');
}

$params['MONTANT'] = number_format($amount_ocu, 2, '.', '');

if($temp=$bluepaid->get_nbShowIfKo()){
	if($temp>0){
		//Définit le nombre de jours entre chaque représentation de l'occurence ayant échouée
		$params['TOLERANCE_JOURS'] = 2;
		$params['TOLERANCE_NBMAX'] = $temp;
	}
}


if($bluepaid->_get_maxOccur()){
	$params['NB_OCCURENCES_MAX'] = $max_occur;
}


$params['PERIODE_NB'] = 1;
$params['PERIODE_TYPE'] = 'M';
$params['TYPE_ABO'] = "STANDARD";
$params['DEVISE'] = $used_currency;
$params['DIVERS'] = $customer->secure_key;
$params['EMAIL_CLIENT'] = $customer->email;
$params['LANGUE'] = strtoupper($iso_code_lang);
$params['URL_RETOUR_BO'] = 'paiement-securise.bluepaid.com/services/bp_return_customer.php';
$params['URL_RETOUR_OK'] = htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/bluepaid/controllers/front/payment_return.php';
$params['URL_RETOUR_STOP'] = htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.$order_filename;
$params['HvTag'] = 'D2C_ps_bpi.bluepaid.com.php';
$params['pvD2CBPI'] = 'BPD2CX_PS';
$params['pvD2CBPI_v'] = $bluepaid->version;

//$params['set_secure_return'] = 'true';
//$params['set_secure_conf'] = 'true';

$url_params = http_build_query($params);

$bluepaid->redirectForVersion($url.'?'.$url_params);
