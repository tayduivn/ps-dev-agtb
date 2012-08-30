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

class MeetingsApiFTSTest extends RestTestBase
{
    public function setUp()
    {
        parent::setUp();
    }
    
    public function tearDown()
    {
    	parent::tearDown();

    }

	public function testModuleSearch()
	{
        $search_engine = SugarSearchEngineFactory::getInstance(SugarSearchEngineFactory::getFTSEngineNameFromConfig(), array(), false);
        $meetings = array();
        for($x = 1; $x < 31; $x++)
        {
            $meeting = new Meeting();
            $meeting->name = "Test Meeting {$x}";
            $meeting->save();
            $meeting->date_start = gmdate("Y-m-d H:i:s", strtotime("+{$x} days"));
            $meeting->date_end  = gmdate("Y-m-d H:i:s", strtotime("+{$x} days"));
            $meeting->assigned_user_id = $this->_user->id;
            $meeting->team_set_id = 1;
            $meeting->team_id = 1;
            $meeting->save();
            $search_engine->indexBean($meeting, FALSE);
            $meetings[] = $meeting;            
        } 
        // verify we get 30 meetings
        $restReply = $this->_restCall("Meetings?max_num=30");

        $this->assertEquals(30, count($restReply['reply']['records']), "Did not get 30 meetings");

        // change a date to the past
        $meetings[5]->date_start = gmdate('Y-m-d H:i:s', strtotime("-50 days"));
        $meetings[5]->save();
        $search_engine->indexBean($meetings[5], FALSE);
        $restReply = $this->_restCall("Meetings?max_num=30");
        // verify we get 29 meetings
        $this->assertEquals(29, count($restReply['reply']['records']), "Did not get 29 Meetings");

        // change the date back
        $meetings[5]->date_start = gmdate("Y-m-d H:i:s", strtotime("+5 days"));
        $meetings[5]->save();
        
        // restore FTS and config override
        foreach($meetings AS $meeting)
        {
            $search_engine->delete($meeting);
            $GLOBALS['db']->query("DELETE FROM meetings WHERE id = '{$meeting->id}'");
        }
	}
 
}