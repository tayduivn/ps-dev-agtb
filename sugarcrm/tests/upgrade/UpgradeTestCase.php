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
        // put two files in custom 1 that needs failure, one that doesn't matter
        if (!is_dir('custom/modules/Accounts')) {
            sugar_mkdir('custom/modules/Accounts', false, true);
        }
        $needsFixed = '
        <?php
        require_once "include/ytree/ytree.php";
        ';

        file_put_contents('custom/modules/Accounts/NeedsFixed.php', $needsFixed);

        $noFixNeeded = '
        <?php
        require_once "vendor/ytree/ytree.php";
        ';

        file_put_contents('custom/modules/Accounts/NoFixNeeded.php', $noFixNeeded);
    }

    public static function tearDownAfterClass()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unlink('custom/modules/Accounts/NeedsFixed.php');
        unlink('custom/modules/Accounts/NoFixNeeded.php');
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
