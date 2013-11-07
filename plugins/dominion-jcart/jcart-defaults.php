<?php

// JCART v1.1
// http://conceptlogic.com/jcart/

// DEFAULT CART TEXT USED IF NOT OVERRIDDEN IN jcart-config.php
// DEFAULTS MUST BE AVAILABLE TO jcart.php AND jcart-javascript.php
// INCLUDED AS A SEPARATE FILE TO SIMPLIFY USER CONFIG

if (!$jcartconfig['path']) die('The path to jCart isn\'t set. Please see <strong>jcart-config.php</strong> for more info.');

if (!$jcartconfig['text']['cart_title']) $jcartconfig['text']['cart_title']							= 'Shopping Cart';
if (!$jcartconfig['text']['single_item']) $jcartconfig['text']['single_item']						= 'Item';
if (!$jcartconfig['text']['multiple_items']) $jcartconfig['text']['multiple_items']					= 'Items';
if (!$jcartconfig['text']['currency_symbol']) $jcartconfig['text']['currency_symbol']				= '$';
if (!$jcartconfig['text']['subtotal']) $jcartconfig['text']['subtotal']								= 'Subtotal';

if (!$jcartconfig['text']['update_button']) $jcartconfig['text']['update_button']					= 'update';
if (!$jcartconfig['text']['checkout_button']) $jcartconfig['text']['checkout_button']				= 'checkout';
if (!$jcartconfig['text']['checkout_paypal_button']) $jcartconfig['text']['checkout_paypal_button']	= 'Checkout with PayPal';
if (!$jcartconfig['text']['remove_link']) $jcartconfig['text']['remove_link']						= 'remove';
if (!$jcartconfig['text']['empty_button']) $jcartconfig['text']['empty_button']						= 'empty';
if (!$jcartconfig['text']['empty_message']) $jcartconfig['text']['empty_message']					= 'Your cart is empty!';
if (!$jcartconfig['text']['item_added_message']) $jcartconfig['text']['item_added_message']			= 'Item added!';

if (!$jcartconfig['text']['price_error']) $jcartconfig['text']['price_error']						= 'Invalid price format!';
if (!$jcartconfig['text']['quantity_error']) $jcartconfig['text']['quantity_error']					= 'Item quantities must be whole numbers!';
if (!$jcartconfig['text']['checkout_error']) $jcartconfig['text']['checkout_error']					= 'Your order could not be processed!';

?>
