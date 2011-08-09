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
 
require_once 'include/utils/file_utils.php';

class CreateCacheDirectoryTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $_original_cwd = '';

    public function setUp()
    {
        global $sugar_config;
        $this->_original_cwd = getcwd();
        $this->_original_cachedir = $sugar_config['cache_dir'];
        $sugar_config['cache_dir'] = 'cache/';
        chdir(dirname(__FILE__));
        $this->_removeCacheDirectory('./cache');
    }

    public function tearDown()
    {
        $this->_removeCacheDirectory('./cache');
        chdir($this->_original_cwd);
        $sugar_config['cache_dir'] = $this->_original_cwd;
    }

    private function _removeCacheDirectory($dir)
    {
        $dir_handle = @opendir($dir);
        if ($dir_handle === false) {
            return;
        }
        while (($filename = readdir($dir_handle)) !== false) {
            if ($filename == '.' || $filename == '..') {
                continue;
            }
            if (is_dir("{$dir}/{$filename}")) {
                $this->_removecacheDirectory("{$dir}/{$filename}");
            } else {
                unlink("{$dir}/{$filename}");
            }
        }
        closedir($dir_handle);
        rmdir("{$dir}");
    }

    public function testCreatesCacheDirectoryIfDoesnotExist()
    {
        $this->assertFalse(file_exists('./cache'), 'check that the cache directory does not exist');
        create_cache_directory('foobar');
        $this->assertTrue(file_exists('./cache'), 'creates a cache directory');
    }

    public function testCreatesDirectoryInCacheDirectoryProvidedItIsGivenAFile()
    {
        $this->assertFalse(file_exists('./cache/foobar-test'));
        create_cache_directory('foobar-test/cache-file.php');
        $this->assertTrue(file_exists('./cache/foobar-test'));
    }

    public function testReturnsDirectoryCreated()
    {
        $created = create_cache_directory('foobar/cache-file.php');
        $this->assertEquals(
            'cache/foobar/cache-file.php',
            $created
        );
    }
}

