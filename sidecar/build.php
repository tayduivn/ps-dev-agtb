<?php

// require('../build/rome/Rome.php');
require('src/include-manifest.php');

// Set Build directory
$outputDir = "build";

// Create build directory if it doesn't exist
if (!file_exists($outputDir)) {
    mkdir($outputDir, 0777, true);
}

$libBuffer = '';

// "Build" the javascript file
$output = fopen($outputDir . "/sidecar.js", "w");
$outputMin = fopen($outputDir . "/sidecar.min.js", "w");

// Save library files into buffer
if ($libraryFiles) {
    foreach ($libraryFiles as $file) {
        $libBuffer .= file_get_contents($file);
    }
}

// Start framework files into buffer
if ($includeFiles) {
    foreach ($includeFiles as $file) {
        $buffer .= file_get_contents($file);

        // JSHint
        $errors .= shell_exec('jshint ' . $file . ' 2>&1');
    }
}

// Write output to temp file for minifying
$tempFile = fopen("temp", "w");
fwrite($tempFile, $buffer);
fclose($tempFile);

// Minification
$minified = shell_exec('uglifyjs temp 2>&1');
$minFile = fopen("temp.min", "w");
fwrite($minFile, $minified);
fclose($minFile);

// Generate Docs
$docs = shell_exec('jsduck src lib/sugarapi --output docs 2>&1');

// Add library files to unminified
$unminified = file_get_contents("temp");
fwrite($output, $libBuffer . "\n" . $unminified);
fclose($output);

// Add library files to minified
$minified = file_get_contents("temp.min");
fwrite($outputMin, $libBuffer . "\n" . $minified);
//fwrite($outputMin, $libBuffer . "\n" . $minified);
fclose($outputMin);

// Clean up
unlink("temp");
unlink("temp.min");

// Aserts
if (file_exists("build/sidecar.js") &&
    file_exists("build/sidecar.min.js")
) {
    exit(0);
} else {
    exit(1);
}