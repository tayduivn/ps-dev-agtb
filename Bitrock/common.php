<?php

$LOCAL_APACHE_DOCROOT   = "/var/www/html";
global $VER;
if ($VER == '') $VER="6.2.0RC";
$VER_SUFFIX     = "BitRock-$VER";
$SUGAR_VERSION  = $VER;
$PUBLISH_DIR    = "/home/public/builds/$VER_SUFFIX";
$BUILD_TYPES    = array( "ce");

$BITROCK_BASE = "/home/build/bitrock-base";
$FASTSTACK_SVN = "http://svn1.sjc.sugarcrm.pvt/faststack/branches/tokyo";

?>
