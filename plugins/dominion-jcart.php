<?php
/*
Plugin Name: Dominion jCart
Description: Modified jCart system for getSimple
Version: 0.5e
Author: Dominion IT
Author URI: http://www.dominion-it.co.za/
*/

require_once("dominion-it-shared/dominion-common.php");
$dominion_jcart_cat_file = 'dominion-chart-cat.xml';
$dominion_jcart_path = GSPLUGINPATH.'dominion-jcart/data/';
$dominion_jcart_setting_file = 'dominion-cart-settings.xml';
$dominion_jcart_language_setting_file = 'dominion-cart-language.cfg';
$dominion_jcart_transaksies_leer = 'transactions.xml';


if (strpos($_SERVER ['REQUEST_URI'],'/admin/') === false) {
  //if in admin panel then this is not needed
  
  //check if plugin is enabled
  if (isPluginEnabled('dominion-jcart')) {
    include GSPLUGINPATH .'dominion-jcart/jcart.php';
  }  
 }  

# get correct id for plugin
$thisfile=basename(__FILE__, ".php");

# register plugin
register_plugin(
	$thisfile, 	# ID of plugin, should be filename minus php
	'Dominion jCart', 	# Title of plugin
	'0.5e', 		# Version of plugin
	'Johannes Pretorius',	# Author of plugin
	'http://www.dominion-it.co.za/', 	# Author URL
	'Modified jCart system for getSimple - THANK YOU for Oleg from Kirov, Russia for all his help and testing.', 	# Plugin Description
	'plugins', 	# Page type of plugin
	'show_dominion_jcart_config'  	# Function that displays content
);

# activate filter
add_filter('content','content_dcart_show'); 
add_action('plugins-sidebar','createSideMenu',array($thisfile,'Dominion jCart'));
add_action('theme-header','set_dcart_headers');
add_action('index-pretemplate','dcart_start_template');
add_action('index-posttemplate','dcart_end_template');

function dcart_start_template(){
  if (isPluginEnabled('dominion-jcart')) {
      //error_log('DOMINION _ JCART = BEFORE SESSION START');
     //session_start(); //debug removed @
     if(!isset($_SESSION)){ 
       session_start();
     }
     //error_log('DOMINION _ JCART = AFTER SESSION START');
     ob_start();
     //error_log('DOMINION _ JCART = STREAM CACHE STARTED');
  }
}
function dcart_end_template(){
  if (isPluginEnabled('dominion-jcart')) {
      ob_flush();
      //error_log('DOMINION _ JCART = STREAM CACHE flushed');
  }
}


/*
  Filter Content for dcart markers (%cart_id%)
    the cart of that id will be inserted in the markers section of the conent
*/
function content_dcart_show($contents){
    //first check if the plugin is enabled
     if (!isPluginEnabled('dominion-jcart')) {
       return $contents;
     }
     
   // $bgColor = implode("",@file(GSDATAOTHERPATH. '/mp3playerextended.cfg'));
    $tmpContent = $contents;
	preg_match_all('/\(%(.*)dcart(.*):(.*)%\)/i',$tmpContent,$tmpArr,PREG_PATTERN_ORDER);
    
    $AlltoReplace = $tmpArr[count($tmpArr)-1];
    $totalToReplace = count($AlltoReplace);
    for ($x = 0;$x < $totalToReplace;$x++) {
       $targetCart= str_replace('&nbsp;',' ',$AlltoReplace[$x]);
       $targetCart = trim($targetCart);
      $adTeks = buildCart($targetCart);
      $tmpContent = preg_replace("/\(%(.*)dcart(.*):(.*)$targetCart(.*)%\)/i",$adTeks,$tmpContent);
    }
    
  return $tmpContent;
}

/*
  * Show the config for the player..
  *
*/
function show_dominion_jcart_config(){
    global $dominion_jcart_path;
    
    global $dominion_jcart_cat_file;
    global $SITEURL;
    
    $ActiveProductID = -1; //will select first one in list

    $catid = isset($_GET['catid'])?$_GET['catid']:-1;
    
    //enabdle -- disable
    if (isset($_POST['dominion-enable'])) {
      EnablePlugin('dominion-jcart');
    } else  if (isset($_POST['dominion-disable'])) {
      DisablePlugin('dominion-jcart');
    }
    
    //delete category and products
    if (isset($_GET['delcatid'])) {
       $delCat = $_GET['delcatid'];
       deleteCategoryandProducts($delCat);
      $catid = -1;
      
    }

    //add New category
    if (isset($_GET['gid'])) {
      $catid = createDefaultCatAndProducts("example-cat".rand(1,234234));
      
    }
    //is there aproduct to edit
    if (isset($_GET['productedit']) && isset($_GET['catid'])) {
       $ActiveProductID = $_GET['productedit'];
    }
    
    
    
    if(isset($_POST['stoorcat']))  {
     //save category
       $catid = $_POST['catid'];
       $xml = getDominionXML($dominion_jcart_path.$dominion_jcart_cat_file);
       $activeItem = $xml->xpath("//id[@group_id=\"$catid\"]");
       $activeItem[0]->group_name->updateCData(stripslashes($_POST['group_name']));
       $activeItem[0]->info->updateCData(stripslashes($_POST['info']));
       $xml->XMLSave($dominion_jcart_path.$dominion_jcart_cat_file);     
    } else  if(isset($_POST['stoorprod']) ) {
     //save product
       $catid = $_POST['catid'];
       $ActiveProductID = $_POST['p_id'];
       $xml = getDominionXML($dominion_jcart_path.$dominion_jcart_cat_file);
       $activeItem = $xml->xpath("//id[@group_id=\"$catid\"]");
       $prodXml = getProductXMLObject($activeItem,$ActiveProductID,$productFile);
       $prodItemxmlObj = $prodXml->xpath("//id[@product_id=\"$ActiveProductID\"]");
       $prodItemxmlObj[0]->info->updateCData(stripslashes($_POST['info']));
       $prodItemxmlObj[0]->price->updateCData(stripslashes($_POST['price']));
       $prodItemxmlObj[0]->product_name->updateCData(stripslashes($_POST['product_name']));       
       $prodItemxmlObj[0]->product_image->updateCData(stripslashes($_POST['product_image']));       
       $prodItemxmlObj[0]->product_default_qty->updateCData(stripslashes($_POST['product_default_qty']));
       $prodXml->XMLSave($dominion_jcart_path.$productFile.".xml");
       dominion_cart_saveIndexes($prodXml,$productFile);
       unset($prodItemxmlObj,$prodXml);
    } else {   
       //load the files..
        if (is_file($dominion_jcart_path.$dominion_jcart_cat_file)) {
            //simplexml_load_file        
            $xml = getDominionXML($dominion_jcart_path.$dominion_jcart_cat_file);
        } else {
           $catid = createDefaultCatAndProducts('example-category');
           $xml = getDominionXML($dominion_jcart_path.$dominion_jcart_cat_file);
        }
    }    
    
    if ($catid <= 0) {
      $activeItem = $xml->xpath("//id");
      $tmpAttr = $activeItem[0]->attributes();
      $catid = $tmpAttr['group_id'];
    }
   
    $activeItem = $xml->xpath("//id[@group_id=\"$catid\"]");

    //add new product
    if (isset($_GET['addnewproduct']) && isset($_GET['catid'])) {
       $ActiveProductID = addNewProduct($activeItem);
    }
    
    //delete entry
    if (isset($_GET['productdelete']) && isset($_GET['catid'])) {
       $DeleteProductID = $_GET['productdelete'];
       deleteProduct($activeItem,$DeleteProductID);
    }
   
    
    $activeProductItem = getProductForEditing($activeItem,$ActiveProductID);
      $prodAttr = $activeProductItem->attributes();
      $productID = $prodAttr['product_id'];
      $p_name = stripslashes($activeProductItem->product_name);
      $pinfo = stripslashes($activeProductItem->info);
      $default_qty = stripslashes($activeProductItem->product_default_qty);
      $price = stripslashes($activeProductItem->price);
      $product_image = stripslashes($activeProductItem->product_image);    
   
    $curItem = $xml->xpath("//id");
    $numScripts =  count($curItem);    
    $adminID = $_GET['id'];
    $dominion_jcart_active_language = 'en_US';
    global $dominion_jcart_language_setting_file;
    if(isset($_POST['stoor_settings']) ) {
       $taal = $_POST['taal'];
       file_put_contents($dominion_jcart_path.$dominion_jcart_language_setting_file,$taal);      
    }
    if (is_file($dominion_jcart_path.$dominion_jcart_language_setting_file)) {
      $dominion_jcart_active_language = file_get_contents($dominion_jcart_path.$dominion_jcart_language_setting_file);
    }
    include getLanguageFile('dominion-jcart',$dominion_jcart_active_language);
?>
<div><p><a id="products" href="#" ><?php echo $dominion_jcart_general['JCART_SYSTEM_OPTION_PRODUCT']; ?></a> | <a id="settings" href="#settings"><?php echo $dominion_jcart_general['JCART_SYSTEM_OPTION_SETTINGS']; ?></a></p></div>
<hr/>
<div id='dcart_products'>
	

<form action="<?php	echo $SITEURL."admin/load.php?id=$adminID";?>"  method="post" id="management">
<?php
  if (isPluginEnabled('dominion-jcart')) {
?>
  <p><b><?php echo $dominion_jcart_general['JCART_SYSTEM_OPTION_DISABLE']; ?>     </b><input type='checkbox' name='dominion-disable' value = '1' onclick='submit();'> </p>
<?php    
    } else {
?>
   <p><b><?php echo $dominion_jcart_general['JCART_SYSTEM_OPTION_ENABLE']; ?>     </b><input type='checkbox' name='dominion-enable' value = '1' onclick='submit();'> </p>
<?php
 }
?>   
</form>
<form action="<?php	echo $SITEURL."admin/load.php?id=$adminID";?>"  method="post" id="management">
   <input type='hidden' name='catid' value='<?php echo $catid;?>'>
  <p><?php echo $dominion_jcart_general['JCART_HEADER']; ?> <a href='http://www.dominion-it.co.za'>Dominion IT</a> - V 0.5e</p>
 <?php
echo "<p>Current Categories : <select onchange='window.location = \"".$SITEURL."admin/load.php?id=$adminID&catid=\"+this.value'>";
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
    echo "</select> <a href='".$SITEURL."admin/load.php?id=$adminID&gid=NewCategory'>".$dominion_jcart_general['JCART_GROUP_ADD']."</a></p>"; 
 ?>
    <p> <?php echo $dominion_jcart_general['JCART_GROUP_NAME']; ?> : <input type='text' name='group_name' value='<?php echo  stripslashes($activeItem[0]->group_name); ?>'><?php if ($numScripts > 1) { ?> <a href='<?php echo $SITEURL; ?>admin/load.php?id=<?php echo $adminID;?>&delcatid=<?php echo $catid;?>' onclick="return confirm('All Products linked to category will also be deleted. Are you sure ?');"><?php echo $dominion_jcart_general['JCART_GROUP_DELETE']; ?></a> <?php } ?><br/>
    <?php echo $dominion_jcart_general['JCART_GROUP_DESCRIPTION']; ?> : <input name='info'  value='<?php echo  stripslashes($activeItem[0]->info); ?>' type='text' size='50'>  <input type='submit' name='stoorcat' value='<?php echo $dominion_jcart_general['JCART_GROUP_SAVE']; ?>'></p>
    
</form>
<hr/>
<?php echo "<a href='".$SITEURL."admin/load.php?id=$adminID&addnewproduct=1&catid=$catid'>".$dominion_jcart_general['JCART_PRODUCT_ADD_NEW']."</a>"; ?>;
<br/>

<form action="<?php	echo $SITEURL."admin/load.php?id=$adminID";?>"  method="post" id="management">
<input type='hidden' name='catid' value='<?php echo $catid;?>'>
<input type='hidden' name='p_id' value='<?php echo $productID;?>'>

<table>
<tr><td><?php echo $dominion_jcart_general['JCART_PRODUCT_NAME']; ?> : </td><td><input type='text' name='product_name' value='<?php echo $p_name;?>'></td></tr>
<tr><td><?php echo $dominion_jcart_general['JCART_PRODUCT_PRICE']; ?> : </td><td><input id='price' type='text' name='price' value='<?php echo $price;?>'></td></tr>
<tr><td><?php echo $dominion_jcart_general['JCART_PRODUCT_QTY']; ?> : </td><td><input id='qty' size='8' type='text' name='product_default_qty' value='<?php echo $default_qty;?>'></td></tr>
<tr><td><?php echo $dominion_jcart_general['JCART_PRODUCT_IMAGE']; ?> : </td><td><select name='product_image'><?php getImagesCombobox($product_image);?>'</select></td></tr>
<tr><td><?php echo $dominion_jcart_general['JCART_PRODUCT_INFO']; ?> : </td><td><textarea id='info' name='info'><?php echo $pinfo;?></textarea></td></tr>
</table>
<input type='submit' name='stoorprod' value='<?php echo $dominion_jcart_general['JCART_PRODUCT_SAVE']; ?>'>

</form>
<?php
  buildProductList($activeItem,$dominion_jcart_active_language );
?>
<hr/>
</div>
<?php
//SETTINGS SAVE START -  TODO.. put in own functions to clean code    
  //save code for the settings tab.
  global $dominion_jcart_setting_file; 
  $dominion_jcart_setting_file = 'dominion-cart-settings.xml';
  unset($xml,$activeItem);
  $settingspath = $dominion_jcart_path;
  if(isset($_POST['stoor_settings']) ) {
     //save category
       $currency = $_POST['currency'];
       $paypal_code = $_POST['paypal_code'];
       $border_color = $_POST['border_color'];
       $text_color = $_POST['text_color'];
        
       $paypal_gw = isset($_POST['paypal_gw'])?$_POST['paypal_gw']:'0';
       $mail_gw = isset($_POST['mail_gw'])?$_POST['mail_gw']:'0';
       $include_jquery = isset($_POST['include_jquery'])?$_POST['include_jquery']:'0';
       $client_required = isset($_POST['client_required'])?$_POST['client_required']:0;
       $webmoney_code = $_POST['webmoney_code'];
       $webmoney_gw = isset($_POST['webmoney_gw'])?$_POST['webmoney_gw']:'0';
       //$webmoney_testmode = isset($_POST['webmoney_testmode'])?$_POST['webmoney_testmode']:'0';
       
       $xml = getDominionXML($settingspath.$dominion_jcart_setting_file);
       $activeItem = $xml->xpath("//id");
       $activeItem[0]->currency->updateCData(stripslashes($currency));
       if (empty($activeItem[0]->paypal_code)) {
         $activeItem[0]->paypal_code->addCData(@$paypal_code);
       } else {
         $activeItem[0]->paypal_code->updateCData(stripslashes($paypal_code));
       }  
       if (empty($activeItem[0]->border_color)) {
         $activeItem[0]->border_color->addCData(@$border_color);
       } else {
         $activeItem[0]->border_color->updateCData(stripslashes($border_color));
       } 
       if (empty($activeItem[0]->text_color)) {
         $activeItem[0]->text_color->addCData(@$text_color);
       } else {
         $activeItem[0]->text_color->updateCData(stripslashes($text_color));
       }       
       $activeItem[0]->paypal_gw->updateCData(stripslashes($paypal_gw));
       if (empty($activeItem[0]->webmoney_code)) {
         $activeItem[0]->webmoney_code->addCData(@$webmoney_code);
       } else {
         $activeItem[0]->webmoney_code->updateCData(stripslashes($webmoney_code));
       }  
       $activeItem[0]->webmoney_gw->updateCData(stripslashes($webmoney_gw));
       //$activeItem[0]->webmoney_testmode->updateCData(stripslashes($webmoney_testmode));
       
       
       $activeItem[0]->mail_gw->updateCData(stripslashes($mail_gw));
       $activeItem[0]->include_jquery->updateCData(stripslashes($include_jquery));
       
       $activeItem[0]->client_required->updateCData(stripslashes($client_required ));
       $xml->XMLSave($settingspath.$dominion_jcart_setting_file);     
       
    } else {
      if (is_file($settingspath.$dominion_jcart_setting_file)) {
          $xml = getDominionXML($settingspath.$dominion_jcart_setting_file);
          
          //Add new config groups if not there yet
          $haveSet = FALSE;
          $activeItem = $xml->xpath("//id[webmoney_code=*]");
          if (count($activeItem) <= 0){
              $haveSet = TRUE;
              $activeItem = $xml->xpath("//id"); 
              $blok = '';
    		  $setting = $activeItem[0]->addChild('webmoney_code');
    		  $setting->addCData(@$blok);
              $blok = '0';
    		  $setting = $activeItem[0]->addChild('webmoney_gw');
    		  $setting->addCData(@$blok);          
             // $blok = '0';
    		 // $setting = $activeItem[0]->addChild('webmoney_testmode');
    		 // $setting->addCData(@$blok);                    
              $xml->XMLSave($settingspath.$dominion_jcart_setting_file);     
          } 
          $activeItem = $xml->xpath("//id[border_color=*]");
          if (count($activeItem) <= 0){
              $haveSet = TRUE;
              $activeItem = $xml->xpath("//id"); 
              $blok = '#66cc66';
    		  $setting = $activeItem[0]->addChild('border_color');
    		  $setting->addCData(@$blok);
              $blok = '#000066';
    		  $setting = $activeItem[0]->addChild('text_color');
    		  $setting->addCData(@$blok);              
              
              $xml->XMLSave($settingspath.$dominion_jcart_setting_file);     
          } 
          $activeItem = $xml->xpath("//id[include_jquery=*]");
          if (count($activeItem) <= 0){
              $haveSet = TRUE;
              $activeItem = $xml->xpath("//id"); 
            $blok = '1';
            $setting = $activeItem[0]->addChild('include_jquery');
            $setting->addCData(@$blok);  
            $xml->XMLSave($settingspath.$dominion_jcart_setting_file);     
          }  
          
          if ($haveSet == FALSE) {
            $activeItem = $xml->xpath("//id");          
          }  
          
      } else {
          $xml = @new DominionSimpleXML('<?xml version="1.0" encoding="UTF-8"?><settings></settings>');
          $settings = $xml->addChild('id');
          $blok = 'R';
		  $setting = $settings->addChild('currency');
		  $setting->addCData(@$blok);
          $blok = '';
		  $setting = $settings->addChild('paypal_code');
		  $setting->addCData(@$blok);
          $blok = '0';
		  $setting = $settings->addChild('paypal_gw');
		  $setting->addCData(@$blok);
          $blok = '0';
		  $setting = $settings->addChild('mail_gw');
		  $setting->addCData(@$blok);
          $blok = '0';
		  $setting = $settings->addChild('client_required');
		  $setting->addCData(@$blok);
          $blok = '';
		  $setting = $settings->addChild('webmoney_code');
		  $setting->addCData(@$blok);
          $blok = '0';
		  $setting = $settings->addChild('webmoney_gw');
		  $setting->addCData(@$blok);          
          $blok = '#66cc66';
    	  $setting = $settings->addChild('border_color');
    	  $setting->addCData(@$blok);   
          $blok = '#000066';
          $setting = $settings->addChild('text_color');
          $setting->addCData(@$blok);  
          $blok = '1';
          $setting = $settings->addChild('include_jquery');
          $setting->addCData(@$blok);  
          
         // $blok = '0';
		  //$setting = $settings->addChild('webmoney_testmode');
		  //$setting->addCData(@$blok);                    
          
          $xml->XMLSave($settingspath.$dominion_jcart_setting_file);     
          $activeItem = $xml->xpath("//id");
          
      }
    }
    $currency = $activeItem[0]->currency;
    $paypal_code = $activeItem[0]->paypal_code;
    $border_color = $activeItem[0]->border_color;
    $text_color = $activeItem[0]->text_color;
    
    $webmoney_code = $activeItem[0]->webmoney_code;
    if ($activeItem[0]->paypal_gw == 1) { $paypal_gw = 'checked="checked"'; } else { $paypal_gw = '';}
    if ($activeItem[0]->webmoney_gw == 1) { $webmoney_gw = 'checked="checked"'; } else { $webmoney_gw = '';}
    //if ($activeItem[0]->webmoney_testmode == 1) { $webmoney_testmode = 'checked="checked"'; } else { $webmoney_testmode = '';}
    
    if ($activeItem[0]->mail_gw == 1) { $mail_gw = 'checked="checked"'; } else { $mail_gw = '';}
    if ($activeItem[0]->include_jquery == 1) { $include_jquery = 'checked="checked"'; } else { $include_jquery = '';}
    
    if ($activeItem[0]->client_required == 1) { $client_required = 'checked="checked"'; } else { $client_required = '';}
//SETTINGS SAVE END    
/*
  These part have been removed .. not used anymore
<tr><th>WebMoney Test Mode</th><td><input <?php echo $webmoney_testmode; ?> type='checkbox' name='webmoney_testmode' value='1'></td></tr>    
*/
?>
<div style="display:none" id='dcart_settings' >
<p><?php echo $dominion_jcart_general['JCART_SETTINGS_HEADER']; ?> </p>
<form action="<?php	echo $SITEURL."admin/load.php?id=$adminID#settings";?>"  method="post" id="management">
<table>
<tr><th><?php echo $dominion_jcart_general['JCART_SETTINGS_CURRENCY']; ?></th><td><input type='text' name='currency' value='<?php echo $currency; ?>' size='2'></td></tr>
<tr><th><?php echo $dominion_jcart_general['JCART_SETTINGS_PAYPAL_CODE']; ?></th><td><input type='text' name='paypal_code' value='<?php echo $paypal_code; ?>'><input <?php echo $paypal_gw; ?> type='checkbox' name='paypal_gw' value='1'><?php echo $dominion_jcart_general['JCART_SETTINGS_PAYPAL_USE_GATEWAY']; ?></td></tr>
<tr><th><?php echo $dominion_jcart_general['JCART_SETTINGS_WEBMONEY_CODE']; ?></th><td><input type='text' name='webmoney_code' value='<?php echo $webmoney_code; ?>'><input <?php echo $webmoney_gw; ?> type='checkbox' name='webmoney_gw' value='1'><?php echo $dominion_jcart_general['JCART_SETTINGS_WEBMONEY_USE_GATEWAY']; ?></td></tr>
<tr><th><?php echo $dominion_jcart_general['JCART_SETTINGS_MAIL_ORDERS']; ?></th><td><input type='checkbox' name='mail_gw' value='1' <?php echo $mail_gw; ?>> - <?php echo $dominion_jcart_general['JCART_SETTINGS_MAIL_ORDERS_DESCRIPTION']; ?></td></tr>
<tr><th><?php echo $dominion_jcart_general['JCART_SETTINGS_REQUIRE_CLIENT_DETAILS']; ?></th><td><input type='checkbox' name='client_required' value='1' <?php echo $client_required; ?>> - <?php echo $dominion_jcart_general['JCART_SETTINGS_REQUIRE_CLIENT_DETAILS_DESC']; ?></td></tr>
<tr><th><?php echo $dominion_jcart_general['JCART_SETTINGS_BORDER_COLOR']; ?></th><td><input type='text' name='border_color' value='<?php echo $border_color; ?>' size='10'> - <?php echo $dominion_jcart_general['JCART_SETTINGS_BORDER_COLOR_DESC']; ?> </td></tr>
<tr><th><?php echo $dominion_jcart_general['JCART_SETTINGS_TEXT_COLOR']; ?></th><td><input type='text' name='text_color' value='<?php echo $text_color; ?>' size='10'> - <?php echo $dominion_jcart_general['JCART_SETTINGS_TEXT_COLOR_DESC']; ?> </td></tr>
<tr><th><?php echo $dominion_jcart_general['JCART_SETTINGS_INCLUDE_JQUERY']; ?></th><td><input type='checkbox' name='include_jquery' value='1' <?php echo $include_jquery; ?>> - <?php echo $dominion_jcart_general['JCART_SETTINGS_INCLUDE_JQUERY_DESC']; ?></td></tr>
<tr><th><?php echo $dominion_jcart_general['JCART_SETTINGS_LANGUAGES'];?></th> <td><select name='taal'> <?php availableLanguages('dominion-jcart',$dominion_jcart_active_language); ?>    </select></td>    </tr>

<tr align='center'><th colspan='2' ><input type='submit' name='stoor_settings' value='<?php echo $dominion_jcart_general['JCART_SETTINGS_SAVE']; ?>'></th></tr>

</table>
</form>

</div>
<script type="text/javascript" >
   $("#settings").click(function(){
      $("#dcart_products").hide();
      $("#dcart_settings").show("fase");
   });
   $("#products").click(function(){
      $("#dcart_settings").hide();
      $("#dcart_products").show("fase");
   });
      
  if (window.location.href.indexOf('#settings') > 1) {
      $("#dcart_products").hide();
      $("#dcart_settings").show("fase");
  }    
</script>

<?php
  dominion_cart_outPutCKEditorCode();
}

/*
* Set dCart header s
*/
function set_dcart_headers(){
  
   global $jcartconfig;
   global $SITEURL;
   global $dominion_jcart_path;
   global $dominion_jcart_setting_file;
   if (is_file($dominion_jcart_path.$dominion_jcart_setting_file)) {
     $xml = getDominionXML($dominion_jcart_path.$dominion_jcart_setting_file);
     $activeItem = $xml->xpath("//id"); 
     $border_color = trim(stripslashes($activeItem[0]->border_color));
     $text_color = trim(stripslashes($activeItem[0]->text_color));
   }
   //updaet code as per warnings log by oscim in forum
   $border_color  = (!isset($border_color) ||($border_color == ''))?"#66cc66":$border_color;
   $text_color = (!isset($text_color) || ($text_color == ''))?'#000066':$text_color;
?>
		<style type="text/css">
			#cartbox { margin-left:auto; margin-right:auto; width:200px; }
			#shopbag  { width:100%;  }
			.clear { clear:both; }
			.jcart { color:<?php echo $text_color; ?>; margin:0 8px 8px 0;  border:solid 2px <?php echo $border_color; ?>; float:left;  text-align:center; width:200px; height:150px;}
			.jcart ul { margin:0; list-style:none; padding:0 2px; text-align:left; }
			.jcart fieldset { border:0; }
			.jcart strong { color:#000066; }
			.jcart .button { margin:5px; padding:2px; }

			fieldset { border:0; }
			#paypal-button { display:block; padding:10px; margin:20px auto; }
            * { margin:0; padding:0;}
 
		</style>

		<link rel="stylesheet" type="text/css" media="screen, projection" href="<?php echo $SITEURL; ?>plugins/dominion-jcart/js/jcart.css" />
<?php
  if ($activeItem[0]->include_jquery == 1) { 
    $jsPath = $SITEURL."plugins/dominion-jcart/js/jquery-1.4.3.min.js";
      echo "<script type='text/javascript' src='$jsPath'></script>";
  }
?>		
		<script type="text/javascript" src="<?php echo $SITEURL; ?>plugins/dominion-jcart/js/jcart-javascript.php?base=<?php echo $SITEURL; ?>"></script>        
   		<script type="text/javascript" src="<?php echo $SITEURL; ?>plugins/dominion-jcart/js/jquery.evtpaginate.js"></script>        
<?php

/*

*/
}

function buildCart($targetCart){
  $dcartcustomer =& $_SESSION['dcartcustomer']; if(!is_object($dcartcustomer)) { $dcartcustomer = array(); }
      global $dominion_jcart_path;
      global $dominion_jcart_cat_file;
  
  if (isset($_GET['final_gw']) && $_GET['final_gw'] == '2') {
    //do final payment gateay as configured (config to come later)
     
     if (isset($_POST['c_name']))  {
        $dcartcustomer['customer_name'] = $_POST['c_name'];
        $dcartcustomer['customer_email'] = $_POST['email'];
        $dcartcustomer['customer_contact'] = $_POST['contact'];
     }    
     $cart =& $_SESSION['jcart']; if(!is_object($cart)) $cart = new jcart();
     require_once("dominion-jcart/dominion-price-validate.php");
     $valid_prices = validatePrices($dominion_jcart_path,$dominion_jcart_cat_file,$cart->get_contents());
     if ($valid_prices !== true) {
		return "Dominion jCart received incorrect values from client system. Please retry else contact support. Thanks for your patiences.";
    } else if ($valid_prices === true) {
        require_once("dominion-jcart/transaction_control.php");
        $ordernumber = dcart_createOrder($dcartcustomer,$cart);
        if ($ordernumber !== false) {
            if (isset($_POST['jcart_mail_checkout'])) {
              include GSPLUGINPATH.'dominion-jcart/jcart-gateway-mail.php';
              return getDominionPaymentPage($dominion_jcart_path,$dominion_jcart_cat_file,$dcartcustomer,$cart,$ordernumber);
            } else if (isset($_POST['jcart_paypal_checkout'])) {
              include GSPLUGINPATH.'dominion-jcart/jcart-gateway-paypal.php';
              return getDominionPaymentPage($dominion_jcart_path,$dominion_jcart_cat_file,$dcartcustomer,$cart,$ordernumber);
            } else if (isset($_POST['jcart_webmoney_checkout'])) {
              include GSPLUGINPATH.'dominion-jcart/jcart-gateway-webmoney.php';
              return getDominionPaymentPage($dominion_jcart_path,$dominion_jcart_cat_file,$dcartcustomer,$cart,$ordernumber);
            }
        } else {
            return "Dominion jCart could not create the order (internal error). Please retry else contact support. Thanks for your patiences.";        
        }        
    }    
    
  } if (isset($_GET['final_gw']) && $_GET['final_gw'] == '1') {
    //do final payment gateay as configured (config to come later)
      include GSPLUGINPATH.'dominion-jcart/dominion-customer-info.php';
      return getDominionCustomerInfoPage();
  }  else if (isset($_GET['pid'])) {
     //have to show the products info
     include GSPLUGINPATH .'dominion-jcart/dominion-product.php';  
     return getDominionProductPage();
  } else {
     //show catalog
     
     include GSPLUGINPATH .'dominion-jcart/dominion-base.php';  
     return getDominionCartPage();
  }
}

function dchart_show_cartbox(){
  if (!isset($_REQUEST['dominion_ischeckout'])) {
    global $jcartconfig; 
    $cart =& $_SESSION['jcart']; if(!is_object($cart)) $cart = new jcart();
    $cart->display_cart($jcartconfig); 
  }  
}

function createDefaultCatAndProducts($cat_name){
  //will return the new category ID
        global $dominion_jcart_cat_file;
        global $dominion_jcart_path;
        $numGroups = 1;
        $newCatID = 1;
        if (is_file($dominion_jcart_path.$dominion_jcart_cat_file)) {
          $xml = getDominionXML($dominion_jcart_path.$dominion_jcart_cat_file);
          $items = $xml->xpath("//id");
          $numGroups = count($items);
          $numGroups++;
          $validate = $xml->xpath("//id[@group_id=\"$numGroups\"]"); 
          if (count($validate)>0) {
            while (count($validate)>0) {
              $numGroups++;
              $validate = $xml->xpath("//id[@group_id=\"$numGroups\"]"); 
            }
          }
          $newproductFile = "dominion-products-$numGroups";
          $newCatID =  $numGroups;
          unset($items);
        } else {
          $xml = @new DominionSimpleXML('<?xml version="1.0" encoding="UTF-8"?><groepe></groepe>');
          $newproductFile = 'dominion-products-1';
        }
        
        $script = $xml->addChild('id');
        $script->addAttribute('group_id', $newCatID);
        $script->addAttribute('group_product_file', $newproductFile);

        $blok = 'Example group description';
		$script_info = $script->addChild('info');
		$script_info->addCData(@$blok);
        $blok = $cat_name;
		$script_info = $script->addChild('group_name');
		$script_info->addCData(@$blok);
        $xml->XMLSave($dominion_jcart_path . $dominion_jcart_cat_file);
        unset($xml);

        $xml = @new DominionSimpleXML('<?xml version="1.0" encoding="UTF-8"?><produkte></produkte>');
        $script = $xml->addChild('id');
        
        $script->addAttribute('group_id', $newCatID);
        $script->addAttribute('product_id', '1');
        $blok = 'Example product description';
		$script_info = $script->addChild('info');
		$script_info->addCData(@$blok);
        $blok = '0.00';
		$script_info = $script->addChild('price');
		$script_info->addCData(@$blok);
        $blok = 'example-product';
		$script_info = $script->addChild('product_name');
		$script_info->addCData(@$blok);
        $blok = 'no-image';
		$script_info = $script->addChild('product_image');
		$script_info->addCData(@$blok);
        $blok = '1';
		$script_info = $script->addChild('product_default_qty');
		$script_info->addCData(@$blok);
        $xml->XMLSave($dominion_jcart_path . $newproductFile.".xml");
        dominion_cart_saveIndexes($xml,$newproductFile);
        unset($xml);
        return $newCatID;
}

function buildProductList($activeItem,$dominion_jcart_active_language ){
    global $dominion_jcart_path;
    global $SITEURL;
    include getLanguageFile('dominion-jcart',$dominion_jcart_active_language);

   $xml = loadProductFileforCat($activeItem,$productFile,$cID); 
    $curProducts = $xml->xpath("//id");
    $numProducts = count($curProducts);
    echo "<p><table><tr><th>".$dominion_jcart_general['JCART_PRODUCT_LIST_IMAGE']."</th><th>".$dominion_jcart_general['JCART_PRODUCT_LIST_NAME']."</th><th>".$dominion_jcart_general['JCART_PRODUCT_LIST_QTY']."</th><th>".$dominion_jcart_general['JCART_PRODUCT_LIST_PRICE']."</th><th></th></tr><tr>";
    for ($x=0;$x < $numProducts;$x++) {
      $prodAttr = $curProducts[$x]->attributes();
      $productID = $prodAttr['product_id'];
      $catID =  $prodAttr['group_id'];
      $name = stripslashes($curProducts[$x]->product_name);
      $default_qty = stripslashes($curProducts[$x]->product_default_qty);
      $price = stripslashes($curProducts[$x]->price);
      $product_image = stripslashes($curProducts[$x]->product_image);
      $adminID = $_GET['id'];
      $pUrl = $SITEURL."admin/load.php?id=".$adminID."&productedit=$productID&catid=$catID";
      $pDeleteUrl = $SITEURL."admin/load.php?id=".$adminID."&productdelete=$productID&catid=$catID";
      if ($numProducts == 1 ) {
        echo "<tr><td>$product_image</td><td><a href='$pUrl'>$name</a></td><td>$default_qty</td><td>$price</td><td></td></tr>";
      } else {
        echo "<tr><td>$product_image</td><td><a href='$pUrl'>$name</a></td><td>$default_qty</td><td>$price</td><td><a href='$pDeleteUrl'>".$dominion_jcart_general['JCART_PRODUCT_LIST_DELETE']."</a></td></tr>";
      }      
    }
    echo "</tr></table></p>";
}

function getProductForEditing($activeCatItem,$productID){
   //get specfic product xml for editing
         global $dominion_jcart_path;
        $xml = loadProductFileforCat($activeCatItem,$productFile,$cID); 
    if ($productID == -1) {
       //we will take the first product
      $curProducts = $xml->xpath("//id");
    } else {
      
      $curProducts = $xml->xpath("//id[@product_id=\"$productID\"]");
    }    
    
    $numProducts = count($curProducts);   
    if ($numProducts >= 0) {
      return $curProducts[0];
    } else {
      return FALSE; //error.. what do we doo.. what do we doo !! ? :D
    }
}

function getProductXMLObject($activeCatItem,$productID,&$productFile){
   //get specfic product xml 
      global $dominion_jcart_path;
      $xml = loadProductFileforCat($activeCatItem,$productFile,$cID); 
      return $xml;
}

function loadProductFileforCat($activeCatItem,&$productFile,&$cID){
        global $dominion_jcart_path;
        
        $atr = $activeCatItem[0]->attributes();
        $productFile =  $atr['group_product_file']; 
        $cID = $atr['group_id'];
        $xml = getDominionXML($dominion_jcart_path.$productFile.".xml");
        return $xml;
}

function addNewProduct($activeCatItem){
         global $dominion_jcart_path;
        $xml = loadProductFileforCat($activeCatItem,$productFile,$cID); 
        $hoevProdukte = count($xml->xpath('//id'));
        $hoevProdukte++;
        $validate = $xml->xpath("//id[@product_id=\"$hoevProdukte\"]"); 
        if (count($validate)>0) {
          while (count($validate)>0) {
            $hoevProdukte++;
            $validate = $xml->xpath("//id[@product_id=\"$hoevProdukte\"]"); 
          }
          unset($validate);
        }        
        $script = $xml->addChild('id');
        
        $script->addAttribute('group_id', $cID);
        $script->addAttribute('product_id', $hoevProdukte);
        $blok = 'Example product description';
		$script_info = $script->addChild('info');
		$script_info->addCData(@$blok);
        $blok = '0.00';
		$script_info = $script->addChild('price');
		$script_info->addCData(@$blok);
        $blok = 'example-product';
		$script_info = $script->addChild('product_name');
		$script_info->addCData(@$blok);
        $blok = 'no-image';
		$script_info = $script->addChild('product_image');
		$script_info->addCData(@$blok);
        $blok = '1';
		$script_info = $script->addChild('product_default_qty');
		$script_info->addCData(@$blok);
        $xml->XMLSave($dominion_jcart_path.$productFile.".xml");
        dominion_cart_saveIndexes($xml,$productFile);
        unset($xml);
        return $hoevProdukte;
}

function deleteProduct($activeCatItem,$DeleteProductID){
         global $dominion_jcart_path;
        $xml = loadProductFileforCat($activeCatItem,$productFile,$cID); 
        
        $theItem = $xml->xpath("//id[@product_id=\"$DeleteProductID\"]");
        if  ((count($theItem) > 0)) {
          $theItem[0]->removeCurrentChild();
          $xml->XMLSave($dominion_jcart_path.$productFile.".xml");
          dominion_cart_saveIndexes($xml,$productFile);
        }  
}

function deleteCategoryandProducts($targeCatID){
         global $dominion_jcart_cat_file;
        global $dominion_jcart_path;
        if (is_file($dominion_jcart_path.$dominion_jcart_cat_file)) {
          $xml = getDominionXML($dominion_jcart_path.$dominion_jcart_cat_file);
          $items = $xml->xpath("//id[@group_id=\"$targeCatID\"]");
          if (count($items) > 0) {
            $atr = $items[0]->attributes();
            $prodfile = $atr['group_product_file'];
            unlink($dominion_jcart_path.$prodfile.".xml");
            $items[0]->removeCurrentChild();
            $xml->XMLSave($dominion_jcart_path.$dominion_jcart_cat_file);
          }
          unset($items);
        }         
}

function getImagesCombobox($imageName){
    $dominion_jcart_path = GSDATAUPLOADPATH;
    $filenames = getFiles($dominion_jcart_path); 
    $selected = 0;
    if (count($filenames) != 0) {
        foreach ($filenames as $file) {
            if (!(($file == "." || $file == ".." || is_dir($dominion_jcart_path . $file) || $file == ".htaccess"))) {
               $ext = substr($file, strrpos($file, '.') + 1);
                if (strtolower($ext) == 'gif' || strtolower($ext) == 'jpg' || strtolower($ext) == 'jpeg' || strtolower($ext) == 'png') {
                   if ($imageName == $file) {
                     $selected = 1;
                     echo "<option selected='selected' value='$file'>$file</option>";
                   } else {
                     echo "<option value='$file'>$file</option>";
                   }
                }
            }
        }
    }
    if ($selected ==1) {
      echo "<option  value='no-image'>no-image</option>";
    } else {
      echo "<option selected='selected' value='no-image'>no-image</option>";
      
    }
}

function dominion_cart_outPutCKEditorCode(){
  global $SITEURL;
			if (defined('GSEDITORHEIGHT')) { $EDHEIGHT = GSEDITORHEIGHT .'px'; } else {	$EDHEIGHT = '500px'; }
			if (defined('GSEDITORLANG')) { $EDLANG = GSEDITORLANG; } else {	$EDLANG = 'en'; }
			if (defined('GSEDITORTOOL')) { $EDTOOL = GSEDITORTOOL; } else {	$EDTOOL = 'basic'; }
			if (defined('GSEDITOROPTIONS') && trim(GSEDITOROPTIONS)!="") { $EDOPTIONS = ", ".GSEDITOROPTIONS; } else {	$EDOPTIONS = ''; }
			
			if ($EDTOOL == 'advanced') {
				$toolbar = "
						['Bold', 'Italic', 'Underline', 'NumberedList', 'BulletedList', 'JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock', 'Table', 'TextColor', 'BGColor', 'Link', 'Unlink', 'Image', 'RemoveFormat', 'Source'],
	          '/',
	          ['Styles','Format','Font','FontSize']
	      ";
			} elseif ($EDTOOL == 'basic') {
				$toolbar = "['Bold', 'Italic', 'Underline', 'NumberedList', 'BulletedList', 'JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock', 'Link', 'Unlink', 'Image', 'RemoveFormat', 'Source']";
			} else {
				$toolbar = GSEDITORTOOL;
			}
?>
<script type="text/javascript" src="../admin/template/js/ckeditor/ckeditor.js"></script>

			<script type="text/javascript">

			var editor = CKEDITOR.replace( 'info', {
	        skin : 'getsimple',
	        forcePasteAsPlainText : true,
	        language : '<?php echo $EDLANG; ?>',
	        defaultLanguage : '<?php echo $EDLANG; ?>',
	        entities : true,
	        uiColor : '#f1e4be',
			height: '350',
			baseHref : '<?php echo $SITEURL; ?>',
	        toolbar : 
	        [
	        <?php echo $toolbar; ?>
			]
			<?php echo $EDOPTIONS; ?>
	        //filebrowserBrowseUrl : '/browser/browse.php',
	        //filebrowserImageBrowseUrl : '/browser/browse.php?type=Images',
	        //filebrowserWindowWidth : '640',
	        //filebrowserWindowHeight : '480'
    		});

			</script>
<script type="text/javascript">
	//<![CDATA[

// Added GS image files to be selectable via the image insert system.
// Author : Dominion IT
// url : www.dominion-it.co.za
//Version : 0.4
//GS version : 2.03
//date last changed  : 27 Sep 2010 21:42
CKEDITOR.on( 'dialogDefinition', function( ev )
	{
		var dialogName = ev.data.name;
		var dialogDefinition = ev.data.definition;
		
        if ( dialogName == 'image' ) {
			var infoTab = dialogDefinition.getContents( 'info' );
            var dlg = dialogDefinition.dialog;
 
			//Add the combo box
            infoTab.add( {
                    id : 'cmbGSImages',
                    type : 'select',
                    labelLayout : 'horizontal',
                    widths : [ '35%','85%' ],
                    style : 'width:90px',
                    label : 'Uploaded Images :',
                    'default' : '',
                    items :
                    [   
                        [ 'Custom' , 'CUSTOM']
    
                       <?php
                            $path = GSDATAUPLOADPATH;
                            $filenames = getFiles($path); 
                            if (count($filenames) != 0) {
                                foreach ($filenames as $file) {
                                    if (!(($file == "." || $file == ".." || is_dir($path . $file) || $file == ".htaccess"))) {
                                       $ext = substr($file, strrpos($file, '.') + 1);
                                        if (strtolower($ext) == 'gif' || strtolower($ext) == 'jpg' || strtolower($ext) == 'jpeg' || strtolower($ext) == 'png') {
                                            $URLtothefile = $SITEURL."data/uploads/$file";
                                            echo ",[ '$file' , '$URLtothefile']";
                                        }
                                    }
                                }
                            }
                       ?>
                        
                    ],
                    onChange : function() {
                      var cmbValue = this.getValue();
                      if (cmbValue != 'CUSTOM') {
                        dlg.setValueOf( 'info', 'txtUrl', cmbValue );
                        this.setValue('CUSTOM');
                      }  
                    }

				});
                infoTab.add( {
                    id : 'cmbGSImagesThumbs',
                    type : 'select',
                    labelLayout : 'horizontal',
                    widths : [ '35%','85%' ],
                    style : 'width:90px',
                    label : 'Thumbnails of Images : ',
                    'default' : '',
                    items :
                    [   
                        [ 'Custom' , 'CUSTOM']
    
                       <?php
                            $path = GSTHUMBNAILPATH;
                            $filenames = getFiles($path); 
                            if (count($filenames) != 0) {
                                foreach ($filenames as $file) {
                                    if (!(($file == "." || $file == ".." || is_dir($path . $file) || $file == ".htaccess"))) {
                                        $ext = substr($file, strrpos($file, '.') + 1);
                                        if (strtolower($ext) == 'gif' || strtolower($ext) == 'jpg' || strtolower($ext) == 'jpeg' || strtolower($ext) == 'png') {
                                             if (strpos($file,'thumbnail.') !== FALSE) {
                                              $URLtothefile = $SITEURL."data/thumbs/$file";
                                              echo ",[ '$file' , '$URLtothefile']";
                                             }  
                                        }
                                    }
                                }
                            }
                       ?>
                        
                    ],
                    onChange : function() {
                      var cmbValue = this.getValue();
                      if (cmbValue != 'CUSTOM') {
                        dlg.setValueOf( 'info', 'txtUrl', cmbValue );
                        this.setValue('CUSTOM');
                      }  
                    }

				}); 
                infoTab.add( {
                    id : 'cmbGSSmallImages',
                    type : 'select',
                    labelLayout : 'horizontal',
                    widths : [ '35%','85%' ],
                    style : 'width:90px',
                    label : 'Small version of Images : ',
                    'default' : '',
                    items :
                    [   
                        [ 'Custom' , 'CUSTOM']
    
                       <?php
                            $path = GSTHUMBNAILPATH;
                            $filenames = getFiles($path); 
                            if (count($filenames) != 0) {
                                foreach ($filenames as $file) {
                                    if (!(($file == "." || $file == ".." || is_dir($path . $file) || $file == ".htaccess"))) {
                                        $ext = substr($file, strrpos($file, '.') + 1);
                                        if (strtolower($ext) == 'gif' || strtolower($ext) == 'jpg' || strtolower($ext) == 'jpeg' || strtolower($ext) == 'png') {
                                             if (strpos($file,'thumbsm.') !== FALSE) {
                                              $URLtothefile = $SITEURL."data/thumbs/$file";
                                              echo ",[ '$file' , '$URLtothefile']";
                                             }  
                                        }
                                    }
                                }
                            }
                       ?>
                        
                    ],
                    onChange : function() {
                      var cmbValue = this.getValue();
                      if (cmbValue != 'CUSTOM') {
                        dlg.setValueOf( 'info', 'txtUrl', cmbValue );
                        this.setValue('CUSTOM');
                      }  
                    }

				}); 
 
		}  
        if ( dialogName == 'link' ) {
			var linkTab = dialogDefinition.getContents( 'info' );
            var linkdlg = dialogDefinition.dialog;
 
			//Add the combo box
            linkTab.add( {
                    id : 'cmbGSFiles',
                    type : 'select',
                    labelLayout : 'horizontal',
                    widths : [ '35%','85%' ],
                    style : 'width:90px',
                    label : 'Uploaded Files ',
                    'default' : '',
                    items :
                    [   
                        [ 'Custom' , 'CUSTOM']
    
                       <?php
                            $path = GSDATAUPLOADPATH;
                            $filenames = getFiles($path); 
                            if (count($filenames) != 0) {
                                foreach ($filenames as $file) {
                                        if (!(($file == "." || $file == ".." || is_dir($path . $file) || $file == ".htaccess"))) {
                                                $URLtothefile = $SITEURL."data/uploads/$file";
                                                $URLtothefile = str_replace("http://","",$URLtothefile);
                                                echo ",[ '$file' , '$URLtothefile']";
                                        
                                        }
                                }
                            }
                       ?>
                        
                    ],
                    onChange : function() {
                      var cmbValue = this.getValue();
                      if (cmbValue != 'CUSTOM') {
                        linkdlg.setValueOf( 'info', 'url', cmbValue );
                        this.setValue('CUSTOM');
                        
                      }  
                    }

				});
		}         
	});
	//]]>
	</script>	

<?php
}

function dominion_cart_saveIndexes(&$cartXML,$CartXMLFile){
 global $dominion_jcart_path ;
//INDEX DATA
    $activeCartItem = $cartXML->xpath("//id");
    if (count($activeCartItem) > 0) {
      //only if there is something to index.
      $teller = count($activeCartItem);
      
      //First get list of all ID's and data
      for ($tmpX = 0;$tmpX < $teller;$tmpX++) {
        $atr = $activeCartItem[$tmpX]->attributes();
        $bID = (integer)$atr['product_id'];
        $bName = stripslashes($activeCartItem[$tmpX]->product_name);
        $bPrice = stripslashes($activeCartItem[$tmpX]->price);
        $curDataIndex[$bID] = $bName;
        $curPriceDataIndex[$bID] = $bPrice;
      }
      
      //Now sort them
      arsort($curDataIndex,SORT_REGULAR);
      arsort($curPriceDataIndex,SORT_REGULAR);      
      
      //create index block if not exist
      $XMLIndex = $cartXML->xpath("//indexbyname");
      if (count($XMLIndex) <= 0) {
            $script = $cartXML->addChild('indexbyname');
            $blok = '';
            $script_info = $script->addChild('index');
            $script_info->addCData(@$blok);
            $cartXML->XMLSave($dominion_jcart_path.$CartXMLFile.".xml");
            $XMLIndex = $cartXML->xpath("//indexbyname");
      }
      
      $indexCSV = '';
      $totaal = count($curDataIndex); 
      $teller = 0;
      foreach ($curDataIndex as $key => $val) {
         $indexCSV .= $key;
         $teller++;
         if ($totaal !=  $teller) { 
           $indexCSV .= ',';
         } 
      }
      $XMLIndex[0]->index->updateCData(@$indexCSV);  
      $cartXML->XMLSave($dominion_jcart_path.$CartXMLFile.".xml");              
      
      //create index block if not exist
      $XMLIndex = $cartXML->xpath("//indexbyprice");
      if (count($XMLIndex) <= 0) {
            $script = $cartXML->addChild('indexbyprice');
            $blok = '';
            $script_info = $script->addChild('index');
            $script_info->addCData(@$blok);
            $cartXML->XMLSave($dominion_jcart_path.$CartXMLFile.".xml");
            $XMLIndex = $cartXML->xpath("//indexbyprice");
      }            
      $indexCSV = '';
      $totaal = count($curPriceDataIndex); 
      $teller = 0;
      foreach ($curPriceDataIndex as $key => $val) {
         $indexCSV .= $key;
         $teller++;
         if ($totaal !=  $teller) { 
           $indexCSV .= ',';
         } 
      }
      $XMLIndex[0]->index->updateCData(@$indexCSV);        
      $cartXML->XMLSave($dominion_jcart_path.$CartXMLFile.".xml");              
    }
    //END INDEX DATA
}
?>