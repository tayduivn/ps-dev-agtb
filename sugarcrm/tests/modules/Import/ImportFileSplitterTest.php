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
 
require_once 'modules/Import/ImportFile.php';
require_once 'modules/Import/ImportFileSplitter.php';

class ImportFileSplitterTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $_goodFile;
    protected $_badFile;
    
    public function setUp()
    {
    	$GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    	$this->_goodFile = SugarTestImportUtilities::createFile();
		$this->_badFile  = $GLOBALS['sugar_config']['import_dir'].'thisfileisntthere'.date("YmdHis");
		$this->_whiteSpaceFile  = SugarTestImportUtilities::createFileWithWhiteSpace();
    }
    
    public function tearDown()
    {
        SugarTestImportUtilities::removeAllCreatedFiles();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }
    
    public function testLoadNonExistantFile()
    {
        $importFileSplitter = new ImportFileSplitter($this->_badFile);
        $this->assertFalse($importFileSplitter->fileExists());
    }
    
    public function testLoadGoodFile()
    {
        $importFileSplitter = new ImportFileSplitter($this->_goodFile);
        $this->assertTrue($importFileSplitter->fileExists());
    }
    
    public function testSplitSourceFile()
    {
        $importFileSplitter = new ImportFileSplitter($this->_goodFile);
        $importFileSplitter->splitSourceFile(',','"');
        
        $this->assertEquals($importFileSplitter->getRecordCount(),2000);
        $this->assertEquals($importFileSplitter->getFileCount(),2);
    }
    
    public function testSplitSourceFileNoEnclosure()
    {
        $importFileSplitter = new ImportFileSplitter($this->_goodFile);
        $importFileSplitter->splitSourceFile(',','');
        
        $this->assertEquals($importFileSplitter->getRecordCount(),2000);
        $this->assertEquals($importFileSplitter->getFileCount(),2);
    }
    
    public function testSplitSourceFileWithHeader()
    {
        $importFileSplitter = new ImportFileSplitter($this->_goodFile);
        $importFileSplitter->splitSourceFile(',','"',true);
        
        $this->assertEquals($importFileSplitter->getRecordCount(),1999);
        $this->assertEquals($importFileSplitter->getFileCount(),2);
    }
    
    public function testSplitSourceFileWithThreshold()
    {
        $importFileSplitter = new ImportFileSplitter($this->_goodFile,500);
        $importFileSplitter->splitSourceFile(',','"');
        
        $this->assertEquals($importFileSplitter->getRecordCount(),2000);
        $this->assertEquals($importFileSplitter->getFileCount(),4);
    }
    
    public function testGetSplitFileName()
    {
        $importFileSplitter = new ImportFileSplitter($this->_goodFile);
        $importFileSplitter->splitSourceFile(',','"');
        
        $this->assertEquals($importFileSplitter->getSplitFileName(0),"{$this->_goodFile}-0");
        $this->assertEquals($importFileSplitter->getSplitFileName(1),"{$this->_goodFile}-1");
        $this->assertEquals($importFileSplitter->getSplitFileName(2),false);
    }
	
	/**
	 * @ticket 25119
	 */
    public function testTrimSpaces()
    {
        $splitter = new ImportFileSplitter($this->_whiteSpaceFile);
        $splitter->splitSourceFile(',',' ',false);
        
        $csvString = file_get_contents("{$this->_whiteSpaceFile}-0");
        
        $this->assertEquals(
            trim(file_get_contents("{$this->_whiteSpaceFile}-0")),
            trim(file_get_contents("{$this->_whiteSpaceFile}"))
            );
    }
}
