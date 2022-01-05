<?php

	$url = "https://www.servername.de/convert.php";
	$inputFile = "/var/www/html/test/test.pptx";
	$outputFile = "/var/www/html/test/test.jpg";

	$pptx = new CURLFile($inputFile);
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, ['pptx' => $pptx]);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$jpg = curl_exec($ch);	
	curl_close($ch);
	
	file_put_contents($outputFile, $jpg);
