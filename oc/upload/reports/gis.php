<?php
error_reporting(E_ERROR);

// Configuration
if (is_file('../config.php')) {
	require_once('../config.php');
}

// Install
if (!defined('DIR_APPLICATION')) {
	header('Location: ../install/index.php');
	exit;
}

$orders_year = isset($_GET["year"]) ? $_GET["year"] : date("Y");

define ("FILENAME", "gis" . $orders_year ); //Export default filename

//SQL Query, customize if if you need any more (or less) fields
define ("SQL","
SELECT Order_ID, Scout_ID, 
	GROUP_CONCAT(Products SEPARATOR ', ') AS 'Products',
    First_Name, Last_Name, Address, City, State, Zip
FROM (
SELECT 
    oc_order.order_id as 'Order_ID',
    cust.customer_id as 'Scout_ID', 
    oc_order.date_added as 'Order_Date', 
    CONCAT( prod.name, ' #', prod.quantity) AS 'Products', 
    oc_order.shipping_firstname as 'First_Name',
    oc_order.shipping_lastname as 'Last_Name',
    oc_order.shipping_address_1 as 'Address',
    oc_order.shipping_city as 'City',
    oc_order.shipping_zone as 'State',
    oc_order.shipping_postcode as 'Zip'
 FROM ocdevon.oc_customer AS cust, 
    ocdevon.oc_customer_affiliate as af, 
    ocdevon.oc_customer_transaction AS tr,
    ocdevon.oc_order_product as prod, 
    ocdevon.oc_order
 WHERE tr.customer_id = cust.customer_id AND
    af.customer_id = cust.customer_id AND
    prod.order_id = tr.order_id AND
    oc_order.order_id = tr.order_id AND
    year(oc_order.date_added) = " . $orders_year . " AND
    cust.customer_group_id = 2 AND
    prod.product_id in (50,51) 
) AS sales 
GROUP BY Order_ID, Scout_ID;
");

$password = isset($_GET["pw"]) ? $_GET["pw"] : $_SERVER['HTTP_PW'];

if ($password == GIS_PASSWORD) {

        //Connect to the database and fetch the data
        $link = mysqli_connect(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE) or die("DB: Couldn't make connection. Please check the database configurations.");
        $setSql = SQL;
        $setRec = mysqli_query($link, $setSql);

        //Fetch the column names
        $columns = mysqli_fetch_fields($setRec);
        foreach($columns as $column){
                $setMainHeader .= $column->name.", ";
        }

        while($rec = mysqli_fetch_row($setRec))  {
          $rowLine = '';
          foreach ($rec as $value) {
                if(!isset($value) || $value == "") {
                  $value = ",";
                } else {
                  //Escape all the special characters
                  $value = strip_tags(str_replace('"', '""', $value));
                  $value = '"' . $value . '"' . ",";
                }
                $rowLine .= $value;
          }
          $setData .= trim($rowLine)."\n";
        }

        $setData = str_replace("\r", "", $setData);

        if ($setData == "") {
          $setData = "No matching records found";
        }

        //Download headers
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=".FILENAME."-".date("Y_m_d-Hi_s").".csv");
        header("Pragma: no-cache");
        header("Expires: 0");

        //Print the table rows as an Excel row with the column name as a header
        echo ucwords($setMainHeader)."\n".$setData."\n";
}
else {
  echo "Nope";
}
?>

