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

class SugarFileUtilsTest extends Sugar_PHPUnit_Framework_TestCase 
{
    private $_filename;
    private $_old_default_permissions;
    
    public function setUp() 
    {	
        if (is_windows())
            $this->markTestSkipped('Skipping on Windows');
        
        $this->_filename = realpath(dirname(__FILE__).'/../../../cache/').'file_utils_override'.mt_rand().'.txt';
        touch($this->_filename);
        $this->_old_default_permissions = $GLOBALS['sugar_config']['default_permissions'];
        $GLOBALS['sugar_config']['default_permissions'] =
            array (
                'dir_mode' => 0777,
                'file_mode' => 0660,
                'user' => $this->_getCurrentUser(),
                'group' => $this->_getCurrentGroup(),
              );
    }
    
    public function tearDown() 
    {
        if(file_exists($this->_filename)) {
            unlink($this->_filename);
        }
        $GLOBALS['sugar_config']['default_permissions'] = $this->_old_default_permissions;
        SugarConfig::getInstance()->clearCache();
    }
    
    private function _getCurrentUser()
    {
        if ( function_exists('posix_getuid') ) {
            return posix_getuid();
        }
        return '';
    }
    
    private function _getCurrentGroup()
    {
        if ( function_exists('posix_getgid') ) {
            return posix_getgid();
        }
        return '';
    }
    
    private function _getTestFilePermissions()
    {
        return substr(sprintf('%o', fileperms($this->_filename)), -4);
    }
    
    public function testSugarTouch()
    {
        $this->assertTrue(sugar_touch($this->_filename));
    }
    
    public function testSugarTouchWithTime()
    {
        $time = filemtime($this->_filename);
        
        $this->assertTrue(sugar_touch($this->_filename, $time));
        
        $this->assertEquals($time,filemtime($this->_filename));
    }
    
    public function testSugarTouchWithAccessTime()
    {
        $time  = filemtime($this->_filename);
        $atime = gmmktime();
        
        $this->assertTrue(sugar_touch($this->_filename, $time, $atime));
        
        $this->assertEquals($time,filemtime($this->_filename));
        $this->assertEquals($atime,fileatime($this->_filename));
    }
    
    public function testSugarChmod()
    {
    	return true;
        $this->assertTrue(sugar_chmod($this->_filename));
        $this->assertEquals($this->_getTestFilePermissions(),decoct(get_mode()));
    }
    
    public function testSugarChmodWithMode()
    {
        $mode = 0411;
        $this->assertTrue(sugar_chmod($this->_filename, $mode));
        
        $this->assertEquals($this->_getTestFilePermissions(),decoct($mode));
    }
    
    public function testSugarChmodNoDefaultMode()
    {
        $GLOBALS['sugar_config']['default_permissions']['file_mode'] = null;
        $this->assertFalse(sugar_chmod($this->_filename));
    }
    
    public function testSugarChmodDefaultModeNotAnInteger()
    {
        $GLOBALS['sugar_config']['default_permissions']['file_mode'] = '';
        $this->assertFalse(sugar_chmod($this->_filename));
    }
    
    public function testSugarChmodDefaultModeIsZero()
    {
        $GLOBALS['sugar_config']['default_permissions']['file_mode'] = 0;
        $this->assertFalse(sugar_chmod($this->_filename));
    }
    
    public function testSugarChmodWithModeNoDefaultMode()
    {
        $GLOBALS['sugar_config']['default_permissions']['file_mode'] = null;
        $mode = 0411;
        $this->assertTrue(sugar_chmod($this->_filename, $mode));
        
        $this->assertEquals($this->_getTestFilePermissions(),decoct($mode));
    }
    
    public function testSugarChmodWithModeDefaultModeNotAnInteger()
    {
        $GLOBALS['sugar_config']['default_permissions']['file_mode'] = '';
        $mode = 0411;
        $this->assertTrue(sugar_chmod($this->_filename, $mode));
        
        $this->assertEquals($this->_getTestFilePermissions(),decoct($mode));
    }
    
    public function testSugarChown()
    {
        $this->assertTrue(sugar_chown($this->_filename));
        $this->assertEquals(fileowner($this->_filename),$this->_getCurrentUser());
    }
    
    public function testSugarChownWithUser()
    {
        $this->assertTrue(sugar_chown($this->_filename,$this->_getCurrentUser()));
        $this->assertEquals(fileowner($this->_filename),$this->_getCurrentUser());
    }
    
    public function testSugarChownNoDefaultUser()
    {
        $GLOBALS['sugar_config']['default_permissions']['user'] = '';
        
        $this->assertFalse(sugar_chown($this->_filename));
    }
    
    public function testSugarChownWithUserNoDefaultUser()
    {
        $GLOBALS['sugar_config']['default_permissions']['user'] = '';
        
        $this->assertTrue(sugar_chown($this->_filename,$this->_getCurrentUser()));
        
        $this->assertEquals(fileowner($this->_filename),$this->_getCurrentUser());
    }
}
?>
