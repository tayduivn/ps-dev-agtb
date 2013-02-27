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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('modules/ModuleBuilder/controller.php');

/**
 * Bug #51172
 * Employees |  Employees custom fields not working
 *
 * @author imatsiushyna@sugarcrm.com
 * @ticket 51172
 */

class Bug51172Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     *  @var string name custom fields
     */
    protected $field_name = 'test_bug51172';

    /**
     *  @var string modules name
     */
    protected $module = 'Employees';
    protected $add_module = 'Users';

    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        $_REQUEST = array();
        sugar_cache_clear('mod_strings.en_us');

        if(file_exists('custom/modules/'.$this->module.'/language/en_us.lang.php'))
        {
            unlink('custom/modules/'.$this->module.'/language/en_us.lang.php');
        }
        if(file_exists('custom/modules/'.$this->add_module.'/language/en_us.lang.php'))
        {
            unlink('custom/modules/'.$this->add_module.'/language/en_us.lang.php');
        }

        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * @return array
     */
    public function getRequestData()
    {
        return array (
            'name' => $this->field_name,
            'view_module' => $this->module,
            'label' => 'LBL_' . strtoupper($this->field_name),
            'labelValue' => $this->field_name,
        );
    }

    /**
     * @group 51172
     * Check that the label custom fields of Employees module was saved also for Users module
     *
     * @return void
     */
    public function testSaveLabelForCustomFields()
    {
        $_REQUEST = $this->getRequestData();

        $mb = new ModuleBuilderController();
        $mb ->action_SaveLabel();

        $mod_strings = return_module_language($GLOBALS['current_language'], $this->add_module);

        //assert that array $mod_strings Users module contains current label
        $this->assertArrayHasKey( $_REQUEST['label'], $mod_strings);
    }
}
