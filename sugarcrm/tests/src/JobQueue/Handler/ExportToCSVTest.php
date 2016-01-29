<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\SugarcrmTests\JobQueue\Handler;

use Sugarcrm\Sugarcrm\JobQueue\Handler\ExportToCSV;

class ExportToCSVTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var \Account
     */
    protected $account;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        \SugarTestHelper::setUp('current_user', array(true, 1));
        $this->account = \SugarTestAccountUtilities::createAccount();
    }

    /**
     * @inheritdoc
     */
    public function tearDown()
    {
        \SugarTestAccountUtilities::removeAllCreatedAccounts();
        \SugarTestNoteUtilities::removeAllCreatedNotes();
        \SugarTestHelper::tearDown();
    }

    /**
     * @expectedException \Exception
     */
    public function testEmptyData()
    {
        $note = \SugarTestNoteUtilities::createNote(create_guid());
        new ExportToCSV($this->account->module_name, array(), $note->id);
    }

    /**
     * @expectedException \Exception
     */
    public function testNoteDoesNotExist()
    {
        new ExportToCSV($this->account->module_name, array($this->account->id), '');
    }

    /**
     * Result should be in passed file.
     */
    public function testExportToCSV()
    {
        $note = \SugarTestNoteUtilities::createNote(create_guid());
        $handler = new ExportToCSV($this->account->module_name, array($this->account->id), $note->id);
        $handler->run();
        $this->assertNotEquals(0, filesize('upload://' . $note->id));
    }
}
