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
    /**
     * @var Campaign
     */
    private $campaign;

    /**
     * @var MysqliManager
     */
    private $db;

    public function setUp()
    {
        $this->markTestIncomplete("This test breaks on stack66 - working with dev to fix");
        global $focus;

        // Init session user settings
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->setPreference('max_tabs', 2);

        $this->campaign = SugarTestCampaignUtilities::createCampaign();
        $this->db       = $GLOBALS['db'];
        $focus          = $this->campaign;

        // Setting for SubPanel
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_REQUEST['module']        = 'Campaigns';
        $_REQUEST['action']        = 'TrackDetailView';
        $_REQUEST['record']        = $this->campaign->id;
    }

    public function tearDown()
    {
        // Delete created campaings
        SugarTestCampaignUtilities::removeAllCreatedCampaigns();

        // Delete users
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     * @group 41523
     */
    public function testDeletedLeadsOnCapmaingStatusPage()
    {
        // Create 2 leads
        $lead1 = $this->createLeadFromWebForm('User1');
        $lead2 = $this->createLeadFromWebForm('User2');

        // Delete one lead
        $lead1->mark_deleted($lead1->id);

        $this->assertEquals($this->campaign->getDeletedCampaignLogLeadsCount(), 1);

        // Test SubPanel output
        $subpanel = new SubPanelTiles($this->campaign, 'Campaigns');
        $html = $subpanel->display();

        preg_match('|<div id="list_subpanel_lead">.*?<table.*?</table>.*?</table>.*?</tr>(.*?)</table>|s', $html, $match);
        preg_match_all('|<tr|', $match[1], $match);

        $this->assertEquals(count($match[0]), 2);
    }

    /**
     * @param $lastName Last name for new lead
     *
     * @return Lead
     */
    private function createLeadFromWebForm($lastName)
    {
        $postData = array(
            'last_name' => $lastName,
            'campaign_id' => $this->campaign->id,
        );

        // Send request for add lead
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $GLOBALS['sugar_config']['site_url'] . '/index.php?entryPoint=WebToLeadCapture');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

        $this->assertEquals('Thank You For Your Submission.', $response);

        curl_close($ch);

        // Fetch last created lead
        $createdLead = new Lead();
        $query = 'SELECT * FROM leads ORDER BY date_entered DESC LIMIT 1';
        $createdLead->fromArray($this->db->fetchOne($query));

        return $createdLead;
    }
}
