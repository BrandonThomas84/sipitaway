<?php

function getDominionCustomerInfoPage(){
ob_start();
  $webURL = $_SERVER["REQUEST_URI"];
  if (strpos($webURL,'?final_gw=') !== false) {
    $webAdd = '?';
  } else if (strpos($webURL,'&final_gw=') !== false) {
    $webAdd = '&';
  } else if ((strpos($webURL,'final_gw=') === false) && (strpos($webURL,'?') !== false))  {
    $webAdd = '&';
  } else {
    $webAdd = '?';  
  }  
  $webURL = preg_replace("/&final_gw=.*/i","",$webURL);
  $webURL = preg_replace("/\?final_gw=.*/i","",$webURL);
  $gwToUse = '';
  if (isset($_POST['jcart_mail_checkout'])) {
      $gwToUse = 'jcart_mail_checkout';
  } else if (isset($_POST['jcart_paypal_checkout'])) {
      $gwToUse = 'jcart_paypal_checkout';
  }  else if (isset($_POST['jcart_webmoney_checkout'])) {
       $gwToUse = 'jcart_webmoney_checkout';
  }
  
?>
  <form action="<?php echo  $webURL.$webAdd;?>final_gw=2"  method="post" id="customer">
    <input name='customerinfo' type='hidden' value='12429'>
    <input name='<?php echo $gwToUse;?>' type='hidden' value='1'>
     
  <table>
  <tr><td>Name</td><td><input type='text' value='' id='name' name='c_name'></td></tr>
  <tr><td>e-Mail</td><td><input type='text' value='' id='email' name='email'></td></tr>
  <tr><td>Contact Number</td><td><input type='text' value='' id='contact' name='contact'></td></tr>
  <tr align='center'><td colspan='2'><input type='submit' value='Continue Checkout' id='continue' name='CheckSave'></td></tr>
  </table>
  </form>
<?php
  $thePage = ob_get_contents();
  ob_end_clean();
  return  $thePage;
} 
?>