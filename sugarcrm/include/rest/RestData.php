<?php

/**
 * This file is much like the modules.php file where it is used to loaded known
 * objects by the RestFactory.
 */


/*
 * Setup the objects by name first.
 */
$restObjectList = array(
    "login" => array(),
    "logout" => array(),
    "metadata" => array(),
    "serverinfo" => array(),
    "objects" => array()
);

/*
 * Setup the objects source file.
 */
$restObjectList["login"] = "internalObjects/login.php";
$restObjectList["logout"] = "internalObjects/logout.php";
$restObjectList["metadata"] = "internalObjects/metadata.php";
$restObjectList["serverinfo"] = "internalObjects/serverinfo.php";
$restObjectList["objects"] = "internalObjects/objects.php";

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
define("REST_OBJECT_INDEX", 0);
define("REST_OBJECT_RESOURCE_INDEX", 1);
