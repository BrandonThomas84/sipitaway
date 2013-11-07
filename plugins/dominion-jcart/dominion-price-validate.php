<?php

function validatePrices($path,$cat_file,$items){
  $answ = TRUE;
  $xmlCat = getDominionXML($path.$cat_file);
  $laastLeer = '';
  $laasteCat = '';
   foreach ($items as $item) {
       $p_id = $item['p_id'];
       $c_id = $item['c_id'];
       $p_name = $item['name'];
       $p_price = $item['price'];
       $p_qty = $item['qty'];
       $i_total = $item['subtotal'];
       
       if (!is_numeric($p_qty)){
             $answ = FALSE;
             break;
       }
       if (!is_numeric($p_price)){
             $answ = FALSE;
             break;
       }       
       if ($laasteCat <> $c_id) {
           //to not try and search only if needed
           $catSoek = $xmlCat->xpath("//id[@group_id=\"$c_id\"]"); 
           if (count($catSoek) <= 0) {
//           error_log("Cat nie gevind");
             $answ = FALSE;
             break;
           }
           $atrCat = $catSoek[0]->attributes();
           $productFile =  $atrCat['group_product_file']; 
           $laasteCat = $c_id;
       }
       if ($productFile <> $laastLeer) {
         //to not try and search only if needed
         $xmlProd = getDominionXML($path.$productFile.".xml");
         $laastLeer = $productFile;
       }
       $prodSoek = $xmlProd->xpath("//id[@product_id=\"$p_id\"]");
       if (count($prodSoek) <= 0) {
         //error_log("Produk nie gevind");
         $answ = FALSE;
         break;
       }
       $price = $prodSoek[0]->price;
       //error_log("Prys ONTLEED : LP : $price,AP : $p_price,AQ : $p_qty");
       if ($p_price !=  $price) {
         //error_log("Prys nie reg : LP : $price,AP : $p_price,AQ : $p_qty");
         $answ = FALSE;
         break;
       }
       $price = (float)((float)$price * (float)$p_qty);
       if ($i_total !=  $price) {
         //error_log("Ttoaal Prys nie reg : LP : $price,AP : $i_total,AQ : $p_qty");
         $answ = FALSE;
         break;
       }
       
   }
   return $answ;
  
  
}
?>