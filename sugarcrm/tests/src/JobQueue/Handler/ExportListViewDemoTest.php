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

use Sugarcrm\Sugarcrm\JobQueue\Handler\ExportListViewDemo;

class ExportListViewDemoTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var \Account
     */
    protected $account;

    /**
     * @var integer
     */
    protected $stashMasRecordSize;

    public function setUp()
    {
        \SugarTestHelper::setUp('current_user', array(true, 1));
        \SugarTestHelper::setUp('app_list_strings');
        $this->account = \SugarTestAccountUtilities::createAccount();
        $this->stashMasRecordSize = $GLOBALS['sugar_config']['max_record_fetch_size'];
        $GLOBALS['sugar_config']['max_record_fetch_size'] = 1;
    }

    public function tearDown()
    {
        $GLOBALS['sugar_config']['max_record_fetch_size'] = $this->stashMasRecordSize;
        \SugarTestAccountUtilities::removeAllCreatedAccounts();
        \SugarTestHelper::tearDown();
    }

    /**
     * @expectedException \Exception
     */
    public function testNoRecordsToExport()
    {
        new ExportListViewDemo($this->account->module_name, array());
    }

    /**
     * Should divide passed data into chunks.
     */
    public function testDeleteAction()
    {
        $handler = new ExportListViewDemo($this->account->module_name, array($this->account->id, $this->account->id));

        $managerMock = $this->getMock('Manager', array('exportToCSVDemo'));
        $managerMock
            ->expects($this->exactly(2))
            ->method('exportToCSVDemo');

        $reflector = new \ReflectionClass($handler);
        $property = $reflector->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($handler, $managerMock);

        $handler->run();
    }
}
