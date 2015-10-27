<?php
/**
 * Bluepaid payment
 *
 * Accept payment by CB with Bluepaid.
 *
 * @class 		Bluepaid
 * @version		2.9.8.7
 * @category	Payment
 * @author 		Dev2Com
 */

class Bluepaid extends PaymentModule
{
	private	$_html = '';
	private $_postErrors = array();
	private $_postWarnings = array();
	
	private $bpi_default_ip = array(0=>'193.33.47.34', 1=>'193.33.47.35', 2=>'193.33.47.39', 3=>'87.98.218.80' );

	public function __construct()
	{
		$this->name = 'bluepaid';
		$this->version = '2.9.8.7';
		$this->tab = 'payments_gateways';
		$this->need_instance = 1;
		$this->controllers = array('payment', 'validation');
		
		parent::__construct();

		$this->author = 'Dev2Com';
		
		$this->displayName = $this->l('Bluepaid');
		$this->description = $this->l('Accept payments by Credit card with Bluepaid');

		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

		if (Configuration::get('BPI_MERCHID') == "")
			$this->warning = $this->l('Invalid bluepaid merchant ID');
		
		// Retrocompatibility
		$this->initContext();
	}

	/*
	 *
	 * INSTALL / UNINSTALL
	 *
	*/
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
		
		/*
		 *
		 * ORDER STATUS
		 *
		*/
		

		if (!Configuration::get('BLUEPAID_STATUS_ACCEPTED_DEBUG'))
		{
			// create an accepted order status [TEST]
			$lang = array (
					'en' => 'Payment accepted by Bluepaid [TEST]',
					'fr' => 'Paiement accepté par Bluepaid [TEST]',
					'it' => 'Payment accepted by Bluepaid [TEST]',
			);

			$name = array();
			foreach (Language::getLanguages(true) as $language)
				$name[$language['id_lang']] = key_exists($language['iso_code'], $lang) ? $lang[$language['iso_code']] : '';

			$bluepaid_state = new OrderState();
			$bluepaid_state->name = $name;
			$bluepaid_state->invoice = true;
			$bluepaid_state->send_email = true;
			$bluepaid_state->module_name = $this->name;
			$bluepaid_state->color = '#A1F8A1';
			$bluepaid_state->unremovable = true;
			$bluepaid_state->hidden = false;
			$bluepaid_state->logable = false;
			$bluepaid_state->delivery = false;
			$bluepaid_state->shipped = false;
			$bluepaid_state->paid = true;

			if (!$bluepaid_state->save() || !Configuration::updateValue('BLUEPAID_STATUS_ACCEPTED_DEBUG', $bluepaid_state->id))
				return false;
			// add small icon to status
			Tools::copy(
				_PS_MODULE_DIR_.$this->name.'/views/img/os_validated.gif',
				_PS_IMG_DIR_.'os/'.Configuration::get('BLUEPAID_STATUS_ACCEPTED_DEBUG').'.gif');
		}

		if (!Configuration::get('BLUEPAID_STATUS_REFUSED_DEBUG'))
		{
			// create a refused order status [TEST]
			$lang = array (
					'en' => 'Payment error by Bluepaid [TEST]',
					'fr' => 'Erreur de paiement par Bluepaid [TEST]',
					'it' => 'Payment error by Bluepaid [TEST]',
			);

			$name = array();
			foreach (Language::getLanguages(true) as $language)
				$name[$language['id_lang']] = key_exists($language['iso_code'], $lang) ? $lang[$language['iso_code']] : '';

			$bluepaid_state = new OrderState();
			$bluepaid_state->name = $name;
			$bluepaid_state->invoice = false;
			$bluepaid_state->send_email = false;
			$bluepaid_state->module_name = $this->name;
			$bluepaid_state->color = '#EA3737';
			$bluepaid_state->unremovable = true;
			$bluepaid_state->hidden = false;
			$bluepaid_state->logable = false;
			$bluepaid_state->delivery = false;
			$bluepaid_state->shipped = false;
			$bluepaid_state->paid = false;

			if (!$bluepaid_state->save() || !Configuration::updateValue('BLUEPAID_STATUS_REFUSED_DEBUG', $bluepaid_state->id))
				return false;
			// add small icon to status
			Tools::copy(
				_PS_MODULE_DIR_.$this->name.'/views/img/os_validated.gif',
				_PS_IMG_DIR_.'os/'.Configuration::get('BLUEPAID_STATUS_REFUSED_DEBUG').'.gif');
		}
		
		
		
		return true;
	}

	public function uninstall()
	{
		Configuration::deleteByName('BPI_MERCHID');
		Configuration::deleteByName('BPI_MIN_VAL_XPAY');
		Configuration::deleteByName('BPI_XPAY_NBOCCUR');
		Configuration::deleteByName('BPI_XPAY_INITAMOUNT');
		Configuration::deleteByName('BPI_XPAY_INITAMOUNT_TYPE');
		Configuration::deleteByName('BPI_XPAY_NBKO');
		Configuration::deleteByName('BPI_AUTHORIZED_IP');
		Configuration::deleteByName('BPI_DEBUG_MODE');
		
		Db::getInstance()->Execute('DROP TABLE IF EXISTS '._DB_PREFIX_.'bpi_secuaccess');
		return parent::uninstall();
	}
	public function _authorize_Xpayment(){
		  return Configuration::get('BPI_XPAY_AUTHORIZE');
	}
	
	/*
	 *
	 * HOOKS
	 *
	*/
	public function hookRightColumn($params)
	{
		global $cookie;		
		$context = $this->context;
		
		
		$context->smarty->assign('path', 'modules/'.$this->name);
		return $this->display(__FILE__, 'views/templates/front/logo.tpl');
	}
	
	public function hookLeftColumn($params)
	{
		return $this->hookRightColumn($params);
	}
	
	public function getContent()
	{
		global $cookie;
		$html = '';
		if (isset($_POST['submitBluepaid_config']))
		{
			if (empty($_POST['merchid']))
				$this->_postErrors[] = $this->l('Your bluepaid merchant id is empty !');
				
				
			$bpi_xpay_authorize = (isset($_POST["bpi_xpay_authorize"])) ? $_POST["bpi_xpay_authorize"] : 0;
			Configuration::updateValue('BPI_XPAY_AUTHORIZE', $bpi_xpay_authorize);
			
			if (isset($_POST['bpi_min_val_xpay']))
				Configuration::updateValue('BPI_MIN_VAL_XPAY', $_POST['bpi_min_val_xpay']);
			if (isset($_POST['bpi_xpay_nboccur']))
				Configuration::updateValue('BPI_XPAY_NBOCCUR', $_POST['bpi_xpay_nboccur']);
			if (isset($_POST['bpi_xpay_initamount']))
				Configuration::updateValue('BPI_XPAY_INITAMOUNT', $_POST['bpi_xpay_initamount']);
			if (isset($_POST['bpi_xpay_initamount_type']))
				Configuration::updateValue('BPI_XPAY_INITAMOUNT_TYPE', $_POST['bpi_xpay_initamount_type']);	
			if (isset($_POST['bpi_xpay_nbko']))
				Configuration::updateValue('BPI_XPAY_NBKO', $_POST['bpi_xpay_nbko']);
				
			if (isset($_POST['bpi_authorized_ip']))
				Configuration::updateValue('BPI_AUTHORIZED_IP', $_POST['bpi_authorized_ip']);	
			
			if (isset($_POST['debug_mode']))
				Configuration::updateValue('BPI_DEBUG_MODE', $_POST['debug_mode']);	
				
			
			if (!sizeof($this->_postErrors))
			{
				Configuration::updateValue('BPI_MERCHID', $_POST['merchid']);
			}
			else
				$html = '<div class="error">'.$this->l('Please fill the required fields').'</div>';
		}
		
		$smarty = false;
		$context = $this->context;
		
		//Basic settings
		$merchid = Configuration::get('BPI_MERCHID');
		$id_lang = Configuration::get('PS_LANG_DEFAULT');	
		$url_back_office = 'https://moncompte.bluepaid.com/';
		
		
		//X payments	
		$bpi_min_val_xpay = '';	
		$bpi_xpay_nboccur = '';	
		$bpi_xpay_initamount = '';	
		$bpi_xpay_initamount_type = '';	
		$bpi_xpay_nbko = '';	
		$conf = Configuration::getMultiple(array('BPI_MIN_VAL_XPAY', 'BPI_XPAY_NBOCCUR', 'BPI_XPAY_INITAMOUNT', 'BPI_XPAY_INITAMOUNT_TYPE', 'BPI_XPAY_AUTHORIZE', 'BPI_XPAY_NBKO'));
		if (isset($conf['BPI_MIN_VAL_XPAY']))
			$bpi_min_val_xpay = $conf['BPI_MIN_VAL_XPAY'];
		if (isset($conf['BPI_XPAY_NBOCCUR']))
			$bpi_xpay_nboccur = $conf['BPI_XPAY_NBOCCUR'];
		if (isset($conf['BPI_XPAY_INITAMOUNT']))
			$bpi_xpay_initamount = $conf['BPI_XPAY_INITAMOUNT'];
		if (isset($conf['BPI_XPAY_INITAMOUNT_TYPE']))
			$bpi_xpay_initamount_type = $conf['BPI_XPAY_INITAMOUNT_TYPE'];
		$bpi_xpay_authorize = self::_authorize_Xpayment();
		if (isset($conf['BPI_XPAY_NBKO']))
			$bpi_xpay_nbko = $conf['BPI_XPAY_NBKO'];
		
		
		$bpi_authorized_ip = Configuration::get('BPI_AUTHORIZED_IP');
		if(!$bpi_authorized_ip)$bpi_authorized_ip = '193.33.47.34;193.33.47.35;193.33.47.39;87.98.218.80';
		
		
		$base_url = __PS_BASE_URI__;
		$conf_uri = htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').$base_url.'modules/bluepaid/confirmOf.php';
		
		
		$context->smarty->assign(array(
			'legend' => $this->l('Configure your bluepaid module'),
			'merchid' => $merchid,
			'bluepaid_form' => $_SERVER['REQUEST_URI'],
			'bluepaid_confirmation' => $html,
			'bpi_min_val_xpay' => $bpi_min_val_xpay,
			'bpi_xpay_nboccur' => $bpi_xpay_nboccur,
			'bpi_xpay_initamount' => $bpi_xpay_initamount,
			'bpi_xpay_initamount_type' => $bpi_xpay_initamount_type,
			'bpi_xpay_authorize' => $bpi_xpay_authorize,
			'bpi_authorized_ip' => $bpi_authorized_ip,
			'url_back_office' => $url_back_office,
			'totalPayments' => $bpi_xpay_nboccur,
			'bpi_xpay_nbko' => $bpi_xpay_nbko,
			'conf_uri' => $conf_uri,
			'debugmode' => self::isDebugMode(),
		));
		#### Different tpl depending version
		if (version_compare(_PS_VERSION_, '1.6', '<'))
		{
			//$context->controller->addCSS(($this->_path).'views/css/bluepaid_15.css', 'all');
			//$context->controller->addJS(($this->_path).'views/js/bluepaid_15.js', 'all');
			return $this->display(__FILE__, 'views/templates/admin/admin.tpl');
		}
		else
			return $this->display(__FILE__, 'views/templates/admin/admin.tpl');
	}
	

	public function hookPayment($params)
	{
		 if (!$this->active)
        return ;
		if (!$this->_checkCurrency($params['cart']))
			return ;
		
		global $cookie;
		$context = $this->context;		
		
		$total_cart = $params['cart']->getOrderTotal(true);
		$type_display = Configuration::get('BPI_TYPE_DISPLAY');
		$type = explode(",", $type_display);
		
		$context->smarty->assign('comptant', (in_array("1", $type)));
		$context->smarty->assign('direct', (in_array("3", $type)));
		
		if($this->_authorize_Xpayment() && ($total_cart>=$this->_getMinAmount())){	
			$bluepaid_multipayment_nbmax = Configuration::get('BPI_XPAY_NBOCCUR');		
			$context->smarty->assign(array(
				'credit' => (in_array("1", $type)),
				'bluepaid_multipayment' => true,
				'bluepaid_multipayment_nbmax' => $bluepaid_multipayment_nbmax,
			));	
			//With a first payment
			if (Configuration::get('BPI_XPAY_INITAMOUNT') > 0)
			{
				$context->smarty->assign('init_percent_amount', Configuration::get('BPI_XPAY_INITAMOUNT'));	
				$context->smarty->assign('init_percent_amount', true);	
			} else {
				$context->smarty->assign('init_percent_amount', false);	
				
			}
		}
		$cookie->bpi_payment = true;
		#### Different tpl depending version
		if (version_compare(_PS_VERSION_, '1.6', '<'))
			return $this->display(__FILE__, './views/templates/front/payment.tpl');
		else
			return $this->display(__FILE__, 'payment_16.tpl');
	}
	
	public function hookPaymentReturn()
	{
		$smarty = false;
		$context = $this->context;	
		
		if ($params['objOrder']->module != $this->name)
			return;
		
		if (Tools::getValue('error'))
			$context->smarty->assign('status', 'failed');
		else
			$context->smarty->assign('status', 'ok');
		return $this->display(__FILE__, 'payment_return.tpl');
	}
	
	
	/*
	 *
	 * BLUEPAID FUNCTIONS
	 *
	*/
	
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
  	
	function get_bouticId(){
		return trim(Configuration::get('BPI_MERCHID'));
	}
  
	  function bpi_crypt($private_key, $str_to_crypt) {
		$private_key = md5($private_key);
		$letter = -1;
		$new_str = '';
		$strlen = strlen($str_to_crypt);
	
		for ($i = 0; $i < $strlen; $i++) {
			$letter++;
			if ($letter > 31) {
				$letter = 0;
			}
			$neword = ord($str_to_crypt{$i}) + ord($private_key{$letter});
			if ($neword > 255) {
				$neword -= 256;
			}
			$new_str .= chr($neword);
		}
		return base64_encode($new_str);
	}
	
	public function _getMinAmount(){
		return Configuration::get('BPI_MIN_VAL_XPAY');
	}
	public function _get_maxOccur(){
		return Configuration::get('BPI_XPAY_NBOCCUR');
	}
	public function _get_OccurAmount($cartAmonut, &$initamount=false){
		if(!$cartAmonut)return false;
		if($cartAmonut<0)return false;
		$temp_num_occur = Configuration::get('BPI_XPAY_NBOCCUR');
		if($initamount)$temp_num_occur--;
		
		$val_ddline = false;
		$val_ddline = $cartAmonut/$temp_num_occur;
		//Check amount integrity
		$check = $val_ddline*$temp_num_occur;
		if ($initamount)
		{
			$check+=$initamount;			
		}
		$delta = ceil((float)$cartAmonut - (float)$check);
		if (ceil($delta) > 0);
		{
			if ($initamount)$initamount+=$delta;
			else
			{
				$initamount = $delta+$val_ddline;
				$temp_num_occur--;
			}
		}
			
		
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
		return Configuration::get('BPI_XPAY_NBKO');
	}
	
	
	/**
	 *
	 *
	 * CONFIRMATION OF PAYMENT
	 *
	 *	 
	*/


	/**
	* Save order and transaction info.
	*/
	public function saveOrder($cart, $order_status, $bluepaid_multi_response)
	{
		# $id_cart 	= $bluepaid_multi_response['id_client'];
		# $testEtat	= Tools::strtolower($bluepaid_multi_response['etat']);
		$id_trans	= $bluepaid_multi_response['id_trans'];
		$mode		= $bluepaid_multi_response['mode'];
		# $amount		= str_replace(',','.',$bluepaid_multi_response['montant']);
		# $devise		= $bluepaid_multi_response['devise'];
		$divers		= $bluepaid_multi_response['divers'];
		$hash_control = $divers;
		# $langue		= $bluepaid_multi_response['langue'];
		$extra_vars = array();
		$extra_vars['transaction_id'] = $id_trans;

		if ($mode == 'r') //refund mode
			return;
		####  Check merchant Id returned by Bluepaid
		####  Only on accepted transactions => NO RISK FOR REFUSED
		$boutic_id = false;
		$payment_method = $this->displayName;
		if (isset($bluepaid_multi_response['num_abo']) && !empty($bluepaid_multi_response['num_abo']))
			$boutic_id = $this->getBouticId('multi');
		else
		{
			$payment_method = 'Bluepaid';
			$boutic_id = $this->getBouticId();
		}
		if (($order_status == Configuration::get('PS_OS_PAYMENT'))
		&& (($boutic_id !== $bluepaid_multi_response['id_boutique'])
		|| !$boutic_id
		|| !$bluepaid_multi_response['id_boutique']
		|| $bluepaid_multi_response['id_boutique'] === ''))
		{
			$order_status = Configuration::get('PS_OS_ERROR');
			$feedback = 'Merchant ID missmatch for transaction #'.$id_trans;
			$feedback .= '\n';
			$feedback .= 'Your boutic ID : '.$boutic_id;
			$feedback .= '\n';
			$feedback .= '<b>BOUTIC ID furnished : '.$bluepaid_multi_response['id_boutique'].'</b>';
		}
		####  retrieve customer from cart
		$customer = new Customer($cart->id_customer);
		if ($customer->secure_key != $hash_control)$order_status = Configuration::get('PS_OS_ERROR');
		####
		if ($mode == 'test')
		{
			if (!$this->isDebugMode() && (
				$order_status == Configuration::get('PS_OS_PAYMENT')))
				$order_status = Configuration::get('PS_OS_ERROR');
			else if ($this->isDebugMode())
			{
				if ($order_status == Configuration::get('PS_OS_PAYMENT'))
					$order_status = Configuration::get('BLUEPAID_STATUS_ACCEPTED_DEBUG');
				if ($order_status == Configuration::get('PS_OS_ERROR'))
					$order_status = Configuration::get('BLUEPAID_STATUS_REFUSED_DEBUG');
				$feedback = 'ORDER CREATE WITH TESTS CARD !!';
			}
		}

		$paid_total = $cart->getOrderTotal();
		$validate_order = true;
		$add_transaction_id = false;
		#### Order yet validate, do not validate again
		if (((isset($bluepaid_multi_response['num_abo'])
		&& !empty($bluepaid_multi_response['num_abo'])))
		&& ((isset($bluepaid_multi_response['num_prochaine_presentation'])
		&& !empty($bluepaid_multi_response['num_prochaine_presentation'])
		&& (((int)$bluepaid_multi_response['num_prochaine_presentation']) > 2))
		|| (!isset($bluepaid_multi_response['num_prochaine_presentation'])
		&& (isset($bluepaid_multi_response['fini'])
		&& $bluepaid_multi_response['fini'] == 'oui'))))
			$validate_order = false;
		if ($validate_order)
		{
			$extra_vars = array();
			if ($add_transaction_id)
				$extra_vars['transaction_id'] = $id_trans;
			$this->validateOrder(
				(int)$cart->id,
				$order_status,
				$paid_total,
				$payment_method,
				$feedback,
				$extra_vars,
				$cart->id_currency,
				true,
				$customer->secure_key
			);
		}

		$bluepaid_multi_response['payment_method'] = $payment_method;
		#### reload order
		$order = new Order((int)Order::getOrderByCartId($cart->id));
		
		if (version_compare(_PS_VERSION_, '1.5', '<'))
			return $order;
		else
			$this->savePayment($order, $bluepaid_multi_response);		
		return $order;
	}

	/**
	 * Return merchant id saved by admin
	*/
	public function getBouticId($type = 'simple')
	{
		if ($type == 'simple')
		{
			$conf = Configuration::getMultiple(array('BPI_MERCHID'));
			$merchid = $conf['BPI_MERCHID'];
		}
		else if ($type == 'multi')
		{
			$conf = Configuration::getMultiple(array('BPI_MULTI_ACCOUNTID', 'BPI_MERCHID'));
			$merchid = $conf['BPI_MULTI_ACCOUNTID'];
			if (!$merchid)$merchid = $conf['BPI_MERCHID'];
		}
		return trim($merchid);
	}
	/**
	 * Save payment information.
	 */
	public function savePayment($order, $bluepaid_response)
	{
		$payments = $order->getOrderPayments();
				// delete payments created by default
		if (is_array($payments) && !empty($payments))
			foreach ($payments as $payment)
				if (!$payment->transaction_id)
				{
					$order->total_paid_real -= $payment->amount;
					$payment->delete();
				}
				
		if (!$this->isSuccessState($order) && !$bluepaid_response['etat'] != 'ok')
			// no payment creation
			return;
		$invoices = $order->getInvoicesCollection();
		$invoice = count($invoices) > 0 ? $invoices[0] : null;

		$payment_ids = array();
		if (isset($bluepaid_response['num_abo']))
		{
			if (!$order->addOrderPayment(
				$bluepaid_response['montant'],
				$bluepaid_response['payment_method'],
				$bluepaid_response['id_trans'],
				null,
				null,
				$invoice
			))
				die( 'Can\'t save Order Payment');
		}
		else
		{
			// real paid total on platform
			$amount = $bluepaid_response['montant'];

			if (number_format($order->total_paid) == number_format($amount))
				$amount = $order->total_paid; // to avoid rounding problems and pass PaymentModule::validateOrder() check

			if (!$order->addOrderPayment($amount, null, $bluepaid_response['id_trans'], null, null, $invoice))
				die( 'Can\'t save Order Payment');

			$pcc = new OrderPayment($this->lastOrderPaymentId($order));
			$payment_ids[] = $pcc->id;
			$pcc->update();
		}
	}

	private function lastOrderPaymentId($order)
	{
		return Db::getInstance()->getValue('
				SELECT MAX(`id_order_payment`) FROM `'._DB_PREFIX_.'order_payment`
				WHERE `order_reference` = \''.$order->reference.'\';');
	}

	private function isSuccessState($order)
	{
		$os = new OrderState($order->getCurrentState());

		// if state is one of supported states or custom state with paid flag
		return $os->id === (int)Configuration::get('PS_OS_PAYMENT')
				|| $os->id === (int)Configuration::get('PS_OS_OUTOFSTOCK')
				|| $os->id === (int)Configuration::get('BLUEPAID_STATUS_ACCEPTED_DEBUG')
				|| (bool)$os->paid;
	}

	public function isDebugMode()
	{
		return (boolean)Configuration::get('BPI_DEBUG_MODE');
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
	
	// Retrocompatibility 1.4/1.5
	private function initContext()
	{
		if (class_exists('Context'))
			$this->context = Context::getContext();
	 	else
	  	{
			global $smarty, $cookie;
			$this->context = new StdClass();
			$this->context->smarty = $smarty;
			$this->context->cookie = $cookie;
	  	}
	}

	public static function redirectForVersion($link)
	{
		if (version_compare(_PS_VERSION_, '1.5', '<'))
			Tools::redirectLink($link);
		else
			Tools::redirect($link);
	}
}

?>
