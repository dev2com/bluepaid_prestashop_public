<?php
/**
 * Bluepaid payment
 *
 * Accept payment by CB with Bluepaid.
 *
 * @class 		Bluepaid
 * @version		2.1
 * @category	Payment
 * @author 		Bluepaid - Julien L.
 */
require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');
require_once(dirname(__FILE__).'/bluepaid.php');
ini_set('default_charset', 'UTF-8');

global $cart, $cookie;
if (!isset($cookie->bpi_payment) AND $cookie->bpi_payment === false)
	Tools::redirect('order.php');
unset($cookie->bpi_payment);

if (Configuration::get('BPI_MERCHID')){
	//$url =  'https://www.bluepaid.com/in.php';
	$url =  'https://paiement-securise.bluepaid.com/ABOv2/in.php';
}
else{
	echo "erreur configuration";exit();
}
	
$bluepaid = new Bluepaid();

$customer = new Customer(intval($cart->id_customer));
//$customer->secure_key; => hash cart
//$cart->id; => id cart
$param = array('custom' => $cart->id, 'id_module' => $bluepaid->id, 'amount' => $cart->getOrderTotal(true), 'secure_key' => $customer->secure_key);

$products = $cart->getProducts();
$invoice_address = new Address(intval($cart->id_address_invoice));
$delivery_address = new Address(intval($cart->id_address_delivery));
$carrier = new Carrier(intval($cart->id_carrier));
$currency = new Currency(intval($cart->id_currency));
$invoice_country = new Country(intval($invoice_address->id_country));
$delivery_country = new Country(intval($delivery_address->id_country));
$nb = 0;

foreach ($products as $product)
	$nb += $product['cart_quantity'];

$xml = '
';

$xml .= '
	<?xml version="1.0" encoding="UTF-8" ?>
	<control>
		 <utilisateur type="facturation" qualite="2"> 
			 <nom titre="'.(($customer->id_gender == 1) ? 'monsieur' : 'madame').'">'.$invoice_address->lastname.'</nom> 
			<prenom>'.$invoice_address->firstname.'</prenom> 
			<telhome>'.$invoice_address->phone.'</telhome>
			<telmobile>'.$invoice_address->phone_mobile.'</telmobile>
			 <email>'.$customer->email.'</email>
		</utilisateur>';
$xml .= '
		<adresse type="facturation" format="1">
			<rue1>'.$invoice_address->address1.'</rue1>
			<rue2>'.$invoice_address->address2.'</rue2>
			<cpostal>'.$invoice_address->postcode.'</cpostal>
			<ville>'.$invoice_address->city.'</ville>
			<pays>'.$invoice_country->name[intval($cookie->id_lang)].'</pays>
		</adresse>
		<adresse type="livraison" format="1">
			<rue1>'.$delivery_address->address1.'</rue1>
			<rue2>'.$delivery_address->address2.'</rue2>
			<cpostal>'.$delivery_address->postcode.'</cpostal>
			<ville>'.$delivery_address->city.'</ville>
			<pays>'.$delivery_country->name[intval($cookie->id_lang)].'</pays>
		</adresse>
		<wallet version="1.0">
			<datecom>'.date("Y-m-d H:i:s").'</datecom>
			<crypt version="2.0">'.$crypt.'</crypt>
		</wallet>';
		
if(!Tools::getValue('payment')){
	//Si on veut rajouter les types depaiement (abonnement, rembt...)
	Tools::redirect('order.php');
	$xml .= '
		<options-paiement type="comptant" comptant-bpi="1" comptant-bpi-offert="1"></options-paiement>';
}
$xml .= '
	</control>';

	
$xmlParam = '<ParamCBack>';
foreach ($param as $key => $value)
	$xmlParam .= '<obj>
					<name>'.$key.'</name>
					<value>'.$value.'</value>
				</obj>';
$xmlParam .= '</ParamCBack>';

$flux = $bluepaid->clean_xml($xml);
$flux = str_replace('"', "'", $flux);
$flux = mb_convert_encoding($flux, 'UTF-8', mb_detect_encoding($flux));

$flux2 = $bluepaid->clean_xml($xmlParam);
$flux2 = str_replace('"', "'", $flux2);

$form='';

$form='<center><h1>Vous allez &#234;tre redirig&#233; sur la plateforme de paiement Bluepaid ...</h1></center>';
$form.='<form action="'.$url.'" method="POST" name="bpi_formPay">';
$form.='<input type="hidden" name="ID_BOUTIQUE" value="'.Configuration::get('BPI_MERCHID').'" >';
$form.='<input type="hidden" name="ID_CLIENT" value="'.$cart->id.'" >';

$amount_occur=0; $amount_total=$cart->getOrderTotal(true);
$withinitamount=false;
if($init_amount=$bluepaid->_get_InitAmount($amount_total)){
	$amount_total-=$init_amount;
	$form.='<input type="hidden" name="MONTANT_INITIAL" value="'.number_format($init_amount, 2, '.', '').'" >';
	$withinitamount=true;
}

if($amount=$bluepaid->_get_OccurAmount($amount_total, $withinitamount)){
	$form.='<input type="hidden" name="MONTANT" value="'.number_format($amount, 2, '.', '').'" >';
}

if($temp=$bluepaid->get_nbShowIfKo()){
	if($temp>0){
		//Définit le nombre de jours entre chaque représentation de l'occurence ayant échouée
		$form.='<input type="hidden" name="TOLERANCE_JOURS" value="2" >';		
		$form.='<input type="hidden" name="TOLERANCE_NBMAX" value="'.$temp.'" >';
	}
}


if($bluepaid->_get_maxOccur()){
	$form.='<input type="hidden" name="NB_OCCURENCES_MAX" value="'.$bluepaid->_get_maxOccur().'" >';
}
$form.='<input type="hidden" name="PERIODE_NB" value="1" >';
$form.='<input type="hidden" name="PERIODE_TYPE" value="M" >';
$form.='<input type="hidden" name="TYPE_ABO" value="STANDARD" >';

$form.='<input type="hidden" name="DEVISE" value="'.$currency->iso_code.'" >';
$form.='<input type="hidden" name="DIVERS" value="'.$customer->secure_key.'" >';
$form.='<input type="hidden" name="EMAIL_CLIENT" value="'.$customer->email.'" >';
$form.='<input type="hidden" name="URL_RETOUR_BO" value="www.bluepaid.com/services/bp_return_customer.php" >';
$form.='<input type="hidden" name="URLCall" value="http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/bluepaid/payment_return.php">';
$form.='</form>';

echo $form;
echo'<script>document.bpi_formPay.submit();</script>';


/*
<input type="hidden" name="set_secure_return" value="true" >
<input type="hidden" name="set_secure_conf" value="true" >
*/

