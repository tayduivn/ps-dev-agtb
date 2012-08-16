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

require_once('modules/UpgradeWizard/uw_utils.php');

/**
 * Bug50720Test.php
 * 
 * This test checks the upgrade_connectors method in modules/UpgradeWizard/uw_utils.php file.  In particular,
 * we want to ensure that upgrade_connectors will delete the custom connectors.php file.
 *
 */
class Bug50720Test extends Sugar_PHPUnit_Framework_TestCase
{
    var $customConnectors;
    var $file = 'custom/modules/Connectors/metadata/connectors.php';
    
    public function setUp() 
    {
        SugarTestHelper::setup('app_list_strings');
        if(file_exists($this->file))
        {
            $this->customConnectors = file_get_contents($this->file);
        } else {
            mkdir_recursive('custom/modules/Connectors/metadata');
            file_put_contents($this->file, '<?php ');
        }
    }

    public function tearDown() 
    {
        SugarTestHelper::tearDown();
        if(!empty($this->customConnectors))
        {
            file_put_contents($this->file, $this->customConnectors);
        } else if(file_exists($this->file)) {
            unlink($this->file);
        }
    }

    /**
     * testUpgradeConnectors
     *
     * This method calls upgrade_connectors and checks to make sure we have deleted the custom connectors.php file
     */
    public function testUpgradeConnectors() {
        upgrade_connectors();
        $this->assertTrue(!file_exists($this->file), 'upgrade_connectors did not remove file ' . $this->file);
    }
}