<?php
class ControllerAccountEntersale extends Controller {
	private $error = array();

	public function index() {

		$this->config->set('template_cache', false);

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/account', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->language('account/entersale');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment.min.js');
		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment-with-locales.min.js');
		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
		$this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');

		$this->load->model('account/entersale');
		$this->load->model('account/customer');
		$this->load->model('localisation/country');
		$this->load->model('localisation/zone');


		$customer_info = $this->model_account_customer->getCustomer($this->customer->getId());

		$affiliate_info = $this->model_account_customer->getAffiliate($this->customer->getId());

		$wreath_info = $this->model_account_entersale->getProductInfo(50);

		$data['wreath_name'] = $wreath_info['name'];
		$data['wreath_price'] = $wreath_info['price'];

		$swag_info = $this->model_account_entersale->getProductInfo(51);

		$data['swag_name'] = $swag_info['name'];
		$data['swag_price'] = $swag_info['price'];

		$donate_info = $this->model_account_entersale->getProductInfo(53);

		$data['donate_name'] = $donate_info['name'];
		$data['donate_price'] = $donate_info['price'];		

		$data['affiliate_info'] = $affiliate_info;
		$data['customer_info'] = $customer_info;

		$data['countries'] = $this->model_localisation_country->getCountries();	
		$data['payment_methods'] = $this->model_account_entersale->getPaymentMethods();
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
		
			$data['store_id'] = $this->config->get('config_store_id');
			$data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
			$data['store_id'] = $this->config->get('config_store_id');
			$data['store_name'] = $this->config->get('config_name');
			if ($data['store_id']) {
				$data['store_url'] = $this->config->get('config_url');
			} else {
				$data['store_url'] = HTTP_SERVER;
			}
			$data['customer_id'] = 0;
			$data['customer_group_id'] = $this->config->get('config_customer_group_id');
	
			$country_name = $this->model_localisation_country->getCountry($this->request->post['country_id'])['name']; 

			$payment_method = $this->model_account_entersale->getPaymentMethod($this->request->post['payment_method']);

			$zone = $this->model_localisation_zone->getZone($this->request->post['zone_id']);

			$data['firstname'] = $this->request->post['payment_firstname'];
			$data['lastname'] = $this->request->post['payment_lastname'];
			$data['email'] = isset($this->request->post['email']) ? $this->request->post['email'] : '';
			$data['telephone'] = isset($this->request->post['telephone']) ? $this->request->post['telephone'] : '';
			$data['payment_firstname'] = $this->request->post['payment_firstname'];
			$data['payment_lastname'] = $this->request->post['payment_lastname']; 
			$data['payment_company'] = isset($this->request->post['payment_company']) ? $this->request->post['payment_company'] : '';
			$data['payment_address_1'] = $this->request->post['address_1'];
			$data['payment_address_2'] = $this->request->post['address_2'];
			$data['payment_city'] = $this->request->post['city'];
			$data['payment_postcode'] = $this->request->post['postcode'];
			$data['payment_country'] = $country_name; 
			$data['payment_country_id'] = $this->request->post['country_id'];
			$data['payment_zone'] = $zone['code']; 
			$data['payment_zone_id'] = $this->request->post['zone_id'];
			$data['payment_address_format'] = '';
			$data['payment_method'] = $payment_method;
			$data['payment_code'] = $this->request->post['payment_method'];

			$data['shipping_firstname'] = $this->request->post['payment_firstname'];
			$data['shipping_lastname'] = $this->request->post['payment_lastname'];
			$data['shipping_company']  = isset($this->request->post['payment_company']) ? $this->request->post['payment_company'] : '';
			$data['shipping_address_1'] = $this->request->post['address_1'];
			$data['shipping_address_2'] = $this->request->post['address_2'];
			$data['shipping_city'] = $this->request->post['city'];
			$data['shipping_postcode'] = $this->request->post['postcode'];
			$data['shipping_country'] = $country_name;  
			$data['shipping_country_id']  = $this->request->post['country_id'];
			$data['shipping_zone'] = $zone['code'];
			$data['shipping_zone_id'] = $this->request->post['zone_id']; 
			$data['shipping_address_format'] = '';
			$data['shipping_method'] = 'Free Shipping';
			$data['shipping_code'] = 'free.free';		

			$data['comment'] = 'Entered by scout';
			$wreath_quantity = intval($this->request->post['wreath_quantity']);
			$swag_quantity = intval($this->request->post['swag_quantity']);
			$donate_quantity = intval($this->request->post['donate_quantity']);
			
			$total = 
				$wreath_quantity * $wreath_info['price'] + 
				$swag_quantity * $swag_info['price'] + 
				$donate_quantity * $data['donate_price'];

			//if ($affiliate_info) {
				$data['affiliate_id'] = $affiliate_info['customer_id'];
				$data['commission'] = ($total / 100) * $affiliate_info['commission'];
			// } else {
			// 	$data['affiliate_id'] = 0;
			// 	$data['commission'] = 0;
			// }

			$data['total'] = $total;
			$data['affiliate_id'] = $this->customer->getId();
			$data['commission'] = 0.0;
			$data['marketing_id'] = 0;
			$data['tracking'] = $affiliate_info['tracking'];
			$data['language_id'] = 1;
			$data['currency_id'] = 2;
			$data['currency_code'] = 'USD';
			$data['currency_value'] = 1.0;
			$data['ip'] = $this->request->server['REMOTE_ADDR'];

			if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
				$data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];
			} elseif (!empty($this->request->server['HTTP_CLIENT_IP'])) {
				$data['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];
			} else {
				$data['forwarded_ip'] = '';
			}

			if (isset($this->request->server['HTTP_USER_AGENT'])) {
				$data['user_agent'] = $this->request->server['HTTP_USER_AGENT'];
			} else {
				$data['user_agent'] = '';
			}

			if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
				$data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
			} else {
				$data['accept_language'] = '';
			}
			
			$data['products'] = array();
			if ($wreath_quantity > 0) {
				$data['products'][] = array(
					'product_id' => $wreath_info['product_id'],
					'name' => $wreath_info['name'],
					'model' => $wreath_info['name'],
					'quantity' => $wreath_quantity,
					'price' => $wreath_info['price'],
					'total' => $wreath_quantity * $wreath_info['price'],
					'tax' => 0.0,
					'reward' => 0
				);
			}

			if ($swag_quantity > 0) {
				$data['products'][] = array(
					'product_id' => $swag_info['product_id'],
					'name' => $swag_info['name'],
					'model' => $swag_info['name'],
					'quantity' => $swag_quantity,
					'price' => $swag_info['price'],
					'total' => $swag_quantity * $swag_info['price'],
					'tax' => 0.0,
					'reward' => 0
				);
			}

			if ($donate_quantity > 0) {
				$data['products'][] = array(
					'product_id' => $donate_info['product_id'],
					'name' => $donate_info['name'],
					'model' => $donate_info['name'],
					'quantity' => $donate_quantity,
					'price' => $donate_info['price'],
					'total' => $donate_quantity * $donate_info['price'],
					'tax' => 0.0,
					'reward' => 0
				);
			}


			$this->model_account_entersale->addOrder($data);
			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('account/account', '', true));
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_entersale'),
			'href' => $this->url->link('account/entersale', '', true)
		);

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}


		if (isset($this->error['order_date'])) {
			$data['error_order_date'] = $this->error['order_date'];
		} else {
			$data['error_order_date'] = '';
		}


		if (isset($this->error['payment_firstname'])) {
			$data['error_payment_firstname'] = $this->error['payment_firstname'];
		} else {
			$data['error_payment_firstname'] = '';
		}

		if (isset($this->error['payment_lastname'])) {
			$data['error_payment_lastname'] = $this->error['payment_lastname'];
		} else {
			$data['error_payment_lastname'] = '';
		}

		if (isset($this->error['email'])) {
			$data['error_email'] = $this->error['email'];
		} else {
			$data['error_email'] = '';
		}

		if (isset($this->error['address_1'])) {
			$data['error_address_1'] = $this->error['address_1'];
		} else {
			$data['error_address_1'] = '';
		}

		if (isset($this->error['address_2'])) {
			$data['error_address_2'] = $this->error['address_2'];
		} else {
			$data['error_address_2'] = '';
		}

		if (isset($this->error['city'])) {
			$data['error_city'] = $this->error['city'];
		} else {
			$data['error_city'] = '';
		}
		
		if (isset($this->error['postcode'])) {
			$data['error_postcode'] = $this->error['postcode'];
		} else {
			$data['error_postcode'] = '';
		}

		if (isset($this->error['country'])) {
			$data['error_country'] = $this->error['country'];
		} else {
			$data['error_country'] = '';
		}

		if (isset($this->error['zone'])) {
			$data['error_zone'] = $this->error['zone'];
		} else {
			$data['error_zone'] = '';
		}

		if (isset($this->error['telephone'])) {
			$data['error_telephone'] = $this->error['telephone'];
		} else {
			$data['error_telephone'] = '';
		}

		if (isset($this->error['wreath_quantity'])) {
			$data['error_wreath_quantity'] = $this->error['wreath_quantity'];
		} else {
			$data['error_wreath_quantity'] = '';
		}

		if (isset($this->error['swag_quantity'])) {
			$data['error_swag_quantity'] = $this->error['swag_quantity'];
		} else {
			$data['error_swag_quantity'] = '';
		}

		if (isset($this->error['donate_quantity'])) {
			$data['error_donate_quantity'] = $this->error['donate_quantity'];
		} else {
			$data['error_donate_quantity'] = '';
		}

		if (isset($this->error['payment_method'])) {
			$data['error_payment_method'] = $this->error['payment_method'];
		} else {
			$data['error_payment_method'] = '';
		}



		
		if (isset($this->request->post['order_date'])) {
			$data['order_date'] = $this->request->post['order_date'];
		} else {
			$data['order_date'] = date_format(date_create(),"m/d/Y");
		}

		if (isset($this->request->post['country_id'])) {
			$data['country_id'] = $this->request->post['country_id'];
		} else {
			$data['country_id'] = $this->config->get('config_country_id');
		}

		if (isset($this->request->post['zone_id'])) {
			$data['zone_id'] = $this->request->post['zone_id'];
		} else {
			$data['zone_id'] = $this->config->get('config_zone_id');
		}

		if (isset($this->request->post['payment_firstname'])) {
			$data['payment_firstname'] = $this->request->post['payment_firstname']; 
		} else {
			$data['payment_firstname'] = '';
		}

		if (isset($this->request->post['payment_lastname'])) {
			$data['payment_lastname'] = $this->request->post['payment_lastname']; 
		} else {
			$data['payment_lastname'] = '';
		}

		if (isset($this->request->post['payment_company'])) {
			$data['payment_company'] = $this->request->post['payment_company']; 
		} else {
			$data['payment_company'] = '';
		}

		if (isset($this->request->post['address_1'])) {
			$data['address_1'] = $this->request->post['address_1']; 
		} else {
			$data['address_1'] = '';
		}

		if (isset($this->request->post['address_2'])) {
			$data['address_2'] = $this->request->post['address_2']; 
		} else {
			$data['address_2'] = '';
		}
	
		if (isset($this->request->post['city'])) {
			$data['city'] = $this->request->post['city']; 
		} else {
			$data['city'] = '';
		}
		
		if (isset($this->request->post['postcode'])) {
			$data['postcode'] = $this->request->post['postcode']; 
		} else {
			$data['postcode'] = '';
		}
			
		if (isset($this->request->post['country'])) {
			$data['country'] = $this->request->post['country']; 
		} else {
			$data['country'] = '';
		}

		if (isset($this->request->post['zone'])) {
			$data['zone'] = $this->request->post['zone']; 
		} else {
			$data['zone'] = '';
		}

		if (isset($this->request->post['wreath_quantity'])) {
			$data['wreath_quantity'] = $this->request->post['wreath_quantity']; 
		} else {
			$data['wreath_quantity'] = '';
		}

		if (isset($this->request->post['swag_quantity'])) {
			$data['swag_quantity'] = $this->request->post['swag_quantity']; 
		} else {
			$data['swag_quantity'] = '';
		}

		if (isset($this->request->post['donate_quantity'])) {
			$data['donate_quantity'] = $this->request->post['donate_quantity']; 		
		} else {
			$data['donate_quantity'] = '';
		}

		if (isset($this->request->post['payment_method'])) {
			$data['payment_method'] = $this->request->post['payment_method']; 		
		} else {
			$data['payment_method'] = '';
		}

		// if (isset($this->error['custom_field'])) {
		// 	$data['error_custom_field'] = $this->error['custom_field'];
		// } else {
		// 	$data['error_custom_field'] = array();
		// }


		$data['action'] = $this->url->link('account/entersale', '', true);

		// if ($this->request->server['REQUEST_METHOD'] != 'POST') {
		// 	$customer_info = $this->model_account_customer->getCustomer($this->customer->getId());
		// }

		// if (isset($this->request->post['firstname'])) {
		// 	$data['firstname'] = $this->request->post['firstname'];
		// } elseif (!empty($customer_info)) {
		// 	$data['firstname'] = $customer_info['firstname'];
		// } else {
		// 	$data['firstname'] = '';
		// }

		// if (isset($this->request->post['lastname'])) {
		// 	$data['lastname'] = $this->request->post['lastname'];
		// } elseif (!empty($customer_info)) {
		// 	$data['lastname'] = $customer_info['lastname'];
		// } else {
		// 	$data['lastname'] = '';
		// }

		if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		// } elseif (!empty($customer_info)) {
		// 	$data['email'] = $customer_info['email'];
		} else {
			$data['email'] = '';
		}

		if (isset($this->request->post['telephone'])) {
			$data['telephone'] = $this->request->post['telephone'];
		// } elseif (!empty($customer_info)) {
		// 	$data['telephone'] = $customer_info['telephone'];
		} else {
			$data['telephone'] = '';
		}

		// Custom Fields
		$data['custom_fields'] = array();
		
		// $this->load->model('account/custom_field');

		// $custom_fields = $this->model_account_custom_field->getCustomFields($this->config->get('config_customer_group_id'));

		// foreach ($custom_fields as $custom_field) {
		// 	if ($custom_field['location'] == 'account') {
		// 		$data['custom_fields'][] = $custom_field;
		// 	}
		// }

		// if (isset($this->request->post['custom_field']['account'])) {
		// 	$data['account_custom_field'] = $this->request->post['custom_field']['account'];
		// } elseif (isset($customer_info)) {
		// 	$data['account_custom_field'] = json_decode($customer_info['custom_field'], true);
		// } else {
		// 	$data['account_custom_field'] = array();
		// }

		$data['back'] = $this->url->link('account/account', '', true);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('account/entersale', $data));
	}

	protected function validate() {

		// order_date
		$order_date = date_create_from_format("m/d/Y", $this->request->post['order_date']);
		$current_date = date_create();
		$date_diff = date_diff($order_date, $current_date);

		if ($date_diff->invert > 0) {
			$this->error['order_date'] = $this->language->get('error_future_order_date');
			$this->error['warning'] = $this->language->get('error_form');
		}		
		
		if ((utf8_strlen(trim($this->request->post['payment_firstname'])) < 1) || (utf8_strlen(trim($this->request->post['payment_firstname'])) > 32)) {
			$this->error['payment_firstname'] = $this->language->get('error_payment_firstname');
			$this->error['warning'] = $this->language->get('error_form');
		}

		if ((utf8_strlen(trim($this->request->post['payment_firstname'])) < 1) || (utf8_strlen(trim($this->request->post['payment_firstname'])) > 32)) {
			$this->error['payment_firstname'] = $this->language->get('error_payment_firstname');
			$this->error['warning'] = $this->language->get('error_form');
		}

		if ((utf8_strlen(trim($this->request->post['payment_lastname'])) < 1) || (utf8_strlen(trim($this->request->post['payment_lastname'])) > 32)) {
			$this->error['payment_lastname'] = $this->language->get('error_payment_lastname');
			$this->error['warning'] = $this->language->get('error_form');
		}

		if ((utf8_strlen(trim($this->request->post['address_1'])) < 3) || (utf8_strlen(trim($this->request->post['address_1'])) > 128)) {
			$this->error['address_1'] = $this->language->get('error_address_1');
			$this->error['warning'] = $this->language->get('error_form');
		}

		if ((utf8_strlen(trim($this->request->post['city'])) < 2) || (utf8_strlen(trim($this->request->post['city'])) > 128)) {
			$this->error['city'] = $this->language->get('error_city');
			$this->error['warning'] = $this->language->get('error_form');
		}

		if ((utf8_strlen(trim($this->request->post['postcode'])) < 3) || (utf8_strlen(trim($this->request->post['postcode'])) > 10)) {
			$this->error['postcode'] = $this->language->get('error_postcode');
			$this->error['warning'] = $this->language->get('error_form');
		}

		if ( !isset($this->request->post['country_id']) || (utf8_strlen(trim($this->request->post['country_id'])) < 1)) {
			$this->error['country'] = $this->language->get('error_country');
			$this->error['warning'] = $this->language->get('error_form');
		}

		if ( !isset($this->request->post['zone_id']) || (utf8_strlen(trim($this->request->post['zone_id'])) < 1)) {
			$this->error['zone'] = $this->language->get('error_zone');
			$this->error['warning'] = $this->language->get('error_form');
		}

		$wreath_quantity = 0;
		if (isset($this->request->post['wreath_quantity']) && is_numeric($this->request->post['wreath_quantity'])) {
			$wreath_quantity = intval($this->request->post['wreath_quantity']);
		}

		$swag_quantity = 0;
		if (isset($this->request->post['swag_quantity']) && is_numeric($this->request->post['swag_quantity'])) {
			$swag_quantity = intval($this->request->post['swag_quantity']);
		}

		$donate_quantity = 0;
		if (isset($this->request->post['donate_quantity']) && is_numeric($this->request->post['donate_quantity'])) {
			$donate_quantity = intval($this->request->post['donate_quantity']);
		}

		if ($wreath_quantity < 0) {
			$this->error['wreath_quantity'] = $this->language->get('error_negative_quantity');
			$this->error['warning'] = $this->language->get('error_form');
		}

		if ($swag_quantity < 0) {
			$this->error['swag_quantity'] = $this->language->get('error_negative_quantity');
			$this->error['warning'] = $this->language->get('error_form');
		}

		if ($donate_quantity < 0) {
			$this->error['donate_quantity'] = $this->language->get('error_negative_quantity');
			$this->error['warning'] = $this->language->get('error_form');
		}

		if ( !isset($this->request->post['payment_method']) || utf8_strlen(trim($this->request->post['payment_method'])) < 1 ) {
			$this->error['payment_method'] = $this->language->get('error_payment_method');
			$this->error['warning'] = $this->language->get('error_form');
		}
	
		if ($wreath_quantity == 0 && $swag_quantity == 0 && $donate_quantity == 0) {
			$this->error['wreath_quantity'] = $this->language->get('error_missing_quantity');
			$this->error['swag_quantity'] = $this->language->get('error_missing_quantity');
			$this->error['donate_quantity'] = $this->language->get('error_missing_quantity');
			$this->error['warning'] = $this->language->get('error_missing_quantity');
		}



		// if ((utf8_strlen($this->request->post['email']) > 96) || !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
		// 	$this->error['email'] = $this->language->get('error_email');
		// }

		// if (($this->customer->getEmail() != $this->request->post['email']) && $this->model_account_customer->getTotalCustomersByEmail($this->request->post['email'])) {
		// 	$this->error['warning'] = $this->language->get('error_exists');
		// }

		// if ((utf8_strlen($this->request->post['telephone']) < 3) || (utf8_strlen($this->request->post['telephone']) > 32)) {
		// 	$this->error['telephone'] = $this->language->get('error_telephone');
		// }

		// // Custom field validation
		// $this->load->model('account/custom_field');

		// $custom_fields = $this->model_account_custom_field->getCustomFields('account', $this->config->get('config_customer_group_id'));

		// foreach ($custom_fields as $custom_field) {
		// 	if ($custom_field['location'] == 'account') {
		// 		if ($custom_field['required'] && empty($this->request->post['custom_field'][$custom_field['location']][$custom_field['custom_field_id']])) {
		// 			$this->error['custom_field'][$custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
		// 		} elseif (($custom_field['type'] == 'text') && !empty($custom_field['validation']) && !filter_var($this->request->post['custom_field'][$custom_field['location']][$custom_field['custom_field_id']], FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => $custom_field['validation'])))) {
		// 			$this->error['custom_field'][$custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
		// 		}
		// 	}
		// }

		return !$this->error;
	}
}