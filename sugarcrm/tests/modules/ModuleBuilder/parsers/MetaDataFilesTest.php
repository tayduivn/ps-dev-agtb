<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/en/msa/master_subscription_agreement_11_April_2011.pdf
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

/**
 * MetaDataFilesTest
 *
 * This test checks to see that the correct files are loaded from the clients/ directories
 *
 *
 */

require_once('modules/ModuleBuilder/parsers/MetaDataFiles.php');

class MetaDataFilesTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->createdFiles = array();
        SugarTestHelper::setUp('app_list_strings');
    }

    public function tearDown()
    {
        foreach ( $this->createdFiles as $file ) {
            SugarAutoLoader::unlink($file);
        }
        foreach ( $this->createdDirs as $dir ) {
            SugarAutoLoader::unlink($dir);
        }
        
        SugarAutoLoader::saveMap();
    }

    public function testLoadingFieldTemplate()
    {
        $this->createdDirs[] = 'clients/base/fields/fo';
        SugarAutoLoader::ensureDir($this->createdDirs[0]);
        
        $this->createdFiles[] = 'clients/base/fields/fo/rizzle.hbt';
        SugarAutoLoader::put($this->createdFiles[0],'FO RIZZLE (base)');

        $fileList = MetaDataFiles::getClientFiles(array('base'),'field');
        
        $this->assertArrayHasKey($this->createdFiles[0],$fileList,"The file list should contain fo rizzle.");

        $fileContents = MetaDataFiles::getClientFileContents(array('fo/rizzle.hbt'=>$fileList[$this->createdFiles[0]]),'field');

        $this->assertArrayHasKey('fo',$fileContents,"Didn't find the fo section.");
        $this->assertArrayHasKey('templates',$fileContents['fo'],"Didn't figure out that rizzle.hbt was a template");
        $this->assertArrayHasKey('rizzle',$fileContents['fo']['templates'],"Didn't correctly put rizzle in the template section");
        $this->assertEquals('FO RIZZLE (base)',$fileContents['fo']['templates']['rizzle'],"Did not correctly read in the contents of the rizzle template");
    }

    public function testLoadingFieldController()
    {
        $this->createdDirs[] = 'clients/base/fields/fo';
        SugarAutoLoader::ensureDir($this->createdDirs[0]);
        $this->createdDirs[] = 'clients/mobile/fields/fo';
        SugarAutoLoader::ensureDir($this->createdDirs[1]);
        
        $this->createdFiles[] = 'clients/base/fields/fo/fo.js';
        $controllerContentsBase = 'console.log("fo"); // (base/controller)';
        SugarAutoLoader::put($this->createdFiles[0],$controllerContentsBase);

        $this->createdFiles[] = 'clients/base/fields/fo/rizzle.hbt';
        $templateContentsBase = 'FO RIZZLE (base/template)';
        SugarAutoLoader::put($this->createdFiles[1],$templateContentsBase);


        $this->createdFiles[] = 'clients/mobile/fields/fo/fo.js';
        $controllerContentsMobile = 'console.log("fo"); // (mobile/controller)';
        SugarAutoLoader::put($this->createdFiles[2],$controllerContentsMobile);

        $this->createdFiles[] = 'clients/mobile/fields/fo/rizzle.hbt';
        $templateContentsMobile = 'FO RIZZLE (mobile/template)';
        SugarAutoLoader::put($this->createdFiles[3],$templateContentsMobile);

        $fileList = MetaDataFiles::getClientFiles(array('mobile','base'),'field');

        $justMyFileList = array();
        foreach ( $this->createdFiles as $fileName) {
            $this->assertArrayHasKey($fileName,$fileList,"The file list should contain $fileName");
            $justMyFileList[$fileName] = $fileList[$fileName];
        }

        $fileContents = MetaDataFiles::getClientFileContents($justMyFileList,'field');

        $this->assertArrayHasKey('fo',$fileContents,"Didn't find the fo section.");
        $this->assertArrayHasKey('templates',$fileContents['fo'],"Didn't figure out that rizzle.hbt was a template");
        $this->assertArrayHasKey('rizzle',$fileContents['fo']['templates'],"Didn't correctly put rizzle in the template section");
        $this->assertEquals($templateContentsMobile,$fileContents['fo']['templates']['rizzle'],"Did not correctly read in the mobile contents of the rizzle template");

        $this->assertArrayHasKey('controller',$fileContents['fo'],"Didn't figure out that fo.js was a controller");
        $this->assertArrayHasKey('mobile',$fileContents['fo']['controller'],"Didn't find the mobile controller");
        $this->assertArrayHasKey('base',$fileContents['fo']['controller'],"Didn't find the base controller");
        $this->assertEquals($controllerContentsBase,$fileContents['fo']['controller']['base'],"Didn't correctly place the fo (base) controller in the base section");
        $this->assertEquals($controllerContentsMobile,$fileContents['fo']['controller']['mobile'],"Didn't correctly place the fo (mobile) controller in the mobile section");


        $fileList = MetaDataFiles::getClientFiles(array('base'),'field');

        $justMyFileList = array();
        $this->assertArrayHasKey($this->createdFiles[0],$fileList,"The file list should contain ".$this->createdFiles[0]);
        $this->assertArrayHasKey($this->createdFiles[1],$fileList,"The file list should contain ".$this->createdFiles[1]);
        $this->assertArrayNotHasKey($this->createdFiles[2],$fileList,"The file list should NOT contain ".$this->createdFiles[2]);
        $this->assertArrayNotHasKey($this->createdFiles[3],$fileList,"The file list should NOT contain ".$this->createdFiles[3]);

        $justMyFileList[] = $fileList[$this->createdFiles[0]];
        $justMyFileList[] = $fileList[$this->createdFiles[1]];

        $fileContents = MetaDataFiles::getClientFileContents($justMyFileList,'field');

        $this->assertArrayHasKey('fo',$fileContents,"Didn't find the fo section. 2");
        $this->assertArrayHasKey('templates',$fileContents['fo'],"Didn't figure out that rizzle.hbt was a template 2");
        $this->assertArrayHasKey('rizzle',$fileContents['fo']['templates'],"Didn't correctly put rizzle in the template section 2");
        $this->assertEquals($templateContentsBase,$fileContents['fo']['templates']['rizzle'],"Did not correctly read in the base contents of the rizzle template");

        $this->assertArrayHasKey('controller',$fileContents['fo'],"Didn't figure out that fo.js was a controller 2");
        $this->assertArrayNotHasKey('mobile',$fileContents['fo']['controller'],"Found the mobile controller when it shouldn't have");
        $this->assertArrayHasKey('base',$fileContents['fo']['controller'],"Didn't find the base controller");
        $this->assertEquals($controllerContentsBase,$fileContents['fo']['controller']['base'],"Didn't correctly place the fo (base) controller in the base section");

    }

    public function testLoadingViewEverything()
    {
        $this->createdDirs[] = 'modules/Accounts/clients/base/views/fo';
        SugarAutoLoader::ensureDir($this->createdDirs[0]);
        $this->createdDirs[] = 'modules/Accounts/clients/mobile/views/fo';
        SugarAutoLoader::ensureDir($this->createdDirs[1]);
        
        $this->createdFiles[] = 'modules/Accounts/clients/base/views/fo/fo.js';
        $baseController = 'console.log("fo"); // (base/controller)';
        SugarAutoLoader::put($this->createdFiles[0],$baseController);

        $this->createdFiles[] = 'modules/Accounts/clients/base/views/fo/rizzle.hbt';
        $baseTemplate = 'FO RIZZLE (base)';
        SugarAutoLoader::put($this->createdFiles[1],$baseTemplate);

        $this->createdFiles[] = 'modules/Accounts/clients/base/views/fo/fo.php';
        $baseMetaContents = '<?php'."\n".'$viewdefs["Accounts"]["base"]["view"]["fo"] = array("erma"=>"base");';
        SugarAutoLoader::put($this->createdFiles[2],$baseMetaContents);

        $this->createdFiles[] = 'modules/Accounts/clients/mobile/views/fo/fo.js';
        $mobileController = 'console.log("fo"); // (mobile/controller)';
        SugarAutoLoader::put($this->createdFiles[3],$mobileController);

        $this->createdFiles[] = 'modules/Accounts/clients/mobile/views/fo/rizzle.hbt';
        $mobileTemplate = 'FO RIZZLE (mobile)';
        SugarAutoLoader::put($this->createdFiles[4],$mobileTemplate);

        $this->createdFiles[] = 'modules/Accounts/clients/mobile/views/fo/fo.php';
        $mobileMetaContents = '<?php'."\n".'$viewdefs["Accounts"]["mobile"]["view"]["fo"] = array("erma"=>"mobile");';
        SugarAutoLoader::put($this->createdFiles[5],$mobileMetaContents);

        $fileList = MetaDataFiles::getClientFiles(array('mobile','base'),'view','Accounts');

        $justMyFileList = array();
        foreach ( $this->createdFiles as $fileName) {
            $this->assertArrayHasKey($fileName,$fileList,"The file list should contain $fileName");
            $justMyFileList[$fileName] = $fileList[$fileName];
        }

        $fileContents = MetaDataFiles::getClientFileContents($justMyFileList,'view','Accounts');

        $this->assertArrayHasKey('fo',$fileContents,"Didn't find the fo section.");
        $this->assertArrayHasKey('templates',$fileContents['fo'],"Didn't figure out that rizzle.hbt was a template");
        $this->assertArrayHasKey('rizzle',$fileContents['fo']['templates'],"Didn't correctly put rizzle in the template section");
        $this->assertEquals($mobileTemplate,$fileContents['fo']['templates']['rizzle'],"Did not correctly read in the mobile contents of the rizzle template");

        $this->assertArrayHasKey('controller',$fileContents['fo'],"Didn't figure out that fo.js was a controller");
        $this->assertArrayHasKey('mobile',$fileContents['fo']['controller'],"Didn't find the mobile controller");
        $this->assertArrayHasKey('base',$fileContents['fo']['controller'],"Didn't find the base controller");
        $this->assertEquals($baseController,$fileContents['fo']['controller']['base'],"Didn't correctly place the fo (base) controller in the base section");
        $this->assertEquals($mobileController,$fileContents['fo']['controller']['mobile'],"Didn't correctly place the fo (mobile) controller in the mobile section");

        $this->assertArrayHasKey('fo',$fileContents,"Didn't find the fo section.");
        $this->assertArrayHasKey('meta',$fileContents['fo'],"Didn't find the metadata for fo");
        $this->assertArrayHasKey('erma',$fileContents['fo']['meta'],"Didn't correctly put erma in the metadata section");
        $this->assertEquals('mobile',$fileContents['fo']['meta']['erma'],"Did not correctly read in the mobile metadata");

        $fileList = MetaDataFiles::getClientFiles(array('base'),'view','Accounts');

        $justMyFileList = array();
        $this->assertArrayHasKey($this->createdFiles[0],$fileList,"2 The file list should contain ".$this->createdFiles[0]);
        $this->assertArrayHasKey($this->createdFiles[1],$fileList,"2 The file list should contain ".$this->createdFiles[1]);
        $this->assertArrayHasKey($this->createdFiles[2],$fileList,"2 The file list should contain ".$this->createdFiles[2]);
        $this->assertArrayNotHasKey($this->createdFiles[3],$fileList,"2 The file list should NOT contain ".$this->createdFiles[3]);
        $this->assertArrayNotHasKey($this->createdFiles[4],$fileList,"2 The file list should NOT contain ".$this->createdFiles[4]);
        $this->assertArrayNotHasKey($this->createdFiles[5],$fileList,"2 The file list should NOT contain ".$this->createdFiles[5]);

        $justMyFileList[] = $fileList[$this->createdFiles[0]];
        $justMyFileList[] = $fileList[$this->createdFiles[1]];
        $justMyFileList[] = $fileList[$this->createdFiles[2]];

        $fileContents = MetaDataFiles::getClientFileContents($justMyFileList,'view','Accounts');

        $this->assertArrayHasKey('fo',$fileContents,"Didn't find the fo section. 2");
        $this->assertArrayHasKey('templates',$fileContents['fo'],"Didn't figure out that rizzle.hbt was a template 2");
        $this->assertArrayHasKey('rizzle',$fileContents['fo']['templates'],"Didn't correctly put rizzle in the template section 2");
        $this->assertEquals($baseTemplate,$fileContents['fo']['templates']['rizzle'],"Did not correctly read in the base contents of the rizzle template");

        $this->assertArrayHasKey('controller',$fileContents['fo'],"Didn't figure out that fo.js was a controller 2");
        $this->assertArrayNotHasKey('mobile',$fileContents['fo']['controller'],"Found the mobile controller when it shouldn't have");
        $this->assertArrayHasKey('base',$fileContents['fo']['controller'],"Didn't find the base controller");
        $this->assertEquals($baseController,$fileContents['fo']['controller']['base'],"Didn't correctly place the fo (base) controller in the base section");

        $this->assertArrayHasKey('fo',$fileContents,"Didn't find the fo section. 2");
        $this->assertArrayHasKey('meta',$fileContents['fo'],"Didn't find the metadata for fo. 2");
        $this->assertArrayHasKey('erma',$fileContents['fo']['meta'],"Didn't correctly put erma in the metadata section. 2");
        $this->assertEquals('base',$fileContents['fo']['meta']['erma'],"Did not correctly read in the base metadata");
    }


}