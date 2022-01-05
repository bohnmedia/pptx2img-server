<?php

    //Domain
    $domain = "my.server.com";

    // Input and output file
    $inputFile = "/var/www/html/test/input.pptx";
    $outputFile = "/var/www/html/test/output.jpg";

    // PostFields
    $postFields = ["pptx" => new CURLFile($inputFile)];

    // Additional settings (optional)
    $postFields["format"] = "jpeg"; // optional: "jpeg" or "png" (server default: "jpeg")
    $postFields["quality"] = 80;    // optional: only needed, when format is "jpeg" (server default: 80)
    $postFields["dpi"] = 150;       // optional: (server default: 150)

    // Username and passwort (optional)
    $username = "username";
    $password = "password";

    // cURL request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, sprintf("https://%s/convert.php", $domain));
    if (isset($username) && isset($password)) curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $imgData = curl_exec($ch);
    curl_close($ch);

    // Write response to file
    file_put_contents($outputFile, $imgData);
