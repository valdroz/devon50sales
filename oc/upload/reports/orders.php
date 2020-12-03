<?php
error_reporting(E_ERROR);
/* =============================================================
/   * OpenCart 2.x/3.x Order+ Order Options Excel Export Tool version 5

        * Developed by Daniel Brooke Peig (daniel@danbp.org)
        *
        * http://www.danbp.org
        *
        *  Copyright (C) 2017  Daniel Brooke Peig
        *
        * This software is distributed under the MIT License.
        * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), * to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
        *
        * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
        *
        * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
        *
        *
/* =============================================================*/
/*
*
*       INTRODUCTION
*
*       This script should work in all OpenCart 2.x installations because it extracts the data directly from the OpenCart Database and does not rely on any OpenCart directory files.
*       The script just dumps readable order data in an Excel file, you may later use Excel functions to auto-format the data according to your needs.
*
*       INSTRUCTIONS
*
*       1. Modify the script configuration below to your database access information and set a PASSWORD. The password will protect the script from outside access and should be different than the DB password.
*       2. Place the modified script in any accessible (but safe) folder in your server, for example, the OpenCart /admin directory.
*       3. Access the script by using the following URL. Remember to replace YOURPASSWORD with the password you set in the configuration.
*               URL: https://www.yourserver.com/yourfolder/admin/order_export.php?pw=YOURPASSWORD
*       4. If everything is OK you will be prompted to download the Excel file.
*       5. Each line of the Excel file represents one order item or item option. You may group options for the same item by using the column product_item.
*   6. If the characters appear invalid try using the "UTF-8 TXT" version of the script.
*
*/


// Configuration
if (is_file('../config.php')) {
	require_once('../config.php');
}

// Install
if (!defined('DIR_APPLICATION')) {
	header('Location: ../install/index.php');
	exit;
}

define ("FILENAME", "orders"); //Export default filename

//SQL Query, customize if if you need any more (or less) fields
define ("SQL","
SELECT
        oc_order.order_id as 'Order ID',
        oc_order.invoice_no as 'Invoice #',
        oc_order.affiliate_id as 'Scout ID',
        concat(oc_order.firstname,' ',oc_order.lastname) as 'Full Name',
        oc_order.email as 'Email',
        oc_order.telephone as 'Phone',
        DATE_FORMAT(oc_order.date_added,'%m/%d/%Y') as 'Order Date',
        oc_order.total AS 'Order Total',
        oc_order_status.name AS 'Order Status',
        oc_order_product.name AS 'Product Name',
        oc_order_product.model AS 'Product Model',
        oc_order_product.quantity AS 'Quantity',
        oc_order_product.total AS 'Product Total',
        oc_order_product.tax AS 'Tax',
        oc_order.payment_method as 'Payment Method',
        oc_order.shipping_method as 'Shipping Method',
         oc_order.shipping_firstname as 'Shipping: First Name',
         oc_order.shipping_lastname as 'Last Name',
         oc_order.shipping_company as 'Company Name',
         oc_order.shipping_address_1 as 'Address Line 1',
         oc_order.shipping_address_2 as 'Address Line 2',
         oc_order.shipping_city as 'City',
         oc_order.shipping_postcode as 'Postal Code',
         oc_order.shipping_country as 'Country',
        comment as 'Comments'
FROM ocdevon.oc_order
LEFT JOIN oc_order_product ON oc_order.order_id = oc_order_product.order_id
LEFT JOIN oc_order_status ON oc_order.order_status_id = oc_order_status.order_status_id
WHERE oc_order_status.order_status_id > 0
ORDER BY oc_order.order_id, oc_order_product.product_id ASC

");


if($_GET["pw"]==PASSWORD){

        //Connect to the database and fetch the data
        $link = mysqli_connect(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE) or die("DB: Couldn't make connection. Please check the database configurations.");
        $setSql = SQL;
        $setRec = mysqli_query($link, $setSql);

        //Fetch the column names
        $columns = mysqli_fetch_fields($setRec);
        foreach($columns as $column){
                $setMainHeader .= $column->name.",";
        }

        while($rec = mysqli_fetch_row($setRec))  {
          $rowLine = '';
          foreach($rec as $value)       {
                if(!isset($value) || $value == "")  {
                  $value = ",";
                }   else  {
        //Escape all the special characters
                  $value = strip_tags(str_replace('"', '""', $value));
                  $value = ''.$value . '' . ",";
                }
                $rowLine .= $value;
          }
          $setData .= trim($rowLine)."\n";
        }
          $setData = str_replace("\r", "", $setData);
          if ($setData == "") {
            $setData = "\nNo matching records found\n";
          }
  
          //Download headers
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