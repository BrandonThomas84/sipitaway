<?php

function dcart_CreateNewOrderTrackingFile(){
  global $dominion_jcart_transaksies_leer;
  global $dominion_jcart_path;
  $xml = @new DominionSimpleXML('<?xml version="1.0" encoding="UTF-8"?><transaksies></transaksies>');
  $xml->XMLSave($dominion_jcart_transaksies_leer.$dominion_jcart_path);
}
function dcart_createOrder($dcartcustomer,$cart){
  global $dominion_jcart_path;
  $transaksie_beheer_leer = 'trns_control.cnt';
 // global $dominion_jcart_transaksies_leer;
  //if (is_file($dominion_jcart_path.$dominion_jcart_transaksies_leer)) {
//    $xml = dcart_CreateNewOrderTrackingFile();
//  } else {
   //$xml = getDominionXML($dominion_jcart_path.$transaksies_leer);
  //} 
  $fp = fopen($dominion_jcart_path.$transaksie_beheer_leer, "r+");
  if (flock($fp, LOCK_EX)) {
    $neworderN = trim(fgets($fp));
    ftruncate($fp,0);
	rewind($fp);
	if ($neworderN === false){
           return FALSE;
    } 
	$neworderN++;
    fwrite($fp, $neworderN);
    flock($fp, LOCK_UN);
  } else {
    fclose($fp);
    return false;
  }
  fclose($fp);
  
}
 
?>