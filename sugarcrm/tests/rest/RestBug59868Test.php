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

/**
 * Bug 59868 - clients dont agree on how to handle quoted int app string keys
 */
class RestBug59868Test extends RestTestBase
{
    
    public function setUp()
    {
        parent::setUp();

        // Clear the metadata cache to ensure a fresh load of data
        $this->_clearMetadataCache();
    }
    
    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @group Bug59868
     * @group rest
     */
    public function testAppListStringsConvertedCorrectlyInMetadataRequest()
    {
        $this->_clearMetadataCache();
        $reply = $this->_restCall('metadata');

        $json = file_get_contents($GLOBALS['sugar_config']['site_url'] . '/' . $reply['reply']['labels']['en_us']);

        $object = json_decode($json);
        $this->assertTrue(is_object($object->app_list_strings->Elastic_boost_options), "App list string wasnt cast to object");
        $this->assertTrue(isset($object->app_list_strings->industry_dom->_empty_), "App list string wasnt left as an array");
    }
}