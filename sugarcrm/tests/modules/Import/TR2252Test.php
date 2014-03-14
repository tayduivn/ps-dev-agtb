<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('modules/Import/Importer.php');

/**
 * Bug #TR-2252
 * Importing with a related module's ID and name disregards the ID column
 *
 * @ticket TR-2252
 */
class BugTR2252Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $account1;
    private $account2;

    public function setUp()
    {
        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], 'Import');

        $this->account1 = SugarTestAccountUtilities::createAccount();
        $this->account1->name = "AccountBugTR-2252Test";
        $this->account1->save(false);
        $this->account2 = SugarTestAccountUtilities::createAccount();
        $this->account2->name = "AccountBugTR-2252Test";
        $this->account2->save(false);

    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);

        unset($GLOBALS['beanFiles'], $GLOBALS['beanList']);
        unset($GLOBALS['app_strings'], $GLOBALS['app_list_strings'], $GLOBALS['mod_strings']);

        SugarTestAccountUtilities::removeAllCreatedAccounts();
        unset($this->account1, $this->account2);
    }

    /**
     * @group
     */
    public function testSome()
    {
        $csvContent = array(
            0 => array('BugTR-2252TestFN1','BugTR-2252TestLN1',$this->account1->name,$this->account1->id),
            1 => array('BugTR-2252TestFN2','BugTR-2252TestLN2',$this->account2->name,$this->account2->id)
        );

        $file = 'upload://BugTR-2252TestContacts.csv';
        if ($fp = fopen($file, 'w')) {
            fputcsv($fp, $csvContent[0]);
            fputcsv($fp, $csvContent[1]);
            fclose($fp);
        }

        $importSource = new ImportFile($file);

        $_REQUEST['columncount'] = 4;
        $_REQUEST['colnum_0'] = 'first_name';
        $_REQUEST['colnum_1'] = 'last_name';
        $_REQUEST['colnum_2'] = 'account_name';
        $_REQUEST['colnum_3'] = 'account_id';
        $_REQUEST['import_module'] = 'Contacts';
        $_REQUEST['importlocale_charset'] = 'UTF-8';
        $_REQUEST['importlocale_timezone'] = 'GMT';
        $_REQUEST['importlocale_default_currency_significant_digits'] = '2';
        $_REQUEST['importlocale_currency'] = '-99';
        $_REQUEST['importlocale_dec_sep'] = '.';
        $_REQUEST['importlocale_currency'] = '-99';
        $_REQUEST['importlocale_default_locale_name_format'] = 's f l';
        $_REQUEST['importlocale_num_grp_sep'] = ',';
        $_REQUEST['importlocale_dateformat'] = 'm/d/y';
        $_REQUEST['importlocale_timeformat'] = 'h:i:s';

        $focus = new Contact();

        $importer = new Importer($importSource, $focus);
        $importer->import();

        $focus = new Contact();
        $focus->retrieve_by_string_fields(array(
            'first_name' => $csvContent[0][0],
            'last_name' => $csvContent[0][1],
            'deleted' => 0
        ));
        $this->assertNotEmpty($focus->id);
        $this->assertNotEmpty($focus->account_id);
        $this->assertEquals($csvContent[0][3], $focus->account_id);
        if (!empty($focus->id)) {
            $GLOBALS['db']->query("DELETE FROM contacts WHERE id = '{$focus->id}'");
            $GLOBALS['db']->query("DELETE FROM account_contacts WHERE contact_id = '{$focus->id}'");
        }
        //
        $focus = new Contact();
        $focus->retrieve_by_string_fields(array(
                'first_name' => $csvContent[1][0],
                'last_name' => $csvContent[1][1],
                'deleted' => 0
            ));
        $this->assertNotEmpty($focus->id);
        $this->assertNotEmpty($focus->account_id);
        $this->assertEquals($csvContent[1][3], $focus->account_id);
        if (!empty($focus->id)) {
            $GLOBALS['db']->query("DELETE FROM contacts WHERE id = '{$focus->id}'");
            $GLOBALS['db']->query("DELETE FROM account_contacts WHERE contact_id = '{$focus->id}'");
        }
    }
}
