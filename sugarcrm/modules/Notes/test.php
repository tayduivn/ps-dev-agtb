<?php

require_once('include/externalAPI/ExternalAPIFactory.php');

$testApi = ExternalAPIFactory::loadAPI('LotusLiveDirect');

$timebefore = microtime(true);
// echo('SearchDoc: <pre>'.print_r($testApi->searchDoc('',true),true));
echo('QuickCheckLogin: <pre>'.print_r($testApi->quickCheckLogin(),true));
$timeafter = microtime(true);

$timeelapsed = $timeafter - $timebefore;
echo("<br><br><br>\n\n\nTime Elapsed: ".$timeelapsed);
