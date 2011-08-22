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
require_once('modules/Users/authentication/AuthenticationController.php');

class Bug44503Test extends Sugar_PHPUnit_Framework_TestCase
{
	protected $authclassname = null;

	public function setUp() 
    {
    	$this->authclassname = 'TestAuthClass'.mt_rand();
    	
    	sugar_mkdir("custom/modules/Users/authentication/{$this->authclassname}/",null,true);
        
        sugar_file_put_contents(
            "custom/modules/Users/authentication/{$this->authclassname}/{$this->authclassname}.php",
            "<?php
class {$this->authclassname} extends SugarAuthenticate { 
    public \$userAuthenticateClass = '{$this->authclassname}User'; 
    public \$authenticationDir = '{$this->authclassname}'; 
            
    public function _construct(){
	    parent::SugarAuthenticate();
	}
}"
            );
        sugar_file_put_contents(
            "custom/modules/Users/authentication/{$this->authclassname}/{$this->authclassname}User.php",
            "<?php
class {$this->authclassname}User extends SugarAuthenticateUser {
}"
            );
        
	}
	
	public function tearDown()
	{
	    if ( !is_null($this->authclassname) && is_dir("custom/modules/Users/authentication/{$this->authclassname}/") )
	        rmdir_recursive("custom/modules/Users/authentication/{$this->authclassname}/");
	}
	
	public function testLoadingCustomAuthClassFromAuthenicationController()
	{
	    $authController = new AuthenticationController($this->authclassname);
	    
	    $this->assertInstanceOf($this->authclassname,$authController->authController);
	    $this->assertInstanceOf($this->authclassname.'User',$authController->authController->userAuthenticate);
	}
}
