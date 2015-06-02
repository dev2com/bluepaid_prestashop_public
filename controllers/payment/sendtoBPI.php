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
unset($cookie->bpi_payment);
	
$bluepaid = new Bluepaid();

	
echo '
<center><h1>Vous allez &#234;tre redirig&#233; sur la plateforme de paiement Bluepaid ...</h1></center>
<form action="'.$url.'" method="POST" name="bpi_formPay">
<input type="hidden" name="id_boutique" value="'.Configuration::get('BPI_MERCHID').'" >
<input type="hidden" name="id_client" value="'.$cart_id.'" >
<input type="hidden" name="montant" value="'.$amount.'" >
<input type="hidden" name="devise" value="'.$used_currency.'" >
<input type="hidden" name="langue" value="FR" >
<input type="hidden" name="divers" value="'.$customer->secure_key.'" >
<input type="hidden" name="email_client" value="'.$customer->email.'" >
<input type="hidden" name="url_retour_bo" value="paiement-securise.bluepaid.com/services/bp_return_customer.php" >
<input type="hidden" name="url_retour_ok" value="'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/bluepaid/controllers/front/payment_return.php" >
<input type="hidden" name="url_retour_stop" value="'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.$order_filename.'" >
<input type="hidden" name="HvTag" value="D2C_ps_bpi.bluepaid.com.php" >
</form>
<script>document.bpi_formPay.submit();</script>';

/*
<input type="hidden" name="set_secure_return" value="true" >
<input type="hidden" name="set_secure_conf" value="true" >
*/

