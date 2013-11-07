<?php
function getDominionCartPage() {
  global $jcartconfig;
  
  global $gs_base_url;
  global $dominion_jcart_path;
  global $SITEURL;
  
  global $dominion_jcart_cat_file;
  
  $webURL = $_SERVER["REQUEST_URI"];
  if (strpos($webURL,'?catid=') !== false) {
    $webAdd = '?';
  } else if (strpos($webURL,'&catid=') !== false) {
    $webAdd = '&';
  } else if ((strpos($webURL,'catid=') === false) && (strpos($webURL,'?') !== false))  {
    $webAdd = '&';
  } else {
    $webAdd = '?';  
  }
  
  $webURL = preg_replace("/&catid=.*/i","",$webURL);
  $webURL = preg_replace("/\?catid=.*/i","",$webURL);
  $backURL = $webURL;
  $backURL = preg_replace("/&dominion_ischeckout=1/i","",$backURL);
  $backURL = preg_replace("/\?dominion_ischeckout=1/i","",$backURL);
  
  
  $webURL = $webURL.$webAdd.'catid=';
  
  if (isset($_SESSION['jcart'])) {
    //error_log("jcart is SET");
    
  } else {
    //error_log("jcart is NOT SET");
  }
  //error_log(print_r($_SESSION,true));

  $cart =& $_SESSION['jcart']; if(!is_object($cart)) $cart = new jcart();
  $catid = isset($_GET['catid'])?$_GET['catid']:-1;
  //Load our settings
  $setting_file = 'dominion-cart-settings.xml';
  $xml = getDominionXML($dominion_jcart_path.$setting_file);
  $activeItem = $xml->xpath("//id");
  $currency = $activeItem[0]->currency;  
//  $webmoney_code = trim($activeItem[0]->webmoney_code);  
  //End Load our settings
  global $dominion_jcart_language_setting_file;
  
    if (is_file($dominion_jcart_path.$dominion_jcart_language_setting_file)) {
      $dominion_jcart_active_language = file_get_contents($dominion_jcart_path.$dominion_jcart_language_setting_file);
    }
    include getLanguageFile('dominion-jcart',$dominion_jcart_active_language);
  
  ob_start();
?>
         <div id="cartbox">
            
				<?php if (!isset($_GET['dominion_ischeckout'])) { $cart->display_cart($jcartconfig); } ?>
		 </div>
			
          <div style='padding-top:10px;padding-bottom:10px;'> 
            <?php 
              if (!isset($_GET['dominion_ischeckout'])) {
                $xml = getDominionXML($dominion_jcart_path.$dominion_jcart_cat_file);             
                $curItem = $xml->xpath("//id");
                if ($catid == -1) {
                  $atr = $curItem[0]->attributes();
                  $catid = $atr['group_id'];
                }
                $numScripts =  count($curItem);                
                echo $dominion_jcart_general['JCART_PAGE_CATEGORIES']." : <select onchange='window.location = \"".$webURL."\"+this.value'>";
                for ($x=0;$x<$numScripts;$x++){
                  $atr = $curItem[$x]->attributes();
                  $sID = $atr['group_id'];
                  $sName = stripslashes($curItem[$x]->group_name);
                  if ($sID == $catid ) {
                     echo "<option value='$sID' selected='selected'>$sName</option>";
                  } else {
                    echo "<option value='$sID'>$sName</option>";
                  }  
                }
                echo "</select> "; 
                unset($curItem);
                $curItem = $xml->xpath("//id[@group_id=\"$catid\"]");
                $atr = $curItem[0]->attributes();
                $gpF = $atr['group_product_file'];
                unset($curItem,$xml);
                $xmlProducts = getDominionXML($dominion_jcart_path.$gpF.".xml");
                $pItems = $xmlProducts->xpath("//id");
                $numItems = count($pItems);
                
                
            ?>
            </div>
<div id="shopbag">          

<?php
            for ($x=0;$x < $numItems;$x++) {
                $itemName = stripslashes($pItems[$x]->product_name);
                $itemPrice = stripslashes($pItems[$x]->price);
                $itemQty = stripslashes($pItems[$x]->product_default_qty);
                $itemImage = stripslashes($pItems[$x]->product_image);
                if ($itemImage <> 'no-image') {
                  $itemImage = strrev($itemImage);
                  $type = substr($itemImage,0,strpos($itemImage,'.'));
                  $type = strrev($type);
                  $itemImage = substr($itemImage,strpos($itemImage,'.'),strlen($itemImage));
                  $itemImage = strtolower(strrev($itemImage));
                  if (is_file(GSDATAPATH."thumbs/thumbsm.".$itemImage.strtoupper($type))) {
                    $itemImage = $SITEURL. "data/thumbs/thumbsm.".$itemImage.strtoupper($type); 
                  } else {
                    $itemImage =$SITEURL. "data/thumbs/thumbsm.".$itemImage.strtolower($type); 
                  }
                }  
                
                //$itemInfo = stripslashes($pItems[$x]->info);
                $atr = $pItems[$x]->attributes();
                $p_catid = $atr['group_id'];
                $p_id = $atr['product_id'];
                
?>            
				<form method="post" action="" class="jcart" >
					<p>
						<input type="hidden" name="dominion-cart-id" value="<?php echo "$p_id - $p_catid";?>" />
						<input type="hidden" name="dominion-cart-name" value="<?php echo $itemName; ?>" />
						<input type="hidden" name="dominion-cart-price" value="<?php echo $itemPrice; ?>" />
                      <table width="100%" >
                      <?php 
                        if ($itemImage <> 'no-image') {
                      ?>
                      <tr><td rowspan='3' valign='top' width='47px'><a href='<?php echo $backURL.$webAdd."pid=$p_id&catid=$p_catid";?>'><?php  echo "<img src='$itemImage' height='70%' width='100%'>";  ?></a></td><td><a href='<?php echo $backURL.$webAdd."pid=$p_id&catid=$p_catid";?>'><?php echo $itemName; ?></a></td></tr>
                     <?php
                        } else {
                      ?>        
                      <tr ><td colspan='2'><center><a href='<?php echo $backURL.$webAdd."pid=$p_id&catid=$p_catid";?>'><?php echo $itemName; ?></a></center></td></tr>
                     <?php
                       }
                     ?>                     
                      <tr><td><?php echo $dominion_jcart_general['JCART_PAGE_PRICE'];?> : <?php if ($currency == '$') { echo "$currency "." ".$itemPrice; } else { echo $currency." ".$itemPrice; }?></td></tr>
                      <tr><td><?php echo $dominion_jcart_general['JCART_PAGE_QTY'];?> : <input type="text" name="dominion-cart-qty" value="<?php echo $itemQty; ?>" size="3" /></td></tr>
                      <tr ><td colspan='2'><input type="submit" name="dominion-cart-button" value="add to cart" class="button" />
					
<?php 
/*
                        if ($activeItem[0]->webmoney_gw == 1) { 
                          if ($webmoney_code == '') {
                            echo "Enter WebMoney Code in Control Panel";
                          } else {
                            $itemNameURL = urlencode("$itemQty x $itemName");
                            
                            //if ($activeItem[0]->webmoney_testmode == 1) { 
//                              echo "<a style='font-size:0.8em' href='wmk:paylink?url=<$webURL>&purse=$webmoney_code&amount=$itemPrice&method=POST&desc=$itemNameURL&mode=test'>Add to WebMoney</a>";
                            //} else {
//                              echo "<a style='font-size:0.8em' href='wmk:paylink?url=<$webURL>&purse=$webmoney_code&amount=$itemPrice&method=POST&desc=$itemNameURL'>Add to WebMoney</a>";
  //                          }                            
                            echo "<a href='wmk:payto?Purse=$webmoney_code&Amount=$itemPrice&Desc=$itemNameURL&BringToFront=Y'>Add to WebMoney</a>";
                            
                          }
                        }   
                        */
                        ?>   
                        </td></tr>
                     </table>                        
					</p>
				</form>
                
<?php
           }
?>             
</div>	
				<div class="clear"></div>
                 <div style="font-size:0.7em;float:right;"><?php echo $dominion_jcart_general['JCART_PAGE_POWER_BY'];?> <a target="dominionit" href='http://www.dominion-it.co.za'>Dominion jCart</a></div>

<div style="margin-left:auto; margin-right:auto; width:200px;">                
           <p> 
				<a class="prev" href="#"><?php echo $dominion_jcart_general['JCART_PAGE_PREVIOUS'];?></a> 
				(<span id="count"></span> / <span id="total"></span>)
				<a class="next" href="#"><?php echo $dominion_jcart_general['JCART_PAGE_NEXT'];?></a> 
			</p>
</div>     
<script type="text/javascript" >
                $(function(){
 
					var wrap = $('#shopbag');
 
					// set up click events to trigger the pagination plugins' behaviour 
 
					$('.prev').click(function(){
						wrap.trigger('prev.evtpaginate');
						return false;
					});
 
					$('.next').click(function(){
						wrap.trigger('next.evtpaginate');
						return false;
					});
 
					// listen out for events triggered by the plugin to update the counter
 
					wrap.bind( 'initialized.evtpaginate', function(e, startnum, totalnum ){
						$('#count').text(startnum);
						$('#total').text(totalnum);
					}); 
 
					wrap.bind( 'finished.evtpaginate', function(e, num, isFirst, isLast ){ $('#count').text(num); } ); 	
 
					wrap.evtpaginate({perPage:6}); // call the plugin!	
 
				});
                </script>         
              
             

<?php } else { $cart->display_cart($jcartconfig); ?>

<p><a href="<?php echo $backURL;?>">&larr; <?php echo $dominion_jcart_general['JCART_PAGE_CONTINUE_SHOPPING'];?></a></p>
</div>	
				<div class="clear"></div>
                 <div style="font-size:0.7em;float:right;"><?php echo $dominion_jcart_general['JCART_PAGE_POWER_BY'];?> <a target="dominionit" href='http://www.dominion-it.co.za'>Dominion jCart</a></div>

<?php }  ?>                
		
<?php
  $thePage = ob_get_contents();
  ob_end_clean();
  return  $thePage;
}
?>