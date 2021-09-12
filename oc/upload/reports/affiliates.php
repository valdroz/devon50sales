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

define ("FILENAME", "affiliates"); //Export default filename

//SQL Query, customize if if you need any more (or less) fields
define ("SQL","
SELECT af.customer_id as 'Scout ID',
 cust.firstname as 'First Name',
 cust.lastname as 'Last Name',
 cust.email as 'Email',
 p.name AS 'Patrol name',
 af.tracking as 'Tracking Code',
 CASE af.status WHEN 1 THEN 'Approved' WHEN 0 THEN 'Not yet approved' END AS 'Status'
FROM 
	oc_customer_affiliate as af, 
    oc_customer as cust, 
    oc_customer_group_description as cust_g,
    ocdevon.oc_custom_field_value_description p 
WHERE 
	af.customer_id = cust.customer_id 
    and cust.customer_group_id = cust_g.customer_group_id 
    and cust.customer_group_id = 2
    and p.custom_field_id = 2 
    and p.custom_field_value_id = JSON_UNQUOTE(JSON_EXTRACT(cust.custom_field, '$.\"2\"' ))     
ORDER BY cust_g.name, cust.firstname, cust.lastname
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

