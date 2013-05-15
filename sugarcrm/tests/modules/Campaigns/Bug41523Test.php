<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once 'include/SubPanel/SubPanelTiles.php';

/**
 * Bug #41523
 * Subject Blank Rows Are Displayed In Campaign Status "Leads Created" Subpanel If Leads Are Deleted
 *
 * @ticket 41523
 */
class Bug41523Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $campaign;

    public function setUp()
    {
        global $focus;

        SugarTestHelper::setUp("app_strings");

        // Init session user settings
        SugarTestHelper::setUp("current_user");
        $GLOBALS['current_user']->setPreference('max_tabs', 2);

        $this->campaign = SugarTestCampaignUtilities::createCampaign();
        $focus          = $this->campaign;

        // Setting for SubPanel
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_REQUEST['module']        = 'Campaigns';
        $_REQUEST['action']        = 'TrackDetailView';
        $_REQUEST['record']        = $this->campaign->id;
    }

    public function tearDown()
    {
        unset($_SERVER['REQUEST_METHOD']);
        $_REQUEST = array();

        SugarTestCampaignUtilities::removeAllCreatedCampaigns();
        SugarTestCampaignUtilities::removeAllCreatedCampaignLogs();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();

        SugarTestHelper::tearDown();
    }

    /**
     * @group 41523
     */
    public function testDeletedLeadsOnCampaignStatusPage()
    {
        // create a few leads
        $leads = array(
            $this->createLeadFromWebForm('User1:' . create_guid()),
            $this->createLeadFromWebForm('User2:' . create_guid()),
            $this->createLeadFromWebForm('User3:' . create_guid()),
        );

        // delete one lead
        $leads[0]->mark_deleted($leads[0]->id);

        $logDeletedLeadsCount = $this->campaign->getDeletedCampaignLogLeadsCount();
        $this->assertEquals(1, $logDeletedLeadsCount);

        // test subpanel output
        $subpanel = new SubPanelTiles($this->campaign, 'Campaigns');
        $html     = $subpanel->display();

        preg_match('|<div id="list_subpanel_lead">.*?<table.*?</table>.*?</tr>(.*?)</table>|s', $html, $match);
        preg_match_all('|module=Leads&action=DetailView|', $match[1], $match);

        $expectedLeadsInSubpanel = count($leads) - $logDeletedLeadsCount;
        $actualLeadsInSubpanel   = count($match[0]);
        $this->assertEquals(
            $expectedLeadsInSubpanel,
            $actualLeadsInSubpanel,
            "The number of leads listed in the Leads subpanel is not correct"
        );
    }

    /**
     * @param $lastName Last name for new lead
     *
     * @return Lead
     */
    private function createLeadFromWebForm($lastName)
    {
        $lead = SugarTestLeadUtilities::createLead("", array("last_name" => $lastName));

        if (!empty($lead)) {
            $campaignLog = SugarTestCampaignUtilities::createCampaignLog($this->campaign->id, "lead", $lead);
            $lead->load_relationship("campaigns");
            $lead->campaigns->add($campaignLog->id);
            $lead->save(false);
        }

        return $lead;
    }
}
