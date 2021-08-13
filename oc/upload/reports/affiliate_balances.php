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

define ("FILENAME", "transactions"); //Export default filename

//SQL Query, customize if if you need any more (or less) fields
define ("SQL","
(SELECT 
	cust.customer_id as ID,
	concat(cust.firstname, ' ', cust.lastname) as 'Scout Full Name',
	gr.name as 'Group',
	cust.email as 'Email',
	prod.name as Product,
	sum(prod.total) as 'Total'
 FROM 
	oc_customer AS cust, 
 	oc_customer_affiliate as af,
	oc_customer_transaction AS tr, 
	oc_customer_group_description AS gr,
	oc_order_product as prod,
	oc_order
WHERE 
	tr.customer_id = cust.customer_id AND 
	cust.customer_group_id = gr.customer_group_id AND 
    af.customer_id = cust.customer_id AND
    prod.order_id = tr.order_id AND
    oc_order.order_id = tr.order_id

GROUP BY
	cust.customer_id,
    concat(cust.firstname, ' ', cust.lastname),
	gr.name,     
	cust.email,
    prod.name
)    
UNION 

( SELECT 
	cust.customer_id,
	concat(cust.firstname, ' ', cust.lastname) as 'Scout Full Name',
	gr.name as 'Group',
	cust.email as 'Email',
	' _Total',
	sum(prod.total) as 'Total'
 FROM 
	oc_customer AS cust, 
 	oc_customer_affiliate as af,
	oc_customer_transaction AS tr, 
	oc_customer_group_description AS gr,
	oc_order_product as prod,
	oc_order
WHERE 
	tr.customer_id = cust.customer_id AND 
	cust.customer_group_id = gr.customer_group_id AND 
    af.customer_id = cust.customer_id AND
    prod.order_id = tr.order_id AND
    oc_order.order_id = tr.order_id

GROUP BY
	cust.customer_id,
    concat(cust.firstname, ' ', cust.lastname),
	gr.name,     
	cust.email
) ORDER BY ID,Product desc

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
                  $value = ''.$value . '' . ", ";
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

