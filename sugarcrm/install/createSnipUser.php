<?php
installLog("creating new user for Snip");

require_once 'modules/SNIP/SugarSNIP.php';
$snip = SugarSNIP::getInstance();
$snip->getSnipUser();

?>