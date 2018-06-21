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

namespace Sugarcrm\SugarcrmTestsBehat\Contexts;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use SugarTestHelper;
use SugarTestUserUtilities;
use PHPUnit\Framework\Assert;
use Sugarcrm\SugarcrmTestsBehat\BehatTestHelper;

/**
 * Defines application features from the specific context.
 */
class Common implements Context
{
    /**
     * @var BehatTestHelper
     */
    private $helper;

    public function __construct()
    {
        SugarTestHelper::init();
        $this->helper = BehatTestHelper::getHelper();
    }

    /** @BeforeScenario */
    public function before($event)
    {
    }

    /** @AfterScenario */
    public function after($event)
    {
        $this->helper->tearDown();
    }

    /**
     * @Given I am logged in
     */
    public function iAmLoggedIn()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser(true, true);
    }

    /**
     * @Given :module record(s) exist:
     *
     * @param TableNode $table
     * @param String $module
     */
    public function recordsExist(TableNode $table, String $module)
    {
        foreach ($table as $row => $data) {
            $this->helper->createRecord($module, $data);
        }
    }

    /**
     * @Given /^(\w+) records exist related via (\w+) link to \*(\w+):$/i
     *
     * @param TableNode $table
     * @param String $module
     * @param String $link
     * @param String $parent
     */
    public function recordsExistRelatedViaTo(
        TableNode $table, String $module, String $link, String $parent
    ) {
        $parentRecord = $this->helper->getRecord($parent);
        if (!$parentRecord) {
            Assert::fail("Unable to find parent record $parent");
        }
        foreach($table as $row) {
            $childRecord = $this->helper->createRecord($module, $row);
            if (!$parentRecord->load_relationship($link)) {
                Assert::fail("Unable to load link $link");
            }
            $parentRecord->$link->add($childRecord);
        }
    }


    /**
     * @When I update :module *:record with the following values:
     *
     * @param TableNode $table
     * @param String $module
     * @param String $ident
     */
    public function updateRecord(TableNode $table, String $module, String $ident)
    {
        $record = $this->helper->getRecord($ident, $module);
        if (!$record) {
            Assert::fail("Unable to find $module $ident");
        }
        foreach($table as $props) {
            $this->helper->updateRecord($record, $props);
        }
    }

    /**
     * @Then :module *:record should have the following values:
     *
     * @param TableNode $table
     * @param String $module
     * @param String $ident
     */
    public function assertRecordValues(TableNode $table, String $module, String $ident)
    {
        $record = $this->helper->getRecord($ident, $module);
        $record->retrieve($record->id);
        if (!$record) {
            Assert::fail("Unable to find $module $ident");
        }

        foreach($table as $row) {
            $this->helper->assertFieldValue($record, $row['fieldName'], $row['value']);
        }
    }
}
