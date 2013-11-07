<?php
// JCART v1.1
// http://conceptlogic.com/jcart/

// THIS FILE IS CALLED WHEN ANY BUTTON ON THE CHECKOUT PAGE (PAYPAL CHECKOUT, UPDATE, OR EMPTY) IS CLICKED
// WE CAN ONLY DEFINE ONE FORM ACTION, SO THIS FILE ALLOWS US TO FORK THE FORM SUBMISSION DEPENDING ON WHICH BUTTON WAS CLICKED
// ALSO ALLOWS US TO VERIFY PRICES BEFORE SUBMITTING TO PAYPAL

function getDominionPaymentPage($path,$cat_file,$dcartcustomer,$cart){
// INITIALIZE JCART AFTER SESSION START

ob_start();
    // Here we will construct a new email message to be sent to the merchant
    //  upon completion of the checkout process. The example message below is
    //  intentionally simplistic, and meant to be modified to your preferences.
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
    

    $mailSent = mail($mailToSendTo, 'Website Order received with Dominion jCart', $message);
    if ($mailSent) {
          // EMPTY THE CART
          $cart->empty_cart();
          
          // Done! You can either output a purchase "reciept" here, or redirect the
          //  user to another page.
          echo "<h1>Order Sent!</h1><br/>Thank you. we will contact you shortly regarding your order. <br/>Thanks for your support.";
     }
    
  $thePage = ob_get_contents();
  ob_end_clean();
  return  $thePage;
} 
?>