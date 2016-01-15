<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\SugarcrmTests\JobQueue\Handler;

use Sugarcrm\Sugarcrm\JobQueue\Handler\ExportToCSVDemo;

class ExportToCSVDemoTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var \Account
     */
    protected $account;

    /**
     * @var string $file File name to write results.
     */
    protected $file;

    public function setUp()
    {
        \SugarTestHelper::setUp('current_user', array(true, 1));
        $this->account = \SugarTestAccountUtilities::createAccount();
        $this->file = tempnam(sys_get_temp_dir(), __CLASS__);
    }

    public function tearDown()
    {
        \SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        \SugarTestAccountUtilities::removeAllCreatedAccounts();
        \SugarTestHelper::tearDown();
    }

    /**
     * @expectedException \Exception
     */
    public function testEmptyData()
    {
        new ExportToCSVDemo($this->account->module_name, array(), $this->file);
    }

    /**
     * @expectedException \Exception
     */
    public function testInvalidFile()
    {
        new ExportToCSVDemo($this->account->module_name, array($this->account->id), 'invalidFile');
    }

    /**
     * Result should be in passed file.
     */
    public function testExportToCSV()
    {
        $handler = new ExportToCSVDemo($this->account->module_name, array($this->account->id), $this->file);
        $handler->run();
        $this->assertNotEquals(0, filesize($this->file));
    }
}
