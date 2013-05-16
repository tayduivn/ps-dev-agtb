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
require_once 'tests/rest/RestTestBase.php';
require_once 'include/MetaDataManager/MetaDataManager.php';

class RestMetadataGlobalSearchEnabledTest extends RestTestBase
{

    public function setUp()
    {
        parent::setUp();
    }
    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Tests the getGlobalSearchEnabled method in the MetadataManager
     * @dataProvider moduleVardefDataProvider
     * @group rest
     */
    public function testGlobalSearchEnabled($platform, $seed, $vardefs, $expects, $failMessage)
    {
        $mm = new MetaDataManager($GLOBALS['current_user'], array($platform));
        $actual = $mm->getGlobalSearchEnabled($seed, $vardefs, $platform);
        $this->assertEquals($expects, $actual, $failMessage);
    }

    // Please see `failMessage` property to see what each run is testing for
    public function moduleVardefDataProvider()
    {
        return array(
            array(
                'platform' => 'base',
                'seed' => true,
                'vardefs' => array(),
                'expects' => true,
                'failMessage' => "When globalSearchEnabled not provided, should check if \$seed is Bean; if so should return true"
            ),
            array(
                'platform' => 'base',
                'seed' => false,
                'vardefs' => array(),
                'expects' => false,
                'failMessage' => "When globalSearchEnabled not provided, should check if \$seed is Bean; if NOT should return false"
            ),
            array(
                'platform' => 'base',
                'seed' => true,
                'vardefs' => array(
                    'globalSearchEnabled' => true,
                ),
                'expects' => true,
                'failMessage' => "When globalSearchEnabled used as 'global boolean', that value should be returned (truthy)"
            ),
            array(
                'platform' => 'base',
                'seed' => true,
                'vardefs' => array(
                    'globalSearchEnabled' => false,
                ),
                'expects' => false,
                'failMessage' => "When globalSearchEnabled used as 'global boolean', that value should be returned (falsy)"
            ),
            array(
                'platform' => 'portal',
                'seed' => true,
                'vardefs' => array(
                    'globalSearchEnabled' => array(
                        'portal' => true,
                        'base' => false
                    )
                ),
                'expects' => true,
                'failMessage' => "When globalSearchEnabled used as array with platform, should use value for current platform if exists (truthy)"
            ),
            array(
                'platform' => 'portal',
                'seed' => true,
                'vardefs' => array(
                    'globalSearchEnabled' => array(
                        'portal' => false,
                        'base' => true,
                    )
                ),
                'expects' => false,
                'failMessage' => "When globalSearchEnabled used as array with platform, should use value for current platform if exists (falsy) (even if another platform is truthy)"
            ),
            array(
                'platform' => 'portal',
                'seed' => true,
                'vardefs' => array(
                    'globalSearchEnabled' => array(
                        'notportal1' => false,
                        'notportal2' => false,
                    )
                ),
                'expects' => true,
                'failMessage' => "When globalSearchEnabled used as array but current platform not found should fallback to true ignoring other platform settings"
            ),
        );
    }
}
