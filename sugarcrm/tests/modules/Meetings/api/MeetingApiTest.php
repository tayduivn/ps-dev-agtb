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

require_once('tests/rest/RestTestBase.php');
require_once('modules/SugarFavorites/SugarFavorites.php');
require_once('include/SugarSearchEngine/SugarSearchEngineFactory.php');

class MeetingsApiTest extends RestTestBase
{
    public function setUp()
    {
        parent::setUp();
        for($x = 1; $x < 31; $x++)
        {
			$meeting = new Meeting();
			$meeting->id = uniqid();
	        $meeting->name = "Test Meeting {$x}";
	        $meeting->save();
	        $meeting->date_start = date("Y-m-d", strtotime("+{$x} days"));
	        $meeting->date_end  = date("Y-m-d", strtotime("+{$x} days"));
			$this->meeting[] = $meeting;        	
        }
    }
    
    public function tearDown()
    {
    	parent::tearDown();
        foreach($this->meeting AS $meeting)
        {
            $GLOBALS['db']->query("DELETE FROM meetings WHERE id = '{$meeting->id}'");
        }
    }

	public function testModuleSearch()
	{
        // set the FTS engine as down and make sure the config removes FTS
        searchEngineDown();
        $this->config_file_override = '';
        if(file_exists('config_override.php'))
            $this->config_file_override = file_get_contents('config_override.php');
        else
            $this->config_file_override= '<?php' . "\r\n";
        $new_line= '$sugar_config[\'full_text_engine\'] = true;';
        file_put_contents('config_override.php', $this->config_file_override . "\r\n" . $new_line);

        // verify we get 30 meetings
        
        // change a date to the past
        // verify we get 29 meetings
        

        // change the date back
        // 
        // restore FTS and config override
        restoreSearchEngine();
        file_put_contents('config_override.php', $this->config_file_override);
	}

	public function testModuleSearchFTS()
	{
        // verify 30 meetings
        // change date to past
        // verify 29
        // change date back
	}
}