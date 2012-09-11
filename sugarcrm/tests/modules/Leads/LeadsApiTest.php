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

/***
 * Used to test Leads Module endpoint from LeadsApi.php
 *
 * @group leads
 */
class LeadsApiTest extends RestTestBase
{
    /**
     * @group leadsapi
     */
    public function testConvertProspect()
    {
        global $db;

        $prospectId = $this->createProspect();
        $campaignId = $this->createCampaign();

        $url = 'Leads';
        $postBody = 'last_name=TestLeadFromConvertedProspect&prospect_id='.$prospectId.'&campaign_id='.$campaignId;
        $return = $this->_restCall($url, $postBody, 'POST');

        //verify lead was created
        $leadId = $return['reply']['id'];
        $this->assertNotEmpty($leadId, 'Lead should be created');

        //verify lead link was created
        $prospect = new Prospect();
        $prospect->retrieve($prospectId);
        $this->assertEquals($leadId, $prospect->lead_id, 'Lead id should be set on the prospect');

        //verify campaign log was created
        $campaign = new Campaign();
        $campaign->retrieve($campaignId);
        $campaignLogQuery = $campaign->track_log_leads();
        $result = $db->query($campaignLogQuery);
        $row = $db->fetchByAssoc($result);
        $this->assertEquals('lead', $row['activity_type'], 'Campaign log activity type should be lead');
        $this->assertEquals($prospectId, $row['related_id'], 'Campaign log related_id should be the prospect id');
        $this->assertEquals($leadId, $row['target_id'], 'Campaign log target_id should be the lead id');
    }

    /**
     * @group leadsapi
     */
    public function testEmailToLead()
    {
        $emailId = $this->createEmail();

        $url = 'Leads';
        $postBody = 'last_name=TestLeadFromEmail&inbound_email_id='.$emailId;
        $return = $this->_restCall($url, $postBody, 'POST');

        //verify lead was created
        $leadId = $return['reply']['id'];
        $this->assertNotEmpty($leadId, 'Lead should be created');

        //verify email updated correctly with relationship
        $email = new Email();
        $email->retrieve($emailId);
        $this->assertEquals('Leads', $email->parent_type, 'Parent type should be Leads');
        $this->assertEquals($leadId, $email->parent_id, 'Lead relationship should be set');
        $this->assertEquals('read', $email->status, 'Email status should be read');
    }

    // UTILITY CLASSES

    protected function createProspect()
    {
        $prospect = new Prospect();
        $prospect->last_name = 'TestProspect';
        $prospect->save();
        return $prospect->id;
    }

    protected function createCampaign()
    {
        $campaign = new Campaign();
        $campaign->name = 'TestCampaign';
        $campaign->save();
        return $campaign->id;
    }

    protected function createEmail()
    {
        $email = new Email();
        $email->name = 'TestEmail';
        $email->status = 'unread';
        $email->save();
        return $email->id;
    }

}