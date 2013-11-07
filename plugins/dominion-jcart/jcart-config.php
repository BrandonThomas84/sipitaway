<?php

// JCART v1.1
// http://conceptlogic.com/jcart/

///////////////////////////////////////////////////////////////////////
// REQUIRED SETTINGS

// THE HTML NAME ATTRIBUTES USED IN YOUR ADD-TO-CART FORM
$jcartconfig['item_id']		= 'dominion-cart-id';			// ITEM ID
$jcartconfig['item_name']		= 'dominion-cart-name';		// ITEM NAME
$jcartconfig['item_price']	= 'dominion-cart-price';		// ITEM PRICE
$jcartconfig['item_qty']		= 'dominion-cart-qty';		// ITEM QTY
$jcartconfig['item_add']		= 'dominion-cart-button';		// ADD-TO-CART BUTTON

// PATH TO THE DIRECTORY CONTAINING JCART FILES

$jcartconfig['path'] = 'plugins/dominion-jcart/';

// THE PATH AND FILENAME WHERE SHOPPING CART CONTENTS SHOULD BE POSTED WHEN A VISITOR CLICKS THE CHECKOUT BUTTON
// USED AS THE ACTION ATTRIBUTE FOR THE SHOPPING CART FORM
    $gs_base_url = $_SERVER["REQUEST_URI"];
    $gs_base_url = preg_replace("/&dominion_ischeckout=1/i","",$gs_base_url);
    $gs_base_url = preg_replace("/\?dominion_ischeckout=1/i","",$gs_base_url);    
	if(stripos($gs_base_url, '?') === false) {
		$link_base_url = $gs_base_url."?";
	} else 	{
		$link_base_url = $gs_base_url."&";
	}

$jcartconfig['form_action']	= $link_base_url.'dominion_ischeckout=1';


// YOUR PAYPAL SECURE MERCHANT ACCOUNT ID
$jcartconfig['paypal_id']		= '';


///////////////////////////////////////////////////////////////////////
// OPTIONAL SETTINGS

// OVERRIDE DEFAULT CART TEXT
$jcartconfig['text']['cart_title']				= '';		// Shopping Cart
$jcartconfig['text']['single_item']				= '';		// Item
$jcartconfig['text']['multiple_items']			= '';		// Items
$jcartconfig['text']['currency_symbol']			= 'R ';		// $
$jcartconfig['text']['subtotal']					= '';		// Subtotal

$jcartconfig['text']['update_button']				= '';		// update
$jcartconfig['text']['checkout_button']			= '';		// checkout
$jcartconfig['text']['checkout_paypal_button']	= 'Submit Order';		// Checkout with PayPal
$jcartconfig['text']['remove_link']				= '';		// remove
$jcartconfig['text']['empty_button']				= '';		// empty
$jcartconfig['text']['empty_message']				= '';		// Your cart is empty!
$jcartconfig['text']['item_added_message']		= '';		// Item added!

$jcartconfig['text']['price_error']				= '';		// Invalid price format!
$jcartconfig['text']['quantity_error']			= '';		// Item quantities must be whole numbers!
$jcartconfig['text']['checkout_error']			='';		// Your order could not be processed!

// OVERRIDE THE DEFAULT BUTTONS WITH YOUR IMAGES BY SETTING THE PATH FOR EACH IMAGE
$jcartconfig['button']['checkout']				= '';
$jcartconfig['button']['paypal_checkout']			= '';
$jcartconfig['button']['update']					= '';
$jcartconfig['button']['empty']					= '';

?>
