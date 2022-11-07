<?php


class ModelExtensionGeoLocation extends Model {
	private $logger;

	public function __construct($registry) {
		parent::__construct($registry);
		$this->logger = new Log("geo.log");
	}


	function getGeoLocation_nominatim($street, $city, $region, $country) {

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

	function getGeoLocation($street, $city, $region, $country, $postalcode) {

		$latitude = 40.06512;
		$longitude = -75.43530;

		error_reporting(0);

		try {
			
			$this->log->write("DEBUG: address -> " . (string)$street . " " . (string)$city . " " . (string)$region . " " . + (string)$postalcode);

			$query= urlencode((string)$street . " " . (string)$city . " " . (string)$region . " " . + (string)$postalcode);
	
			$api_uri = "https://api.mapbox.com/geocoding/v5/mapbox.places-permanent/" . $query . ".json?types=address&country=US&access_token=" . MAPBOX_API_KEY . "&types=address&limit=1";

			$this->logger->write("GIS URL: " . $api_uri);
	
			$curl = curl_init();
	
			$headers = array();
			$headers[] = 'User-Agent: PostmanRuntime/7.29.0';
	
			curl_setopt($curl, CURLOPT_URL, $api_uri);
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($curl, CURLOPT_TIMEOUT, 3);
	
			$response = curl_exec($curl);
			$error = curl_errno($curl);
	
			if ($error) {
				$this->logger->write('cURL error: ' . curl_errno($curl));
			}
	
			curl_close($curl);			
	
			if (!$error) {
				$output= json_decode($response);
				if (sizeof($output->features) > 0) {
					$longitude = $output->features[0]->center[0]; 
					$latitude = $output->features[0]->center[1];
				} else {
					$this->logger->write('GEO: NO FEAUTES');	
				}

				$this->logger->write('GEO: ' . $query . ' -> lon=' . $longitude . ', lat=' . $latitude);
			}
	
		} catch (Exception $ex) {
			$this->logger->writer('ERROR: Unable to get GIS location. Error message: ' . $ex->getMessage());
		} catch (Error $ex) {
			$this->logger->writer('ERROR: Unable to get GIS location. Error message: ' . $ex->getMessage());
		}

		$data = array();
		$data['lon'] = $longitude;
		$data['lat'] = $latitude;

		error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
		return $data;
	}

	public function recordGeoLocationForOrder($order_id, $order_data) {

		// Type: Shipping location (0)
		$type_id = 0;

		$this->db->query("DELETE FROM " . DB_PREFIX . "order_gis " . 
						"WHERE order_id = '" . (int)$order_id .
						 "' AND type_id = '" . (int)$type_id . 
						 "'");

		if (!array_key_exists('shipping_address_1', $order_data)) {
			$this->logger->write("No shipping address in order #" . $order_id);
			return -1;
		}

		$geo_data = $this->getGeoLocation(
			$order_data['shipping_address_1'],
			$order_data['shipping_city'],
			$order_data['shipping_zone'],
			$order_data['shipping_country'],		
			$order_data['shipping_postcode'],
		);
	
		if ( $geo_data['lat'] == 0 || $geo_data['lon'] == 0 ) {
			return -1;
		}

		$this->db->query("INSERT INTO " . DB_PREFIX . "order_gis " . 
						"SET order_id = '" . (int)$order_id .
						 "', type_id = '" . (int)$type_id . 
						 "', lat = '" . $geo_data['lat'] . 
						 "', lon = '" . $geo_data['lon'] . 
						 "'");

		return $this->db->getLastId();
	}


}