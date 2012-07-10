<?php

/**
 * SugarAccess contains the operations to authenticate and determine the access levels of a user.
 */
class SugarAccess {
    protected $instance = null;
    protected $client;

    public static function getInstance(){
        if(self::$instance == null){
            self::$instance = new SugarAccess();
        }

        return self::$instance;

    }

    protected function __construct(){
        $this->client = new LicenseServerClient();
    }

    /**
     * @static
     * @param array $moduleList
     * @return array $moduleList
     */
    public function filterModules($moduleList) {
        return $moduleList;
    }

    public function authenticate($email, $password) {

    }
}

/**
 *
 */
class LicenseServerClient {
    const licenseServerUrl = "http://licenseserver";

    protected $userData;

    public function __construct() {

    }

    public function authenticate($email, $password) {

        $info = $this->restCall(self::licenseServerUrl, array(
            "action" => "authenticate",
            "method" => "POST",
        ));

        $this->userData = array(
            "modules" => array("Accounts", "Contacts", "Opportunities"),
            "dbconfig" => array(),
            "instance" => "UNIQUE_INSTANCE_ID"
        );
    }

    public function getModules($email) {
        return $this->userData["modules"];
    }

    public function getInstance($email) {

    }

    protected function restCall($url, $params) {
        $curlOp = curl_init($url);

        curl_setopt($curlOp, "CURLOPT_RETURNTRANSFER", true);

        $result = curl_exec($curlOp);
        curl_close($curlOp);

        return $result;
    }
}