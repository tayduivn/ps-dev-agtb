<?php

/*
 * Setup the objects by name first.
 */
$restObjectList = array(
    "login" => array(),
    "metadata" => array()
);


/*
 * Setup the objects source file.
 */
$restObjectList["login"] = "internalObjects/login.php";
$restObjectList["metadata"] = "internalObjects/metadata.php";

/*
 * setup some defines that we will use later in life.
 */
define("HTTP_OPTIONS",1001);
define("HTTP_GET", 1002);
define("HTTP_HEAD", 1003);
define("HTTP_POST", 1004);
define("HTTP_PUT",1005);
define("HTTP_DELETE", 1006);
define("HTTP_TRACE",1007);
define("HTTP_CONNECT", 1008);
define("HTTP_PATCH", 1009);


