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
unset($cookie->bpi_payment);

	
$bluepaid = new Bluepaid();


$form='';
$form='<center><h1>Vous allez &#234;tre redirig&#233; sur la plateforme de paiement Bluepaid ...</h1></center>';
$form.='<form action="'.$url.'" method="POST" name="bpi_formPay">';
$form.='<input type="hidden" name="ID_BOUTIQUE" value="'.Configuration::get('BPI_MERCHID').'" >';
$form.='<input type="hidden" name="ID_CLIENT" value="'.$cart_id.'" >';


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

//die("AMOUNT OCU : $amount_ocu, check : $check, NUMBER OF OCUR : $max_occur, amount total : $amount_total, delta : $delta, init amount : $initamount");	
		


if($initamount){
	$form.='<input type="hidden" name="MONTANT_INITIAL" value="'.number_format($initamount, 2, '.', '').'" >';
}

$form.='<input type="hidden" name="MONTANT" value="'.number_format($amount_ocu, 2, '.', '').'" >';

if($temp=$bluepaid->get_nbShowIfKo()){
	if($temp>0){
		//Définit le nombre de jours entre chaque représentation de l'occurence ayant échouée
		$form.='<input type="hidden" name="TOLERANCE_JOURS" value="2" >';		
		$form.='<input type="hidden" name="TOLERANCE_NBMAX" value="'.$temp.'" >';
	}
}


if($bluepaid->_get_maxOccur()){
	$form.='<input type="hidden" name="NB_OCCURENCES_MAX" value="'.$max_occur.'" >';
}
$form.='<input type="hidden" name="PERIODE_NB" value="1" >';
$form.='<input type="hidden" name="PERIODE_TYPE" value="M" >';
$form.='<input type="hidden" name="TYPE_ABO" value="STANDARD" >';

$form.='<input type="hidden" name="DEVISE" value="'.$used_currency.'" >';
$form.='<input type="hidden" name="DIVERS" value="'.$customer->secure_key.'" >';
$form.='<input type="hidden" name="EMAIL_CLIENT" value="'.$customer->email.'" >';
$form.='<input type="hidden" name="URL_RETOUR_BO" value="paiement-securise.bluepaid.com/services/bp_return_customer.php" >';


$form.='<input type="hidden" name="URL_RETOUR_OK" value="'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/bluepaid/controllers/front/payment_return.php" >';
$form.='<input type="hidden" name="URL_RETOUR_STOP" value="'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.$order_filename.'" >';

$form.='<input type="hidden" name="HvTag" value="D2C_ps_bpi_mp.bluepaid.com.php" >';

$form.='</form>';

echo $form;
echo'<script>document.bpi_formPay.submit();</script>';


/*
<input type="hidden" name="set_secure_return" value="true" >
<input type="hidden" name="set_secure_conf" value="true" >
*/

