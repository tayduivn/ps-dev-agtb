<?php

/**
 * SugarAccess contains the operations to authenticate and determine the access levels of a user.
 * @singleton
 */
class SugarAccess {
    /**
     * @var null Singleton instance
     */
    protected static $instance = null;

    /**
     * @var LicenseServerClient
     */
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
        $permittedModules = $this->client->getModules();
        print (gettype($permittedModules));
        return array_intersect($moduleList, $permittedModules);
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

    /**
     * Returns the sugar_config required to run the instance
     * @return mixed
     */
    public function getConfig() {
        $instanceData = $this->client->getInstanceData();

        if (!$instanceData) {
            die("No instance Data");
        }

        // TODO: MOCKED
        include('include/SugarAccess/configs/' . $instanceData['flavor'] . '.config.php');
        $GLOBALS['sugar_config'] = $sugar_config;

        foreach ($instanceData['config'] as $key => $value) {
            $GLOBALS['sugar_config'][$key] = $value;
        }

        return $GLOBALS['sugar_config'];
    }
}

/**
 * Client for Licensing Server
 */
class LicenseServerClient {
    const licenseServerUrl = "http://localhost:8888/summer/user/123213";

    /**
     * @var User data / Instance data
     */
    protected $userData;

    /**
     * Load up session data if the user has already authenticated.
     */
    public function __construct() {
        if (isset($_SESSION['userData'])) {
            $this->userData = $_SESSION['userData'];
        }
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
//        $this->userData = array(
//            "modules" => array("Accounts", "Contacts", "Opportunities", "Home"),
//            "instance" => array(
//                "id" => "UNIQUE_INSTANCE_ID",
//                "license_key" => "13984ajdsfsd",
//                "flavor" => "ent",
//                'config' => array(
//                    'dbconfig' =>
//                    array(
//                        'db_host_name' => 'localhost',
//                        'db_host_instance' => '',
//                        'db_user_name' => 'root',
//                        'db_password' => 'root',
//                        'db_name' => 'ent622_1',
//                        'db_type' => 'mysql',
//                        'db_port' => '',
//                        'db_manager' => 'MysqliManager',
//                    ),
//                ),
//
//            ),
//
//            "userinfo" => array(
//                "id" => "1345",
//                "email" => "email@email.com",
//                "status" => "active",
//                "date_created" => "somedate",
//
//            )
//        );

        // Save user data to the session
        $_SESSION["userData"] = $this->userData;
        return $this->userData;
    }

    /**
     * Returns modules that this user has access to
     * @return mixed
     */
    public function getModules() {
        return $this->userData["modules"];
    }


    public function getInstanceData() {
        return $this->userData["instance"];
    }

    /**
     * Returns instance information
     * @return mixed
     */

    /**
     * Makes a curl call to the license server with supplied url and parameters
     * @param $url
     * @param $data
     * @return mixed
     */
    protected function restCall($url, $data) {
        $curlOp = curl_init($url);

        curl_setopt($curlOp, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($curlOp, CURLOPT_POSTFIELDS, $data);

        $result = curl_exec($curlOp);
        curl_close($curlOp);

        return json_decode($result, true);
    }
}