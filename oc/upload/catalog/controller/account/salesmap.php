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
		$current_month = date('m');

		$cust_id = (int)$this->customer->getId();

		$results = $this->model_account_salesmap->getGisOrders();

		foreach ($results as $result) {

			$dm = ($current_year - $result['order_year']) * 12 + ($current_month - $result['order_month']);


			
			if ( $dm < 6 ) {
				if ( $result['scout_id'] == $cust_id ) {				
					$data['my_current'][] = $result;
				} else {
					$data['all_current'][] = $result;
				}
			} else {
				if ( $result['scout_id'] == $cust_id ) {				
					$data['my_old'][] = $result;
				} else {
					$data['all_old'][] = $result;
				}
			}
		}

		$data['this_page_url'] = $this->url->link('account/salesmap', '', true);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$data['mapbox_api_key'] = MAPBOX_API_KEY;

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