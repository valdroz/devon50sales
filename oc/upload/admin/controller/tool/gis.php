<?php
class ControllerToolGis extends Controller {
	public function index() {
		$this->load->language('tool/gis');

		$this->document->setTitle($this->language->get('heading_title'));

		if (isset($this->session->data['error'])) {
			$data['error_warning'] = $this->session->data['error'];

			unset($this->session->data['error']);
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('tool/gis', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['user_token'] = $this->session->data['user_token'];

		$data['backfill_action'] = $this->url->link('tool/gis/backfill', 'user_token=' . $this->session->data['user_token'], true);
		
		$this->load->model('tool/gis');

		$sales_with_no_gis = $this->model_tool_gis->getOrderWithoutGis(1000);

		$data['no_gis_count'] = sizeof($sales_with_no_gis);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('tool/gis', $data));
	}
	

	public function backfill() {
		$this->load->language('tool/gis');

		if (!$this->user->hasPermission('modify', 'tool/gis')) {
			$this->session->data['error'] = $this->language->get('error_permission');

			$this->response->redirect($this->url->link('tool/gis', 'user_token=' . $this->session->data['user_token'], true));
		} else {

			$this->load->model('tool/gis');

			$addresses = $this->model_tool_gis->getOrderWithoutGis(10);

			$this->load->model('extension/geo/location');

			foreach ($addresses as $address) {
				$order_id = $address['order_id'];
				$this->log->write('DEBUG: GET GIS for order ' . $order_id);
				$address['shipping_country'] = 'US';
				$this->model_extension_geo_location->recordGeoLocationForOrder($order_id, $address);		
			}

			$this->response->redirect($this->url->link('tool/gis', 'user_token=' . $this->session->data['user_token'], true));
		}
	}	
}
