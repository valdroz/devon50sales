<?php
class ControllerAccountSales extends Controller {
	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/sales', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->language('account/sales');

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
			'href' => $this->url->link('account/sales', '', true)
		);

		$this->load->model('account/sales');
		
		$data['column_amount'] = sprintf($this->language->get('column_amount'), $this->config->get('config_currency'));

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$current_year = date('Y');

		if (isset($this->request->get['year'])) {
			$orders_year = $this->request->get['year'];
		} else {
			$orders_year = $current_year;
		}		

		$data['transactions'] = array();

		$data['orders_year'] = $orders_year;

		$orders_year_choices = $this->model_account_sales->getTransactionYears();
		
		if (sizeof($orders_year_choices) > 0 ) {
			if ($orders_year_choices[0] != $current_year) {
				$this->array_insert($orders_year_choices,0,$current_year);
			}
		}

		$data['orders_year_choices'] = $orders_year_choices;

		$filter_data = array(
			'year'	=> $orders_year,
			'sort'  => 'date_added',
			'order' => 'DESC',
			'start' => ($page - 1) * 10,
			'limit' => 10
		);

		$transaction_total = $this->model_account_sales->getTotalTransactions($filter_data);

		$results = $this->model_account_sales->getTransactions($filter_data);		

		$currency = $this->config->get('config_currency');

		$total_amount = $this->model_account_sales->getSaleTotal($filter_data);
		$formatter = numfmt_create( 'en_US', NumberFormatter::CURRENCY );
		$total_amount = numfmt_format_currency($formatter, $total_amount, "USD");

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

		$pagination = new Pagination();
		$pagination->total = $transaction_total;
		$pagination->page = $page;
		$pagination->limit = 10;
		$pagination->url = $this->url->link('account/sales', 'page={page}&year=' . $orders_year, true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($transaction_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($transaction_total - 10)) ? $transaction_total : ((($page - 1) * 10) + 10), $transaction_total, ceil($transaction_total / 10));

		$data['continue'] = $this->url->link('account/account', '', true);
		$data['this_page_url'] = $this->url->link('account/sales', '', true);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$data['total_amount'] = $total_amount;

		$this->response->setOutput($this->load->view('account/sales', $data));
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