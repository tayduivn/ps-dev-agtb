<?php
require_once 'modules/UpgradeWizard/TestUpgrader.php';

abstract class UpgradeTestCase extends Sugar_PHPUnit_Framework_TestCase
{

    protected $upgrader;
    /**
     * admin user
     * @var User
     */
    static protected $admin;

    public static function setUpBeforeClass()
    {
        // create admin user
        self::$admin = SugarTestUserUtilities::createAnonymousUser(true, 1);
    }

    public static function tearDownAfterClass()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

	public function setUp()
	{
	    $this->upgrader = new TestUpgrader(self::$admin);
        SugarTestHelper::setUp("files");
	}

	public function tearDown()
	{
	    $this->upgrader->cleanState();
	    $this->upgrader->cleanDir($this->upgrader->getTempDir());
	    SugarTestHelper::tearDown();
	}
}
