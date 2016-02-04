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

use Sugarcrm\Sugarcrm\JobQueue\Handler\ExportRecords;

class ExportRecordsTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var \Account
     */
    protected $account;

    /**
     * @var integer
     */
    protected $stashMaxRecordSize;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->account = \SugarTestAccountUtilities::createAccount();
        $this->stashMaxRecordSize = $GLOBALS['sugar_config']['max_record_fetch_size'];
        $GLOBALS['sugar_config']['max_record_fetch_size'] = 1;
    }

    /**
     * @inheritdoc
     */
    public function tearDown()
    {
        $GLOBALS['sugar_config']['max_record_fetch_size'] = $this->stashMaxRecordSize;
        \SugarTestAccountUtilities::removeAllCreatedAccounts();
        \SugarTestNoteUtilities::removeAllCreatedNotes();
        \SugarTestHelper::tearDown();
    }

    /**
     * @expectedException \Exception
     */
    public function testNoRecordsToExport()
    {
        $note = \SugarTestNoteUtilities::createNote(create_guid());
        new ExportRecords($this->account->module_name, array(), $note->id);
    }

    /**
     * @expectedException \Exception
     */
    public function testNoteDoesNotExist()
    {
        new ExportRecords($this->account->module_name, array($this->account->id), '');
    }

    /**
     * Should divide passed data into chunks.
     */
    public function testExportAction()
    {
        $note = \SugarTestNoteUtilities::createNote(create_guid());
        $handler = new ExportRecords(
            $this->account->module_name,
            array($this->account->id, $this->account->id),
            $note->id
        );

        $managerMock = $this->getMock('Manager', array('ExportToCSV'));
        $managerMock
            ->expects($this->exactly(2))
            ->method('ExportToCSV');

        $reflector = new \ReflectionClass($handler);
        $property = $reflector->getProperty('JQClient');
        $property->setAccessible(true);
        $property->setValue($handler, $managerMock);

        $handler->run();
    }
}
