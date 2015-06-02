<?php
require_once(dirname(__FILE__).'./../../../../config/config.inc.php');
/** Call init.php to initialize context */
require_once(dirname(__FILE__).'/../../../../init.php');
require_once(dirname(__FILE__).'/../../bluepaid.php');
ini_set('default_charset', 'UTF-8');


$order_confirmation_filename = "index.php?controller=order-confirmation";
if (version_compare(_PS_VERSION_, '1.6', '<'))$order_confirmation_filename = "order-confirmation.php";

$payment = new bluepaid();

if (!Tools::getValue('id_client'))
	$errors .= $payment->displayName.' '.$payment->l('id panier non renseigné')."\n";
else
	$id_cart = intval(Tools::getValue('id_client'));
	
if (!Tools::getValue('divers'))
	$errors .= $payment->displayName.' '.$payment->l('hash control not specified')."\n";	
else
	$hashControl = Tools::getValue('divers');
	
if (!Tools::getValue('etat'))
	$errors .= $payment->displayName.' '.$payment->l('Etat de la transaction non spécifié')."\n";	
else
	$hashControl = Tools::getValue('divers');

if (isset($_GET['mode'])){
	if($_GET['mode'] == "test"){
		//transaction de test
	}
}
	
if (!isset($_GET['montant']))
	$errors .= $payment->displayName.' '.$payment->l('"Montant" non specifie, impossible de vérifie le montant reçu')."\n";
else
	$amount = floatval(Tools::getValue('montant'));
	
if (empty($errors))
{
	$cart = new Cart($id_cart);
	if (!$cart->id)
		$errors = $payment->l('cart not found')."\n";
	elseif (Order::getOrderByCartId($id_cart))
		$errors = $payment->l('order already exists')."\n";
	else
	{
		$feedback = $payment->l('Transaction OK:').' RefID='.Tools::getValue('id_client').' & TransactionID='.Tools::getValue('id_trans');	
		if ($cookie->id_cart == intval($cookie->last_id_cart))
			unset($cookie->id_cart);
	}
}
else
	$errors .= $payment->l('One or more error occured during the validation')."\n";

$customer = new Customer(intval($cart->id_customer));	
if (!empty($errors) AND isset($id_cart) AND isset($amount))
{
	if ($hashControl != $customer->secure_key)
		$errors .= $payment->displayName.$payment->l('hash control invalid (data do not come from Bluepaid)')."\n";
		
	if ($cookie->id_cart == intval($cookie->last_id_cart))
		unset($cookie->id_cart);
}

if (version_compare(_PS_VERSION_, '1.6', '<')) 
	$url = $order_confirmation_filename.'?';
else
	$url = $order_confirmation_filename.'&';
	


if (!empty($errors) OR !$id_cart){
	$url.= 'error=true';
}
else
{
	$customer = new Customer(intval($cart->id_customer));
	$url.= 'id_cart='.$id_cart.'&key='.$customer->secure_key;
}
Tools::redirect($url);	
			
?>
