<?php

// require('../build/rome/Rome.php');
require('src/include-manifest.php');

// Set Build directory
$outputDir = "build";

if (!file_exists($outputDir)) {
    mkdir($outputDir, 0777, true);
}

// "Build" the javascript file
$output = fopen("build/sidecar.js", "w");

// Start the output
if ($includeFiles) {
    foreach ($includeFiles as $file) {
        $buffer .= file_get_contents($file);
    }
}

// Write output to file
fwrite($output, $buffer);
fclose($output);

// Minify if possible.
if (function_exists('exec')) {
    exec('uglifyjs ' . $outputDir . "/sidecar.js");
    exec('jshint ' . $outputDir . "/sidecar.js");
}