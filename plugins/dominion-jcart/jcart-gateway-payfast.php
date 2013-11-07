<?php

function getDominionPaymentPage($path,$cat_file,$dcartcustomer,$cart){

  global $dominion_jcart_setting_file;
  $xml = getDominionXML($path.$dominion_jcart_setting_file);
  $activeItem = $xml->xpath("//id");
  $webmoney_code = trim($activeItem[0]->webmoney_code); 
  unset($activeItem,$xml);
ob_start();


		if($webmoney_code <> ''){
            if (isset($dcartcustomer['customer_name'])) {
              $message = "Customer : ".$dcartcustomer['customer_name']."\n\n";
              $message .= "Customer e-Mal: ".$dcartcustomer['customer_email']."\n\n";
              $message .= "Customer Contact Number: ".$dcartcustomer['customer_contact']."\n\n";
            }  else {
              $message = 'Customer information needs to be passed in session to form if not using build in customer form. \n\n';
            }            
            $message .= "New Order:\n\n";

              foreach ($cart->get_contents() as $item) {
                 $message .= 'Item: ' . $item['name'];
                 $message .= "\nPrice: " . $item['price'];
                 $message .= "\nQuantity: " . $item['qty'];
                 $message .= "\nTotal: " . $item['subtotal'];
                 $message .= "\n\n";
              }

            $xml = getDominionXML(GSDATAOTHERPATH."user.xml");
            $mailToSendTo = stripslashes($xml->EMAIL);
            unset($xml);
            $mailSent = mail($mailToSendTo, 'WebMoney Order received with Dominion jCart', $message);
            if ($mailSent) {
               echo "<div>";
               echo "<table style='border:1px solid black;' width='80%'><tr><th>Item</th><th>Qty</th><th>Price</th></tr>";
                  $totaal = (float)0.0;
                  $itemNameURL = '';
                  foreach ($cart->get_contents() as $item) {
                     $totaal += (float)$item['subtotal'];
                     echo "<tr><td>".$item['name']."</td><td>".$item['qty']."</td><td>".$item['subtotal']."</td></tr>";
                     $itemNameURL .= $item['qty']." x ".$item['name']." | ";
		          }
                  $itemNameURL = urlencode($itemNameURL);
                  echo "<tr><th colspan='2' align='right'>Total</th><td>$totaal</td></tr>";
               echo "</table></div>";   
               echo "<div>Please make WebMoney by clicking this : <a href='wmk:payto?Purse=$webmoney_code&Amount=$totaal&Desc=$itemNameURL&BringToFront=Y'>Make WebMoney Payment</a></div>";
                  $cart->empty_cart();
                
             }            
 			 exit;
		} else	{
			echo 'WebMoney integration requires a merchant ID. Please see the <a href="http://www.dominion-it.co.za/tutorials/dcart.php">installation instructions</a> for more info.<br /><br />';
			echo 'A merchant ID can be set in <strong>Admin -> Plugins -> Dominion jCart - Settings</strong>:<br /><br />';
			exit;
		}
	

$thePage = ob_get_contents();
ob_end_clean();
return  $thePage;
}
?>