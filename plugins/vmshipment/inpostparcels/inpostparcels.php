<?php
defined ('_JEXEC') or die('Restricted access');

if (!class_exists ('vmPSPlugin')) {
	require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');
}

if (JVM_VERSION === 2 || JVM_VERSION === 3)
{
    require (JPATH_ROOT . DS . 'plugins' . DS . 'vmshipment' . DS . 'inpostparcels' . DS . 'inpostparcels' . DS . 'helpers' . DS . 'define.php');
} else {
    require (JPATH_ROOT . DS . 'plugins' . DS . 'vmshipment' . DS . 'inpostparcels' . DS . 'helpers' . DS . 'define.php');
}

require_once (JPATH_VMINPOSTPARCELSPLUGIN . DS . 'inpostparcels' . DS . 'helpers' . DS . 'inpostparcels_helper.php');

class plgVmShipmentInpostparcels extends vmPSPlugin
{
	// instance of class
	public static $_this = FALSE;

	/**
	 * @param object $subject
	 * @param array  $config
	 */
	function __construct (& $subject, $config)
	{
		//if (self::$_this)
		//   return self::$_this;
		parent::__construct ($subject, $config);

		$this->_loggable = TRUE;
		$this->_tablepkey = 'id';
		$this->_tableId = 'id';
		$this->tableFields = array_keys ($this->getTableSQLFields ());
		$varsToPush = $this->getVarsToPush ();
		$this->setConfigParameterable ($this->_configTableFieldName, $varsToPush);

		inpostparcels_helper::setLang();
		//$parameters = new vmParameters('TableShipmentmethods', 'inpostparcels', 'plugin', 'vmshipment');
		//print_r($parameters);

		//vmWarn('AA_GG');
		//vmWarn('VMSHIPMENT_WEIGHT_COUNTRIES_WEIGHT_CONDITION_WRONG');

		// 	self::$_this
		//$this->createPluginTable($this->_tablename);
		//self::$_this = $this;
	}

	/**
	 * Create the table for this plugin if it does not yet exist.
	 *
	 * @author Valérie Isaksen
	 */
	public function getVmPluginCreateTableSQL () {

		return $this->createTableSQL ('Shipment Weight Countries Table');
	}

	/**
	 * @return array
	 */
	function getTableSQLFields ()
	{
		$SQLfields = array(
			'id'                           => 'int(1) UNSIGNED NOT NULL AUTO_INCREMENT',
			'virtuemart_order_id'          => 'int(11) UNSIGNED',
			'parcel_id'                    => 'varchar(200)',
			'parcel_status'                => 'varchar(200)',
			'parcel_detail'                => 'text',
			'parcel_target_machine_id'     => 'varchar(200)',
			'parcel_target_machine_detail' => 'text',
			'sticker_creation_date'        => 'timestamp',
			'api_source'                   => 'varchar(3)',
			'variables'                    => 'text',
			'order_number'                 => 'char(32)',
			'virtuemart_shipmentmethod_id' => 'mediumint(1) UNSIGNED',
			'shipment_name'                => 'varchar(5000)',
			'order_weight'                 => 'decimal(10,4)',
			'shipment_weight_unit'         => 'char(3) DEFAULT \'KG\'',
			'shipment_cost'                => 'decimal(10,2)',
			'shipment_package_fee'         => 'decimal(10,2)',
			'tax_id'                       => 'smallint(1)'
		);
		return $SQLfields;
	}

	/**
	 * This method is fired when showing the order details in the frontend.
	 * It displays the shipment-specific data.
	 *
	 * @param integer $virtuemart_order_id The order ID
	 * @param integer $virtuemart_shipmentmethod_id The selected shipment method id
	 * @param string  $shipment_name Shipment Name
	 * @return mixed Null for shipments that aren't active, text (HTML) otherwise
	 * @author Valérie Isaksen
	 * @author Max Milbers
	 */
	public function plgVmOnShowOrderFEShipment ($virtuemart_order_id, $virtuemart_shipmentmethod_id, &$shipment_name) {

		$this->onShowOrderFE ($virtuemart_order_id, $virtuemart_shipmentmethod_id, $shipment_name);
	}

	/**
	 * This event is fired after the order has been stored; it gets the shipment method-
	 * specific data.
	 *
	 * @param int    $order_id The order_id being processed
	 * @param object $cart  the cart
	 * @param array  $order The actual order saved in the DB
	 * @return mixed Null when this method was not selected, otherwise true
	 * @author Valerie Isaksen
	 */
	function plgVmConfirmedOrder (VirtueMartCart $cart, $order)
	{
		if (!($method = $this->getVmPluginMethod ($order['details']['BT']->virtuemart_shipmentmethod_id)))
		{
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement ($method->shipment_element))
		{
			return FALSE;
		}

		$order_id = $order['details']['BT']->virtuemart_order_id;

		//error_log("** order_id = " . $order_id);
		//error_log("** session = " . json_encode($_SESSION));
		//error_log("_POST = " . json_encode($_POST));

		// Set the mobile phone number
		$phone = "";
		if (isset($_SESSION['inpostparcels']['receiver_phone']))
		{
			$phone = $_SESSION['inpostparcels']['receiver_phone'];
		}

		if (isset($_POST['receiver_phone']) &&
			$_POST['receiver_phone'] != "" &&
			$phone == "")
		{
			$phone = $_POST['receiver_phone'];
		}

		// Get the user's target locker machine ID
		$parcel_target_machine_id = "";

		if (isset($_SESSION['inpostparcels']['parcel_target_machine_id']))
		{
			$parcel_target_machine_id = $_SESSION['inpostparcels']['parcel_target_machine_id'];
		}

		if ($parcel_target_machine_id == "" &&
			isset($_POST['shipping_inpostparcels']) &&
			$_POST['shipping_inpostparcels'] != "")
		{
			$parcel_target_machine_id = 
				$_POST['shipping_inpostparcels'];
		}

		$parcel_detail = array(
			'description' => JText::_ ('COM_VIRTUEMART_INPOSTPARCELS_ORDER').$order_id,
			'receiver'    => array(
				'email' => $_SESSION['inpostparcels']['user_email'],
				'phone' => $phone,
			),
			'size'   => $_SESSION['inpostparcels']['parcel_size'],
			'tmp_id' => inpostparcels_helper::generate(4, 15),
			'target_machine' => $parcel_target_machine_id,
		);

		switch (inpostparcels_helper::getCurrentApi())
		{
			case 'PL':
				$parcel_detail['cod_amount'] = ($order['details']['BT']->virtuemart_paymentmethod_id == 1)? sprintf("%.2f" ,$order['details']['BT']->order_total) : '';
				break;
		}

		$parcel_target_machine_detail = @$_SESSION['inpostparcels']['parcelTargetAllMachinesDetail'][$parcel_target_machine_id];

		// Setup the values to be saved into the database table.
		$values['virtuemart_order_id']          = $order_id;
		$values['parcel_detail']                = json_encode($parcel_detail);
		$values['parcel_target_machine_id']     = $parcel_target_machine_id;
		$values['parcel_target_machine_detail'] = json_encode($parcel_target_machine_detail);
		$values['api_source'] = inpostparcels_helper::getCurrentApi();

		$values['order_number']                 = $order['details']['BT']->order_number;
		$values['virtuemart_shipmentmethod_id'] = $order['details']['BT']->virtuemart_shipmentmethod_id;
		$values['shipment_name']        = $this->renderPluginName ($method);
		$values['order_weight']         = $this->getOrderWeight ($cart, $method->weight_unit);
		
		$values['shipment_weight_unit'] = $method->weight_unit;

		$costs = $this->getCosts($cart, $method, $cart->cartPrices);
		if(empty($costs))
		{
			$values['shipment_cost']        = 0;
			$values['shipment_package_fee'] = 0;
		}
		else
		{
			$values['shipment_cost'] = $method->shipment_cost;
			$values['shipment_package_fee'] = $method->package_fee;
		}

		$values['tax_id']               = $method->tax_id;
		$this->storePSPluginInternalData ($values);

		unset($_SESSION['inpostparcels']);
		//unset($cart->ST);
		return TRUE;
	}

	/**
	 * This method is fired when showing the order details in the backend.
	 * It displays the shipment-specific data.
	 * NOTE, this plugin should NOT be used to display form fields, since it's called outside
	 * a form! Use plgVmOnUpdateOrderBE() instead!
	 *
	 * @param integer $virtuemart_order_id The order ID
	 * @param integer $virtuemart_shipmentmethod_id The order shipment method ID
	 * @param object  $_shipInfo Object with the properties 'shipment' and 'name'
	 * @return mixed Null for shipments that aren't active, text (HTML) otherwise
	 * @author Valerie Isaksen
	 */
	public function plgVmOnShowOrderBEShipment ($virtuemart_order_id, $virtuemart_shipmentmethod_id) {
		if (!($this->selectedThisByMethodId ($virtuemart_shipmentmethod_id))) {
			return NULL;
		}
		$html = $this->getOrderShipmentHtml ($virtuemart_order_id);
		return $html;
	}

	/**
	 * @param $virtuemart_order_id
	 * @return string
	 */
	function getOrderShipmentHtml ($virtuemart_order_id) {
		$db = JFactory::getDBO ();
		$q = 'SELECT * FROM `' . $this->_tablename . '` '
			. 'WHERE `virtuemart_order_id` = ' . $virtuemart_order_id;
		$db->setQuery ($q);
		if (!($shipinfo = $db->loadObject ())) {
			vmWarn (500, $q . " " . $db->getErrorMsg ());
			return '';
		}

		if (!class_exists ('CurrencyDisplay')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'currencydisplay.php');
		}

        $parcelDetail = json_decode($shipinfo->parcel_detail);

		$currency = CurrencyDisplay::getInstance ();
		$tax = ShopFunctions::getTaxByID ($shipinfo->tax_id);
		$taxDisplay = is_array ($tax) ? $tax['calc_value'] . ' ' . $tax['calc_value_mathop'] : $shipinfo->tax_id;
		$taxDisplay = ($taxDisplay == -1) ? JText::_ ('COM_VIRTUEMART_PRODUCT_TAX_NONE') : $taxDisplay;

        //inpostparcels_helper::setLang();

		$html = '<table class="adminlist">' . "\n";
		$html .= $this->getHtmlHeaderBE ();
		$html .= $this->getHtmlRowBE ('COM_VIRTUEMART_INPOSTPARCELS_SHIPPING_NAME', $shipinfo->shipment_name);
        $html .= $this->getHtmlRowBE ('COM_VIRTUEMART_INPOSTPARCELS_MACHINE', $shipinfo->parcel_target_machine_id);
        $html .= $this->getHtmlRowBE ('COM_VIRTUEMART_INPOSTPARCELS_MOBILE', $parcelDetail->receiver->phone);
        $html .= $this->getHtmlRowBE ('COM_VIRTUEMART_INPOSTPARCELS_WEIGHT', $shipinfo->order_weight . ' ' . ShopFunctions::renderWeightUnit ($shipinfo->shipment_weight_unit));
		$html .= $this->getHtmlRowBE ('COM_VIRTUEMART_INPOSTPARCELS_COST', $currency->priceDisplay ($shipinfo->shipment_cost));
		$html .= $this->getHtmlRowBE ('COM_VIRTUEMART_INPOSTPARCELS_PACKAGE_FEE', $currency->priceDisplay ($shipinfo->shipment_package_fee));
		$html .= $this->getHtmlRowBE ('COM_VIRTUEMART_INPOSTPARCELS_TAX', $taxDisplay);
		$html .= '</table>' . "\n";

		return $html;
	}

	///
	// displayListFE
	//
	public function __displayListFE (VirtueMartCart $cart, $selected = 0, &$htmlIn)
	{
		error_log("In here but we should not be.");
		if ($this->getPluginMethods ($cart->vendorId) === 0)
		{
			if (empty($this->_name))
			{
                		vmAdminInfo ('displayListFE cartVendorId=' . $cart->vendorId);
                		$app = JFactory::getApplication ();
                		$app->enqueueMessage (JText::_ ('COM_VIRTUEMART_CART_NO_' . strtoupper ($this->_psType)));
                		return FALSE;
			}
			else
			{
				return FALSE;
			}
		}

		$html = array();
		$method_name = $this->_psType . '_name';
		
		foreach ($this->methods as $method)
		{
			if ($this->checkConditions ($cart, $method, $cart->pricesUnformatted))
			{
				//$methodSalesPrice = $this->calculateSalesPrice ($cart, $method, $cart->pricesUnformatted);
				$prices = $cart->cartPrices;
				$methodSalesPrice = $this->setCartPrices ($cart, $prices,$method);
				error_log("methodSalesPrice " . json_encode($methodSalesPrice));
				$method->$method_name = $this->renderPluginName ($method);
				//$html [] = $this->getPluginHtml ($cart, $method, $selected, $methodSalesPrice);
				$html [] = $this->getPluginHtml ($method, $selected, $methodSalesPrice);
			}
		}
		error_log("END In here but we should not be.");
		if (!empty($html))
		{
			$htmlIn[] = $html;
			return TRUE;
		}

		return FALSE;
	}

	///
	// getPluginHtml
	//
	// @brief Build the display for the InPost method.
	// @param mixed The plugin
	// @param mixed The current plugin
	// @param mixed The price information
	//
	protected function getPluginHtml ($plugin, $selectedPlugin, $pluginSalesPrice)
	{
		$pluginmethod_id = $this->_idName;
		$pluginName      = $this->_psType . '_name';
		if ($selectedPlugin == $plugin->$pluginmethod_id)
		{
			$checked = 'checked="checked"';
		}
		else
		{
			$checked = '';
		}

		if (!class_exists ('CurrencyDisplay'))
		{
			require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'currencydisplay.php');
		}
		$currency = CurrencyDisplay::getInstance ();
		$costDisplay = "";
		if ($pluginSalesPrice)
		{
			$costDisplay = $currency->priceDisplay ($pluginSalesPrice);
			$costDisplay = '<span class="' . $this->_type . '_cost"> (' . JText::_ ('COM_VIRTUEMART_PLUGIN_COST_DISPLAY') . $costDisplay . ")</span>";
		}
		foreach ($this->methods as $this->_currentMethod)
		{
			// Get the Cart information
			$cart = VirtueMartCart::getCart();
			$cart->prepareCartData();

			$content = $this->renderByLayout ('edit_shippment',
				$this->prepareData(
					$cart,
					$this->methods[0],
					$this->_psType . '_id_' . $plugin->$pluginmethod_id),
				'inpostparcels',
				'vmshipment');

		$html = '<input type="radio" name="' . $pluginmethod_id . '" id="' . $this->_psType . '_id_' . $plugin->$pluginmethod_id . '"   value="' . $plugin->$pluginmethod_id . '" ' . $checked . ">\n"
		. '<label for="' . $this->_psType . '_id_' . $plugin->$pluginmethod_id . '">' . '<span class="' . $this->_type . '">' . $plugin->$pluginName . $costDisplay . "</span></label>
		$content
		\n";
		}

		return $html;
	}

	///
	// prepareData
	//
	// @param mixed  The cart being worked on
	// @param string The type of method being worked on
	// @param string The shipment ID selected
	//
	protected function prepareData(VirtueMartCart $cart, $method, $radio_id)
	{
		//error_log("Cart = " . json_encode($cart));
		//error_log("Method = " . json_encode($method));
		//error_log("Radio_id = " . json_encode($radio_id));
		//error_log("_POST = " . json_encode($_POST));

		$address = (($cart->ST == 0) ? $cart->BT : $cart->ST);

		$inpostparcels = array();

		$inpostparcels['radio_id']   = $radio_id;
		$inpostparcels['address']    = $address;
		$inpostparcels['user_email'] = $cart->BT['email'];

		// The receiver's phone number is in the $_POST data
		if (isset($_POST['receiver_phone']) &&
			$_POST['receiver_phone'] != "")
		{
			$inpostparcels['user_phone'] = $_POST['receiver_phone'];
		}
		else
		{
			if (isset($address['phone_2']) &&
				$address['phone_2'] != "")
			{
				$inpostparcels['user_phone'] = $address['phone_2'];
			}
		}
		if(isset($inpostparcels['user_phone']) &&
			!preg_match('/^[1-9]{1}\d{8}$/', $inpostparcels['user_phone']))
		{
			$inpostparcels['user_phone'] = null;
		}
		elseif(!isset($inpostparcels['user_phone']))
		{
			$inpostparcels['user_phone'] = null;
		}

		// Get the user selected machine from the $_POST data
		if (isset($_POST['shipping_inpostparcels']) &&
			$_POST['shipping_inpostparcels'] != "")
		{
			$inpostparcels['parcel_target_machine_id'] =
				$_POST['shipping_inpostparcels'];
		}

		// get machines
		$machine_params = array();
		switch(inpostparcels_helper::getCurrentApi())
		{
			case 'PL':
				$machine_params['payment_available'] = true;
				break;
			case 'UK':
				break;
		}

		$allMachines = inpostparcels_helper::connectInpostparcels(
			array(
				'url'        => $method->API_URL.'machines',
				'token'      => $method->API_KEY,
				'methodType' => 'GET',
				'params'     => $machine_params
			)
		);

		$parcelTargetAllMachinesId = array();
		$parcelTargetAllMachinesDetail = array();
		$machines = array();

		if(is_array(@$allMachines['result']) && !empty($allMachines['result']))
		{
			foreach($allMachines['result'] as $key => $machine)
			{
				$parcelTargetAllMachinesId[$machine->id] = addslashes($machine->id.', '.@$machine->address->city.', '.@$machine->address->street);
                $parcelTargetAllMachinesDetail[$machine->id] = array(
                    'id' => $machine->id,
                    'address' => array(
                        'building_number' => @$machine->address->building_number,
                        'flat_number' => @$machine->address->flat_number,
                        'post_code' => @$machine->address->post_code,
                        'province' => @$machine->address->province,
                        'street' => @$machine->address->street,
                        'city' => @$machine->address->city
                    )
                );
                if($machine->address->post_code == $address['zip']){
                    $machines[$key] = $machine;
                    continue;
                }elseif($machine->address->city == $address['city']){
                    $machines[$key] = $machine;
                }

				$inpostparcels['parcelTargetAllMachinesId'] = $parcelTargetAllMachinesId;
				$inpostparcels['parcelTargetAllMachinesDetail'] = $parcelTargetAllMachinesDetail;
			}
		}

		$parcelTargetMachinesId = array();
		$parcelTargetMachinesDetail = array();
		$inpostparcels['defaultSelect'] = JText::_ ('COM_VIRTUEMART_INPOSTPARCELS_VIEW_SELECT_MACHINE');
		if(is_array(@$machines) && !empty($machines))
		{
			foreach($machines as $key => $machine)
			{
				$parcelTargetMachinesId[$machine->id] = $machine->id.', '.@$machine->address->city.', '.@$machine->address->street;
                $parcelTargetMachinesDetail[$machine->id] = array(
                    'id' => $machine->id,
                    'address' => array(
                        'building_number' => @$machine->address->building_number,
                        'flat_number' => @$machine->address->flat_number,
                        'post_code' => @$machine->address->post_code,
                        'province' => @$machine->address->province,
                        'street' => @$machine->address->street,
                        'city' => @$machine->address->city
                    )
                );
			}
			$inpostparcels['parcelTargetMachinesId'] = $parcelTargetMachinesId;
		}
		else
		{
			$inpostparcels['defaultSelect'] = JText::_ ('COM_VIRTUEMART_INPOSTPARCELS_DEFAULT_SELECT');
		}
		$inpostparcels['parcelTargetMachinesId'] = $parcelTargetMachinesId;


		$_SESSION['inpostparcels'] = $inpostparcels;

		return array('inpostparcels' => $inpostparcels);
	}


    /**
     * @param \VirtueMartCart $cart
     * @param int             $method
     * @param array           $cart_prices
     * @return bool
     */
	protected function checkConditions ($cart, $method, $cart_prices)
	{
		// check countries
//        $address = (($cart->ST == 0) ? $cart->BT : $cart->ST);
//        if(!in_array($address['virtuemart_country_id'], $method->ALLOWED_COUNTRY)){
//            return false;
//        }

		if($cart->STsameAsBT == 0){
			$type = ($cart->ST == 0 ) ? 'BT' : 'ST';
		} else {
			$type = 'BT';
		}

		$address = $cart -> getST();

		if(!is_array($address)) $address = array();
		if(isset($cart_prices['salesPrice'])){
			$hashSalesPrice = $cart_prices['salesPrice'];
		} else {
			$hashSalesPrice = '';
		}

		if(empty($address['virtuemart_country_id']))
			$address['virtuemart_country_id'] = 0;
		if(empty($address['zip']))
			$address['zip'] = 0;

		$hash = $method->virtuemart_shipmentmethod_id.$type.$address['virtuemart_country_id'].'_'.$address['zip'].'_'.$hashSalesPrice;

		if(isset($result[$hash]))
		{
			return $result[$hash];
		}
		$orderWeight = $this->getOrderWeight ($cart, $method->weight_unit);

		$countries = array();
		if (!empty($method->countries)) {
			if (!is_array ($method->countries)) {
				$countries[0] = $method->countries;
			} else {
				$countries = $method->countries;
			}
		}

		// check weight
		if ($orderWeight > $method->MAX_WEIGHT)
		{
			return false;
		}
		$userFieldsModel = VmModel::getModel('Userfields');
		if ($userFieldsModel->fieldPublished('virtuemart_country_id', $type))
		{
			if (!isset($address['virtuemart_country_id']))
			{
				$address['virtuemart_country_id'] = 0;
			}

			if (in_array ($address['virtuemart_country_id'], $countries) || count ($countries) == 0)
			{
				//vmdebug('checkConditions '.$method->shipment_name.' fit ',$weight_cond,(int)$zip_cond,$nbproducts_cond,$orderamount_cond);
				vmdebug('shipmentmethod '.$method->shipment_name.' = TRUE for variable virtuemart_country_id = '.$address['virtuemart_country_id'].', Reason: Countries in rule '.implode($countries,', ').' or none set');
				$country_cond = true;
			}
			else
			{
				vmdebug('shipmentmethod '.$method->shipment_name.' = FALSE for variable virtuemart_country_id = '.$address['virtuemart_country_id'].', Reason: Country '.implode($countries,', ').' does not fit');
				$country_cond = false;
			}
		}
		else
		{
			vmdebug('shipmentmethod '.$method->shipment_name.' = TRUE for variable virtuemart_country_id, Reason: no boundary conditions set');
			$country_cond = true;
		}

		// check dimensions
		foreach ($cart->products as $product)
		{
			$product_dimensions[] = (float)$product->product_width.'x'.(float)$product->product_height.'x'.(float)$product->product_length;
		}

        $calculateDimension = inpostparcels_helper::calculateDimensions($product_dimensions,
            array(
                'MAX_DIMENSION_A' => $method->MAX_DIMENSION_A,
                'MAX_DIMENSION_B' => $method->MAX_DIMENSION_B,
                'MAX_DIMENSION_C' => $method->MAX_DIMENSION_C
            )
        );

		if(!$calculateDimension['isDimension'])
		{
			vmdebug('shipmentmethod '.$method->shipment_name.' = TRUE for variable virtuemart_country_id, Reason: bad dimensions.');
			return false;
        	}

		if (!$country_cond)
		{
			return false;
		}

		$_SESSION['inpostparcels']['parcel_size'] = $calculateDimension['parcelSize'];

		return true;
	}

	/**
	 * @param VirtueMartCart $cart
	 * @param                $method
	 * @param                $cart_prices
	 * @return int
	 */
	function getCosts (VirtueMartCart $cart, $method, $cart_prices)
	{
		//error_log("In inpostparcels.php getCosts");

		//return $method->PRICE;
		if ($method->free_shipment && $cart_prices['salesPrice'] >= $method->free_shipment)
		{
			return 0.0;
		}
		else
		{
			return $method->shipment_cost + $method->package_fee;
		}

	}

	/**
	 * Create the table for this plugin if it does not yet exist.
	 * This functions checks if the called plugin is active one.
	 * When yes it is calling the standard method to create the tables
	 *
	 * @author Valérie Isaksen
	 *
	 */
	function plgVmOnStoreInstallShipmentPluginTable ($jplugin_id) {

        $db = JFactory::getDBO ();
        $db->setQuery ("SELECT count(id) as count FROM #__virtuemart_adminmenuentries WHERE name='COM_VIRTUEMART_INPOSTPARCELS_LINK'");

        if($db->loadResult() <= 0){
            $query = "INSERT INTO #__virtuemart_adminmenuentries (id, module_id, parent_id, name, link, depends, icon_class, ordering, published, tooltip, view, task) VALUES (null, 2, 0, 'COM_VIRTUEMART_INPOSTPARCELS_LINK', '', '', 'vmicon vmicon-16-page_white_stack', 1, 1, '', 'inpostparcels', '')";
            $db->setQuery ($query);
            $db->query();
        }

	    return $this->onStoreInstallPluginTable ($jplugin_id);;
    }

	/**
	 * @param VirtueMartCart $cart
	 * @return null
	 */
	public function plgVmOnSelectCheckShipment (VirtueMartCart &$cart) {

        $id = $this->_idName;
        if (!($method = $this->selectedThisByMethodId ($cart->$id))) {
            return NULL; // Another method was selected, do nothing
        }

        if(@$_POST['shipping_inpostparcels']['parcel_target_machine_id'] == ''){
            vmError('', 'COM_VIRTUEMART_INPOSTPARCELS_VALID_SELECT');
            return false;
        }

        if(!preg_match('/^[1-9]{1}\d{8}$/', @$_POST['shipping_inpostparcels']['receiver_phone'])){
            vmError('', 'COM_VIRTUEMART_INPOSTPARCELS_VALID_MOBILE');
            return false;
        }

        if(@$_SESSION['inpostparcels']['user_email'] == ''){
            vmError('', 'COM_VIRTUEMART_INPOSTPARCELS_VALID_EMAIL');
            return false;
        }

        $_SESSION['inpostparcels']['shipping_inpostparcels'] = $_POST['shipping_inpostparcels'];

        if($cart->ST == 0){
            $cart->ST = $cart->BT;
        }

        $shipping = $_SESSION['inpostparcels']['parcelTargetAllMachinesDetail'][$_POST['shipping_inpostparcels']['parcel_target_machine_id']];

        $cart->ST['address_1'] = $shipping['address']['street'].' '.$shipping['address']['building_number'];
        if(@$shipping['address']['flat_number'] != ''){
            $cart->ST['address_1'] .= '/'.$shipping['address']['flat_number'];
        }
        $cart->ST['city'] = $shipping['address']['city'];
        $cart->ST['zip'] = $shipping['address']['post_code'];
        //$cart->ST['state'] = $_SESSION['inpostparcels']['parcelTargetMachineDetail']['address']['province'];

        return $this->OnSelectCheck ($cart);
	}

	/**
	 * plgVmDisplayListFE
	 * This event is fired to display the pluginmethods in the cart (edit shipment/payment) for example
	 *
	 * @param object  $cart Cart object
	 * @param integer $selected ID of the method selected
	 * @return boolean True on success, false on failures, null when this plugin was not selected.
	 * On errors, JError::raiseWarning (or JError::raiseError) must be used to set a message.
	 *
	 * @author Valerie Isaksen
	 * @author Max Milbers
	 */
	public function plgVmDisplayListFEShipment (VirtueMartCart $cart, $selected = 0, &$htmlIn) {

		return $this->displayListFE ($cart, $selected, $htmlIn);
	}

	/**
	 * @param VirtueMartCart $cart
	 * @param array          $cart_prices
	 * @param                $cart_prices_name
	 * @return bool|null
	 */
	public function plgVmOnSelectedCalculatePriceShipment (VirtueMartCart $cart, array &$cart_prices, &$cart_prices_name) {

		return $this->onSelectedCalculatePrice ($cart, $cart_prices, $cart_prices_name);
	}

	/**
	 * plgVmOnCheckAutomaticSelected
	 * Checks how many plugins are available. If only one, the user will not have the choice. Enter edit_xxx page
	 * The plugin must check first if it is the correct type
	 *
	 * @author Valerie Isaksen
	 * @param VirtueMartCart cart: the cart object
	 * @return null if no plugin was found, 0 if more then one plugin was found,  virtuemart_xxx_id if only one plugin is found
	 *
	 */
	function plgVmOnCheckAutomaticSelectedShipment (VirtueMartCart $cart, array $cart_prices = array(), &$shipCounter) {

		if ($shipCounter > 1) {
			return 0;
		}

		return $this->onCheckAutomaticSelected ($cart, $cart_prices, $shipCounter);
	}

	/**
	 * This method is fired when showing when priting an Order
	 * It displays the the payment method-specific data.
	 *
	 * @param integer $_virtuemart_order_id The order ID
	 * @param integer $method_id  method used for this order
	 * @return mixed Null when for payment methods that were not selected, text (HTML) otherwise
	 * @author Valerie Isaksen
	 */
	function plgVmOnShowOrderPrint ($order_number, $method_id) {

		return $this->onShowOrderPrint ($order_number, $method_id);
	}

	function plgVmDeclarePluginParamsShipment ($name, $id, &$data)
	{

		return $this->declarePluginParams ('shipment', $name, $id, $data);
	}
	function plgVmDeclarePluginParamsShipmentVM3 (&$data)
	{
		return $this->declarePluginParams ('shipment', $data);
	}


    /**
     * @author Max Milbers
     * @param $data
     * @param $table
     * @return bool
     */
	function plgVmSetOnTablePluginShipment(&$data,&$table)
	{
		$name = $data['shipment_element'];
		$id = $data['shipment_jplugin_id'];

		if (!empty($this->_psType) and !$this->selectedThis ($this->_psType, $name, $id))
		{
			return FALSE;
		}
		else
		{
			if(empty($data['API_URL']))
			{
				error_log("URL is empty.");
				//vmWarn('VMSHIPMENT_WEIGHT_COUNTRIES_ZIP_CONDITION_WRONG');
			}
		}

		return $this->setOnTablePluginParams ($name, $id, $table);
	}

}

// No closing tag
