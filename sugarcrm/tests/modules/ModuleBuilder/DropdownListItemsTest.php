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

require_once 'include/utils.php';

class DropdownListItemsTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected static $custFilePath = 'custom/include/required_list_items.php';

    public static function setupBeforeClass()
    {
        // Create a custom file that adds a new list and overrides the current
        $testItems = array(
            'test_list_1' => array(
                'list_item_0',
                'list_item_1',
                'list_item_2',
            ),
            'sales_stage_dom' => array(
                'Prospecting'
            ),
        );
        
        write_array_to_file('app_list_strings_required', $testItems, self::$custFilePath);
        SugarAutoLoader::addToMap(self::$custFilePath);
    }
    
    public static function tearDownAfterClass()
    {
        SugarAutoLoader::unlink(self::$custFilePath, true);
    }
    
    public function testGetRequiredDropdownListItems()
    {
        $items = getRequiredDropdownListItems();

        // Asserts we have known items
        $this->arrayHasKey('sales_stage_dom', $items, 'sales_stage_key was missing from the list of items');
        $this->arrayHasKey('test_list_1', $items, 'test_list_1 was missing from the list of items');

        // Tests overriding the OOTB required sales stage list
        $this->assertCount(1, $items['sales_stage_dom'], 'sales_stage_dome should have 1 item only');
    }
    
    public function testGetRequiredDropdownListItemsByDDL()
    {
        $items = getRequiredDropdownListItemsByDDL('test_list_1');

        $this->assertCount(3, $items, 'test_list_1 should contain only 3 items');
    }
}
