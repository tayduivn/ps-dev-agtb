<?php
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement 
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.  
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may 
 *not use this file except in compliance with the License. Under the terms of the license, You 
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or 
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or 
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit 
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the 
 *Software without first paying applicable fees is strictly prohibited.  You do not have the 
 *right to remove SugarCRM copyrights from the source code or user interface. 
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and 
 * (ii) the SugarCRM copyright notice 
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer 
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.  
 ********************************************************************************/

require_once 'include/SugarObjects/SugarConfig.php';
require_once 'include/SugarObjects/VardefManager.php';

/**
 * @group bug32797
 */
class Bug32797Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $_old_sugar_config = null;

    public function setUp()
    {
        $this->_old_sugar_config = $GLOBALS['sugar_config'];
        $GLOBALS['sugar_config'] = array('require_accounts' => false);
    }

    public function tearDown()
    {
        $config = SugarConfig::getInstance();
        $config->clearCache();
        $GLOBALS['sugar_config'] = $this->_old_sugar_config;
    }

    public function vardefProvider()
    {
        return array(
            array(
                array('fields' => array('account_name' => array('required' => true))),
                array('fields' => array('account_name' => array('required' => false)))
            ),
            array(
                array('fields' => array('account_name' => array('required' => false))),
                array('fields' => array('account_name' => array('required' => false)))
            ),
            array(
                array('fields' => array('account_name' => array('required' => null))),
                array('fields' => array('account_name' => array('required' => false)))
            ),
            array(
                array('fields' => array('account_name' => array())),
                array('fields' => array('account_name' => array()))
            ),
            array(
                array('fields' => array()),
                array('fields' => array())
            )
        );
    }

    /**
     * @dataProvider vardefProvider
     */
    public function testApplyGlobalAccountRequirements($vardef, $vardefToCompare)
    {
        $this->assertEquals($vardefToCompare, VardefManager::applyGlobalAccountRequirements($vardef));
    }

    public function vardefProvider1()
    {
        return array(
            array(
                array('fields' => array('account_name' => array('required' => true))),
                array('fields' => array('account_name' => array('required' => true)))
            ),
            array(
                array('fields' => array('account_name' => array('required' => false))),
                array('fields' => array('account_name' => array('required' => true)))
            )
        );
    }

    /**
     * @dataProvider vardefProvider1
     */
    public function testApplyGlobalAccountRequirements1($vardef, $vardefToCompare)
    {
        $GLOBALS['sugar_config']['require_accounts'] = true;
        $this->assertEquals($vardefToCompare, VardefManager::applyGlobalAccountRequirements($vardef));
    }

    public function vardefProvider2()
    {
        return array(
            array(
                array('fields' => array('account_name' => array('required' => true))),
                array('fields' => array('account_name' => array('required' => true)))
            ),
            array(
                array('fields' => array('account_name' => array('required' => false))),
                array('fields' => array('account_name' => array('required' => false)))
            )
        );
    }

    /**
     * @dataProvider vardefProvider2
     */
    public function testApplyGlobalAccountRequirements2($vardef, $vardefToCompare)
    {
        unset($GLOBALS['sugar_config']['require_accounts']);
        $this->assertEquals($vardefToCompare, VardefManager::applyGlobalAccountRequirements($vardef));
    }
}