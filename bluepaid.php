<?php
/**
 * Bluepaid payment
 *
 * Accept payment by CB with Bluepaid.
 *
 * @class 		Bluepaid
 * @version		2.9
 * @category	Payment
 * @author 		Dev2Com - Julien L.
 */

class Bluepaid extends PaymentModule
{
	private	$_html = '';
	private $_postErrors = array();
	private $_postWarnings = array();
	
	private $bpi_default_ip = array(0=>'193.33.47.34', 1=>'193.33.47.35', 2=>'193.33.47.39', 3=>'87.98.218.80' );

	// category defined by Receive&Pay
	private $categories = array(
		1 => 'Alimentation & gastronomie',
		2 => 'Auto & moto',
		3 => 'Culture & divertissements',
		4 => 'Maison & jardin',
		5 => 'Electroménager',
		6 => 'Enchères et achats groupés',
		7 => 'Fleurs & cadeaux',
		8 => 'Informatique & logiciels',
		9 => 'Santé & beauté',
		10 => 'Services aux particuliers',
		11 => 'Services aux professionnels',
		12 => 'Sport',
		13 => 'Vêtements & accessoires',
		14 => 'Voyage & tourisme',
		15 => 'Hifi, photo & vidéos',
		16 => 'Téléphonie & communication',
		17 => 'Bijoux et métaux précieux',
		18 => 'Articles et accessoires pour bébé',
		19 => 'Sonorisation & lumière'
	);

	// return values defined by Receive&Pay
	private $tags = array(
		// Zone Paiement
		0 => 'Commande avortée',
		1 => 'OK Commande acceptée validée',
		2 => 'KO Commande refusée (fraude)',
		3 => 'SU Commande sous surveillance FIA-NET',
		// Zone Surveillance (si Tag vaut 3)
		10 => 'Surveillance OK, la commande est "libérée"',
		11 => 'Surveillance KO, la commande est annulée',
		// Zone Livraison
		100 => 'OK Clôture de la transaction (livraison)',
		101 => 'KO Annulation de la transaction'
	);

	private $_carrier_type = array(
		1 => 'Retrait de la marchandise chez le marchand',
		2 =>'Utilisation d\'un réseau de points-retrait tiers (type kiala, alveol, etc.)',
		3 => 'Retrait dans un aéroport, une gare ou une agence de voyage',
		4 => 'Transporteur (La Poste, Colissimo, UPS, DHL... ou tout transporteur privé)',
		5 => 'Emission d’un billet électronique, téléchargements'
	);

	public function __construct()
	{
		$this->name = 'bluepaid';
		$this->version = '2.9';
		//$this->tab = 'payments_gateways';
		$this->tab = 'Payment';
		
		parent::__construct();

		$this->displayName = $this->l('Bluepaid');
		$this->description = $this->l('Acceptez les paiements avec "Bluepaid" ! ');

		if (Configuration::get('BPI_MERCHID') == "")
			$this->warning = $this->l('Votre identifiant de compte d\'encaissement n\'est pas valide');
	}

	public function install()
	{
		!Db::getInstance()->Execute('
		CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'bpi_secuaccess` (
		`id_user` varchar(20) NOT NULL, `date_creation` datetime) ENGINE=MyISAM  DEFAULT CHARSET=utf8');		
		if (!Configuration::get('BPI_TYPE_DISPLAY'))
			Configuration::updateValue('BPI_TYPE_DISPLAY', 1);
		if (!parent::install() OR !$this->registerHook('payment') OR !$this->registerHook('paymentReturn') OR !$this->registerHook('adminOrder') OR !$this->registerHook('rightColumn'))
			return false;		
		
		$orderState = new OrderState();
		$langs = Language::getLanguages();
		foreach ($langs AS $lang)
			$orderState->name[$lang['id_lang']] = 'Waiting for Bluepaid validation';
		$orderState->name[2] = 'Attente de validation Bluepaid';
		$orderState->invoice = true;
		$orderState->send_email = false;
		$orderState->logable = true;
		$orderState->color = '#3333FF';
		$orderState->save();
		Configuration::updateValue('BPI_ID_ORDERSTATE', intval($orderState->id));
		
		return true;
	}

	public function uninstall()
	{
		
		Configuration::updateValue('BPI_MERCHID', "");
		Configuration::updateValue('BPI_MIN_VAL_XPAY', "");
		Configuration::updateValue('BPI_XPAY_NBOCCUR', "");
		Configuration::updateValue('BPI_XPAY_INITAMOUNT', "");
		Configuration::updateValue('BPI_XPAY_INITAMOUNT_TYPE', "");
		Configuration::updateValue('BPI_XPAY_AUTHORIZE', "");
		
		Db::getInstance()->Execute("DELETE FROM "._DB_PREFIX_."configuration WHERE name LIKE 'BPI_ID_ORDERSTATE'");
		Db::getInstance()->Execute('DROP TABLE IF EXISTS '._DB_PREFIX_.'bpi_secuaccess');
		return parent::uninstall();
	}
	
	public function _getMinAmount(){
		$conf = Configuration::getMultiple(array('BPI_MIN_VAL_XPAY'));
		return $conf['BPI_MIN_VAL_XPAY'];
	}
	public function _get_maxOccur(){
		$conf = Configuration::getMultiple(array('BPI_XPAY_NBOCCUR'));
		return $conf['BPI_XPAY_NBOCCUR'];
	}
	public function _get_OccurAmount($cartAmonut, $initamount=false){
		if(!$cartAmonut)return false;
		if($cartAmonut<0)return false;
		$conf = Configuration::getMultiple(array('BPI_XPAY_NBOCCUR'));
		$temp_num_occur=$conf['BPI_XPAY_NBOCCUR'];;
		if($initamount)$temp_num_occur--;
		return $cartAmonut/$temp_num_occur;		
	}
	public function _get_InitAmount($cartAmount=0){
		if(!$cartAmount)return false;
		if($cartAmount<0)return false;
		$conf = Configuration::getMultiple(array('BPI_XPAY_NBOCCUR', 'BPI_XPAY_INITAMOUNT', 'BPI_XPAY_INITAMOUNT_TYPE'));
		$bpi_xpay_nboccur = array_key_exists('bpi_xpay_nboccur', $_POST) ? $_POST['bpi_xpay_nboccur'] : (array_key_exists('BPI_XPAY_NBOCCUR', $conf) ? $conf['BPI_XPAY_NBOCCUR'] : '');
		$bpi_xpay_initamount = array_key_exists('bpi_xpay_initamount', $_POST) ? $_POST['bpi_xpay_initamount'] : (array_key_exists('BPI_XPAY_INITAMOUNT', $conf) ? $conf['BPI_XPAY_INITAMOUNT'] : '');
		$bpi_xpay_initamount_type = array_key_exists('bpi_xpay_initamount_type', $_POST) ? $_POST['bpi_xpay_initamount_type'] : (array_key_exists('BPI_XPAY_INITAMOUNT_TYPE', $conf) ? $conf['BPI_XPAY_INITAMOUNT_TYPE'] : '');
		
		if($bpi_xpay_initamount && $bpi_xpay_initamount>0){
			switch($bpi_xpay_initamount_type){
				case 'amount':
					if($bpi_xpay_initamount>$cartAmount)return false;
				break;
				case 'percent':
					$temp=0;
					$temp=$cartAmount*$bpi_xpay_initamount/100;
					return $temp;
				break;
			}
		}
		return false;		
	}
	public function get_nbShowIfKo(){
		$conf = Configuration::getMultiple(array('BPI_XPAY_NBKO'));
		return $conf['BPI_XPAY_NBKO'];
	}

	public function duplicateCart()
	{
		global $cart;
		if (method_exists('Cart', 'duplicate'))
		{
			$arr = $cart->duplicate();
			return $arr['cart']->id;
		}
		else
			return self::_duplicateCart(intval($cart->id));
	}

	private function _duplicateCart($id_cart)
	{
		$cart = new Cart(intval($id_cart));
		if (!$cart->id OR $cart->id == 0)
			return false;
		$db = Db::getInstance();
		$cart->id = 0;
		$cart->save();
		if (!$cart->id OR $cart->id == 0 OR $cart->id == $id_cart)
			return false;

		/* Products */
		$products = $db->ExecuteS('
		SELECT id_product, id_product_attribute, quantity, date_add
		FROM '._DB_PREFIX_.'cart_product
		WHERE id_cart='.intval($id_cart));
		$sql = 'INSERT INTO '._DB_PREFIX_.'cart_product(id_cart, id_product, id_product_attribute, quantity, date_add) VALUES ';
		if ($products)
		{
			foreach ($products AS $product)
				$sql .= '('.intval($cart->id).','.intval($product['id_product']).', '.intval($product['id_product_attribute']).', '.intval($product['quantity']).', \''.pSQL($product['date_add']).'\'),';
			$db->Execute(rtrim($sql, ','));
		}

		/* Customization */
		$customs = $db->ExecuteS('
		SELECT c.id_customization, c.id_product_attribute, c.id_product, c.quantity, cd.type, cd.index, cd.value
		FROM '._DB_PREFIX_.'customization c
		JOIN '._DB_PREFIX_.'customized_data cd ON (cd.id_customization = c.id_customization)
		WHERE c.id_cart='.intval($id_cart));

		$custom_ids = array();
		$sql_custom_data = 'INSERT INTO '._DB_PREFIX_.'customized_data (id_customization, type, index, value) VALUES ';
		if ($customs)
		{
			foreach ($customs AS $custom)
			{
				$db->Execute('INSERT INTO '._DB_PREFIX_.'customization (id_customization, id_cart, id_product_attribute, id_product, quantity)
								VALUES(\'\', '.intval($cart->id).', '.intval($custom['id_product_attribute']).', '.intval($custom['id_product']).', '.intval($custom['quantity']).')');
				$custom_ids[$custom['id_customization']] = $db->Insert_ID();
			}

			foreach ($customs AS $custom)
				$sql_custom_data .= '('.intval($custom_ids[$custom['id_customization']]).', '.intval($custom['type']).', '.intval($custom['index']).', \''.pSQL($custom['value']).'\'),';

			$db->Execute($sql_custom_data);
		}
		
		/* Discounts */
		$discounts = $db->ExecuteS('SELECT id_discount FROM '._DB_PREFIX_.'cart_discount WHERE id_cart='.intval($id_cart));
		if ($discounts)
		{
			$sql = 'INSERT INTO '._DB_PREFIX_.'cart_discount(id_cart, id_discount) VALUES ';
			foreach ($discounts AS $discount)
				$sql .= '('.intval($cart->id).', '.intval($discount['id_discount']).'),';
			$db->Execute(rtrim($sql, ','));
		}
		return $cart->id;
	}
	
	function clean_xml($xml)
	{		
		$xml = str_replace("\\'", "'", $xml);	
		$xml = str_replace("\\\"", "\"", $xml);		
		$xml = str_replace("\\\\", "\\", $xml);		
		$xml = str_replace("\t", "", $xml);		
		$xml = str_replace("\n", "", $xml);		
		$xml = str_replace("\r", "", $xml);		
		$xml = trim($xml);
		return ($xml);	
	}
	function get_delivery_date($delivery_times)
	{
		$date =  mktime(0,0,0,date("m" ), date("d" ) + $delivery_times ,date("Y" ));
		return (date("Y-m-d", $date));
	}

	public function hookPayment($params)
	{
		 if (!$this->active)
        return ;
		if (!$this->_checkCurrency($params['cart']))
			return ;
		
		global $smarty, $cookie;
		
		$cookie->bpi_payment = true;
		$total_cart = $params['cart']->getOrderTotal(true);
		$type_display = Configuration::get('BPI_TYPE_DISPLAY');
		$type = explode(",", $type_display);
		$smarty->assign('comptant', (in_array("1", $type)));
		$smarty->assign('direct', (in_array("3", $type)));
		
		if($this->_authorize_Xpayment() && ($total_cart>=$this->_getMinAmount())){
			$smarty->assign('credit', (in_array("1", $type)));		
		}
		
		
		return $this->display(__FILE__, 'bluepaid.tpl');
	}
	
	public function hookRightColumn($params)
	{
		global $smarty;
		$smarty->assign('path', 'modules/'.$this->name);
		return $this->display(__FILE__, 'logo.tpl');
	}
	
	public function hookLeftColumn($params)
	{
		return $this->hookRightColumn($params);
	}
	
	function hookPaymentReturn()
	{
		global $smarty;

		if ($params['objOrder']->module != $this->name)
			return;
		
		if (Tools::getValue('error'))
			$smarty->assign('status', 'failed');
		else
			$smarty->assign('status', 'ok');
		return $this->display(__FILE__, 'payment_return.tpl');
  }
  
  function _authorize_Xpayment(){
		$conf = Configuration::getMultiple(array('BPI_XPAY_AUTHORIZE'));
		return $conf['BPI_XPAY_AUTHORIZE'];
  }
	
	//Mise en place du module ===> Configuration
	public function displayFormSettings()
	{
		$conf = Configuration::getMultiple(array('BPI_MERCHID'));
		$merchid = array_key_exists('merchid', $_POST) ? $_POST['merchid'] : (array_key_exists('BPI_MERCHID', $conf) ? $conf['BPI_MERCHID'] : '');
		$id_lang = Configuration::get('PS_LANG_DEFAULT');	
		
		$this->_html .=
		'<form action="'.Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']).'" method="post">
			<fieldset>
			<legend><img src="../img/admin/contact.gif" />'.$this->l('Configurez votre module Bluepaid').'</legend>
				<table border="0" width="600" cellpadding="0" cellspacing="0" id="form">
					<tr><td colspan="3">Les informations suivantes vous ont été transmises par Bluepaid lors de votre inscription à leur service. Si vous n\'êtes pas encore inscrit chez Bluepaid, cliquez <a style=\'color:blue;text-decoration:underline\' href=\'http://www.bluepaid.com/devis.php\' target=\'_blank\'>ici</a><br /><br /></td></tr>
					<tr><td width="130" style="height: 35px;"><label for=\'merchid\'>Identifiant de compte d\'encaissement</label></td><td><input size="10" type="text" name="merchid" value="'.$merchid.'" /></td><td><input class="button" name="submitBluepaid_config" value="Update settings" type="submit" /></td></tr>
				</table>
			</fieldset>
		</form>';	
	}
	
	//Mise en place du module ===> Configuration des paiements multiples
	function displayFormSettingsAbo(){
		$conf = Configuration::getMultiple(array('BPI_MIN_VAL_XPAY', 'BPI_XPAY_NBOCCUR', 'BPI_XPAY_INITAMOUNT', 'BPI_XPAY_INITAMOUNT_TYPE', 'BPI_XPAY_AUTHORIZE', 'BPI_XPAY_NBKO'));
		$bpi_min_val_xpay = array_key_exists('bpi_min_val_xpay', $_POST) ? $_POST['bpi_min_val_xpay'] : (array_key_exists('BPI_MIN_VAL_XPAY', $conf) ? $conf['BPI_MIN_VAL_XPAY'] : '');
		$bpi_xpay_nboccur = array_key_exists('bpi_xpay_nboccur', $_POST) ? $_POST['bpi_xpay_nboccur'] : (array_key_exists('BPI_XPAY_NBOCCUR', $conf) ? $conf['BPI_XPAY_NBOCCUR'] : '');
		$bpi_xpay_initamount = array_key_exists('bpi_xpay_initamount', $_POST) ? $_POST['bpi_xpay_initamount'] : (array_key_exists('BPI_XPAY_INITAMOUNT', $conf) ? $conf['BPI_XPAY_INITAMOUNT'] : '');
		$bpi_xpay_initamount_type = array_key_exists('bpi_xpay_initamount_type', $_POST) ? $_POST['bpi_xpay_initamount_type'] : (array_key_exists('BPI_XPAY_INITAMOUNT_TYPE', $conf) ? $conf['BPI_XPAY_INITAMOUNT_TYPE'] : '');
		$bpi_xpay_authorize = array_key_exists('bpi_xpay_authorize', $_POST) ? $_POST['bpi_xpay_authorize'] : (array_key_exists('BPI_XPAY_AUTHORIZE', $conf) ? $conf['BPI_XPAY_AUTHORIZE'] : '');
		
		
		$bpi_xpay_nbko = array_key_exists('bpi_xpay_nbko', $_POST) ? $_POST['bpi_xpay_nbko'] : (array_key_exists('BPI_XPAY_NBKO', $conf) ? $conf['BPI_XPAY_NBKO'] : '');
		
		
		$id_lang = Configuration::get('PS_LANG_DEFAULT');	
		
			
		$form='<br /><br />';
		$form.='<form action="'.Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']).'" method="post">';
			$form.='<fieldset>';
				$form.='<legend><img src="../img/admin/contact.gif" />'.$this->l('Configurez le paiement en plusieurs fois avec Bluepaid').'</legend>';
				$form.='<table border="0" width="600" cellpadding="0" cellspacing="0" id="form">';
					$form.='<caption style="text-align:left"><p>Indiquez ici les paramètres d\'utilisation du paiement en plusieurs fois</p></caption>';
					
					$form.='<tr>';
						$form.='<td width="130" align="left"><label for=\'bpi_xpay_authorize\' style=\'text-align:left\'>Autoriser les paiements en X fois</label></td>';
						$check_authorize="";
						if($bpi_xpay_authorize==1)$check_authorize=' checked="checked"';
						//echo $check_authorize;
						$form.='<td><input type="checkbox" name="bpi_xpay_authorize" id="bpi_xpay_authorize" value="1"'.$check_authorize.' /></td>';
					$form.='</tr>';			
					$form.='<tr>';
						$form.='<td colspan="2" height="15px"></td>';
					$form.='</tr>';
					
					#Montant mini pour le déclenchement de l'option paiement en plusieurs fois
					$form.='<tr>';
						$form.='<td width="130" align="left"><label style=\'text-align:left\' for=\'merchid\'>Autoriser les paiements en X fois dès </label></td>';
						$form.='<td><input size="10" type="text" name="bpi_min_val_xpay" value="'.$bpi_min_val_xpay.'" style="top:0px" /></td>';
					$form.='</tr>';
					$form.='<tr>';
						$form.='<td align="left" colspan="2"><i>Indiquez ici le montant minimum pour le paiement en plusieurs fois</i></td>';
					$form.='</tr>';					
					$form.='<tr>';
						$form.='<td colspan="2" height="15px"></td>';
					$form.='</tr>';
					
					#Nombre d'occurrences maxi
					$form.='<tr>';
						$form.='<td width="130" align="left"><label style=\'text-align:left\' for=\'merchid\'>Proposer le paiement en</label></td>';
						$form.='<td>';
							$form.='<select name="bpi_xpay_nboccur">';
								for($i=1; $i<=10; $i++):
									$selectit="";
									if($i==$bpi_xpay_nboccur)$selectit=" selected";
									$form.='<option value="'.$i.'"'.$selectit.'>'.$i.'</option>';
								endfor;
							$form.='</select> '.$this->l("time").'';
						$form.='</td>';
					$form.='</tr>';
					$form.='<tr>';
						$form.='<td align="left" colspan="2"><i>Indiquez ici le nombre de prélèvements à effectuer</i></td>';
					$form.='</tr>';					
					$form.='<tr>';
						$form.='<td colspan="2" height="15px"></td>';
					$form.='</tr>';
					
					#Montant initial différent
					$form.='<tr>';
						$form.='<td width="130" align="left"><label style=\'text-align:left\' for=\'merchid\'>Montant initial </label></td>';
						$form.='<td>';
						$form.='<input size="10" type="text" name="bpi_xpay_initamount" value="'.$bpi_xpay_initamount.'" style="top:0px" />';
							$form.='<select name="bpi_xpay_initamount_type">';
								$selec_percent=""; $select_amount="";
								if($bpi_xpay_initamount_type=='percent')$selec_percent=" selected";
								if($bpi_xpay_initamount_type=='amount')$select_amount=" selected";
								$form.='<option value="percent"'.$selec_percent.'>%</option>';
								$form.='<option value="amount"'.$select_amount.'>€, $... </option>';
							$form.='</select>';
						
						$form.='</td>';
					$form.='</tr>';
					$form.='<tr>';
						$form.='<td align="left" colspan="2"><i>Indiquez ici le montant ou % à prélever lors de la première transaction  (laisser vide si identique au montant des occurences à venir)</td>';
					$form.='</tr>';					
					$form.='<tr>';
						$form.='<td colspan="2" height="15px"></td>';
					$form.='</tr>';	
					
					
					#Transactions d'abonnement ayant été refusées => représentation
					$form.='<tr>';
						$form.='<td width="130" align="left"><label style=\'text-align:left\' for=\'merchid\'>Nombre de représentations si KO </label></td>';
						$form.='<td>';
							$form.='<select name="bpi_xpay_nbko">';
								for($i=0; $i<=5; $i++):
									$selectit="";
									if($i==$bpi_xpay_nbko)$selectit=" selected";
									$form.='<option value="'.$i.'"'.$selectit.'> '.$i.' </option>';
								endfor;
							$form.='</select> time';
						$form.='</td>';
					$form.='</tr>';
					$form.='<tr>';
						$form.='<td align="left" colspan="2"><i>Indiquez ici le nombre de représentations à effectuer pour une transaction ayant été refusée)</i>"</td>';
					$form.='</tr>';	
					
					
									
					
					$form.='<tr>';
						$form.='<td colspan="2" align="right"><br /><input class="button" name="submitBluepaid_configxpay" value="Update settings" type="submit" /></td>';
					$form.='</tr>';
					
				$form.='</table>';
			$form.='</fieldset>';
		$form.='</form>';
		
		
		
		$this->_html .= $form;
	}
	
	function displayFormSettingsSecurity(){
		$conf = Configuration::getMultiple(array('BPI_AUTHORIZED_IP'));
		$bpi_authorized_ip = array_key_exists('bpi_authorized_ip', $_POST) ? $_POST['bpi_authorized_ip'] : (array_key_exists('BPI_AUTHORIZED_IP', $conf) ? $conf['BPI_AUTHORIZED_IP'] : '193.33.47.34;193.33.47.35;193.33.47.39;87.98.218.80');
		
		
		$this->_html .= ('<form action="'.Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']).'" method="post">');
			$this->_html .= ('<fieldset>');
				$this->_html .= ('<legend><img src="../img/admin/warning.gif" />Configuration de sécurité</legend>');
				$this->_html .= ('<h4>Attention</h4><p>Ne modifiez ces valeurs que si vous êtes surs de ce que vous faites.</p>');
				$this->_html .= ('<label>'.$this->l('Authorized IP :').'</label>');
				$this->_html .= (' <div class="margin-form">');
					$this->_html .= (' <input type="text" name="bpi_authorized_ip" size="50" value="'.$bpi_authorized_ip.'" placeholder="XXX.XX.XX.XX" /> <input class="button" name="submitBluepaid_security" value="Update settings" type="submit" />');					
				$this->_html .= (' </div>');
				$this->_html .= (' <div class="margin-form"><i>Indiquez ici les adresses IP des serveurs autorisés à comuniquer les informations de transactions à votre site.<br />Séparez les adresses par des ";". Par exemple : 193.33.47.34<b>;</b>193.33.47.35</i></div>');
			$this->_html .= ('</fieldset>');
		$this->_html .= ('</form>');
	}
	
	public function getContent()
	{
		global $cookie;
		$this->_html = '<h2><img src="../modules/bluepaid/cadenas.png" alt="Bluepaid"/> <img src="../modules/bluepaid/base_line.png" alt="Le paiement so blue"/></h2>';

		if (isset($_POST['submitBluepaid_config']))
		{
			if (empty($_POST['merchid']))
				$this->_postErrors[] = $this->l('L\'identifiant de compte d\'encaissement Bluepaid est obligatoire.');
			if (!sizeof($this->_postErrors))
			{
				Configuration::updateValue('BPI_MERCHID', $_POST['merchid']);
			}
			else
				$this->displayErrors();
		}
		
		
		
		if (isset($_POST['submitBluepaid_configxpay'])){
			if (!sizeof($this->_postErrors)){	
				$bpi_xpay_authorize = (isset($_POST["bpi_xpay_authorize"])) ? $_POST["bpi_xpay_authorize"] : 0;
				
		
				Configuration::updateValue('BPI_XPAY_AUTHORIZE', $_POST['bpi_xpay_authorize']);
				Configuration::updateValue('BPI_MIN_VAL_XPAY', $_POST['bpi_min_val_xpay']);
				Configuration::updateValue('BPI_XPAY_NBOCCUR', $_POST['bpi_xpay_nboccur']);
				Configuration::updateValue('BPI_XPAY_INITAMOUNT', $_POST['bpi_xpay_initamount']);
				Configuration::updateValue('BPI_XPAY_INITAMOUNT_TYPE', $_POST['bpi_xpay_initamount_type']);	
				Configuration::updateValue('BPI_XPAY_NBKO', $_POST['bpi_xpay_nbko']);		
				
				
				
			}
			else
				$this->displayErrors();
		}
		
		if (isset($_POST['submitBluepaid_security']))
		{
			if (!sizeof($this->_postErrors))
			{
				Configuration::updateValue('BPI_AUTHORIZED_IP', $_POST['bpi_authorized_ip']);
			}
			else
				$this->displayErrors();
		}
		
		
		
		$this->displayFormSettings();
		$this->displayFormSettingsAbo();
		$this->displayFormSettingsSecurity();
		$this->displayInformations();

		return $this->_html;
	}

	public function displayInformations()
	{
		$url = 'https://moncompte.bluepaid.com/index.php'; 

		$this->_html.= '<br /><br />
		<fieldset><legend>'.$this->l('Suivez vos encaissements depuis votre espace client Bluepaid').'</legend>
			<p>'.$this->l('Votre espace d\'administration Bluepaid').' :&nbsp;<a href="'.$url.'" target="_blank" style="color:blue;text-decoration:underline">'.$url.'</a></p><br/>
			<p>'.$this->l('Votre espace d\'administration Bluepaid vous permet de suivre vos transactions, reversements, effectuer des remboursements, modifier vos informations de banque...').'</p>
			<!--<p><b>'.$this->l('Return URL').'</b>:&nbsp;http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.$this->name.'/push.php</p>-->
		</fieldset>
		<div class="clear">&nbsp;</div>
		<!--<fieldset>
			<legend>PrestaShop Addons</legend>
			'.$this->l('This module has been developped by Bluepaid SAS and can only be sold by Bluepaid').' <a href="http://addons.bluepaid.com">addons.bluepaid.com</a>.<br />
			'.$this->l('Please report all bugs to').' <a href="mailto:addons@bluepaid.com">addons@bluepaid.com</a> '.$this->l('or using our').' <a href="http://addons.bluepaid.com/contact-form.php">'.$this->l('contact form').'</a>.
		</fieldset>-->';
	}

	public function displayConf()
	{
		$this->_html .= '
		<div class="conf confirm">
			<img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />
			'.$this->l('Settings updated').'
		</div>';
	}

	public function displayErrors()
	{
		$nbErrors = sizeof($this->_postErrors);
		$this->_html .= '
		<div class="error">
			<h3>'.($nbErrors > 1 ? $this->l('il y a ') : $this->l('Il y a')).' '.$nbErrors.' '.($nbErrors > 1 ? $this->l('erreurs') : $this->l('erreur')).'</h3>
			<ul>';
		foreach ($this->_postErrors AS $error)
			$this->_html .= '<li>'.$error.'</li>';
		$this->_html .= '
			</ul>
		</div>';
	}
	
	public function displayWarnings()
	{
		$nbWarnings = sizeof($this->_postWarning);
		$this->_html .= '
		<div class="warn">
			<h3>'.($nbWarnings > 1 ? $this->l('There are') : $this->l('There is')).' '.$nbWarnings.' '.($nbWarnings > 1 ? $this->l('warnings') : $this->l('warning')).'</h3>
			<ul>';
		foreach ($this->_postWarning AS $warning)
			$this->_html .= '<li>'.$warning.'</li>';
		$this->_html .= '
			</ul>
		</div>';
	}
  
	
	public function Is_authorizedIp($ip=''){		
		if($ip){
			$a_ipaddressbpi=Configuration::get('BPI_AUTHORIZED_IP');
			if($a_ipaddressbpi)
				$ipaddressbpi=explode(';', $a_ipaddressbpi);
			else 
				$ipaddressbpi = $this->bpi_default_ip;
			if($ipaddressbpi){
				if(is_array($ipaddressbpi)){
					foreach($ipaddressbpi as $key=>$value){
						if($value == $ip){
							return true;
						}
					}
				}elseif($ipaddressbpi==$ip){
					return true;
				}
				return false;			
			}
		}
		return false;
	}
	
	private function _checkCurrency($cart)
	{
		$currency_order = new Currency(intval($cart->id_currency));
		$currencies_module = $this->getCurrency();
		$currency_default = Configuration::get('PS_CURRENCY_DEFAULT');
		
		
		if (is_array($currencies_module))
			foreach ($currencies_module AS $currency_module)
				if ($currency_order->id == $currency_module['id_currency'])
					return true;
	}
  
  ///////OLDS////////

	public function getSACCarriers()
	{
		$carriers = Db::getInstance()->ExecuteS('SELECT id_carrier, id_sac_carrier FROM '._DB_PREFIX_.'sac_carriers');
		$sac_carrier = array();
		foreach ($carriers AS $carrier)
			$sac_carrier[$carrier['id_carrier']] = $carrier['id_sac_carrier'];
		return $sac_carrier;
	}

	public function getRNPCategories()
	{
		$categories = Db::getInstance()->ExecuteS('SELECT id_category, id_rnp FROM '._DB_PREFIX_.'rnp_categories');
		$rnp_cat = array();
		if ($categories)
			foreach ($categories AS $category)
				$rnp_cat[$category['id_category']] = $category['id_rnp'];
		return $rnp_cat;
	}

}

?>
