<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
 
require_once 'modules/Import/Forms.php';
require_once 'include/Sugar_Smarty.php';
require_once 'modules/Import/controller.php';
require_once 'modules/Import/views/view.step3.php';
require_once 'modules/Import/views/view.step4.php';

class ImportFormsTest extends Sugar_PHPUnit_Framework_OutputTestCase
{
    public function setUp()
    {
        $beanList = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
        $mod_strings = array();
        require('modules/Import/language/en_us.lang.php');
        $GLOBALS['mod_strings'] = $mod_strings;
        $_SESSION['developerMode'] = true;
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($_SESSION['developerMode']);
        unset($GLOBALS['beanFiles']);
        unset($GLOBALS['beanList']);
        unset($GLOBALS['mod_strings']);
    }

    public function testLoadImportBean()
    {
        $oldisadmin = $GLOBALS['current_user']->is_admin;
        $GLOBALS['current_user']->is_admin = '1';

        $controller = new ImportController;
        $_REQUEST['import_module'] = 'Accounts';
        $controller->loadBean();

        $this->assertEquals($controller->bean->object_name, 'Account');

        $GLOBALS['current_user']->is_admin = $oldisadmin;
    }

    public function testLoadImportBeanNotImportable()
    {
        $controller = new ImportController;
        $_REQUEST['import_module'] = 'vCals';
        $controller->loadBean();
        
        $this->assertFalse($controller->bean);
    }

    public function testLoadImportBeanUserNotAdmin()
    {
        $controller = new ImportController;
        $_REQUEST['import_module'] = 'Users';
        $controller->loadBean();
        
        $this->assertFalse($controller->bean);
    }

    public function errorSet()
    {
         return array(
            array(E_USER_WARNING,'sample E_USER_WARNING','test12.php',4),
            array(E_WARNING,'sample E_WARNING','test4.php',2232),
            array(E_USER_NOTICE,'sample E_USER_NOTICE','test8.php',932),
            array(E_NOTICE,'sample E_NOTICE','12test.php',39),
            array(E_STRICT,'sample E_STRICT','t12est.php',42),
            array(12121212121,'sample unknown error','te43st.php',334),
            );
    }

    /**
     * @dataProvider errorSet
     */
    public function testHandleImportErrors($errno, $errstr, $errfile, $errline)
    {
        $old_error_reporting = error_reporting(E_ALL);

        ImportViewStep4::handleImportErrors($errno, $errstr, $errfile, $errline);

        switch ($errno) {
            case E_USER_WARNING:
            case E_WARNING:
                //$this->assertEquals("WARNING: [$errno] $errstr on line $errline in file $errfile<br />",$output);
                break;
            case E_USER_NOTICE:
            case E_NOTICE:
                //$this->assertEquals("NOTICE: [$errno] $errstr on line $errline in file $errfile<br />",$output);
                break;
            case E_STRICT:    
                //$this->assertEquals('',$output);
                break;
            default:
                $this->expectOutputString("Unknown error type: [$errno] $errstr on line $errline in file $errfile<br />\n");
                break;
            }
        error_reporting($old_error_reporting);
    }

    public function testGetControlIdField()
    {
        $html = getControl('Contacts','assigned_user_id');

        $this->assertRegExp('/name=\'assigned_user_id\'/',$html);
        $this->assertRegExp('/id=\'assigned_user_id\'/',$html);
        $this->assertRegExp('/type=\'text\'/',$html);
    }

    public function testGetControlEmail()
    {
        $html = getControl('Contacts','email1');

        $this->assertRegExp('/name=\'email1\'/',$html);
        $this->assertRegExp('/id=\'email1\'/',$html);
        $this->assertRegExp('/type=\'text\'/',$html);
    }

    public function testGetControlCurrencyList()
    {
        global $app_strings;

        $html = getControl('Opportunities','currency_id');

        $focus = loadBean('Opportunities');

        require_once('modules/Opportunities/Opportunity.php');

        $string = str_ireplace('</select>','<option value="">'.$app_strings['LBL_NONE'].'</option></select>',getCurrencyDropDown($focus, 'currency_id', '', 'EditView'));
        $this->assertContains($string,$html,"Failed to find string '$string' in '$html'");

        $string = "<script>function CurrencyConvertAll() { return; }</script>";
        $this->assertContains($string,$html,"Failed to find string '$string' in '$html'");
    }

    public function testGetControlVardef()
    {
        VardefManager::loadVardef(
                'Contacts',
                'Contact');
        $vardef = $GLOBALS['dictionary']['Contact']['fields']['assigned_user_id'];

        $html = getControl('Contacts','assigned_user_id',$vardef);

        $this->assertRegExp('/name=\'assigned_user_id\'/',$html);
        $this->assertRegExp('/id=\'assigned_user_id\'/',$html);
        $this->assertRegExp('/type=\'text\'/',$html);
    }

    public function testGetControlValue()
    {
        $html = getControl('Contacts','email1',null,'poo');

        $this->assertRegExp('/name=\'email1\'/',$html);
        $this->assertRegExp('/id=\'email1\'/',$html);
        $this->assertRegExp('/type=\'text\'/',$html);
        $this->assertRegExp('/value=\'poo\'/',$html);
    }
    //BEGIN SUGARCRM flav=pro ONLY
    /**
     * @ticket 32626
     */
    public function testGetControlEnumWhenOptionsAreInTheModStrings()
    {
        $html = getControl('Manufacturers','status',null,'poo');

        $this->assertNotContains('manufacturer_status_dom',$html);
    }
    //END SUGARCRM flav=pro ONLY

    /**
     * @group bug41447
     */
    public function testGetControlDatetimecombo()
    {
        $html = getControl('Calls','date_start');

        global $timedate;
        $string = '", "' . $timedate->get_user_time_format() . '", "';

        $this->assertContains($string, $html);
    }
}
