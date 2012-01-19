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

/**
 * Bug47553Test.php
 * @author Collin Lee
 *
 * This is a test that simulates the DynamicField saveToVardef call to ensure that the vardef definition for the Employees module
 * is correctly built when the Users vardef is rebuilt.  In particular we are interested to make sure that the status field has
 * a studio attribute set to false and that the status field is not required for the Employees vardef definition.
 */

require_once('modules/DynamicFields/DynamicField.php');

class Bug47553Test extends Sugar_PHPUnit_Framework_TestCase
{

    var $cachedEmployeeVardefs;

    public function setUp()
    {
        global $beanList, $beanFiles;
        require('include/modules.php');

        if(file_exists('cache/modules/Employees/Employeevardefs.php'))
        {
            $this->cachedEmployeeVardefs = file_get_contents('cache/modules/Employees/Employeevardefs.php');
            unlink('cache/modules/Employees/Employeevardefs.php');
        }
    }

    public function tearDown()
    {
        if(!empty($this->cachedEmployeeVardefs))
        {
            file_put_contents('cache/modules/Employees/Employeevardefs.php', $this->cachedEmployeeVardefs);
        }
    }

    public function testSaveUsersVardefs()
    {
        global $dictionary;
        $dynamicField = new DynamicField('Users');
        VardefManager::loadVardef('Users', 'User');
        $dynamicField->saveToVardef('Users', $dictionary['User']['fields']);
        //Test that we have refreshed the Employees vardef
        $this->assertTrue(file_exists('cache/modules/Employees/Employeevardefs.php'), 'cache/modules/Employees/Emloyeevardefs.php file not created');

        //Test that status is not set to be required
        $this->assertFalse($dictionary['Employee']['fields']['status']['required'], 'status field set to required');

        //Test that the studio attribute is set to false for status field
        $this->assertFalse($dictionary['Employee']['fields']['status']['studio'], 'status field studio not set to false');
    }

}