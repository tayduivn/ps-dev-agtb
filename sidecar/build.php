<?php
/**
 * This script is used to built the javascript associated to the sidecar framework
 *
 * It will concatenate and minify any files specified in an array in src/include-manifest.php. It will
 * also build documentation for the framework if the appropriate library is available.
 *
 *
 * REQUIREMENTS:
 *  nodejs
 *  uglifyjs
 *  jshint
 *  jsduck (ruby gem)
 *
 *
 * The variable $buildFiles is specified in src/inlcude-mainifest.php and consists of an array of the format below.
 * $buildFiles = array(
 *                      'outputFileName' => array(
 *                                                  'file1.js',
 *                                                  'file2.js'
 *                                                  )
 *                      )
 *
 **/

require('src/include-manifest.php');

// Set Build directory
$outputDir = "minified";
$errors='';

// Create build directory if it doesn't exist
if (!file_exists($outputDir)) {
    mkdir($outputDir, 0777, true);
}

if ($buildFiles) {
    foreach ($buildFiles as $groupName => $fileList) {
        $filename = $outputDir . "/". $groupName . ".js";
        $minFilename = $outputDir . "/". $groupName . ".min.js";
        $concatBuffer = '';
        $miniAndConcatBuffer = '';
        if ($fileList['toConcat']) {
            foreach ($fileList['toConcat'] as $file) {
                $concatBuffer .= file_get_contents($file);
            }
        }
        if ($fileList['toMinifyAndConcat']) {
            foreach ($fileList['toMinifyAndConcat'] as $file) {
                $miniAndConcatBuffer .= file_get_contents($file);
                $errors .= shell_exec('jshint ' . $file . ' 2>&1');
            }
        }

        // "Build" the javascript file
        $output = fopen($filename, "w");
        $outputMin = fopen($minFilename, "w");

        // Write output to temp file for minifying
        $tempFile = fopen("temp", "w");
        fwrite($tempFile, $miniAndConcatBuffer);
        fclose($tempFile);

        // Minification
        $minified = shell_exec('uglifyjs temp 2>&1');
        $minFile = fopen("temp.min", "w");
        fwrite($minFile, $minified);
        fclose($minFile);

        // Add library files to unminified
        $unminified = file_get_contents("temp");
        fwrite($output, $concatBuffer . "\n" . $unminified);
        fclose($output);

        // Add library files to minified
        $minified = file_get_contents("temp.min");
        fwrite($outputMin, $concatBuffer . "\n" . $minified);
        //fwrite($outputMin, $libBuffer . "\n" . $minified);
        fclose($outputMin);

        // Clean up
        unlink("temp");
        unlink("temp.min");
    }
}

// Generate Docs
$docs = shell_exec('jsduck src lib/sugarapi --output docs 2>&1');

// Aserts
if (file_exists("minified/sidecar.js") &&
    file_exists("minified/sidecar.min.js")
) {
    exit(0);
} else {
    exit(1);
}