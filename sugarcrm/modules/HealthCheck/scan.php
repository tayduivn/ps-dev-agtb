<?php
ob_clean();

require_once __DIR__ . '/Scanner/ScannerWeb.php';

$scanner = new ScannerWeb();
$scanner->setVerboseLevel(0);
$scanner->setLogFile('healthcheck.log');
$scanner->setInstanceDir(getcwd());

echo json_encode($scanner->scan());

sugar_cleanup(true);