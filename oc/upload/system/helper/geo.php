<?php


function get_geo_location($street, $city, $region, $country) {

    $latitude = 0;
    $longitude = 0;

    try {

        $gis_street = urlencode($street);
        $gis_city = urlencode($city);
        $gis_region = urlencode($region);
        $gis_country = urlencode($country);

        $api_uri = "https://nominatim.openstreetmap.org/search?street=" . $gis_street . "&city=" . $gis_city . "&state=" . $gis_region . "&country=" . $gis_country . "&format=json";

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
            echo 'cURL error: ' . curl_errno($curl);
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
        echo 'Unable to get GIS location. Error message: ' . $ex->getMessage() . '\n';
    }

    $data['lon'] = $longitude;
    $data['lat'] = $latitude;

	return $data;
}