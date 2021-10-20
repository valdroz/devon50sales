<?php
class ModelAccountEntersale extends Model {
    
	public function getOrder($order_id) {
		$order_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$order_id . "' AND customer_id = '" . (int)$this->customer->getId() . "' AND customer_id != '0' AND order_status_id > '0'");

		if ($order_query->num_rows) {
			$country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$order_query->row['payment_country_id'] . "'");

			if ($country_query->num_rows) {
				$payment_iso_code_2 = $country_query->row['iso_code_2'];
				$payment_iso_code_3 = $country_query->row['iso_code_3'];
			} else {
				$payment_iso_code_2 = '';
				$payment_iso_code_3 = '';
			}

			$zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$order_query->row['payment_zone_id'] . "'");

			if ($zone_query->num_rows) {
				$payment_zone_code = $zone_query->row['code'];
			} else {
				$payment_zone_code = '';
			}

			$country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$order_query->row['shipping_country_id'] . "'");

			if ($country_query->num_rows) {
				$shipping_iso_code_2 = $country_query->row['iso_code_2'];
				$shipping_iso_code_3 = $country_query->row['iso_code_3'];
			} else {
				$shipping_iso_code_2 = '';
				$shipping_iso_code_3 = '';
			}

			$zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$order_query->row['shipping_zone_id'] . "'");

			if ($zone_query->num_rows) {
				$shipping_zone_code = $zone_query->row['code'];
			} else {
				$shipping_zone_code = '';
			}

			return array(
				'order_id'                => $order_query->row['order_id'],
				'invoice_no'              => $order_query->row['invoice_no'],
				'invoice_prefix'          => $order_query->row['invoice_prefix'],
				'store_id'                => $order_query->row['store_id'],
				'store_name'              => $order_query->row['store_name'],
				'store_url'               => $order_query->row['store_url'],
				'customer_id'             => $order_query->row['customer_id'],
				'firstname'               => $order_query->row['firstname'],
				'lastname'                => $order_query->row['lastname'],
				'telephone'               => $order_query->row['telephone'],
				'email'                   => $order_query->row['email'],
				'payment_firstname'       => $order_query->row['payment_firstname'],
				'payment_lastname'        => $order_query->row['payment_lastname'],
				'payment_company'         => $order_query->row['payment_company'],
				'payment_address_1'       => $order_query->row['payment_address_1'],
				'payment_address_2'       => $order_query->row['payment_address_2'],
				'payment_postcode'        => $order_query->row['payment_postcode'],
				'payment_city'            => $order_query->row['payment_city'],
				'payment_zone_id'         => $order_query->row['payment_zone_id'],
				'payment_zone'            => $order_query->row['payment_zone'],
				'payment_zone_code'       => $payment_zone_code,
				'payment_country_id'      => $order_query->row['payment_country_id'],
				'payment_country'         => $order_query->row['payment_country'],
				'payment_iso_code_2'      => $payment_iso_code_2,
				'payment_iso_code_3'      => $payment_iso_code_3,
				'payment_address_format'  => $order_query->row['payment_address_format'],
				'payment_method'          => $order_query->row['payment_method'],
				'shipping_firstname'      => $order_query->row['shipping_firstname'],
				'shipping_lastname'       => $order_query->row['shipping_lastname'],
				'shipping_company'        => $order_query->row['shipping_company'],
				'shipping_address_1'      => $order_query->row['shipping_address_1'],
				'shipping_address_2'      => $order_query->row['shipping_address_2'],
				'shipping_postcode'       => $order_query->row['shipping_postcode'],
				'shipping_city'           => $order_query->row['shipping_city'],
				'shipping_zone_id'        => $order_query->row['shipping_zone_id'],
				'shipping_zone'           => $order_query->row['shipping_zone'],
				'shipping_zone_code'      => $shipping_zone_code,
				'shipping_country_id'     => $order_query->row['shipping_country_id'],
				'shipping_country'        => $order_query->row['shipping_country'],
				'shipping_iso_code_2'     => $shipping_iso_code_2,
				'shipping_iso_code_3'     => $shipping_iso_code_3,
				'shipping_address_format' => $order_query->row['shipping_address_format'],
				'shipping_method'         => $order_query->row['shipping_method'],
				'comment'                 => $order_query->row['comment'],
				'total'                   => $order_query->row['total'],
				'order_status_id'         => $order_query->row['order_status_id'],
				'language_id'             => $order_query->row['language_id'],
				'currency_id'             => $order_query->row['currency_id'],
				'currency_code'           => $order_query->row['currency_code'],
				'currency_value'          => $order_query->row['currency_value'],
				'date_modified'           => $order_query->row['date_modified'],
				'date_added'              => $order_query->row['date_added'],
				'ip'                      => $order_query->row['ip']
			);
		} else {
			return false;
		}
	}

	public function getOrders($start = 0, $limit = 20) {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 1;
		}

		$query = $this->db->query("SELECT o.order_id, o.firstname, o.lastname, os.name as status, o.date_added, o.total, o.currency_code, o.currency_value FROM `" . DB_PREFIX . "order` o LEFT JOIN " . DB_PREFIX . "order_status os ON (o.order_status_id = os.order_status_id) WHERE o.customer_id = '" . (int)$this->customer->getId() . "' AND o.order_status_id > '0' AND o.store_id = '" . (int)$this->config->get('config_store_id') . "' AND os.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY o.order_id DESC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	public function getOrderProduct($order_id, $order_product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product_id . "'");

		return $query->row;
	}

	public function getOrderProducts($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

		return $query->rows;
	}

	public function getOrderOptions($order_id, $order_product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product_id . "'");

		return $query->rows;
	}

	public function getOrderVouchers($order_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_voucher` WHERE order_id = '" . (int)$order_id . "'");

		return $query->rows;
	}

	public function getOrderTotals($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' ORDER BY sort_order");

		return $query->rows;
	}

	public function getOrderHistories($order_id) {
		$query = $this->db->query("SELECT date_added, os.name AS status, oh.comment, oh.notify FROM " . DB_PREFIX . "order_history oh LEFT JOIN " . DB_PREFIX . "order_status os ON oh.order_status_id = os.order_status_id WHERE oh.order_id = '" . (int)$order_id . "' AND os.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY oh.date_added");

		return $query->rows;
	}

	public function getTotalOrders() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` o WHERE customer_id = '" . (int)$this->customer->getId() . "' AND o.order_status_id > '0' AND o.store_id = '" . (int)$this->config->get('config_store_id') . "'");

		return $query->row['total'];
	}

	public function getTotalOrderProductsByOrderId($order_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

		return $query->row['total'];
	}

	public function getTotalOrderVouchersByOrderId($order_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order_voucher` WHERE order_id = '" . (int)$order_id . "'");

		return $query->row['total'];
	}

	public function getProductInfo($product_id) {
		$query = $this->db->query("SELECT product_id, model, price FROM " .  DB_PREFIX . "product WHERE product_id = " . (int)$product_id);

		return array(
			"product_id" => $query->row['product_id'],
			"name" => $query->row['model'],
			"price" => $query->row['price']
		);
	}

	public function addOrder($data) {

		$this->db->query("INSERT INTO `" . DB_PREFIX . "order` SET invoice_prefix = '" . $this->db->escape($data['invoice_prefix']) .
		 "', store_id = '" . (int)$data['store_id'] . 
		 "', store_name = '" . $this->db->escape($data['store_name']) . 
		 "', store_url = '" . $this->db->escape($data['store_url']) . 
		 "', customer_id = '" . (int)$data['customer_id'] . 
		 "', customer_group_id = '" . (int)$data['customer_group_id'] . 
		 "', firstname = '" . $this->db->escape($data['firstname']) . 
		 "', lastname = '" . $this->db->escape($data['lastname']) . 
		 "', email = '" . $this->db->escape($data['email']) . 
		 "', telephone = '" . $this->db->escape($data['telephone']) . 
		 "', custom_field = '" . $this->db->escape(isset($data['custom_field']) ? json_encode($data['custom_field']) : '') . 
		 "', payment_firstname = '" . $this->db->escape($data['payment_firstname']) . 
		 "', payment_lastname = '" . $this->db->escape($data['payment_lastname']) . 
		 "', payment_company = '" . $this->db->escape($data['payment_company']) . 
		 "', payment_address_1 = '" . $this->db->escape($data['payment_address_1']) . 
		 "', payment_address_2 = '" . $this->db->escape($data['payment_address_2']) . 
		 "', payment_city = '" . $this->db->escape($data['payment_city']) . 
		 "', payment_postcode = '" . $this->db->escape($data['payment_postcode']) . 
		 "', payment_country = '" . $this->db->escape($data['payment_country']) . 
		 "', payment_country_id = '" . (int)$data['payment_country_id'] . 
		 "', payment_zone = '" . $this->db->escape($data['payment_zone']) . 
		 "', payment_zone_id = '" . (int)$data['payment_zone_id'] . 
		 "', payment_address_format = '" . $this->db->escape($data['payment_address_format']) . 
		 "', payment_custom_field = '" . $this->db->escape(isset($data['payment_custom_field']) ? json_encode($data['payment_custom_field']) : '') . 
		 "', payment_method = '" . $this->db->escape($data['payment_method']) . 
		 "', payment_code = '" . $this->db->escape($data['payment_code']) . 
		 "', shipping_firstname = '" . $this->db->escape($data['shipping_firstname']) . 
		 "', shipping_lastname = '" . $this->db->escape($data['shipping_lastname']) . 
		 "', shipping_company = '" . $this->db->escape($data['shipping_company']) . 
		 "', shipping_address_1 = '" . $this->db->escape($data['shipping_address_1']) . 
		 "', shipping_address_2 = '" . $this->db->escape($data['shipping_address_2']) . 
		 "', shipping_city = '" . $this->db->escape($data['shipping_city']) . 
		 "', shipping_postcode = '" . $this->db->escape($data['shipping_postcode']) . 
		 "', shipping_country = '" . $this->db->escape($data['shipping_country']) . 
		 "', shipping_country_id = '" . (int)$data['shipping_country_id'] . 
		 "', shipping_zone = '" . $this->db->escape($data['shipping_zone']) . 
		 "', shipping_zone_id = '" . (int)$data['shipping_zone_id'] . 
		 "', shipping_address_format = '" . $this->db->escape($data['shipping_address_format']) . 
		 "', shipping_custom_field = '" . $this->db->escape(isset($data['shipping_custom_field']) ? json_encode($data['shipping_custom_field']) : '') . 
		 "', shipping_method = '" . $this->db->escape($data['shipping_method']) . 
		 "', shipping_code = '" . $this->db->escape($data['shipping_code']) . 
		 "', comment = '" . $this->db->escape($data['comment']) . 
		 "', total = '" . (float)$data['total'] . 
		 "', affiliate_id = '" . (int)$data['affiliate_id'] . 
		 "', commission = '" . (float)$data['commission'] . 
		 "', marketing_id = '" . (int)$data['marketing_id'] . 
		 "', tracking = '" . $this->db->escape($data['tracking']) . 
		 "', language_id = '" . (int)$data['language_id'] . 
		 "', currency_id = '" . (int)$data['currency_id'] . 
		 "', currency_code = '" . $this->db->escape($data['currency_code']) . 
		 "', currency_value = '" . (float)$data['currency_value'] . 
		 "', ip = '" . $this->db->escape($data['ip']) . 
		 "', forwarded_ip = '" .  $this->db->escape($data['forwarded_ip']) . 
		 "', user_agent = '" . $this->db->escape($data['user_agent']) . 
		 "', accept_language = '" . $this->db->escape($data['accept_language']) . 
		 "', order_status_id = '5" .  
		 "', date_added = NOW(), date_modified = NOW()");


		$order_id = $this->db->getLastId();

		$this->db->query("INSERT INTO " . DB_PREFIX . "order_total SET order_id = '" . (int)$order_id . 
		"', code = 'total', title = 'Total', " . 
		"value = '" . (float)$data['total'] . "', " .
		"sort_order = '9'");		

		$this->db->query("INSERT INTO " . DB_PREFIX . "order_total SET order_id = '" . (int)$order_id . 
		"', code = 'shipping', title = 'Free Shipping', " . 
		"value = '0.0', " .
		"sort_order = '3'");		

		$this->db->query("INSERT INTO " . DB_PREFIX . "order_total SET order_id = '" . (int)$order_id . 
		"', code = 'sub_total', title = 'Sub-Total', " . 
		"value = '" . (float)$data['total'] . "', " .
		"sort_order = '1'");		

		$this->db->query("INSERT INTO " . DB_PREFIX . "customer_transaction SET order_id = '" . (int)$order_id . 
		"', customer_id = '" . (int)$data['affiliate_id'] . 
		"', description = 'Cash or Check Order #" . $order_id . 
		"', amount = '" . (float)$data['commission'] . 
		"', date_added = NOW()");		

		// Products
		if (isset($data['products'])) {
			foreach ($data['products'] as $product) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "order_product SET order_id = '" . (int)$order_id . 
				"', product_id = '" . (int)$product['product_id'] . 
				"', name = '" . $this->db->escape($product['name']) . 
				"', model = '" . $this->db->escape($product['model']) . 
				"', quantity = '" . (int)$product['quantity'] . 
				"', price = '" . (float)$product['price'] . 
				"', total = '" . (float)$product['total'] . 
				"', tax = '" . (float)$product['tax'] . 
				"', reward = '" . (int)$product['reward'] . "'");

				// $order_product_id = $this->db->getLastId();

				// foreach ($product['option'] as $option) {
				// 	$this->db->query("INSERT INTO " . DB_PREFIX . "order_option SET order_id = '" . (int)$order_id . "', order_product_id = '" . (int)$order_product_id . "', product_option_id = '" . (int)$option['product_option_id'] . "', product_option_value_id = '" . (int)$option['product_option_value_id'] . "', name = '" . $this->db->escape($option['name']) . "', `value` = '" . $this->db->escape($option['value']) . "', `type` = '" . $this->db->escape($option['type']) . "'");
				// }
				$this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = (quantity - " . (int)$product['quantity'] . ") WHERE product_id = '" . (int)$product['product_id'] . "' AND subtract = '1'");
			}
		}

		return $order_id;

	}


	public function getPaymentMethods() {
		return array(
			array(
				"payment_method" => "cod",
				"name" => "Cash"
			),
			array(
				"payment_method" => "cheque",
				"name" => "Cheque"
			)
		);
	}


	public function getPaymentMethod($code) {

		switch ($code) {
			case 'cod':
				return 'Cash';
				
			case 'cheque':
				return 'Cheque';
				
		}
		return '';
	}

	public function getShipingMethods() {
		return array(
			array(
				"code" => "free.free",
				"title" => "Free no rush delivery (untill Dec 20)"
			),
			array(
				"code" => "pickup.pickup",
				"title" => "Early delivery (before Thanksgiving)"
			)
		);
	}

	public function getShipingMethod($code) {

		switch ($code) {
			case 'free.free':
				return 'Free Delivery';
				
			case 'pickup.pickup':
				return 'Early Delivery';
				
			case 'flat.flat':
				return 'Flat Shipping Rate';

			case 'item.item':
				return 'Itemized Shipping Rate';
	
			}
		return '';
	}


}