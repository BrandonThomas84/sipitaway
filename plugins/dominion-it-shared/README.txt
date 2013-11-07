Upload and Download Files Over HTTP Within PHP
==============================================

These functions used to upload and download files over HTTP. It opens socket 
connection to the remote host and make HTTP requests. Use these functions to 
easily upload and download files within your PHP code.

Author:  Nashruddin Amin <me@nashruddin.com>
License: GPL
Version: 1.1
Website: http://www.nashruddin.com

Copyright (c) 2008 Nashruddin Amin

Features
--------
* No Dependencies
* Upload and download any files
* Easy to use
* Easy to customize

Requirements
------------
* PHP 4 or 5

Usage
-----
1. download file http://www.example.com/path/spec.pdf and save to C:/tmp/spec2.pdf

	<?php
	$remote = "http://www.example.com/path/spec.pdf";
	$local  = "C:/tmp/spec2.pdf";
	
	$res = get_file($remote, $local);
	
	if ($res) {
		echo 'done.';
	} else {
		echo 'something went wrong.';
	}
	?>

2. Simulate HTML form to upload a file

	<?php
	/*
	 * the code below simulate HTML form like this:
	 *
	 * <form enctype="multipart/form-data" action="http://www.example.com/upload.php" method="post">
	 * <input type="file" name="image">
	 * <input type="submit">
	 * </form>
	 *
	 * the file http://www.example.com/upload.php should be able to process the incoming file. 
	 * maybe something like this:
	 *
	 * <?php
	 * move_uploaded_file($_FILES['image']['tmp_name'], '/var/www/image/uploaded-file.jpg');
	 * ?>
	 */
	$filename = 'C:/tmp/myphoto.jpg';
	$handler  = 'http://www.example.com/upload.php';
	$field	  = 'image';
	
	$res = send_file($filename, $handler, $field);
	
	if ($res) {
		echo 'done.';
	} else {
		echo 'something went wrong.';
	}
	?>

Contact
-------
Please send comments and bug reports to me@nashruddin.com.
