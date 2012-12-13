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

require_once 'include/MetaDataManager/MetaDataManager.php';

/**
 * Bug 58926 
 */
class Bug58926Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
    }
    
    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Tests if an app_list_string or app_string has special html characters in it if it will be decoded properly in the MetadataManager
     * when requested
     * 
     * @group Bug58926
     */
    public function testAppStringsWithSpecialChars()
    {

        $result = array(
                'app_list_strings' => array(
                        'moduleList' => array(
                                'Leads' => "Lead's Are Special",
                            ),
                        'moduleListSingular' => array(
                                'Leads' => "Leads' Are Special",
                            ),
                    ),
                'app_strings' => array(
                        'LBL_NEXT' => "Next's Are the worst",
                    ),
            );

        $mm = new MetaDataManagerBug58926($GLOBALS['current_user']);
        
        $test['app_list_strings']['moduleList']['Leads'] = $mm->getDecodeStrings("Lead&#39;s Are Special");
        $test['app_list_strings']['moduleListSingular']['Leads'] = $mm->getDecodeStrings("Leads&#39; Are Special");
        $test['app_strings']['LBL_NEXT'] = $mm->getDecodeStrings("Next&#39;s Are the worst");
                

        $this->assertEquals($test, $result, "Decoding did not work");
    }
}

/**
 * Accessor class to the metadatamanager to allow access to protected methods
 */
class MetaDataManagerBug58926 extends MetaDataManager
{
    public function getDecodeStrings($data)
    {
        return $this->decodeStrings($data);
    }
}