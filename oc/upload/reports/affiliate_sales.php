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

define ("FILENAME", "scout_sales"); //Export default filename

//SQL Query, customize if if you need any more (or less) fields
define ("SQL","
SELECT 
 cust.customer_id as 'Scout ID',
 cust.firstname as 'Scout First Name',
 cust.lastname as 'Scout Last Name',
 gr.name as 'Group',
 cust.email as 'Email',
 oc_order.date_added as 'Order Date',
 oc_order.order_id as 'Order ID',
 prod.name as 'Product Name',
 prod.price as 'Product Price',
 prod.quantity as 'Quantity',
 prod.total as 'Total',
 oc_order.shipping_firstname as 'Shipping: First Name',
 oc_order.shipping_lastname as 'Last Name',
 oc_order.shipping_company as 'Company Name',
 oc_order.shipping_address_1 as 'Address Line 1',
 oc_order.shipping_address_2 as 'Address Line 2',
 oc_order.shipping_city as 'City',
 oc_order.shipping_postcode as 'Postal Code',
 oc_order.shipping_country as 'Country'
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
    oc_order.order_id = tr.order_id
ORDER BY cust.customer_id asc, oc_order.date_added desc
 ");

$password = isset($_GET["pw"]) ? $_GET["pw"] : $_SERVER['HTTP_PW'];

if ($password == PASSWORD) {

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
                  $value = ", ";
                } else {
                  //Escape all the special characters
                  $value = strip_tags(str_replace('"', '""', $value));
                  $value = '"' . $value . '"' . ", ";
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
        //header("Content-type: application/octet-stream");
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=".FILENAME."-".date("Y_m_d-Hi_s").".csv");
        header("Pragma: no-cache");
        header("Expires: 0");

        //Print the table rows as an Excel row with the column name as a header
        echo ucwords($setMainHeader)."\n".$setData."\n";
}
//Message to display in case of wrong access password
else {
  echo "Nope";
}
?>

