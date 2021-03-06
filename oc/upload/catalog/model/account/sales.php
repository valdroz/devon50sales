<?php
class ModelAccountSales extends Model {
	public function getTransactions($data = array()) {

		$cust_id = (int)$this->customer->getId();

		$sql = "
		SELECT 
		 oc_order.date_added as order_date,
		 oc_order.order_id as order_id,
		 prod.name as product_name,
		 prod.price as product_price,
		 prod.quantity as quantity,
		 prod.total as total,
		 oc_order.shipping_firstname as sh_first_name,
		 oc_order.shipping_lastname as sh_last_name,
		 oc_order.shipping_company as sh_company_name,
		 oc_order.shipping_address_1 as sh_addr_line_1,
		 oc_order.shipping_address_2 as sh_addr_line_2,
		 oc_order.shipping_city as sh_city,
		 oc_order.shipping_postcode as sh_postcode,
		 oc_order.shipping_country as sh_country
		FROM 
		 oc_customer AS cust, 
		 oc_customer_affiliate as af,
		 oc_customer_transaction AS tr, 
		 oc_customer_group_description AS gr,
		 oc_order_product as prod,
		 oc_order
		WHERE tr.customer_id = cust.customer_id AND 
			cust.customer_group_id = gr.customer_group_id AND 
			af.customer_id = cust.customer_id AND
			prod.order_id = tr.order_id AND
			oc_order.order_id = tr.order_id AND 
			cust.customer_id = '" . $cust_id . "'
		ORDER BY oc_order.date_added desc
		 ";

		$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getTotalTransactions() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "customer_transaction` WHERE customer_id = '" . (int)$this->customer->getId() . "'");

		return $query->row['total'];
	}

}