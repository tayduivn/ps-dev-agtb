<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
* The contents of this file are subject to the SugarCRM Master Subscription
* Agreement ("License") which can be viewed at
* http://www.sugarcrm.com/crm/master-subscription-agreement
* By installing or using this file, You have unconditionally agreed to the
* terms and conditions of the License, and You may not use this file except in
* compliance with the License. Under the terms of the license, You shall not,
* among other things: 1) sublicense, resell, rent, lease, redistribute, assign
* or otherwise transfer Your rights to the Software, and 2) use the Software
* for timesharing or service bureau purposes such as hosting the Software for
* commercial gain and/or for the benefit of a third party. Use of the Software
* may be subject to applicable fees and any use of the Software without first
* paying applicable fees is strictly prohibited. You do not have the right to
* remove SugarCRM copyrights from the source code or user interface.
*
* All copies of the Covered Code must include on each user interface screen:
* (i) the "Powered by SugarCRM" logo and
* (ii) the SugarCRM copyright notice
* in the same form as they appear in the distribution. See full license for
* requirements.
*
* Your Warranty, Limitations of liability and Indemnity are expressly stated
* in the License. Please refer to the License for the specific language
* governing these rights and limitations under the License. Portions created
* by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
********************************************************************************/

require_once 'modules/Import/sources/ImportFile.php';
require_once('modules/Import/Importer.php');
require_once('modules/Import/ImportCacheFiles.php');

/**
 * Bug #56584
 * @ticket 56584
 */
class Bug56584Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $testFile;
    
    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('mod_strings', array('Import'));
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('current_user', array(true, 1));

        $this->testFile = 'tests/modules/Tasks/Bug56584Test.csv';

        $_REQUEST = array(
            'colnum_0'    => 'contact_name',
            'colnum_1'    => 'name',
            'colnum_2'    => 'status',
            'columncount' => '3',
            'importlocale_charset' => 'UTF-8',
            'importlocale_currency' => '-99',
            'importlocale_dateformat' => 'd/m/Y',
            'importlocale_dec_sep' => '.',
            'importlocale_default_currency_significant_digits' => '2',
            'importlocale_default_locale_name_format' => 's f l',
            'importlocale_num_grp_sep' => ',',
            'importlocale_timeformat' => 'H:i',
            'importlocale_timezone' => 'Europe/Helsinki',
            'import_module' => 'Tasks',
        );
    }

    public function tearDown()
    {
        $uid  = $GLOBALS['current_user']->id;
        $GLOBALS['db']->query("DELETE FROM contacts " .
            "WHERE created_by = '$uid' ");
        $GLOBALS['db']->query("DELETE FROM tasks " .
            "WHERE created_by = '$uid' ");

        SugarTestHelper::tearDown();
    }

    public function testImport()
    {
        global $db, $current_user;

        $taskBean     = new Task();
        $importSource = new ImportFile($this->testFile, ',', '', false);
        $importer     = new Importer($importSource, $taskBean);
        $contactBean  = new Contact();
        $contacts     = array();

        $importer->import();

        $result = $db->query("SELECT id, first_name, last_name " .
                             "FROM $contactBean->table_name " .
                             "WHERE created_by='$current_user->id'");

        while ($row = $db->fetchRow($result))
        {
            $contacts[] = $row;
        }

        $this->assertEquals(1, count($contacts), 'Invalid number of contacts created.');

        foreach ($contacts as $record)
        {
            $taskBean->retrieve_by_string_fields(array(
                'contact_id' => $record['id'],
            ));

            $this->assertNotEmpty($record['first_name'], 'First name of contact "' . $record['id'] . '" is empty.');
            $this->assertNotEmpty($record['last_name'], 'Last name of contact "' . $record['id'] . '" is empty.');
            $this->assertEquals($record['first_name'] . ' ' . $record['last_name'], $taskBean->contact_name);
        }
    }

    public function contactParams()
    {
        return array(
            array('John Doe', 'John', 'Doe'),
            array('John Doe Jr.', 'John', 'Doe Jr.'),
            array('Doe', '', 'Doe'),
        );
    }

    /**
     * @dataProvider contactParams
     * @param string $rawValue
     * @param string $firstName
     * @param string $lastName
     */
    public function testAssignConcatenatedName($rawValue, $firstName, $lastName)
    {
        $testBean = new Contact();
        $fieldDef = $testBean->getFieldDefinition('name');

        assignConcatenatedValue($testBean, $fieldDef, $rawValue);

        $this->assertEquals($firstName, $testBean->first_name, 'First name is invalid.');
        $this->assertEquals($lastName, $testBean->last_name, 'Last name is invalid.');
    }

    public function teamParams()
    {
        return array(
            array('Big Team', 'Big', 'Team'),
            array('Very Big Team', 'Very', 'Big Team'),
            array('Team', 'Team', ''),
        );
    }

    /**
     * @dataProvider teamParams
     * @param string $rawValue
     * @param string $name
     * @param string $name2
     */
    public function testAssignConcatenatedTeamName($rawValue, $name, $name2)
    {
        global $dictionary;

        $testBean = new Team();
        $fieldDef = $dictionary['User']['fields']['team_name'];

        assignConcatenatedValue($testBean, $fieldDef, $rawValue);

        $this->assertEquals($name, $testBean->name, 'First name is invalid.');
        $this->assertEquals($name2, $testBean->name_2, 'Last name is invalid.');
    }
}
