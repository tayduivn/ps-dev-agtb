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

        // JSHint
        echo "\nRunning JSHint on ". $file . "\n";
        $errors = shell_exec('jshint ' . $file);
        print_r($errors);
    }
}

// Write output to file
fwrite($output, $buffer);
fclose($output);

if (function_exists('exec')) {
    // Minification
    echo "\nUglifying\n";
    $minified = shell_exec('uglifyjs ' . $outputDir . "/sidecar.js");
    $minFile = fopen($outputDir . "/sidecar.min.js", "w");
    fwrite($minFile, $minified);
    fclose($minFile);

    // Generate Docs
    echo "\nGenerating Documentation\n";
    $docs = shell_exec('jsduck src --output docs');
}