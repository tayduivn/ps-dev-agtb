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

/**
 * Bug49991Test.php
 * @author Collin Lee
 *
 * This test will check the enhancements made so that we may better load custom files.  While the bug was
 * originally filed for the Connectors module, this change was applied to the SugarView layer to allow all
 * views to take advantage of not having to repeatedly check the custom directory for the presence of a file.
 */
require_once('include/MVC/View/SugarView.php');

class Bug49991Test extends Sugar_PHPUnit_Framework_TestCase
{

var $mock;
var $sourceBackup;

public function setUp()
{
    $this->mock = new Bug49991SugarViewMock();
    mkdir_recursive('custom/modules/Connectors/tpls');
    if(file_exists('custom/modules/Connectors/tpls/source_properties.tpl'))
    {
        $this->sourceBackup = file_get_contents('custom/modules/Connectors/tpls/source_properties.tpl');
    }
    copy('modules/Connectors/tpls/source_properties.tpl', 'custom/modules/Connectors/tpls/source_properties.tpl');
    SugarAutoLoader::addToMap('custom/modules/Connectors/tpls/source_properties.tpl', false);
}

public function tearDown()
{
    if(!empty($this->sourceBackup))
    {
        file_put_contents('custom/modules/Connectors/tpls/source_properties.tpl', $this->sourceBackup);
    } else {
        unlink('custom/modules/Connectors/tpls/source_properties.tpl');
        SugarAutoLoader::delFromMap('custom/modules/Connectors/tpls/source_properties.tpl', false);
    }
    unset($this->mock);
}

/**
 * testGetCustomFilePathIfExists
 *
 * Simple test just to assert that we have found the custom file
 */
public function testGetCustomFilePathIfExists()
{
    $this->assertEquals('custom/modules/Connectors/tpls/source_properties.tpl', $this->mock->getCustomFilePathIfExistsTest('modules/Connectors/tpls/source_properties.tpl'), 'Could not find the custom tpl file');
}

}

class Bug49991SugarViewMock extends SugarView {

    public function getCustomFilePathIfExistsTest($file)
    {
        return $this->getCustomFilePathIfExists($file);
    }
}