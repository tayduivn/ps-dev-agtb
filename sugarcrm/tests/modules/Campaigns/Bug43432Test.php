<?php

/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

class Bug43432Test extends Sugar_PHPUnit_Framework_OutputTestCase {

     var $campaign = null;

     public function setUp() {

          global $current_user;
          $this->_user = SugarTestUserUtilities::createAnonymousUser();
          $GLOBALS['current_user'] = $this->_user;

		  require('include/modules.php');
		  $GLOBALS['beanList'] = $beanList;
		  $GLOBALS['beanFiles'] = $beanFiles;

          global $_SERVER;
          $_SERVER['REMOTE_ADDR'] = "127.0.0.1";
          $count = 0;
          
          unset($_POST);
          $campaign = SugarTestCampaignUtilities::createCampaign();
          $this->campaign_id = $campaign->id;

          $_POST['campaign_id'] = $campaign->id;
          $_POST['last_name'] = 'Test_name';
          $_POST['redirect_url'] = "#?attrib_$count=value_$count";
          $_POST['assigned_user_id'] = '1';
          $_POST['team_id'] = '1';
          $_POST['submit'] = 'Send message';
          $_POST['really_uniq_field'] = 'really_uniq_field_value';

          //increase strlen to pass check Campaigns/WebToLeadCapture.php on line 171
          while (strlen($_POST['redirect_url']) < 2084) {
               $count++;
               $_POST['redirect_url'] .= "&attrib_$count=value_$count";
          }
          $redirect_url = $_POST['redirect_url'];

     }

     public function tearDown() {
          SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
          SugarTestCampaignUtilities::removeAllCreatedCampaigns();
          unset($_POST);
     }

     function testNotSetSubmitInForm() {
          // cut all "die()" and use eval() to avoid breaking test
          $str = file_get_contents('modules/Campaigns/WebToLeadCapture.php');
          $str = str_replace('die();', '', $str);
          $str = str_replace('<?php', ' ', $str);
          $str = str_replace('?>', ' ', $str);

          eval($str);
          $this->expectOutputNotRegex('/value="' . $_POST['submit'] .  '"/');
          
     }
     
     function testNotSetRedirectUrlInForm() {
          // cut all "die()" and use eval() to avoid breaking test
          $str = file_get_contents('modules/Campaigns/WebToLeadCapture.php');
          $str = str_replace('die();', '', $str);
          $str = str_replace('<?php', ' ', $str);
          $str = str_replace('?>', ' ', $str);

          eval($str);
          $this->expectOutputNotRegex('/value="' . $_POST['redirect_url'] .  '"/');
     }

}

?>