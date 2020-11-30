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
SELECT
 af.customer_id as 'Scout ID',
 cust.firstname as 'First Name',
 cust.lastname as 'Last Name',
 gr.name as 'Group',
 cust.email as 'Email',
 tr.tr_count as 'Transaction Count',
 IF ( ( tr.amount - 100 ) < 0, 0, tr.amount - 100 ) as 'Commission' ,
 af.commission as 'Commission %'
FROM
 oc_customer AS cust,
 oc_customer_affiliate as af,
 (SELECT tr1.customer_id,  count(*) as tr_count, sum(tr1.amount) as amount FROM oc_customer_transaction AS tr1 GROUP BY tr1.customer_id) AS tr,
 oc_customer_group_description AS gr
WHERE tr.customer_id = cust.customer_id AND cust.customer_group_id = gr.customer_group_id AND af.customer_id = cust.customer_id

");


if($_GET["pw"]==PASSWORD){

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
        $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
        echo "Invalid password! Remember to write the URL properly and include your password:<BR>".(isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]".$uri_parts[0]."?pw=your_password";
}
?>

