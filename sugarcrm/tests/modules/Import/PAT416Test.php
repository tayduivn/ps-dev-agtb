<?php
/*
* By installing or using this file, you are confirming on behalf of the entity
* subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
* the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
* http://www.sugarcrm.com/master-subscription-agreement
*
* If Company is not bound by the MSA, then by installing or using this file
* you are agreeing unconditionally that Company will be bound by the MSA and
* certifying that you have authority to bind Company accordingly.
*
* Copyright (C) 2004-2013 SugarCRM Inc. All rights reserved.
*/

require_once('modules/Import/Importer.php');

/**
 * Bug #PAT-416
 * Importing with a related module's ID and name disregards the ID column
 *
 * @author bsitnikovski@sugarcrm.com
 * @ticket PAT-416
 */
class BugPAT416Test extends Sugar_PHPUnit_Framework_TestCase
{

    private $dummy_defs;

    public function setUp()
    {
        $this->dummy_defs = array(
            'account_name' => array(
                'id_name' => 'account_id',
                'type' => 'relate',
             ),
             'account_id' => array(
                'id_name' => 'account_id',
                'type' => 'relate',
             ),
             'test_field' => array(
                'id_name' => 'test_field',
                'type' => 'varchar',
             ),
        );
    }

    public function importColumnsProvider()
    {
        $set1 = array("account_id", "account_name");
        $exp1 = array(0, 1);

        $set2 = array("account_name", "account_id");
        $exp2 = array(1, 0);

        $set3 = array("account_name", "test_field", "account_id");
        $exp3 = array(2, 0, 1);

        return array(
            array($set1, $exp1),
            array($set2, $exp2),
            array($set3, $exp3),
        );
    }

    /**
     * Test the functionality based on data provider
     *
     * @dataProvider importColumnsProvider
     */
    public function testImportColumnsOrder($set, $exp)
    {
        $importer = new ImporterMockBugPAT416Test($set);
        $fields_order = $importer->getImportColumnsOrder($this->dummy_defs);
        $this->assertEquals($exp, $fields_order);
    }
}

/**
 * Mock class for Importer
 */
class ImporterMockBugPAT416Test extends Importer
{
    /**
     * Override this method to just initialize import columns
     */
    public function __construct($importColumns)
    {
        $this->importColumns = $importColumns;
    }

    /**
     * Override this method to access protected method
     */
    public function getImportColumnsOrder($field_defs)
    {
        return parent::getImportColumnsOrder($field_defs);
    }
}
