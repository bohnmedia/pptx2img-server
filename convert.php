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
    $resolution = isset($_POST["resolution"]) ? $_POST["resolution"] : 150;
    $format = isset($_POST["format"]) ? $_POST["format"] : "jpeg";
    $quality = isset($_POST["quality"]) ? (int)$_POST["quality"] : 80;
    if ($format === "jpg") $format = "jpeg";

    // Create unique tmp directory for file output
    $tmpDir = sprintf('%s/pptx2img-%s', sys_get_temp_dir(), md5(mt_rand()));
    mkdir($tmpDir);

    // Convert pptx to pdf
    putenv('HOME=' . __DIR__);
    exec("/usr/bin/libreoffice --headless --impress --convert-to pdf " . $inputFile . " --outdir " . $tmpDir);

    // Get filelist of output directory
    $files = glob($tmpDir . '/*');

    // Convert pdf to jpeg
    $outputFile = sprintf('%s/output.png', $tmpDir);
    $image = new Imagick();
    $image->setResolution($resolution, $resolution);
    $image->readImage($files[0]);
    $image->setImageFormat($format);
    if ($format === "jpeg") {
        $image->setImageCompression(Imagick::COMPRESSION_JPEG);
        $image->setImageCompressionQuality($quality);
    }
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
