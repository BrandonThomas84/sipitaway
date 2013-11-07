<?php
function getDominionProductPage() {
   global $jcartconfig;
   global $dominion_jcart_setting_file;
   global $dominion_jcart_path;
   $cart =& $_SESSION['jcart']; if(!is_object($cart)) $cart = new jcart();
  
  global $dominion_jcart_cat_file;
  global $SITEURL;
  
  $webURL = $_SERVER["REQUEST_URI"];
  if (strpos($webURL,'?pid=') !== false) {
    $webAdd = '?';
  } else if (strpos($webURL,'&pid=') !== false) {
    $webAdd = '&';
  } else if ((strpos($webURL,'pid=') === false) && (strpos($webURL,'?') !== false))  {
    $webAdd = '&';
  } else {
    $webAdd = '?';  
  }
  
  $webURL = preg_replace("/&pid=.*/i","",$webURL);
  $webURL = preg_replace("/\?pid=.*/i","",$webURL);
  
  $webURL = $webURL.$webAdd.'catid='.$_GET['catid'];
  $catid = $_GET['catid'];
  $pid = $_GET['pid'];
  //Load our settings
  
  $xml = getDominionXML($dominion_jcart_path.$dominion_jcart_setting_file);
  $activeItem = $xml->xpath("//id");
  $currency = $activeItem[0]->currency;  
  //$webmoney_code = trim($activeItem[0]->webmoney_code);
   
  //End Load our settings
  ob_start();
           echo "<a href='$webURL'>Back to Product List</a><br/>";                
                $xml = getDominionXML($dominion_jcart_path.$dominion_jcart_cat_file);   
                $curItem = $xml->xpath("//id[@group_id=\"$catid\"]");
                $atr = $curItem[0]->attributes();
                $gpF = $atr['group_product_file'];
                unset($curItem,$xml);
                $xmlProducts = getDominionXML($dominion_jcart_path.$gpF.".xml");
                $pItems = $xmlProducts->xpath("//id[@product_id=\"$pid\"]");
                if (count($pItems) > 0) {
                  $itemName = stripslashes($pItems[0]->product_name);
                  $itemPrice = stripslashes($pItems[0]->price);
                  $itemQty = stripslashes($pItems[0]->product_default_qty);
                  $itemImage = stripslashes($pItems[0]->product_image); 
                  if ($itemImage <> "no-image") {
                    $itemImage = $SITEURL.'data/uploads/'.$itemImage;
                  }   
                  $itemInfo = stripslashes($pItems[0]->info);
                  $atr = $pItems[0]->attributes();
                  $p_catid = $atr['group_id'];
                  $p_id = $atr['product_id'];
                  echo "<div>";
                  
?>
<div style='clear:both;height:80px;'> 
 <div style='float:right'><?php $cart->display_cart_no_checkout($jcartconfig);?></div>      

<div style='float:left'>           
                <form method="post" action="" class="jcart" style="height:50px;width:180px;">
                
                    <fieldset>

						<input type="hidden" name="dominion-cart-id" value="<?php echo "$p_id - $p_catid";?>" />
						<input type="hidden" name="dominion-cart-name" value="<?php echo $itemName; ?>" />
						<input type="hidden" name="dominion-cart-price" value="<?php echo $itemPrice; ?>" />
                        <label>Price: <?php if ($currency == '$') { echo "\\$currency"." ".$itemPrice; } else { echo $currency." ".$itemPrice; }?></label><br/> 
                      
                        <label>Qty: <input type="text" name="dominion-cart-qty" value="<?php echo $itemQty; ?>" size="3" /></label>
						<input type="submit" name="dominion-cart-button" value="add to cart" class="button" />
                        <?php 
/*  
  if ($activeItem[0]->webmoney_gw == 1) { 
                          if ($webmoney_code == '') {
                            echo "Enter WebMoney Code in Control Panel";
                          } else {
                            $itemNameURL = urlencode("$itemQty x $itemName");
                            //if ($activeItem[0]->webmoney_testmode == 1) { 
//                              echo "<a href='wmk:paylink?url=<$webURL>&purse=$webmoney_code&amount=$itemPrice&method=POST&desc=$itemNameURL&mode=test'>Add to WebMoney</a>";
                            //} else {
                              //echo "<a href='wmk:paylink?url=<$webURL>&purse=$webmoney_code&amount=$itemPrice&method=POST&desc=$itemNameURL'>Add to WebMoney</a>";
                              echo "<a href='wmk:payto?Purse=$webmoney_code&Amount=$itemPrice&Desc=$itemNameURL&BringToFront=Y'>Add to WebMoney</a>";
                              
                            //}                            
                          }
                        }
*/                        
                        ?>
                        
                    </fieldset>
				</form>
  </div>                
</div>
<div>
<?php                
                 if ($currency == '$') { $prys =  "\\$currency"." ".$itemPrice; } else { $prys = $currency." ".$itemPrice; };
                  echo "<div><h1>$itemName</h1>  - Price : $prys</div>";
                  
                  
                  if ($itemImage <> 'no-image') {
                   
                    echo "<div><img src='$itemImage' width='80%'><p>$itemInfo</p></div>";
                  }   else {
                   echo "<div><p>$itemInfo</p></div>";
                  }
                   

                  echo "</div>";
                }  
                echo "<a href='$webURL'>Back to Product List</a><br/>";                

?>
<div style="font-size:0.7em;float:right;">Powered by <a target="dominionit" href='http://www.dominion-it.co.za'>Dominion jCart</a></div>
  </div>
<?php
  $thePage = ob_get_contents();
  ob_end_clean();
  unset($xml);
  return  $thePage;
}
?> 