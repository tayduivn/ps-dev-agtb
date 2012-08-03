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
 
class Bug35014Test extends Sugar_PHPUnit_Framework_TestCase
{
	private $campaign_id;
	
	public function setUp()
    {

        $this->markTestIncomplete('SugarTestCampaignUtilities does not exist');
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $campaign = SugarTestCampaignUtilities::createCampaign();
        $this->campaign_id = $campaign->id;
	}

    public function tearDown()
    {
        //SugarTestCampaignUtilities::removeAllCreatedCampaigns();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }
    
    public function testLeadCaptureResponse()
    {
        // SET GLOBAL PHP VARIABLES
        $_POST = array
        (
            'first_name' => 'Sadek',
            'last_name' => 'Baroudi',
            'campaign_id' => $this->campaign_id,
            'redirect_url' => 'http://www.sugarcrm.com/index.php',
            'assigned_user_id' => 1,
            'team_id' => '1',
            'team_set_id' => 'Global',
            'req_id' => 'last_name;',
        );
        
        // RUN TEST 1
        $postString = '';
        foreach($_POST as $k => $v)
        {
            $postString .= "{$k}=".urlencode($v)."&";
        }
        $postString = rtrim($postString, "&");
        
        $ch = curl_init("{$GLOBALS['sugar_config']['site_url']}/index.php?entryPoint=WebToLeadCapture");
        curl_setopt($ch, CURLOPT_POST, count($_POST) + 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        ob_start();
        $return = curl_exec($ch);
        $output = ob_get_clean();
        
        $matches = array();
        preg_match("/Location: .*/", $output, $matches);
        $this->assertTrue(count($matches) > 0, "Could not get the header information for the response");
        
        $location = '';
        if(count($matches) > 0){
            $location = str_replace("Location :", "", $matches[0]);
        }
        
        $query_string = substr($location, strpos($location, "?") + 1);
        $query_string_array = explode("&", $query_string);
        
        $post_compare_array = array();
        $skipKeys = array('module', 'action', 'entryPoint', 'client_id_address');
        foreach($query_string_array as $key_val)
        {
            $key_val_array = explode("=", $key_val);
            if(in_array($key_val_array[0], $skipKeys))
                continue;
            $post_compare_array[$key_val_array[0]] = $key_val_array[1];
        }
        
        // the redirect_url doesn't get returned, so we unset it
        unset($_POST['redirect_url']);
        
        $this->assertEquals($_POST, $post_compare_array, "The returned get location doesn't match that of the post passed in");
        
        
        // SET GLOBAL PHP VARIABLES
        $_POST = array
        (
            'first_name' => 'Sadek',
            'last_name' => 'Baroudi',
            'campaign_id' => $this->campaign_id,
            'redirect_url' => 'http://www.sugarcrm.com/index.php',
            'assigned_user_id' => 1,
            'team_id' => '1',
            'team_set_id' => 'Global',
            'req_id' => 'last_name;',
            'SuperLongGetVar' => 
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
        		'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
        		'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
        		'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
        		'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
        		'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
        		'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
        		'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
        		'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
        		'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
        		'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
        		'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
        		'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
        		'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
            	'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis'.
        		'PneumonoultramicroscopicsilicovolcanoconiosisPneumonoultramicroscopicsilicovolcanoconiosis',
        );
        
        
        // RUN TEST 1
        $postString = '';
        foreach($_POST as $k => $v)
        {
            $postString .= "{$k}=".urlencode($v)."&";
        }
        $postString = rtrim($postString, "&");
        
        $ch = curl_init("{$GLOBALS['sugar_config']['site_url']}/index.php?entryPoint=WebToLeadCapture");
        curl_setopt($ch, CURLOPT_POST, count($_POST) + 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        ob_start();
        $return = curl_exec($ch);
        $output = ob_get_clean();
        
        $matches = array();
        preg_match('/form name="redirect"/', $output, $matches);
        $this->assertTrue(count($matches) > 0, "Should have output a form since we have a long get string");
    }
}
?>