<?php

// JCART v1.1
// http://conceptlogic.com/jcart/

// SESSION BASED SHOPPING CART CLASS FOR JCART

/**********************************************************************
Based on Webforce Cart v.1.5
(c) 2004-2005 Webforce Ltd, NZ
http://www.webforce.co.nz/cart/
**********************************************************************/

// USER CONFIG
include_once('jcart-config.php');

// DEFAULT CONFIG VALUES
//include_once('jcart-defaults.php');



// JCART
class jcart {
	var $total = 0;
	var $itemcount = 0;
	var $items = array();
	var $itemprices = array();
	var $itemqtys = array();
	var $itemname = array();

	// CONSTRUCTOR FUNCTION
	function cart() {}

	// GET CART CONTENTS
	function get_contents()
		{
		$items = array();
		foreach($this->items as $tmp_item)
			{
			$item = FALSE;
            $p_id = trim(substr($tmp_item,0,strpos($tmp_item,'-')-1));
            $c_id = trim(substr($tmp_item,strpos($tmp_item,'-')+1,strlen($tmp_item)));
			$item['p_id'] = $p_id;
            $item['c_id'] = $c_id;
            $item['id'] = $tmp_item;
			$item['qty'] = $this->itemqtys[$tmp_item];
			$item['price'] = $this->itemprices[$tmp_item];
			$item['name'] = $this->itemname[$tmp_item];
			$item['subtotal'] = $item['qty'] * $item['price'];
			$items[] = $item;
			}
		return $items;
		}


	// ADD AN ITEM
	function add_item($item_id, $item_qty=1, $item_price, $item_name)
		{
		// VALIDATION
		$valid_item_qty = $valid_item_price = false;

		// IF THE ITEM QTY IS AN INTEGER, OR ZERO
		if (preg_match("/^[0-9-]+$/i", $item_qty))
			{
			$valid_item_qty = true;
			}
		// IF THE ITEM PRICE IS A FLOATING POINT NUMBER
		if (is_numeric($item_price))
			{
			$valid_item_price = true;
			}

		// ADD THE ITEM
		if ($valid_item_qty !== false && $valid_item_price !== false)
			{
			// IF THE ITEM IS ALREADY IN THE CART, INCREASE THE QTY
			if ((isset($this->itemqtys[$item_id])) && ($this->itemqtys[$item_id] > 0))
				{
				$this->itemqtys[$item_id] = $item_qty + $this->itemqtys[$item_id];
				$this->_update_total();
				}
			// THIS IS A NEW ITEM
			else
				{
				$this->items[] = $item_id;
				$this->itemqtys[$item_id] = $item_qty;
				$this->itemprices[$item_id] = $item_price;
				$this->itemname[$item_id] = $item_name;
				}
			$this->_update_total();
			return true;
			}

		else if	($valid_item_qty !== true)
			{
			$error_type = 'qty';
			return $error_type;
			}
		else if	($valid_item_price !== true)
			{
			$error_type = 'price';
			return $error_type;
			}
		}


	// UPDATE AN ITEM
	function update_item($item_id, $item_qty)
		{
		// IF THE ITEM QTY IS AN INTEGER, OR ZERO
		// UPDATE THE ITEM
		if (preg_match("/^[0-9-]+$/i", $item_qty))
			{
			if($item_qty < 1)
				{
				$this->del_item($item_id);
				}
			else
				{
				$this->itemqtys[$item_id] = $item_qty;
				}
			$this->_update_total();
			return true;
			}
		}


	// UPDATE THE ENTIRE CART
	// VISITOR MAY CHANGE MULTIPLE FIELDS BEFORE CLICKING UPDATE
	// ONLY USED WHEN JAVASCRIPT IS DISABLED
	// WHEN JAVASCRIPT IS ENABLED, THE CART IS UPDATED ONKEYUP
	function update_cart()
		{
		// POST VALUE IS AN ARRAY OF ALL ITEM IDs IN THE CART
		if (is_array($_POST['jcart_item_ids']))
			{
			// TREAT VALUES AS A STRING FOR VALIDATION
			$item_ids = implode($_POST['jcart_item_ids']);
			}

		// POST VALUE IS AN ARRAY OF ALL ITEM QUANTITIES IN THE CART
		if (is_array($_POST['jcart_item_qty']))
			{
			// TREAT VALUES AS A STRING FOR VALIDATION
			$item_qtys = implode($_POST['jcart_item_qty']);
			}

		// IF NO ITEM IDs, THE CART IS EMPTY
		if ($_POST['jcart_item_id'])
			{
			// IF THE ITEM QTY IS AN INTEGER, OR ZERO, OR EMPTY
			// UPDATE THE ITEM
			if (preg_match("/^[0-9-]+$/i", $item_qtys) || $item_qtys == '')
				{
				// THE INDEX OF THE ITEM AND ITS QUANTITY IN THEIR RESPECTIVE ARRAYS
				$count = 0;

				// FOR EACH ITEM IN THE CART
				foreach ($_POST['jcart_item_id'] as $item_id)
					{
					// GET THE ITEM QTY AND DOUBLE-CHECK THAT THE VALUE IS AN INTEGER
					$update_item_qty = intval($_POST['jcart_item_qty'][$count]);

					if($update_item_qty < 1)
						{
						$this->del_item($item_id);
						}
					else
						{
						// UPDATE THE ITEM
						$this->update_item($item_id, $update_item_qty);
						}

					// INCREMENT INDEX FOR THE NEXT ITEM
					$count++;
					}
				return true;
				}
			}
		// IF NO ITEMS IN THE CART, RETURN TRUE TO PREVENT UNNECSSARY ERROR MESSAGE
		else if (!$_POST['jcart_item_id'])
			{
			return true;
			}
		}


	// REMOVE AN ITEM
	/*
	GET VAR COMES FROM A LINK, WITH THE ITEM ID TO BE REMOVED IN ITS QUERY STRING
	AFTER AN ITEM IS REMOVED ITS ID STAYS SET IN THE QUERY STRING, PREVENTING THE SAME ITEM FROM BEING ADDED BACK TO THE CART
	SO WE CHECK TO MAKE SURE ONLY THE GET VAR IS SET, AND NOT THE POST VARS

	USING POST VARS TO REMOVE ITEMS DOESN'T WORK BECAUSE WE HAVE TO PASS THE ID OF THE ITEM TO BE REMOVED AS THE VALUE OF THE BUTTON
	IF USING AN INPUT WITH TYPE SUBMIT, ALL BROWSERS DISPLAY THE ITEM ID, INSTEAD OF ALLOWING FOR USER FRIENDLY TEXT SUCH AS 'remove'
	IF USING AN INPUT WITH TYPE IMAGE, INTERNET EXPLORER DOES NOT SUBMIT THE VALUE, ONLY X AND Y COORDINATES WHERE BUTTON WAS CLICKED
	CAN'T USE A HIDDEN INPUT EITHER SINCE THE CART FORM HAS TO ENCOMPASS ALL ITEMS TO RECALCULATE TOTAL WHEN A QUANTITY IS CHANGED, WHICH MEANS THERE ARE MULTIPLE REMOVE BUTTONS AND NO WAY TO ASSOCIATE THEM WITH THE CORRECT HIDDEN INPUT
	*/
	function del_item($item_id)
		{
		$ti = array();
		$this->itemqtys[$item_id] = 0;
		foreach($this->items as $item)
			{
			if($item != $item_id)
				{
				$ti[] = $item;
				}
			}
		$this->items = $ti;
		$this->_update_total();
		}


	// EMPTY THE CART
	function empty_cart()
		{
		$this->total = 0;
		$this->itemcount = 0;
		$this->items = array();
		$this->itemprices = array();
		$this->itemqtys = array();
		$this->itemname = array();
		}


	// INTERNAL FUNCTION TO RECALCULATE TOTAL
	function _update_total()
		{
		$this->itemcount = 0;
		$this->total = 0;
		if(sizeof($this->items > 0))
			{
			foreach($this->items as $item)
				{
				$this->total = $this->total + ($this->itemprices[$item] * $this->itemqtys[$item]);

				// TOTAL ITEMS IN CART (ORIGINAL wfCart COUNTED TOTAL NUMBER OF LINE ITEMS)
				$this->itemcount += $this->itemqtys[$item];
				}
			}
		}

    
    function display_cart_no_checkout($conigSettings){
      $this->display_cart($conigSettings,false);
    }
    
	// PROCESS AND DISPLAY CART
	function display_cart($conigSettings,$showcheckout = TRUE){
          $dominion_base = getcwd();
          $dominion_base = str_replace("/plugins/dominion-jcart/js","",$dominion_base);
          $dominion_base = str_replace("\plugins\dominion-jcart\js","",$dominion_base);
         require_once($dominion_base."/plugins/dominion-it-shared/dominion-common.php");   
         $dominion_jcart_active_language = 'en_US';
          $dominion_jcart_language_setting_file = 'dominion-cart-language.cfg';
          if (is_file($dominion_base."/plugins/dominion-jcart/data/".$dominion_jcart_language_setting_file)) {
            $dominion_jcart_active_language = file_get_contents($dominion_base."/plugins/dominion-jcart/data/".$dominion_jcart_language_setting_file);
          }
          include getLanguageFile('dominion-jcart',$dominion_jcart_active_language,$dominion_base."/plugins/");         
          //Set Language stuff
          $conigSettings['text']['cart_title'] = $dominion_jcart_general['JCART_CART_TITLE'];
          $conigSettings['text']['single_item'] = $dominion_jcart_general['JCART_CART_SINGLE_ITEM'];
          $conigSettings['text']['multiple_items']= $dominion_jcart_general['JCART_CART_MULTI_ITEMS'];
          
          $conigSettings['text']['subtotal']= $dominion_jcart_general['JCART_CART_SUBTOTAL'];
          $conigSettings['text']['update_button']= $dominion_jcart_general['JCART_CART_UPDATE'];
          $conigSettings['text']['checkout_button']= $dominion_jcart_general['JCART_CART_CHECKOUT'];
          $conigSettings['text']['remove_link']= $dominion_jcart_general['JCART_CART_REMOVE'];
          $conigSettings['text']['empty_button']= $dominion_jcart_general['JCART_CART_EMPTY'];
          $conigSettings['text']['empty_message']= $dominion_jcart_general['JCART_CART_EMPTY_MESSAGE'];
          $conigSettings['text']['item_added_message']= $dominion_jcart_general['JCART_CART_ITEM_ADD_NOTIFICATION'];
          $conigSettings['text']['price_error']= $dominion_jcart_general['JCART_CART_INVALID_PRICE'];
          $conigSettings['text']['quantity_error']= $dominion_jcart_general['JCART_CART_QTY_ERROR'];
          $conigSettings['text']['checkout_error']= $dominion_jcart_general['JCART_CART_CHECKOUT_ERROR'];
          
          
          //End laguage stuff
          //Dominion Cart settinngs to be placed here as configured by user.
          $settingspath = $dominion_base."/plugins/dominion-jcart/data/";
          $setting_file = 'dominion-cart-settings.xml';
          
          $xml = getDominionXML($settingspath.$setting_file);
          $activeItem = $xml->xpath("//id");
          $currency = $activeItem[0]->currency." ";
          $paypal_code = $activeItem[0]->paypal_code;
          $paypal_gw = ($activeItem[0]->paypal_gw == 1)?1:0;
          $mail_gw = ($activeItem[0]->mail_gw == 1)?1:0;     
          $webmoney_gw = ($activeItem[0]->webmoney_gw == 1)?1:0;     
          $client_required = ($activeItem[0]->client_required == 1)?1:0;          
          
          //Set to our settiongs
          
          
          unset($xml,$activeItem);
          //End Dominion settigns place.
          
		// ASSIGN USER CONFIG VALUES AS POST VAR LITERAL INDICES
		// INDICES ARE THE HTML NAME ATTRIBUTES FROM THE USERS ADD-TO-CART FORM
        
        if (isset($_POST[$conigSettings['item_id']])) {
    		$conigSettings['item_id'] = $_POST[$conigSettings['item_id']];
    		$conigSettings['item_qty'] = $_POST[$conigSettings['item_qty']];
    		$conigSettings['item_price'] = $_POST[$conigSettings['item_price']];
    		$conigSettings['item_name'] = $_POST[$conigSettings['item_name']];
        }    

		// ADD AN ITEM
        
		if (isset($_POST[$conigSettings['item_add']]) && ($_POST[$conigSettings['item_add']])) {  
			$item_added = $this->add_item($conigSettings['item_id'], $conigSettings['item_qty'], $conigSettings['item_price'], $conigSettings['item_name']);
			// IF NOT TRUE THE ADD ITEM FUNCTION RETURNS THE ERROR TYPE
			if ($item_added !== true)
				{
				$error_type = $item_added;
				switch($error_type)
					{
					case 'qty':
						$error_message = $conigSettings['text']['quantity_error'];
						break;
					case 'price':
						$error_message = $conigSettings['text']['price_error'];
						break;
					}
				}
			}

		// UPDATE A SINGLE ITEM
		// CHECKING POST VALUE AGAINST $conigSettings['text'] ARRAY FAILS?? HAVE TO CHECK AGAINST $jcart ARRAY
		if (isset($_POST['jcart_update_item']) && ($_POST['jcart_update_item'] == $conigSettings['text']['update_button']))	{
			$item_updated = $this->update_item($_POST['item_id'], $_POST['item_qty']);
			if ($item_updated !== true)
				{
				$error_message = $conigSettings['text']['quantity_error'];
				}
			}

		// UPDATE ALL ITEMS IN THE CART
		if (isset($_POST['jcart_update_cart']) && ($_POST['jcart_update_cart'] || $_POST['jcart_checkout'])) {
			$cart_updated = $this->update_cart();
			if ($cart_updated !== true)
				{
				$error_message = $conigSettings['text']['quantity_error'];
				}
			}

		// REMOVE AN ITEM
        
		if (isset($_GET['jcart_remove']) && ($_GET['jcart_remove'] && !$_POST[$item_add] && !$_POST['jcart_update_cart'] && !$_POST['jcart_check_out'])) {
			$this->del_item($_GET['jcart_remove']);
			}

		// EMPTY THE CART
		if (isset($_POST['jcart_empty']) && ($_POST['jcart_empty'])) {
			$this->empty_cart();
		}

		// DETERMINE WHICH TEXT TO USE FOR THE NUMBER OF ITEMS IN THE CART
		if ($this->itemcount >= 0) {
			$conigSettings['text']['items_in_cart'] = $conigSettings['text']['multiple_items'];
		}
		if ($this->itemcount == 1) {
			$conigSettings['text']['items_in_cart'] = $conigSettings['text']['single_item'];
		}

		// DETERMINE IF THIS IS THE CHECKOUT PAGE
		// WE FIRST CHECK THE REQUEST URI AGAINST THE USER CONFIG CHECKOUT (SET WHEN THE VISITOR FIRST CLICKS CHECKOUT)
		// WE ALSO CHECK FOR THE REQUEST VAR SENT FROM HIDDEN INPUT SENT BY AJAX REQUEST (SET WHEN VISITOR HAS JAVASCRIPT ENABLED AND UPDATES AN ITEM QTY)
        
        if (!isset($_REQUEST['webPath'])) {
           //is not ajax post
		  $is_checkout = strpos($_SERVER['REQUEST_URI'], $conigSettings['form_action']);
          $gs_base_url = $_SERVER["REQUEST_URI"];
          $gs_base_url = preg_replace("/&dominion_ischeckout=1/i","",$gs_base_url);
          $gs_base_url = preg_replace("/\?dominion_ischeckout=1/i","",$gs_base_url);    
          if(stripos($gs_base_url, '?') === false) {
        	$link_base_url = $gs_base_url."?";
          } else 	{
        	$link_base_url = $gs_base_url."&";
          }
        } else {
          //ajax post.. use the url passed from ajax to check form
          $conigSettings['form_action']= $_REQUEST['webPath'];
          if(stripos($conigSettings['form_action'], '?') === false) {
          		$link_base_url = $conigSettings['form_action']."?";
          } else 	{
          		$link_base_url = $conigSettings['form_action']."&";
          }   
          $conigSettings['form_action']= $link_base_url.'dominion_ischeckout=1';          
          $is_checkout = strpos($_SERVER['REQUEST_URI'], $conigSettings['form_action']);
        }        
        if  ($is_checkout !== false || (isset($_REQUEST['jcart_is_checkout']) && $_REQUEST['jcart_is_checkout'] == 'true')) {
			$is_checkout = true;
			}
		else
			{
			$is_checkout = false;
			}

		// OVERWRITE THE CONFIG FORM ACTION TO POST TO jcart-gateway.php INSTEAD OF POSTING BACK TO CHECKOUT PAGE
		// THIS ALSO ALLOWS US TO VALIDATE PRICES BEFORE SENDING CART CONTENTS TO PAYPAL
   	     if ($is_checkout == true) {

          if ($client_required == '1') {
           $conigSettings['form_action']= $link_base_url.'final_gw=1';
          }  else {
			$conigSettings['form_action']= $link_base_url.'final_gw=2';
          }  
		}

		// DEFAULT INPUT TYPE
		// CAN BE OVERRIDDEN IF USER SETS PATHS FOR BUTTON IMAGES
		$input_type = 'submit';

		// IF THIS ERROR IS TRUE THE VISITOR UPDATED THE CART FROM THE CHECKOUT PAGE USING AN INVALID PRICE FORMAT
		// PASSED AS A SESSION VAR SINCE THE CHECKOUT PAGE USES A HEADER REDIRECT
		// IF PASSED VIA GET THE QUERY STRING STAYS SET EVEN AFTER SUBSEQUENT POST REQUESTS

		if (isset($_SESSION['quantity_error']) && ($_SESSION['quantity_error'] == true)) {
			$error_message = $conigSettings['text']['quantity_error'];
			unset($_SESSION['quantity_error']);
			}

		// OUTPUT THE CART

		// IF THERE'S AN ERROR MESSAGE WRAP IT IN SOME HTML
		if (isset($error_message) && ($error_message)) {
			$error_message = "<p class='jcart-error'>$error_message</p>";
		} else { $error_message= ''; }

		// DISPLAY THE CART HEADER
		echo "<div id='jcart'>";
		echo "$error_message";
		echo "<form method='post' action='{$conigSettings['form_action']}'>";
		echo "<fieldset>";
		echo "<table border='1'>";
		echo "<tr>";
		echo "<th id='jcart-header' colspan='3'>";
		echo "<strong id='jcart-title'>" . $conigSettings['text']['cart_title'] . "</strong> (" . $this->itemcount . "&nbsp;" . $conigSettings['text']['items_in_cart'] .")";
		echo "</th>";
		echo "</tr>". "";

		// IF ANY ITEMS IN THE CART
		if (($this->itemcount > 0) && ($is_checkout == true)) {

			// DISPLAY LINE ITEMS
			foreach($this->get_contents() as $item)
				{
				echo "<tr>";

				// ADD THE ITEM ID AS THE INPUT ID ATTRIBUTE
				// THIS ALLOWS US TO ACCESS THE ITEM ID VIA JAVASCRIPT ON QTY CHANGE, AND THEREFORE UPDATE THE CORRECT ITEM
				// NOTE THAT THE ITEM ID IS ALSO PASSED AS A SEPARATE FIELD FOR PROCESSING VIA PHP
    				echo "<td class='jcart-item-qty'>";
    				echo "<input type='text' size='2' id='jcart-item-id-" . $item['id'] . "' name='jcart_item_qty[ ]' value='" . $item['qty'] . "' />";
    				echo "</td>";
    				echo "<td class='jcart-item-name'>";
    				echo "" . $item['name'] . "<input type='hidden' name='jcart_item_name[ ]' value='" . $item['name'] . "' />";
    				echo "<input type='hidden' name='jcart_item_id[ ]' value='" . $item['id'] . "' />";
    				echo "</td>";
    				echo "<td class='jcart-item-price'>";
    				echo "<span>" . $currency  . number_format($item['subtotal'],2) . "</span><input type='hidden' name='jcart_item_price[ ]' value='" . $item['price'] . "' />";
    				echo "<a class='jcart-remove' href='?jcart_remove=" . $item['id'] . "'>" . $conigSettings['text']['remove_link'] . "</a>";
    				echo "</td>";
    				echo "</tr>";
				}
			}

		// THE CART IS EMPTY
		else
			{
              if ($is_checkout == true) {
			    echo "<tr><td colspan='3' class='empty'>" . $conigSettings['text']['empty_message'] . "</td></tr>";
              }  
			}

		// DISPLAY THE CART FOOTER
		echo "<tr>";
		echo "<th id='jcart-footer' colspan='3'>";

		// IF THIS IS THE CHECKOUT HIDE THE CART CHECKOUT BUTTON
        $src = '';
        if ($showcheckout == FALSE) {
          echo "<input type='hidden' id='view_only_cart' name='view_only_cart' value='true' />";
        }
        
        //error_log(print_r($_REQUEST,true));
        $moetNIEcheckoutWys = isset($_REQUEST['dont_show_checkout'])?$_REQUEST['dont_show_checkout']:FALSE;
        //error_log(print_r($moetNIEcheckoutWys,true));
        if ($moetNIEcheckoutWys == 'true') {
         $showcheckout =  FALSE;
        }
        
		if (($is_checkout !== true) && ($showcheckout == TRUE))	{
			if ($conigSettings['button']['checkout']) { $input_type = 'image'; $src = ' src="' . $conigSettings['button']['checkout'] . '" alt="' . $conigSettings['text']['checkout_button'] . '" title="" ';	}
			echo "<input type='" . $input_type . "' " . $src . "id='jcart-checkout' name='jcart_checkout' class='jcart-button' value='" . $conigSettings['text']['checkout_button'] . "' />";
		}

		echo "<span id='jcart-subtotal'>" . $conigSettings['text']['subtotal'] . ": <strong>" . $currency  . number_format($this->total,2) . "</strong></span>";
		echo "</th>";
		echo "</tr>";
		echo "</table>";

		echo "<div class='jcart-hide'>";
		if ($conigSettings['button']['update']) { $input_type = 'image'; $src = ' src="' . $conigSettings['button']['update'] . '" alt="' . $conigSettings['text']['update_button'] . '" title="" ';	}
		echo "<input type='" . $input_type . "' " . $src ."name='jcart_update_cart' value='" . $conigSettings['text']['update_button'] . "' class='jcart-button' />";
		if ($conigSettings['button']['empty']) { $input_type = 'image'; $src = ' src="' . $conigSettings['button']['empty'] . '" alt="' . $conigSettings['text']['empty_button'] . '" title="" ';	}
		echo "<input type='" . $input_type . "' " . $src ."name='jcart_empty' value='" . $conigSettings['text']['empty_button'] . "' class='jcart-button' />";
		echo "</div>";

		// IF THIS IS THE CHECKOUT DISPLAY THE PAYPAL CHECKOUT BUTTON
		if (($is_checkout == true) && ($showcheckout == TRUE)) {
            $disable_paypal_checkout = '';
            if ($this->itemcount <= 0) {
              $disable_paypal_checkout = 'disabled';
            }
            
			// HIDDEN INPUT ALLOWS US TO DETERMINE IF WE'RE ON THE CHECKOUT PAGE
			// WE NORMALLY CHECK AGAINST REQUEST URI BUT AJAX UPDATE SETS VALUE TO jcart-relay.php
			echo "<input type='hidden' id='jcart-is-checkout' name='jcart_is_checkout' value='true' />";

			// SEND THE URL OF THE CHECKOUT PAGE TO jcart-gateway.php
			// WHEN JAVASCRIPT IS DISABLED WE USE A HEADER REDIRECT AFTER THE UPDATE OR EMPTY BUTTONS ARE CLICKED
			$protocol = 'http://'; if (!empty($_SERVER['HTTPS'])) { $protocol = 'https://'; }
			echo "<input type='hidden' id='jcart-checkout-page' name='jcart_checkout_page' value='" . $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "' />";

			// PAYPAL CHECKOUT BUTTON
            echo "<table border='0' style='border:none;'><tr style='border:none;'>";
            
            //JOHANNES TODO : Maak dat die knoppies apart opgestel kan word. !
            if ($paypal_gw == 1) {
  			  //if ($conigSettings['button']['paypal_checkout'])	{ 
                 //$input_type = 'image'; $src = ' src="' . $conigSettings['button']['paypal_checkout'] . '" alt="' . $conigSettings['text']['checkout_paypal_button'] . '" title="" '; 
              //}
			  echo "<td style='border:none;'><input type='" . $input_type . "' " . $src ."id='jcart-paypal-checkout' name='jcart_paypal_checkout' value='Pay via PayPal'" . $disable_paypal_checkout . " /></td>";
            }

            if ($mail_gw == 1) {
  			  //if ($conigSettings['button']['paypal_checkout'])	{ $input_type = 'image'; $src = ' src="' . $conigSettings['button']['paypal_checkout'] . '" alt="' . $conigSettings['text']['checkout_paypal_button'] . '" title="" '; }
			  echo "<td style='border:none;'><input type='" . $input_type . "' " . $src ."id='jcart-paypal-checkout' name='jcart_mail_checkout' value='Submit Order'" . $disable_paypal_checkout . " /></td>";
            }
            if ($webmoney_gw == 1) {
  			  //if ($conigSettings['button']['paypal_checkout'])	{ $input_type = 'image'; $src = ' src="' . $conigSettings['button']['paypal_checkout'] . '" alt="' . $conigSettings['text']['checkout_paypal_button'] . '" title="" '; }
			  echo "<td style='border:none;'><input type='" . $input_type . "' " . $src ."id='jcart-paypal-checkout' name='jcart_webmoney_checkout' value='Pay via WebMoney'" . $disable_paypal_checkout . " /></td>";
            }
            
            echo "</tr></table>";            
            
            
			}
		echo "</fieldset>";
		echo "</form>";

		// IF UPDATING AN ITEM, FOCUS ON ITS QTY INPUT AFTER THE CART IS LOADED (DOESN'T SEEM TO WORK IN IE7)
		if (isset($_POST['jcart_update_item']) && ($_POST['jcart_update_item'])) {
			echo "" . '<script type="text/javascript">$(function(){$("#jcart-item-id-' . $_POST['item_id'] . '").focus()});</script>' . "";
			}

		echo "</div><!-- END JCART -->";
		}
	}
?>