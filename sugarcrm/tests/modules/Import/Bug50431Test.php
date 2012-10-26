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
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


require_once('modules/Import/views/view.step3.php');

/**
 * Bug50431Test.php
 *
 * This file tests the getMappingClassName function in modules/Import/views/view.step3.php
 *
 */
class Bug50431Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $customMappingFile = 'custom/modules/Import/maps/ImportMapCustomTestImportToken.php';
    private $customMappingFile2 = 'custom/modules/Import/maps/ImportMapTestImportToken.php';
    private $customMappingFile3 = 'custom/modules/Import/maps/ImportMapOther.php';
    private $outOfBoxTestFile = 'modules/Import/maps/ImportMapTestImportToken.php';
    private $source = 'TestImportToken';

    public function setUp()
    {
        if (!is_dir('custom/modules/Import/maps'))
        {
            mkdir_recursive('custom/modules/Import/maps');
        }

        file_put_contents($this->customMappingFile, '<?php class ImportMapCustomTestImportToken { } ');
        file_put_contents($this->customMappingFile2, '<?php class ImportMapTestImportToken { } ');
        file_put_contents($this->customMappingFile3, '<?php class ImportMapOther { } ');
        file_put_contents($this->outOfBoxTestFile, '<?php class ImportMapTestImportTokenOutOfBox { } ');
        SugarAutoLoader::addToMap($this->customMappingFile, false);
        SugarAutoLoader::addToMap($this->customMappingFile2, false);
        SugarAutoLoader::addToMap($this->customMappingFile3, false);
        SugarAutoLoader::addToMap($this->outOfBoxTestFile, false);
    }

    public function tearDown()
    {
        if(file_exists($this->customMappingFile))
        {
            unlink($this->customMappingFile);
            SugarAutoLoader::delFromMap($this->customMappingFile, false);
        }

        if(file_exists($this->customMappingFile2))
        {
            unlink($this->customMappingFile2);
            SugarAutoLoader::delFromMap($this->customMappingFile2, false);
        }

        if(file_exists($this->customMappingFile3))
        {
            unlink($this->customMappingFile3);
            SugarAutoLoader::delFromMap($this->customMappingFile3, false);
        }

        if(file_exists($this->outOfBoxTestFile))
        {
            unlink($this->outOfBoxTestFile);
            SugarAutoLoader::delFromMap($this->outOfBoxTestFile, false);
        }
    }

    public function testGetMappingClassName()
    {
        $view = new Bug50431ImportViewStep3Mock();
        $result = $view->getMappingClassName($this->source);

        $this->assertEquals('ImportMapCustomTestImportToken', $result, 'Failed to load ' . $this->customMappingFile);

        unlink($this->customMappingFile);
        SugarAutoLoader::delFromMap($this->customMappingFile, false);
        $result = $view->getMappingClassName($this->source);

        $this->assertEquals('ImportMapTestImportToken', $result, 'Failed to load ' . $this->customMappingFile2);

        unlink($this->customMappingFile2);
        SugarAutoLoader::delFromMap($this->customMappingFile2, false);

        $result = $view->getMappingClassName($this->source);

        $this->assertEquals('ImportMapTestImportToken', $result, 'Failed to load ' . $this->outOfBoxTestFile);

        unlink($this->outOfBoxTestFile);
        SugarAutoLoader::delFromMap($this->outOfBoxTestFile, false);

        $result = $view->getMappingClassName($this->source);

        $this->assertEquals('ImportMapOther', $result, 'Failed to load ' . $this->customMappingFile3);
    }

}


class Bug50431ImportViewStep3Mock extends ImportViewStep3
{
    public function getMappingClassName($source)
    {
        return parent::getMappingClassName($source);
    }
}