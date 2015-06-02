<?php
/**
 * Bluepaid payment
 *
 * Accept payment by CB with Bluepaid.
 *
 * @class 		Bluepaid
 * @version		2.8
 * @category	Payment
 * @author 		Dev2Com - Julien L.
 */
//------------------------------
if(!file_exists('logs'))
	mkdir('logs');
$file = "logs/logs-".date("Y-m-d").".txt";
$fd = fopen($file, "a+");
$data = "--------------------------------- \n";
$data .= date("d-m-Y H:i:s")." \n";
$data .= "--------------------------------- \n";
$data .= "--------------------------------- \n";
if(isset($_POST)){
	foreach($_POST as $key=>$val){
		$data .= $key."=>".$val."\n";
	}
}

if(isset($_GET)){
	foreach($_GET as $key=>$val){
		$data .= $key."=>".$val."\n";
	}
}
$data .= "--------------------------------- \n";
fwrite($fd, $data);
fclose($fd);
//--------------------
	
if('POST' != $_SERVER['REQUEST_METHOD']){
	die('Accès non autorisé');
}
	
$test_etat=false;
if(isset($_REQUEST['etat']) && !empty($_REQUEST['etat'])){
	$test_etat=strtolower($_REQUEST["etat"]);
	if($test_etat=="ok"||$test_etat=="ko"){}
	else{
		$test_etat=false;
	}
}
if($test_etat){
	
	include_once(dirname(__FILE__).'/../../config/config.inc.php');
	
	$id_cart 	= Tools::getValue('id_client');
	$testEtat	= strtolower(Tools::getValue('etat'));
	$id_trans	= Tools::getValue('id_trans');
	$mode		= Tools::getValue('mode');
	$amount		= str_replace(',','.',Tools::getValue('montant'));
	$devise		= Tools::getValue('devise');
	$divers		= Tools::getValue('divers');
	$langue		= Tools::getValue('langue');
	
	$orderState = _PS_OS_PAYMENT_;
	
	if (!Tools::getValue('etat'))
		$orderState  = _PS_OS_ERROR_;
	
	if (!Tools::getValue('divers'))
		$orderState  = _PS_OS_ERROR_;
	else
		$hashControl = Tools::getValue('divers');
	
	if (!$id_cart)
		exit();		
	$cart = new Cart($id_cart);
	if (!$cart->id)
		exit();	
	$customer = new Customer(intval($cart->id_customer));
		
	
	global $cookie, $cart;	
	if (!Order::getOrderByCartId($cart->id))
	{
			if(isset($_REQUEST["mode"]) && Tools::getValue('mode')=="test"){
				$feedback = 'Order Create IN TEST';	
			}else{
				$feedback = 'Order Create';	
			}	
			$feedback.= " Transaction ".Tools::getValue('id_trans');			
	}
	
	if($id_order = Order::getOrderByCartId(intval($cart->id)))
		$order = new Order(intval($id_order));	
		
	include_once(dirname(__FILE__).'/bluepaid.php');	
	$payment = new bluepaid();
		
	if(!$payment->Is_authorizedIp($_SERVER['REMOTE_ADDR'])){
		die('INVALID SOURCE '.$_SERVER['REMOTE_ADDR']);
	}	
	if ($customer->secure_key != $hashControl){
		$orderState = _PS_OS_CANCELED_;
		$errors .= $payment->displayName.$payment->l('hash control invalid (data do not come from Bluepaid)')."\n";
	}
				
	switch($testEtat){
		case "ok":
			if(isset($_REQUEST["mode"])){
				$testValue=strtolower(Tools::getValue('mode'));
				switch($testValue){
					case "test":
						//$id_order_state = Configuration::get('PS_OS_PAYMENT');
						$id_order_state = Configuration::get('PS_OS_ERROR');
						$payment->validateOrder(intval($cart->id), $id_order_state, $amount, 'bluepaid', $feedback, "", $cart->id_currency, false, $cart->secure_key);							
					break;
					
					case "r":
						##PAIEMENT ANNULE
						/*$id_order_state = Configuration::get('PS_OS_REFUND');
						$payment->validateOrder(intval($cart->id), $id_order_state, $amount, 'bluepaid', $feedback, NULL, $cart->id_currency, false, $cart->secure_key);	*/
						
						$history = new OrderHistory();
						$history->id_order = (int)$cart->id;
						$history->changeIdOrderState(Configuration::get('PS_OS_REFUND'), $history->id_order);
						$history->addWithemail();
					break;
					
					case "":
					case " ":
						$id_order_state = Configuration::get('PS_OS_PAYMENT');
						$payment->validateOrder(intval($cart->id), $id_order_state, $amount, 'bluepaid', $feedback, "", $cart->id_currency, false, $cart->secure_key);		
						$id_order = Order::getOrderByCartId(intval($cart->id));
						$history = new OrderHistory();
						$history->id_order = $id_order;
						$history->changeIdOrderState(Configuration::get('PS_OS_PAYMENT'), $id_order);
						if ($cookie->id_cart == intval($cookie->last_id_cart))
							unset($cookie->id_cart);
					break;
				}
				
			}else{
				$id_order_state = Configuration::get('PS_OS_PAYMENT');
				$payment->validateOrder(intval($cart->id), $id_order_state, $amount, 'bluepaid', $feedback, "", $cart->id_currency, false, $cart->secure_key);	
			}
		break;
		
		case "ko":
			$id_order_state = Configuration::get('PS_OS_ERROR');
			$payment->validateOrder(intval($cart->id), $id_order_state, $amount, 'bluepaid', "Transaction securisee par Bluepaid", "", $cart->id_currency, false, $cart->secure_key);	
		break;
		
		case "attente":
			//On ne fait rien c l'état initial
		break;
		
		default:			
			//$id_order_state = Configuration::get('PS_OS_ERROR');
			//$payment->validateOrder(intval($cart->id), $id_order_state, $amount, 'bluepaid', $feedback, NULL, $cart->id_currency, false, $cart->secure_key);	
		break;
	}	
}

?>