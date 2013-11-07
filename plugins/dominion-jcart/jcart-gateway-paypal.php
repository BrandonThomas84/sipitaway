<?php

// JCART v1.1
// http://conceptlogic.com/jcart/

// THIS FILE IS CALLED WHEN ANY BUTTON ON THE CHECKOUT PAGE (PAYPAL CHECKOUT, UPDATE, OR EMPTY) IS CLICKED
// WE CAN ONLY DEFINE ONE FORM ACTION, SO THIS FILE ALLOWS US TO FORK THE FORM SUBMISSION DEPENDING ON WHICH BUTTON WAS CLICKED
// ALSO ALLOWS US TO VERIFY PRICES BEFORE SUBMITTING TO PAYPAL

function getDominionPaymentPage($path,$cat_file,$dcartcustomer,$cart){

  global $dominion_jcart_setting_file;
  $xml = getDominionXML($path.$dominion_jcart_setting_files);
  $activeItem = $xml->xpath("//id");
  $paypal_code = trim($activeItem[0]->paypal_code);
  unset($activeItem,$xml);
ob_start();
		// PAYPAL COUNT STARTS AT ONE INSTEAD OF ZERO
		$paypal_count = 1;
		$items_query_string = '';
		foreach ($cart->get_contents() as $item)
			{
			// BUILD THE QUERY STRING
			$items_query_string .= '&item_name_' . $paypal_count . '=' . $item['name'];
			$items_query_string .= '&amount_' . $paypal_count . '=' . $item['price'];
			$items_query_string .= '&quantity_' . $paypal_count . '=' . $item['qty'];

			// INCREMENT THE COUNTER
			++$paypal_count;
			}

		// EMPTY THE CART
		//$cart->empty_cart();

		if($paypal_code <> ''){
			// REDIRECT TO PAYPAL WITH MERCHANT ID AND CART CONTENTS
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

            // The following line uses PHP's built-in mail() function to send the email.
            //  Most servers/hosts support this method by default, while others may
            //  require it to be enabled, or configured differently. For more information
            //  about the mail() function see: http://php.net/manual/en/function.mail.php
            //
            // The function call returns a boolean value indicating whether the mail was
            //  successfully accepted by the server's mail daemon for delivery.
            $xml = getDominionXML(GSDATAOTHERPATH."user.xml");
            $mailToSendTo = stripslashes($xml->EMAIL);
            //error_log('email : '.$EMAIL);'
            unset($xml);
            

            $mailSent = mail($mailToSendTo, 'PayPal Order received with Dominion jCart', $message);
            if ($mailSent) {
                  // EMPTY THE CART
                  $cart->empty_cart();
                  
                  // Done! You can either output a purchase "reciept" here, or redirect the
                  //  user to another page.
                  header( 'Location: https://www.paypal.com/cgi-bin/webscr?cmd=_cart&upload=1&business=' . $paypal_code . $items_query_string);
             }            
			
			exit;
			}
		else
			// THE USER HAS NOT CONFIGURED A PAYPAL ID
			// DISPLAY THE PAYPAL URL WITH AN ERROR MESSAGE
			{
			echo 'PayPal integration requires a secure merchant ID. Please see the <a href="http://www.dominion-it.co.za/tutorials/dcart.php">installation instructions</a> for more info.<br /><br />';
			echo 'Below is the URL that would be sent to PayPal if a merchant ID was set in <strong>Admin -> Plugins -> Dominion jCart - Settings</strong>:<br /><br />';
			echo 'https://www.paypal.com/cgi-bin/webscr?cmd=_cart&upload=1&business=PAYPAL_ID' . $items_query_string;
			exit;
			}
		
  $thePage = ob_get_contents();
  ob_end_clean();
  return  $thePage;
    
}
?>