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
 
require_once 'include/Dashlets/DashletGeneric.php';

/**
 * @ticket 41013
 */
class Bug41013Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $_moduleName;
    
    public function setup()
    {
        $this->_moduleName = 'TestModuleForDashletLoadLanguageTest'.mt_rand();
        
        sugar_mkdir("custom/modules/{$this->_moduleName}/metadata/",null,true);
        sugar_file_put_contents("custom/modules/{$this->_moduleName}/metadata/dashletviewdefs.php",
            '<?php $dashletData[\''.$this->_moduleName.'Dashlet\'][\'searchFields\'] = array(); $dashletData[\''.$this->_moduleName.'Dashlet\'][\'columns\'] = array(\'Foo\'); ?>');
        
    }
    
    public function tearDown()
    {
        if ( is_dir("custom/modules/{$this->_moduleName}") )
            rmdir_recursive("custom/modules/{$this->_moduleName}");
        
        unset($GLOBALS['dashletStrings']);
    }
    
    public function testCanLoadCustomMetadataTwiceInARow() 
    {
        $dashlet = new DashletGenericMock();
        $dashlet->seedBean->module_dir = $this->_moduleName;
        
        $dashlet->loadCustomMetadata();
        
        $this->assertEquals(array('Foo'),$dashlet->columns);
        
        $dashlet->columns = array();
        
        $dashlet->loadCustomMetadata();
        
        $this->assertEquals(array('Foo'),$dashlet->columns);
    }
}

class DashletGenericMock extends DashletGeneric
{
    public function __construct()
    {
    }
    
    public function loadCustomMetadata()
    {
        parent::loadCustomMetadata();
    }
}
