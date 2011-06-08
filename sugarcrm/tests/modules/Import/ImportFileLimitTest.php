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

class ImportFileLimitTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $_fileSample1;
    protected $_fileSample2;
    protected $_fileSample3;
    protected $_fileSample4;

    protected $_fileLineCount1 = 555;
    protected $_fileLineCount2 = 111;
    protected $_fileLineCount3 = 2;
    protected $_fileLineCount4 = 0;

    public function setUp()
    {
    	$GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    	$this->_fileSample1 = SugarTestImportUtilities::createFile( $this->_fileLineCount1 );
        $this->_fileSample2 = SugarTestImportUtilities::createFile( $this->_fileLineCount2 );
        $this->_fileSample3 = SugarTestImportUtilities::createFile( $this->_fileLineCount3 );
        $this->_fileSample4 = SugarTestImportUtilities::createFile( $this->_fileLineCount4 );
    }

    public function tearDown()
    {
        SugarTestImportUtilities::removeAllCreatedFiles();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    public function testGetFileRowCount()
    {
        $this->assertEquals($this->_fileLineCount1, ImportFile::getNumberOfLinesInfile( $this->_fileSample1) );
        $this->assertEquals($this->_fileLineCount2, ImportFile::getNumberOfLinesInfile( $this->_fileSample2) );
        $this->assertEquals($this->_fileLineCount3, ImportFile::getNumberOfLinesInfile( $this->_fileSample3) );
        $this->assertEquals($this->_fileLineCount4, ImportFile::getNumberOfLinesInfile( $this->_fileSample4) );
    }
}

