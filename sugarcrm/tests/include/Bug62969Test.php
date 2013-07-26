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


require_once 'include/utils.php';

/**
 * @ticket 62969
 */
class Bug62969Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $_customDir = 'custom/include/language';
    protected $_customFile = 'en_us.lang.php';
    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');

        // create a custom language file
        $customLangFileContent = <<<EOQ
<?php
\$app_list_strings['parent_type_display']=array (
  'Accounts' => 'Account',
  'Contacts' => 'Contact',
  'Tasks' => 'Task',
  'Opportunities' => 'Opportunity',
  'Products' => 'Product',
  'Quotes' => 'Quote',
  'Bugs' => 'Bug Tracker',
  'Cases' => 'Case',
  'Leads' => 'Lead',
  'Project' => 'Project',
  'ProjectTask' => 'Project Task',
  //'Prospects' => 'Target',
);
EOQ;
        if (!file_exists($this->_customDir)) {
            mkdir($this->_customDir);
        }
        file_put_contents($this->_customDir . '/' . $this->_customFile, $customLangFileContent);

        // add to loader map
        SugarAutoLoader::addToMap($this->_customDir . '/' . $this->_customFile, true);

        // clear cache so it can be reloaded later
        $cache_key = 'app_list_strings.'.$GLOBALS['current_language'];
       	sugar_cache_clear($cache_key);
    }

    public function tearDown()
    {
        // remove the custom language file
        if (file_exists($this->_customDir . '/' . $this->_customFile)) {
            unlink($this->_customDir . '/' . $this->_customFile);
        }

        // delete from loader map
        SugarAutoLoader::delFromMap($this->_customDir . '/' . $this->_customFile, true);

        // clear cache so it can be reloaded later
        $cache_key = 'app_list_strings.'.$GLOBALS['current_language'];
       	sugar_cache_clear($cache_key);

        // reload app_list_strings
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);

        SugarTestHelper::tearDown();
    }

    /*
     * to test that the custom array is used for parent_type_display
     */
    public function testBug62969()
    {
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        $this->assertArrayNotHasKey('Prospects', $GLOBALS['app_list_strings']['parent_type_display'], 'Should not have Prospects');
    }
}
