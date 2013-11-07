<?php
function isPluginEnabled($pluginid){
  return file_exists(GSDATAOTHERPATH."$pluginid-enabled");
}

function EnablePlugin($pluginid){
 if (!isPluginEnabled($pluginid)){
   $fH = fopen(GSDATAOTHERPATH."$pluginid-enabled",'x');
   fwrite($fH,'enabled');
   fclose($fH);
 }
}

function DisablePlugin($pluginid){
 if (isPluginEnabled($pluginid)){
   unlink(GSDATAOTHERPATH."$pluginid-enabled");
 }
}

class DominionSimpleXML extends SimpleXMLElement{   
  public function addCData($cdata_text){   
    //obtained form  get-simple source
   $node= dom_import_simplexml($this);   
   $no = $node->ownerDocument;   
   $node->appendChild($no->createCDATASection($cdata_text));   
  } 
  
  public function updateCData($cdata_text){   
   $node= dom_import_simplexml($this);   
   $no = $node->ownerDocument;   
   $node->removeChild($node->firstChild);   
   $node->appendChild($no->createCDATASection($cdata_text));   
  } 
  
  public function removeCurrentChild(){
    $dom=dom_import_simplexml($this);
    $dom->parentNode->removeChild($dom);
  }
  
  public function XMLSave($file){
     //For future use if added funcitonaily is required
     $this->asXML($file);
  }

} 

function getDominionXML($file) {
    //obtained form  get-simple source
	$xml = @file_get_contents($file);
	$data = simplexml_load_string($xml, 'DominionSimpleXML', LIBXML_NOCDATA);
	return $data;
}

function getLanguageFile($plugin_name,$language,$basepath="") {
  if ($basepath == "") {
    return GSPLUGINPATH."dominion-it-shared/lang/$plugin_name/$language".'.php';
  } else {
    return $basepath."dominion-it-shared/lang/$plugin_name/$language".'.php';
  }  
}

function availableLanguages($plugin_name,$currLanguage = ''){
  /*
    This will return list of options for select box.
  */
  $files = scandir(GSPLUGINPATH."dominion-it-shared/lang/$plugin_name/" );
  $hoev = count($files);
  for ($x = 0;$x < $hoev ; $x++) {
    $file = $files[$x];
    if (($file <> '.') && ($file <> '..') && ($file <> '.htaccess')) {
      //is file
      $file = substr($file,0,strpos($file,'.'));
      if ($currLanguage == $file) {
        echo "<option value='$file' selected='selected'>$file</option>";
      } else {
        echo "<option value='$file'>$file</option>";
      }  
    }
  }
}

function getBaseSiteURLandAddChar(&$baseURL,&$addChar){
//get the base url for normal or FANCY URL's.. if fancy it will remove all added values and keep url string for normal it will insure the id gets kept
  $baseURL = $_SERVER["REQUEST_URI"];
  if (strpos($baseURL,'?id=') !== false) {
    $id = $_GET['id'];
    $baseURL = preg_replace("/\?id=.*/i","",$baseURL); 
    $baseURL .= "?id=$id";
    $addChar = '&';
  }  else {
    $addChar = '?';  
    $baseURL = preg_replace("/\?.*/i","",$baseURL); 
  }
}

function bouDatumCombos($dateValue,$targetDatBlock,$targetForm,$monthLanguageArray = null) {
  /*
  Build 3 combox that has the date time of current given date value
  And a hidden field where the complete date ACTUALLY gets stored to. ($targetDatBlock)
  */
  $jaar = date('Y',strtotime($dateValue));
  $maand = date('m',strtotime($dateValue));
  $dag = date('d',strtotime($dateValue));

  echo "<input type='hidden' value='$dag-$maand-$jaar' name='$targetDatBlock' />";
  echo "<select onchange='setDTForHidField($targetForm.$targetDatBlock,$targetForm.dt_year,$targetForm.dt_month,$targetForm.dt_day); stelMaandDae($targetForm.dt_year,$targetForm.dt_month,$targetForm.dt_day);' name='dt_month' id='dt_month'>";
  for ($xM=1;$xM <=  12;$xM++) {
    $mW =($xM < 10)?'0'.$xM:$xM;
    $selT = ($xM == $maand)?"selected='selected'":' ';
    if ($monthLanguageArray == null) {
      $Mt = date('F',strtotime("$xM/28/2000"));
    } else {
      $Mt = $monthLanguageArray[date('n',strtotime("$xM/28/2000"))];
    }    

    echo "<option value='$mW' $selT>$Mt</option>";
  }
  echo "</select>";
  echo "<select onchange='setDTForHidField($targetForm.$targetDatBlock,$targetForm.dt_year,$targetForm.dt_month,$targetForm.dt_day);' name='dt_day' id='dt_day'>";
  for ($xM=1;$xM <=  31;$xM++) {
    $mW =($xM < 10)?'0'.$xM:$xM;
    $selT = ($xM == $dag)?"selected='selected'":' ';
    echo "<option value='$mW' $selT>$mW</option>";
  }
  echo "</select>";
  echo "<select onchange='setDTForHidField($targetForm.$targetDatBlock,$targetForm.dt_year,$targetForm.dt_month,$targetForm.dt_day); stelMaandDae($targetForm.dt_year,$targetForm.dt_month,$targetForm.dt_day);' name='dt_year' id='dt_year'>";
  $huidigeJaar = date('Y');
  for ($xM=1900;$xM <=  $huidigeJaar;$xM++) {
    $mW =$xM;
    $selT = ($xM == $jaar)?"selected='selected'":' ';
    echo "<option value='$mW' $selT>$mW</option>";
  }
  echo "</select>";  
  

}

function getAllAvailableSlugs(){
 $dataPad = GSDATAPATH."pages/";
  $files = scandir($dataPad);
  $hoev = count($files);
  
  for ($x = 0;$x < $hoev ; $x++) {
    $file = $files[$x];
    if (($file <> '.') && ($file <> '..') && ($file <> '.htaccess')) {
      $Slugxml = getDominionXML($dataPad.$file);
      $dataBlok = $Slugxml->xpath("/item/url");
      $slugLys[] = $dataBlok[0][0];
    }
  }
  return $slugLys;
}

function removeDirectory($dir) { 
   /*
     Use with CARE, This deletes EVERYTHING inside the directory you give. Give the wrong dir.And
	 you will be sorry !!
   */
    $files = scandir( $dir ); 
	
    foreach( $files as $file ){ 
	    if (($file == '.') || ($file == '..')) { continue; }
		
        if( is_dir( $dir.'/'.$file )) {
		  removeDirectory( $dir.'/'.$file ); 
		 
		} else  {
            unlink( $dir.'/'.$file ); 
		
	    }		
    } 
    
    if (is_dir($dir)) rmdir( $dir ); 
    
} 

function cloneDirectoryStructure($sourceDir,$targetBaseDir){
  /*
    Will create the directory structure (subdirs etc) as from source.
  */
    $dirs = scandir( $sourceDir ); 
	
    foreach( $dirs as $dir ){ 
	  if (($dir == '.') || ($dir == '..')) { continue; }
	  //echo "SRC: ".$sourceDir."/".$dir.'<br/>';
	  if (is_dir($sourceDir."/".$dir)) {
	     cloneDirectoryStructure($sourceDir."/".$dir,$targetBaseDir."/".$dir);
	  }
	 // echo "TRG: ".$targetBaseDir."/".$dir.'<br/>';
	  if (!is_dir($targetBaseDir."/".$dir)) {
	    mkdir($targetBaseDir."/".$dir,0777,true);
	  }
	}
  
}
function removeFilesofExtinDirectory($dir,$fileext_to_delete='',$include_subdirs=false) { 
   /*
     will delete all files of extention type in directory.
	 NOTE : Passing no extention will delete everything in that directory.
   */
    $files = scandir( $dir ); 
	
    foreach( $files as $file ){ 
	    if (($file == '.') || ($file == '..')) { continue; }
		if (is_dir($file) && $include_subdirs == true) { removeFilesofExtinDirectory($dir.'/'.$file,$fileext_to_delete,true); }
		
		if ($fileext_to_delete == '') { if (is_file($file)) { unlink($file); continue; } }
		
		
		$ext = pathinfo($file,PATHINFO_EXTENSION);
		if ($ext == $fileext_to_delete) {
		  if (is_file($file)) {
		    unlink($file);
		  }	
		}
    } 
    
} 

function add_remove_SlashAtEndofPathorURL($pathorURL,$addSlash = true){
    $lastC = substr($pathorURL, strlen($pathorURL)-1, 1);
    if ($lastC != '/' and $lastC != '\\') {
        $pathorURL .= '/';
    }
    return $pathorURL;
}

/*
code from php.net : jerome at buttered-cat dot com
changed constructor to or accept a target folder string or a array of folders to zip
*/
class ZipFolder {
    protected $zip;
    protected $root;
    protected $ignored_names;
    
    function __construct($file, $folder, $ignored=null) {
        $this->zip = new ZipArchive();
        $this->ignored_names = is_array($ignored) ? $ignored : $ignored ? array($ignored) : array();
        if ($this->zip->open($file, ZIPARCHIVE::CREATE)!==TRUE) {
            throw new Exception("cannot open <$file>\n");
        }
		if (is_array($folder)) {
		  foreach ($folder as $folderentry) {
			$folderentry = substr($folderentry, -1) == '/' ? substr($folderentry, 0, strlen($folderentry)-1) : $folderentry;
		    if(strstr($folderentry, '/')) {
				 $this->root = substr($folderentry, 0, strrpos($folderentry, '/')+1);
				 $folderentry = substr($folderentry, strrpos($folderentry, '/')+1);
			}
			$this->zip($folderentry);		  
		  }
		} else {
          $folder = substr($folder, -1) == '/' ? substr($folder, 0, strlen($folder)-1) : $folder;
          if(strstr($folder, '/')) {
             $this->root = substr($folder, 0, strrpos($folder, '/')+1);
             $folder = substr($folder, strrpos($folder, '/')+1);
          }
          $this->zip($folder);
		}  
        $this->zip->close();
    }
	
    
    function zip($folder, $parent=null) {
        $full_path = $this->root.$parent.$folder;
        $zip_path = $parent.$folder;
        $this->zip->addEmptyDir($zip_path);
        $dir = new DirectoryIterator($full_path);
        foreach($dir as $file) {
            if(!$file->isDot()) {
                $filename = $file->getFilename();
                if(!in_array($filename, $this->ignored_names)) {
                    if($file->isDir()) {
                        $this->zip($filename, $zip_path.'/');
                    }
                    else {
                        $this->zip->addFile($full_path.'/'.$filename, $zip_path.'/'.$filename);
                    }
                }
            }
        }
    }
	
   
} //end ZipFolder class

?>