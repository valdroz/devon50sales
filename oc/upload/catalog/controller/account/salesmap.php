<?php
class ControllerAccountSalesmap extends Controller {
	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/salesmap', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->language('account/salesmap');

		$this->document->setTitle($this->language->get('heading_title'));

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
			'text' => $this->language->get('text_sales'),
			'href' => $this->url->link('account/salesmap', '', true)
		);

		$this->load->model('account/salesmap');
		
		$data['column_amount'] = sprintf($this->language->get('column_amount'), $this->config->get('config_currency'));

		if (isset($this->request->get['view_mod'])) {
			$view_mode = $this->request->get['view_mod'];
		} else {
			$view_mode = "self";
		}

		$current_year = date('Y');

		$data['transactions'] = array();

		$orders_year_choices = $this->model_account_salesmap->getTransactionYears();
		
		if (sizeof($orders_year_choices) > 0 ) {
			if ($orders_year_choices[0] != $current_year) {
				$this->array_insert($orders_year_choices,0,$current_year);
			}
		}

		$data['orders_year_choices'] = $orders_year_choices;

		$filter_data = array(
			'year'	=> $current_year,
			'sort'  => 'date_added',
			'order' => 'DESC',
			'start' => 0,
			'limit' => 10
		);

		$results = $this->model_account_salesmap->getTransactions($filter_data);		

		$currency = $this->config->get('config_currency');

		foreach ($results as $result) {
			$data['transactions'][] = array(
				'total'      => $this->currency->format($result['total'], $currency ),
				'product_price' => $this->currency->format($result['product_price'], $currency),
				'order_date'  => date($this->language->get('date_format_short'), strtotime($result['order_date'])),
				'order_id' => $result['order_id'],
				'product_name' => $result['product_name'],
				'quantity' => $result['quantity'],
				'sh_first_name' => $result['sh_first_name'],
				'sh_last_name' => $result['sh_last_name'],
				'sh_company_name' => $result['sh_company_name'],
				'sh_addr_line_1' => $result['sh_addr_line_1'],
				'sh_addr_line_2' => $result['sh_addr_line_2'],
				'sh_city' => $result['sh_city'],
				'sh_zone' => $result['sh_zone'],
				'sh_postcode' => $result['sh_postcode'],
				'sh_country' => $result['sh_country'],
				'email' => $result['email'],
				'telephone' => $result['telephone'],
				'comment' => $result['comment'],
				'shipping_code' => $result['shipping_code'],
				'payment_method' => strlen($result['payment_method']) > 22 ? substr($result['payment_method'], 0, 22) . '...' : $result['payment_method']
			);
		}

		$data['continue'] = $this->url->link('account/account', '', true);
		$data['this_page_url'] = $this->url->link('account/salesmap', '', true);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('account/salesmap', $data));
	}


	/**
	 * @param array      $array
	 * @param int|string $position
	 * @param mixed      $insert
	 */
	public function array_insert(&$array, $position, $insert)
	{
		if (is_int($position)) {
			array_splice($array, $position, 0, $insert);
		} else {
			$pos   = array_search($position, array_keys($array));
			$array = array_merge(
				array_slice($array, 0, $pos),
				$insert,
				array_slice($array, $pos)
			);
		}
	}
}