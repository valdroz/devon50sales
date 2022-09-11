<?php
class ModelToolGis extends Model {


	public function getOrderWithoutGis($limit)
	{
		$sql = "SELECT " .
			"ord.order_id as order_id, " .
			"ord.shipping_address_1 as 'shipping_address_1', " .
			"ord.shipping_address_2 as 'shipping_address_2', " .
			"ord.shipping_city as 'shipping_city', " .
			"ord.shipping_zone as 'shipping_zone', " .
			"ord.shipping_postcode as 'shipping_postcode' " .
			"FROM " .
			DB_DATABASE . "." . DB_PREFIX . "order AS ord " .
			"WHERE shipping_address_1 IS NOT NULL AND shipping_address_1 != '' AND " . 
			"order_id not in (" .
			"SELECT gis.order_id FROM " . DB_DATABASE . "." . DB_PREFIX . "order_gis as gis" .
			") LIMIT " . $limit;

		$query = $this->db->query($sql);

		return $query->rows;
	}

}