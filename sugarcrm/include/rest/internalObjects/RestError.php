<?php

class RestError {

    private $errorData = null;


    function __construct() {

        $this->errorData = array(
            404 => "The requested resource could not be found. Check the URI for errors, and" .
                " verify that there are no sharing issues."
        );

    }

    public function ReportError($code) {
        header("HTTP/1.0 {$code}");
        print $this->errorData[$code];
    }
}

