<?php

/**
 * SugarAccess contains the operations to authenticate and determine the access levels of a user.
 * @singleton
 */
class SugarAccess {
    protected $instance = null;
    protected $client;

    /**
     * Returns an instance of SugarAccess
     * @static
     * @return null
     */
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new SugarAccess();
        }

        return self::$instance;
    }

    /**
     * Create an instance of SugarAccess
     */
    protected function __construct() {
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

    /**
     * Authenticates against the licensing server
     * @param $email
     * @param $password
     * @return mixed
     */
    public function authenticate($email, $password) {
        return $this->client->authenticate($email, $password);
    }
}

/**
 * Client for Licensing Server
 */
class LicenseServerClient {
    const licenseServerUrl = "http://licenseserver";

    protected $userData;

    public function __construct() {

    }

    public function authenticate($email, $password) {

        $info = $this->restCall(self::licenseServerUrl, array(
            "action" => "authenticate",
        ));

        // MOCK DATA
        $this->userData = array(
            "modules" => array("Accounts", "Contacts", "Opportunities"),
            "dbconfig" => array(),
            "instance" => "UNIQUE_INSTANCE_ID"
        );

        return $info;
    }

    public function getModules($email) {
        return $this->userData["modules"];
    }

    public function getInstanceData($email) {
        return $this->userData["instance"];
    }

    protected function restCall($url, $data) {
        $curlOp = curl_init($url);

        curl_setopt($curlOp, "CURLOPT_RETURNTRANSFER", true);
        curl_setopt($curlOp, CURLOPT_POST, 1);
        curl_setopt($curlOp, CURLOPT_POSTFIELDS, $data);

        $result = curl_exec($curlOp);
        curl_close($curlOp);

        return $result;
    }
}