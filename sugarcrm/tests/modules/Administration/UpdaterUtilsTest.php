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
require_once 'modules/Administration/updater_utils.php';

class UpdaterUtilsTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->oldLicense = $GLOBALS['license'];
        $GLOBALS['license'] = new UpdateUtilsSettingMock();
        $this->settings = $GLOBALS['license'];

        $this->fakeLicense = array(
            'license_users' => 50,
            'num_lic_oc' => 0,
            'license_num_portal_users' => 500,
            'license_validation_key' => 'abcdefgh',
            'license_vk_end_date' => gmdate('Y-m-d',gmmktime(1,2,3,4,5,gmdate('Y')+2)),
            'license_expire_date' => gmdate('Y-m-d',gmmktime(1,2,3,4,5,gmdate('Y')+2)),
            'enforce_portal_user_limit' => 1,
            'enforce_user_limit' => 1,
        );
    }

    public function tearDown()
    {
        $GLOBALS['license'] = $this->oldLicense;
    }

    public function testEnforcePortalUserLimit()
    {
        $fakeLicenseData = $this->fakeLicense;
        
        checkDownloadKey($fakeLicenseData);
        $this->assertTrue((bool)$this->settings->savedSettings['license']['enforce_portal_user_limit'],"Not enforcing portal user limit when we should be.");

        $GLOBALS['license'] = $this->settings;
        $this->settings->savedSettings = array();
        $fakeLicenseData['enforce_portal_user_limit'] = '0';
        checkDownloadKey($fakeLicenseData);
        $this->assertFalse((bool)$this->settings->savedSettings['license']['enforce_portal_user_limit'],"Enforcing portal user limit when we shouldn't be.");
        
    }
}

class UpdateUtilsSettingMock
{
    public $savedSettings = array();

    public function saveSetting($section, $key, $data) {
        $this->savedSettings[$section][$key] = $data;
    }
}