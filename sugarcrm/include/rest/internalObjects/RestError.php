<?php

class RestError {

    private $errorData = null;


    function __construct() {

        $this->errorData = array(
            404 => "The requested resource could not be found. Check the URI for errors, and" .
                " verify that there are no sharing issues.",
            415 => "The server is refusing to service the request because the entity of the ".
                "request is in a format not supported by the requested resource for the requested method."
        );

    }

    /**
     * @param $code The http error code to use.
     * @param null $msg An extra message body to append to the default one.
     */
    public function ReportError($code, $msg = null) {
        header("HTTP/1.0 {$code}");
        print $this->errorData[$code];

        if ($msg != null) {
            print "\n{$msg}";
        }

    }
}

