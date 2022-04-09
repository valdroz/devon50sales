<?php


class ModelExtensionGeoLocation extends Model {
	private $logger;

	public function __construct($registry) {
		parent::__construct($registry);
		$this->logger = new Log("geo.log");
	}


	function getGeoLocation($street, $city, $region, $country) {

		$latitude = 0;
		$longitude = 0;
	
		try {
		
			$gis_street = urlencode($street);
			$gis_city = urlencode($city);
			$gis_region = urlencode($region);
			$gis_country = urlencode($country);
	
			$api_uri = "https://nominatim.openstreetmap.org/search?street=" . $gis_street . "&city=" . $gis_city . "&state=" . $gis_region . "&country=" . $gis_country . "&format=json";

			$this->logger->write("GIS URL: " . $api_uri);

	
			$curl = curl_init();
	
			$headers = array();
			$headers[] = 'User-Agent: np_d50_raise';
	
			curl_setopt($curl, CURLOPT_URL, $api_uri);
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($curl, CURLOPT_TIMEOUT, 3);
	
			$response = curl_exec($curl);
			$error = curl_errno($curl);
	
			if ($error) {
				$logger->write('cURL error: ' . curl_errno($curl));
			}
	
			curl_close($curl);			
	
			if (!$error) {
				$output= json_decode($response);
				if ($output && sizeof($output) > 0) {
					$latitude = $output[0]->lat;
					$longitude = $output[0]->lon;
				}
			}
	
		} catch (Exception $ex) {
			$logger->write('Unable to get GIS location. Error message: ' . $ex->getMessage());
		}

		$data = array();
		$data['lon'] = $longitude;
		$data['lat'] = $latitude;
	
		return $data;
	}


	public function recordGeoLocationForOrder($order_id, $order_data) {

		$geo_data = $this->getGeoLocation(
			$order_data['payment_address_1'],
			$order_data['payment_city'],
			$order_data['payment_zone'],
			$order_data['payment_country'],		
		);
	
		if ( $geo_data['lat'] == 0 || $geo_data['lon'] == 0 ) {
			return -1;
		}

		// Payment location type
		$type_id = 1;

		$this->db->query("DELETE FROM " . DB_PREFIX . "order_gis " . 
						"WHERE order_id = '" . (int)$order_id .
						 "' AND type_id = '" . (int)$type_id . 
						 "'");

		$this->db->query("INSERT INTO " . DB_PREFIX . "order_gis " . 
						"SET order_id = '" . (int)$order_id .
						 "', type_id = '" . (int)$type_id . 
						 "', lat = '" . $geo_data['lat'] . 
						 "', lon = '" . $geo_data['lon'] . 
						 "'");

		return $this->db->getLastId();
	}


}