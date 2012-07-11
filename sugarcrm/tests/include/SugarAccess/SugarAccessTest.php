<?php

require_once('include/SugarAccess/SugarAccess.php');


class SugarAccessTest extends PHPUnit_Framework_TestCase{

    public function testFilterModules(){
        $arr = array(0,1,2,3,4,5);

        $sugarAccess = SugarAccess::getInstance();

        $arr2 = $sugarAccess->filterModules($arr);
        $this->assertEquals($arr, $arr2);
        $arr = 'A';

        $arr2 = $sugarAccess->filterModules($arr);

        $this->assertEquals($arr, $arr2);

        $arr = 1;

        $arr2 = $sugarAccess->filterModules($arr);

        $this->assertEquals($arr, $arr2);



    }

    public function testAccessAuthenticate(){
        $sugarAccess = SugarAccess::getInstance();
        $email = new Email();
        $password = "sally";

        $testData = array(
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

        $this->assertEquals($testData, $sugarAccess->authenticate($email, $password));

    }

    public function testGetModules(){
        $licenseServer = new LicenseServerClient();
        $this->assertEquals(3, count($licenseServer->getModules("someone@email.com")));
        $this->assertContains("Contacts",$licenseServer->getModules("someone@email.com"));
        $this->assertContains("Opportunities",$licenseServer->getModules("someone@email.com"));
        $this->assertContains("Accounts",$licenseServer->getModules("someone@email.com"));

    }

    public function testGetInstanceData(){
        $email = "someone@email.com";
        $licenseServer = new LicenseServerClient();


        $testEmail = $licenseServer->getInstanceData($email);
        $this->assertEquals(array(
            "id" => "UNIQUE_INSTANCE_ID",
            "license_key" => "13984ajdsfsd"
        ), $testEmail);

    }

    /**
     *
     */
    public function testStubAuthenticate(){
        $stub = new LicenseStubClient();
        $email = new Email();
        $password = "sally";

        $testData = array(
            "modules" => array("Accounts", "Contacts", "Opportunities"),
            "instanceinfo" => array(
                "id" => "stubID",
                "license_key" => "13984ajdsfsd"
            ),
            "userinfo" => array(
                "id" => "sally",
                "email" => "sally@gmail.com",
                "status" => "active",
                "date_created" => "07/11/12",
                "flavor" => "ent",
                "dbconfig" => array()
            )
        );
        $this->assertEquals($testData, $stub->authenticate($email, $password));

    }
}


class LicenseStubClient {
    const licenseServerUrl = "http://licenseserver";

    /**
     * @var
     */
    protected $userData;

    public function __construct() {
        if(isset($_SESSION['userData']))$this->userData = $_SESSION['userData'];
    }

    /**
     * @param $email
     * @param $password
     * @return User
     */
    public function authenticate($email, $password) {

        // MOCK DATA
        $this->userData = array(
            "modules" => array("Accounts", "Contacts", "Opportunities"),
            "instanceinfo" => array(
                "id" => "stubID",
                "license_key" => "13984ajdsfsd"
            ),
            "userinfo" => array(
                "id" => "sally",
                "email" => "sally@gmail.com",
                "status" => "active",
                "date_created" => "07/11/12",
                "flavor" => "ent",
                "dbconfig" => array()
            )
        );

        // Save user data to the session
        $_SESSION["userData"] = $this->userData;

        return $this->userData;
    }

}
?>