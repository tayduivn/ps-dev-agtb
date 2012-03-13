<?php

class Result {

    private $data = null;

    function __construct($errNum, $errMsg, $data) {
        $data["error"] = $errNum;

        if ($errMsg == null || empty($errMsg)) {
            $data["err_msg"] = "";
        }

        $data["data"] = $data;
    }

}