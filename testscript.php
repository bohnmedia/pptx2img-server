<?php

    $domain = "server.eintragen.de";
	$inputFile = "/var/www/html/test/test.pptx";
	$outputFile = "/var/www/html/test/test.jpg";
    $format = "jpeg"; // optional: "jpeg" or "png" (server default: "jpeg")
    $quality = 80;    // optional: only needed, when format is "jpeg" (server default: 80)
    $dpi = 150;       // optional: (server default: 150)
	$url = sprintf("https://%s/convert.php", $domain);
	
	$pptx = new CURLFile($inputFile);
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'pptx' => $pptx,
        'format' => $format,   // optional
        'quality' => $quality, // optional
        'dpi' => $dpi          // optional
    ]);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$jpg = curl_exec($ch);
	curl_close($ch);
	
	file_put_contents($outputFile, $jpg);
