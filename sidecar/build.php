<?php

require('../build/Rome.php');

$rome = new Rome();

// Set Build directory
$rome->setBuildDir("build");

// "Build" the javascript file
$output = fopen("build/sidecar.js", "w");

// Load the manifest file
$manifest = fopen("include.php", "r");

// Write output to file
fwrite($buffer, $output);
fclose($output);