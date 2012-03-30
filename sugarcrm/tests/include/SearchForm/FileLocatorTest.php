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

require_once 'include/SearchForm/SearchForm2.php';

class FileLocatorTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $form;
    protected $tempfiles = array();

    public function setUp()
    {
        $acc = new Account();
        $this->form = new SearchFormMock($acc, "Accounts");
    }

    public function tearDown()
    {
        if(!empty($this->tempfiles)) {
            foreach($this->tempfiles as $file) {
                @unlink($file);
            }
        }
    }


    /**
     * Check file locator
     */
    public function testFileLocatorOptions()
    {
        $options = $this->form->getOptions();
        $this->assertNotEmpty($options['locator_class_params'][0]);
        $this->assertContains("custom/modules/Accounts/tpls/SearchForm", $options['locator_class_params'][0]);
        $this->assertContains("modules/Accounts/tpls/SearchForm", $options['locator_class_params'][0]);
    }

    /**
     * Check file locator
     */
    public function testFileLocatorSetOptions()
    {
        $paths = array('a', 'b', 'c');

        $options = array(
            'locator_class' => 'FileLocator',
            'locator_class_params' => array(
                $paths
            )
            );
        $this->form->setOptions($options);
        $options = $this->form->getOptions();
        $this->assertEquals($paths, $options['locator_class_params'][0]);
    }

    /**
     * Check file locator
     */
    public function testFileLocatorOptionsCtor()
    {
        $paths = array('a', 'b', 'c');

        $options = array(
            'locator_class' => 'FileLocator',
            'locator_class_params' => array(
                $paths
            )
            );
        $this->form = new SearchForm(new Account(), "Accounts", 'index', $options);
        $options = $this->form->getOptions();
        $this->assertEquals($paths, $options['locator_class_params'][0]);
    }

    public function testFileLocatorFindSystemFile()
    {
        $this->assertEquals("include/SearchForm/tpls/SearchFormGenericAdvanced.tpl",
            $this->form->locateFile('SearchFormGenericAdvanced.tpl'),
            "Wrong file location"
            );
    }

    public function testFileLocatorFindCustomFile()
    {
        sugar_mkdir('custom/include/SearchForm/tpls/', 0755, true);
        sugar_mkdir('custom/modules/Accounts/tpls/SearchForm', 0755, true);
        $this->tempfiles[]= 'custom/include/SearchForm/tpls/FileLocatorTest.tpl';
        file_put_contents('custom/include/SearchForm/tpls/FileLocatorTest.tpl', "unittest");
        $this->assertEquals("custom/include/SearchForm/tpls/FileLocatorTest.tpl",
            $this->form->locateFile('FileLocatorTest.tpl'),
            "Wrong file location"
            );

        $this->tempfiles[] = "custom/modules/Accounts/tpls/SearchForm/FileLocatorTest.tpl";
        file_put_contents('custom/modules/Accounts/tpls/SearchForm/FileLocatorTest.tpl', "unittest");
        $this->assertEquals("custom/modules/Accounts/tpls/SearchForm/FileLocatorTest.tpl",
            $this->form->locateFile('FileLocatorTest.tpl'),
            "Wrong file location"
            );
    }
}

class SearchFormMock extends SearchForm
{
    public function locateFile($file)
    {
        return parent::locateFile($file);
    }
}
