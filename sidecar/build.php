<?php

// require('../build/rome/Rome.php');
require('src/include-manifest.php');

// Set Build directory
$outputDir = "build";

if (!file_exists($outputDir)) {
    mkdir($outputDir, 0777, true);
}

$libBuffer = '';

// "Build" the javascript file
$output = fopen($outputDir . "/sidecar.js", "w");

// Start 3rd party lib output
if ($libraryFiles) {
    foreach ($libraryFiles as $file) {
        $libBuffer .= file_get_contents($file);
    }
}

// Start the output
if ($includeFiles) {
    foreach ($includeFiles as $file) {
        $buffer .= file_get_contents($file);

        // JSHint
        echo "\nRunning JSHint on " . $file . "\n";
        $errors = shell_exec('jshint ' . $file);
        print_r($errors);
    }
}

// Write output to temp file for minifying
$tempFile = fopen("temp", "w");
fwrite($tempFile, $buffer);
fclose($tempFile);

if (function_exists('exec')) {
    // Minification
    echo "\nUglifying\n";
    $minified = shell_exec('uglifyjs temp');
    $minFile = fopen("temp.min", "w");
    fwrite($minFile, $minified);
    fclose($minFile);

    // Generate Docs
    echo "\nGenerating Documentation\n";
    $docs = shell_exec('jsduck src lib/sugarapi --output docs');
}

// Add library files to unminified
$unminified = file_get_contents("temp");
fwrite($output, $libBuffer . "\n" . $unminified);
fclose($output);

// Add library files to minified
$minified = file_get_contents("temp.min");
$outputMin = fopen($outputDir . "/sidecar.min.js", "w");
fwrite($outputMin, $libBuffer . "\n" . $minified);
fclose($outputMin);

// Clean up
unlink("temp");
unlink("temp.min");