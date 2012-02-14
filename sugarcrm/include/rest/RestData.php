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
$httpVerbs = array(
    "HTTP_OPTIONS" => 1001,
    "HTTP_GET" => 1002,
    "HTTP_HEAD" => 1003,
    "HTTP_POST" => 1004,
    "HTTP_PUT" => 1005,
    "HTTP_DELETE" => 1006,
    "HTTP_TRACE" => 1007,
    "HTTP_CONNECT" => 1008,
    "HTTP_PATCH" => 1009);
