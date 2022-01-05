<?php

	// Check for file
	if (!isset($_FILES["pptx"]) || !$_FILES["pptx"]["tmp_name"]) {
		http_response_code(400);
		header("Content-Type: text/plain");
		echo "ppxt file missing";
		exit();
	}

	// Config
	$inputFile = $_FILES["pptx"]["tmp_name"];
	$outputFormat = 'pdf';

	// Create unique tmp directory for file output
	$tmpDir = sprintf('%s/pptx2img-%s', sys_get_temp_dir(), md5(mt_rand()));
	mkdir($tmpDir);

	// Convert pptx to pdf
	putenv('HOME=' . __DIR__);
	exec("/usr/bin/libreoffice --headless --impress --convert-to pdf " . $inputFile . " --outdir " . $tmpDir);

	// Get filelist of output directory
	$files = glob($tmpDir . '/*');
	
	// Convert pdf to png
	$outputFile = sprintf('%s/output.png', $tmpDir);
	$image = new Imagick();
	$image->setResolution(150, 150);
	$image->readImage($files[0]);
	$image->setImageFormat('jpeg');
	$image->setImageCompression(Imagick::COMPRESSION_JPEG);    
	$image->setImageCompressionQuality(100);    
	$image->writeImage($outputFile);
	$image->clear(); 
	$image->destroy();

	// Output image
	header('Content-Type: ' . mime_content_type($outputFile));
	header('Content-Length: ' . filesize($outputFile));
	readfile($outputFile);
	
	// Delete tmp directory
	unlink($outputFile);
	foreach ($files as $file) {
		unlink($file);
	}
	rmdir($tmpDir);