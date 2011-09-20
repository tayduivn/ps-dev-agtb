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


require_once('modules/Configurator/Configurator.php');
require_once('modules/EmailMan/EmailMan.php');

/***
 * Test cases for Bug 44113
 */
class Bug44113Test extends Sugar_PHPUnit_Framework_TestCase
{
	private $cfg;   // configurator
	private $emailMan;
    private $email_xss; // the security settings to be saved in config_ovverride
    
	public function setUp()
	{
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->is_admin = '1';

          // email_xss settings to be saved using config_override
        $this->email_xss = array(
            'applet' => 'applet',
            'form' => 'form',
            'iframe' => 'iframe',
            'script' => 'script'
            );

	}
	
	public function tearDown()
	{
		unset($this->cfg);
        unset($this->emailMan);
        unset($this->email_xss);
        unset($GLOBALS['current_user']);

	}

    public function testEmailManController()
    {


      require_once('modules/EmailMan/controller.php');
      require_once('include/MVC/Controller/SugarController.php');

      global $sugar_config;
      $conn = new EmailManController();

        // populate the REQUEST array because configurator will read that to write config_override 
      foreach ($this->email_xss as $key=>$val) {
           $_REQUEST["$key"] = $val;
      }

      $new_security_settings = base64_encode(serialize($this->email_xss));



      // make sure that settings from config.php are untouched
      require("config.php");
      $original_security_settings = $sugar_config['email_xss'];
      $this->assertNotEquals($original_security_settings, $new_security_settings,
                            "ensure that original email_xss is not touched");

       $conn->action_Save();   // testing the save,
                              // it should use the above request vars
                              // to create a new config_override.php 

      // now check to make sure that config_override received the updated settings
      require("config_override.php");
      $this->assertEquals($new_security_settings, $sugar_config['email_xss'],
                          "testing that new email_xss settings got saved");

   }



    /**
     * make sure that new configs are saved using handleOverride
     */
	public function testSavingToConfigOverride()
	{
        $this->cfg = new Configurator();
        global $sugar_config;

       $new_security_settings = base64_encode(serialize($this->email_xss));

       $this->cfg->config['email_xss'] = $new_security_settings;
       $this->cfg->handleOverride();

       // just test to make sure that configuration is saved
       $this->assertEquals($sugar_config['email_xss'], $new_security_settings,
                         "testing configurator");


    }

}

?>