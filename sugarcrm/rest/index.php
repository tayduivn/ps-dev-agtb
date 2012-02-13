<?php


    $uri = $_SERVER["REQUEST_URI"];
    $method = $_SERVER["REQUEST_METHOD"];
    $encoding = $_SERVER["HTTP_ACCEPT_ENCODING"];

    if ($method == "GET") {
        print "<p>(*){$uri}</p>\n<p>";
        print_r($_GET);
        print "</p>\n";
        $tmp = explode("/", $_GET["url"]);
        var_dump($tmp);
    } else {

    }






