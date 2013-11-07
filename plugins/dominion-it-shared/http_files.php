<?php
/*
  Additional changes made by Johannes Pretorius
  get_file  : can have optional query parameters send with url
  
  send_file : added mime types for html files.
  
  made echo = error_log
  
  send_file function : list of fields and there data (key is fieldname)  : Added Johannes Pretorius 23 July 2011
     (sending mulit fields with file)

  get_file : addded https  support 	 
*/
/*
 * Upload and download files over HTTP within PHP code
 *
 * PHP versions 4 and 5
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author 	  Nashruddin Amin <me@nashruddin.com>
 * @copyright Nashruddin Amin 2008
 * @license	  GNU General Public License 3.0
 * @version   1.1
 */
 
/**
 * get a file from specified url
 *
 * @param string $remote url of the file
 * @param string $local  save contents to this file
 *
 * @return boolen true on success, false on failure.
 */
function get_file($remote, $local,$addQuery = false,$use_ssl=false)
{
    //error_log($remote);
	/* get hostname and path of the remote file */
	$host = parse_url($remote, PHP_URL_HOST);
	if ($addQuery == true) { 
	  $path = parse_url($remote, PHP_URL_PATH )."?".parse_url($remote, PHP_URL_QUERY  );
	} else {
	  $path = parse_url($remote, PHP_URL_PATH );
    }	
	
	/* prepare request headers */
	$reqhead = "GET $path HTTP/1.1\r\n"
			 . "Host: $host\r\n"
			 . "Connection: Close\r\n\r\n";
	/* open socket connection to remote host on port 80 */
	if ($use_ssl == true) {
	     $sslhost = "ssl://".$host;
	   $fp = fsockopen($sslhost, 443, $errno, $errmsg, 30);
	   //error_log('ssl');
	} else { 
	  $fp = fsockopen($host, 80, $errno, $errmsg, 30);
	  //error_log('gewoon');
	}  
	
	/* check the connection */
	if (!$fp) {
		error_log("Cannot connect to $host!\n");
		return false;
	}
	
	/* send request */
	fwrite($fp, $reqhead);
	/* read response */
	$res = "";
	while(!feof($fp)) {
		$res .= fgets($fp, 4096);
		error_log($res);
	}		
	fclose($fp);
	
	/* separate header and body */
	$neck = strpos($res, "\r\n\r\n");
	$head = substr($res, 0, $neck);
	$body = substr($res, $neck+4);

	/* check HTTP status */
	$lines = explode("\r\n", $head);
	preg_match('/HTTP\/(\\d\\.\\d)\\s*(\\d+)\\s*(.*)/', $lines[0], $m);
	$status = $m[2];

	if ($status == 200) {
		file_put_contents($local, $body);
		return(true);
	} else {
		return(false);
	}
}

/**
 * upload a file to server
 *
 * @param string $fname   	name of file to upload
 * @param string $handler 	server-side script to handle the uploading file
 * @param string $filefieldname		name of the form's input (<input type="file" />)
 * @param array $fields_and_data = list of fields and there data (key is fieldname)  : Added Johannes Pretorius 23 July 2011
 * @return boolean true on success, false on failure
 */
function send_file($fname, $handler, $filefieldname,$fields_and_data =array())
{
	/* check if file exists */
	if (!file_exists($fname)) {
		error_log("file not found. $fname");
		return false;
	}

	/* get file's extension */
	preg_match("/\.([^\.]+)$/", $fname, $matches);
	$ext = $matches[1];
	
	/* guess mimetype from file's extension 
	   please add some more mimetypes here */
	switch(strtolower($ext)) {
		case "doc":
			$mime = "application/msword";
			break;
		case "jpeg":
		case "jpg":		
		case "jpe":
			$mime = "image/jpeg";
			break;
		case "gif":
			$mime = "image/gif";
			break;
		case "pdf":
			$mime = "application/pdf";
			break;
		case "png":
			$mime = "image/png";
			break;
		case "html":
		case "htm":
		case "txt":
		default:
			$mime = "text/plain";
			break;
	}		
	
	/* get hostname and path of remote script */
	$host = parse_url($handler, PHP_URL_HOST);
	$path = parse_url($handler, PHP_URL_PATH);
	
	/* setup request header and body */
	$boundary = "---------" . str_replace(' ','',(str_replace(".", "", microtime())));
	$reqbody  ='';
	foreach ($fields_and_data as $fldname => $flddata){
			    $reqbody  .= "--$boundary\r\n";
				$reqbody  .= "Content-Disposition: form-data; name=\"$fldname\"\r\n";
				$reqbody  .="\r\n";
				$reqbody  .="$flddata\r\n";
			  }	
	$reqbody  .= "--$boundary\r\n"
			  . "Content-Disposition: form-data; name=\"$filefieldname\"; filename=\"$fname\"\r\n"
			  . "Content-Type: $mime\r\n\r\n"
			  . file_get_contents($fname) . "\r\n"
			  . "--$boundary--\r\n";
			  
	$bodylen  = strlen($reqbody);
	$reqhead  = "POST $path HTTP/1.1\r\n"
			  . "Host: localhost\r\n"
			  . "Content-Type: multipart/form-data; boundary=$boundary\r\n"
			  . "Content-Length: $bodylen\r\n"
			  . "Connection: Close\r\n\r\n";
	
	/* open socket connection to remote host on port 80 */
	$fp = fsockopen($host, 80, $errno, $errmsg, 30);
	
	/* check the connection */
	if (!$fp) {
		error_log("Cannot connect to $host!\n");
		return false;
	}
	
	/* send request */
	fwrite($fp, $reqhead);
	fwrite($fp, $reqbody);
	file_put_contents('c:\Program Files\Apache Software Foundation\Apache2.2\htdocs\GetSimple_3.1B_r520\totsmaar.txt',$reqhead.$reqbody);
	
	/* read response */
	$res = "";
	while(!feof($fp)) {
		$res .= fgets($fp, 4096);
	}		
	fclose($fp);

	/* separate header and body */
	$neck = strpos($res, "\r\n\r\n");
	$head = substr($res, 0, $neck);
	$body = substr($res, $neck+4);

	/* check HTTP status */
	$lines = explode("\r\n", $head);
	preg_match('/HTTP\/(\\d\\.\\d)\\s*(\\d+)\\s*(.*)/', $lines[0], $m);
	$status = $m[2];
	
	if ($status == 200) {
		//error_log("SUKSES");
		return(true);
		
	} else {
	//error_log("DAMMIT : $head" );
		return(false);
	}
}
