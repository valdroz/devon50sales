<?php
class ModelAccountSalesmap extends Model {
	public function getGisOrders() {

		$cust_id = (int)$this->customer->getId();

		$sql = "
		SELECT 
			gis_sales.order_id, scout_id, scout_firstname, scout_lastname, order_date, order_year, order_month, products, cust_firstname, cust_lastname, address_line_1, address_line_2, city, state, postal_code, lat, lon
		FROM (
				SELECT sales.order_id, scout_id, scout.firstname as scout_firstname, scout.lastname as scout_lastname, order_date, order_year, order_month,
					GROUP_CONCAT(products SEPARATOR ', ') AS 'products',
					cust_firstname, cust_lastname, address_line_1, address_line_2, city, state, postal_code
				FROM (
					SELECT 
						oc_order.order_id as order_id,
						cust.customer_id as scout_id, 
						oc_order.date_added as order_date, 
						year(oc_order.date_added) as order_year,
						month(oc_order.date_added) as order_month,
						CONCAT( prod.quantity, ' ', prod.name, '(s); ' ) AS 'products', 
						oc_order.shipping_firstname as 'cust_firstname',
						oc_order.shipping_lastname as 'cust_lastname',
						oc_order.shipping_address_1 as 'address_line_1',
						oc_order.shipping_address_2 as 'address_line_2',
						oc_order.shipping_city as 'city',
						oc_order.shipping_zone as 'state',
						oc_order.shipping_postcode as 'postal_code'
					FROM 
						ocdevon.oc_customer AS cust, 
						ocdevon.oc_customer_affiliate as af, 
						ocdevon.oc_customer_transaction AS tr,
						ocdevon.oc_order_product as prod, 
						ocdevon.oc_order
					WHERE 
						tr.customer_id = cust.customer_id AND
						af.customer_id = cust.customer_id AND
						prod.order_id = tr.order_id AND
						oc_order.order_id = tr.order_id AND
						cust.customer_group_id = 2 AND
						prod.product_id in (50,51) 
				) AS sales, ocdevon.oc_customer as scout
				WHERE scout.customer_id = scout_id
				GROUP BY sales.order_id, sales.scout_id
			) as gis_sales, ocdevon.oc_order_gis as gis
		WHERE  gis_sales.order_id = gis.order_id AND gis.type_id = 0		
	 ";

		$query = $this->db->query($sql);

		return $query->rows;
	}

}