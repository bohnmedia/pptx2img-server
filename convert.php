<?php

    // Config
    $fileCacheTime = 60 * 60 * 24; // 1 day
    $protocol = 'https'; // Protocol for image file download
    $resolution = 150; // DPI
    $format = "jpeg"; // "jpeg" or "png"
    $quality = 80; // 1 - 100

    // Variables
    $inputFile = $_FILES["pptx"]["tmp_name"];
    $resolution = isset($_POST["resolution"]) ? $_POST["resolution"] : $resolution;
    $format = isset($_POST["format"]) ? $_POST["format"] : $format;
    if ($format === "jpg") $format = "jpeg";
    $fileExtension = $format;
    if ($fileExtension === "jpeg") $fileExtension = "jpg";
    $quality = isset($_POST["quality"]) ? (int)$_POST["quality"] : $quality;
    $tmpDir = sprintf('%s/pptx2img-%s', sys_get_temp_dir(), md5(mt_rand()));
	$outputDir = sprintf('%s/output', __DIR__);
    $outputFile = sprintf('output/%s.%s', md5(time(). rand()), $fileExtension);
    $absOutputFile = sprintf('%s/%s', __DIR__, $outputFile);
    $outputFileLink = sprintf('%s://%s/%s', $protocol, $_SERVER["SERVER_NAME"], $outputFile);
	
    // Create output dir
    if (!is_dir($outputDir)) {
        mkdir($outputDir);
    }

    // Delete old files from output directory
    $files = glob(__DIR__ . '/output/*');
    $cacheTo = time() - $fileCacheTime;
    foreach ($files as $file) {
        if (filectime($file) < $cacheTo) unlink($file);
    }

    // Change home dir
    putenv('HOME=' . __DIR__);

    // Check for file
    if (!isset($_FILES["pptx"]) || !$_FILES["pptx"]["tmp_name"]) {
        http_response_code(400);
        header("Content-Type: text/plain");
        echo "ppxt file missing";
        exit();
    }

    // Create unique tmp directory for file output
    mkdir($tmpDir);

    // Convert pptx to xml
    exec("/usr/bin/libreoffice --headless --impress --convert-to xml " . $inputFile . " --outdir " . $tmpDir);

    // Get filelist of output directory
    $files = glob($tmpDir . '/*');

    // Get xml data
    $xmlData = file_get_contents($files[0]);

    // Remove page number
    $xmlData = preg_replace("/<text:page-number>.*?<\/text:page-number>/", "", $xmlData);

    // Find text
    $text = "";
    preg_match_all("/<text:p[^\/].*?>(.*?)<\/text:p>/", $xmlData, $matches);
    foreach ($matches[1] as $match) $text .= " " . strip_tags($match);

    // Trim and remove multiple whitespaces
    $text = preg_replace("/\s+/", " ", trim(str_replace(chr(160), ' ', str_replace(chr(194), ' ', $text))));

    // Delete tmp files
    foreach ($files as $file) unlink($file);

    // Convert pptx to pdf
    exec("/usr/bin/libreoffice --headless --impress --convert-to pdf " . $inputFile . " --outdir " . $tmpDir);

    // Get filelist of output directory
    $files = glob($tmpDir . '/*');

    // Convert pdf to jpeg
    $image = new Imagick();
    $image->setResolution($resolution, $resolution);
    $image->readImage($files[0]);
    $image->setImageFormat($format);
    if ($format === "jpeg") {
        $image->setImageCompression(Imagick::COMPRESSION_JPEG);
        $image->setImageCompressionQuality($quality);
    }
    if ($format === "png") {
        $image->setImageCompression(Imagick::COMPRESSION_ZIP);
    }
    $image->writeImage($absOutputFile);
    $image->clear(); 
    $image->destroy();

    // Delete tmp files
    foreach ($files as $file) unlink($file);

    // Delete tmp directory
    rmdir($tmpDir);

    // Prepare output string
    $output = json_encode([
        'text' => $text,
        'image' => $outputFileLink
    ]);

    // Output
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Length: ' . strlen($output));
    echo $output;
