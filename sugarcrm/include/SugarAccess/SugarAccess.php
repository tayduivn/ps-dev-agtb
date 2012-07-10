<?php

/**
 * SugarAccess contains the operations to authenticate and determine the access levels of a user.
 * @singleton
 */
class SugarAccess {
    protected static $instance = null;
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

    /**
     * @var User data / Instance data
     */
    protected $userData;

    public function __construct() {
        if(isset($_SESSION['userData']))$this->userData = $_SESSION['userData'];
    }

    /**
     * Authenticates an email address and returns the necessary information about the user
     * and his/her instance.
     * @param $email Email address as account name
     * @param $password
     * @return mixed Licensing and instance data related to the user.
     */
    public function authenticate($email, $password) {
        $this->userData = $this->restCall(self::licenseServerUrl, array(
            "action" => "authenticate",
        ));

        // MOCK DATA
        $this->userData = array(
            "modules" => array("Accounts", "Contacts", "Opportunities"),
            "instanceinfo" => array(
                "id" => "UNIQUE_INSTANCE_ID",
                "license_key" => "13984ajdsfsd"
            ),
            "userinfo" => array(
                "id" => "1345",
                "email" => "email@email.com",
                "status" => "active",
                "date_created" => "somedate",
                "flavor" => "ent",
                "dbconfig" => array()
            )
        );

        // Save user data to the session
        $_SESSION["userData"] = $this->userData;

        return $this->userData;
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