<?php

// require('../build/rome/Rome.php');
require('src/include-manifest.php');

// Set Build directory
$outputDir = "build";

if (!file_exists($outputDir)) {
    mkdir($outputDir, 0777, true);
}

// "Build" the javascript file
$output = fopen($outputDir . "/sidecar.js", "w");

// Start the output
if ($includeFiles) {
    foreach ($includeFiles as $file) {
        $buffer .= file_get_contents($file);
    }
}

// Write output to file
fwrite($output, $buffer);
fclose($output);

if (function_exists('exec')) {
    // Minification
    try {
        echo "\nUglifying\n";
        $minified = shell_exec('uglifyjs ' . $outputDir . "/sidecar.js");
        $minFile = fopen($outputDir . "/sidecar.min.js", "w");
        fwrite($minFile, $minified);
        fclose($minFile);
    } catch (Exception $e) {
        echo 'Caught exception: ',  $e->getMessage(), "\n";
        echo 'uglifyjs not installed';
    }

    // JSHint
    try {
        echo "\nJSHint\n";
        $errors = shell_exec('jshint ' . $outputDir . "/sidecar.js");
        print_r($errors);
    } catch (Exception $e) {
        echo 'Caught exception: ',  $e->getMessage(), "\n";
        echo 'jshint not installed';
    }
}