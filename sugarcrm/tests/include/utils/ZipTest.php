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

require_once('include/utils/zip_utils.php');
/**
 * @ticket 40957
 */
class ZipTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->testdir = sugar_cached("tests/include/utils/ziptest");
        sugar_mkdir($this->testdir.'/testarchive',null,true);
        sugar_touch($this->testdir.'/testarchive/testfile1.txt');
        sugar_touch($this->testdir.'/testarchive/testfile2.txt');
        sugar_touch($this->testdir.'/testarchive/testfile3.txt');
        sugar_mkdir($this->testdir.'/testarchiveoutput',null,true);
    }

    public function tearDown()
    {
        if ( is_dir($this->testdir) )
            rmdir_recursive($this->testdir);
    }

    public function testZipADirectory()
	{
		zip_dir($this->testdir.'/testarchive',$this->testdir.'/testarchive.zip');

		$this->assertTrue(file_exists($this->testdir.'/testarchive.zip'));
	}

	public function testZipADirectoryFailsWhenDirectorySpecifedDoesNotExists()
	{
	    $this->assertFalse(zip_dir($this->testdir.'/notatestarchive',$this->testdir.'/testarchive.zip'));
	}

	/**
     * @depends testZipADirectory
     */
    public function testExtractEntireArchive()
	{
	    zip_dir($this->testdir.'/testarchive',$this->testdir.'/testarchive.zip');
		unzip($this->testdir.'/testarchive.zip',$this->testdir.'/testarchiveoutput');

	    $this->assertTrue(file_exists($this->testdir.'/testarchiveoutput/testfile1.txt'));
	    $this->assertTrue(file_exists($this->testdir.'/testarchiveoutput/testfile2.txt'));
	    $this->assertTrue(file_exists($this->testdir.'/testarchiveoutput/testfile3.txt'));
	}

	/**
     * @depends testZipADirectory
     */
    public function testExtractSingleFileFromAnArchive()
	{
	    zip_dir($this->testdir.'/testarchive',$this->testdir.'/testarchive.zip');
		unzip_file($this->testdir.'/testarchive.zip','testfile1.txt',$this->testdir.'/testarchiveoutput');

	    $this->assertTrue(file_exists($this->testdir.'/testarchiveoutput/testfile1.txt'));
	    $this->assertFalse(file_exists($this->testdir.'/testarchiveoutput/testfile2.txt'));
	    $this->assertFalse(file_exists($this->testdir.'/testarchiveoutput/testfile3.txt'));
	}

	/**
     * @depends testZipADirectory
     */
    public function testExtractTwoIndividualFilesFromAnArchive()
	{
	    zip_dir($this->testdir.'/testarchive',$this->testdir.'/testarchive.zip');
		unzip_file($this->testdir.'/testarchive.zip',array('testfile2.txt','testfile3.txt'),$this->testdir.'/testarchiveoutput');

	    $this->assertFalse(file_exists($this->testdir.'/testarchiveoutput/testfile1.txt'));
	    $this->assertTrue(file_exists($this->testdir.'/testarchiveoutput/testfile2.txt'));
	    $this->assertTrue(file_exists($this->testdir.'/testarchiveoutput/testfile3.txt'));
	}

	public function testExtractFailsWhenArchiveDoesNotExist()
	{
	    $this->assertFalse(unzip($this->testdir.'/testarchivenothere.zip',$this->testdir.'/testarchiveoutput'));
	}

	public function testExtractFailsWhenExtractDirectoryDoesNotExist()
	{
	    $this->assertFalse(unzip($this->testdir.'/testarchive.zip',$this->testdir.'/testarchiveoutputnothere'));
	}

	public function testExtractFailsWhenFilesDoNotExistInArchive()
	{
	    $this->assertFalse(unzip_file($this->testdir.'/testarchive.zip','testfile4.txt',$this->testdir.'/testarchiveoutput'));
	}
}
