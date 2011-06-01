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
require_once 'modules/Campaigns/Campaign.php';
require_once 'modules/CampaignTrackers/CampaignTracker.php';

class CampaignTrackersTest extends Sugar_PHPUnit_Framework_TestCase
{
	var $campaign = 'campaignforcamplogunittest';
	var $campaign_tracker;

    
    public function setup()
    {
		global $current_user;	

		$current_user = SugarTestUserUtilities::createAnonymousUser();
		//for the purpose of this test, we need to create a campaign and relate it to a campaign tracker object

		//create campaign
    	$c = new Campaign();
    	$c->name = 'CT test ' . time();
    	$c->campaign_type = 'Email';
    	$c->status = 'Active';
    	$timeDate = new TimeDate();
    	$c->end_date = $timeDate->to_display_date(date('Y')+1 .'-01-01');
    	$c->assigned_id = $current_user->id;
    	$c->team_id = '1';
    	$c->team_set_id = '1';
    	$c->save();		
		$this->campaign = $c;
		
		//create campaign tracker
		$ct = new CampaignTracker();
		$ct->tracker_name ='CampaignTrackerTest' . time();
		$ct->tracker_url = 'sugarcrm.com';
		$ct->campaign_id = $this->campaign->id;
		$ct->save();
		$this->campaign_tracker = $ct;



		
    }
    
    public function tearDown()
    {
		//delete the campaign and campaign tracker
		$GLOBALS['db']->query('DELETE FROM campaign_log WHERE campaign_id = \''.$this->campaign->id.'\' ');
		$GLOBALS['db']->query('DELETE FROM campaign_trkrs WHERE id = \''.$this->campaign_tracker->id.'\' ');
		unset($this->campaign_tracker);
        unset($this->campaign_log );
        unset($current_user);
    }
	

	public function testSave(){
		//save was already performed, so just confirm that the http protocol got added on save
		$this->assertSame('http://sugarcrm.com', $this->campaign_tracker->tracker_url, 'http protocol was not added to campaign_tracker->tracker_url on save');
		
	}
	
	

	public function testFillInAdditionalDetailFields(){
		global $current_user;

		$this->campaign_tracker->fill_in_additional_detail_fields();

		//test that campaign name gets filled in
		$this->assertSame($this->campaign->name, $this->campaign_tracker->campaign_name, 'campaign name was not set properly during function call');

		//test that message url gets filed out
		$this->assertFalse(empty($this->campaign_tracker->message_url), 'message url was not populated correctly during function call');		
	}

	public function testGetSummaryText(){
		//test that tracker name is returned
		$this->assertSame($this->campaign_tracker->tracker_name, $this->campaign_tracker->get_summary_text(), 'campaign tracker name is not set properly in summary text');
		
	}


	

}