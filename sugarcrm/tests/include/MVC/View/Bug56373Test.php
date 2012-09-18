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
 
require_once('include/MVC/View/SugarView.php');

class Bug56373Test extends Sugar_PHPUnit_Framework_TestCase
{


	// Currently, getBreadCrumbList in BreadCrumbStack.php limits you to 10
	// Also, the Constructor in BreadCrumbStack.php limits it to 10 too.
    /*
     * @group bug56373
     */
    public function testProcessRecentRecordsForHTML() {
        $view = new SugarViewMock();

        $history = array(
                        array('item_summary' => '&lt;img src=x alert(true)', 'module_name'=>'Accounts'),
                        array('item_summary' => '&lt;script&gt;alert(hi)&lt;/script&gt;', 'module_name'=>'Accounts'),


        );
        $out = $view->processRecentRecords($history);
        foreach($out as $key => $row) {
            $this->assertEquals($row['item_summary'], $history[$key]['item_summary']);
           $this->assertNotRegExp('/[<>]/',$row['item_summary_short']);
           $this->assertContains($history[$key]['item_summary'], $row['image']);
        }

    }

}

class SugarViewMock extends SugarView
{
    public function processRecentRecords($history)
    {
        return parent::processRecentRecords($history);
    }
}