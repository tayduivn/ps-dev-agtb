<?php
//FILE SUGARCRM flav=PRO ONLY
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


require_once('modules/Campaigns/utils.php');

/**
 * Bug #54098
 * Manage Subscriptions Doesn't Properly Work With More Than Two Default Target Lists
 *
 * @ticket 54098
 */

class Bug54098Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $_aProspectlists_Prospects;
    private $_aProspectlists_Campaigns;

    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestCampaignUtilities::removeAllCreatedCampaigns();
        SugarTestProspectListsUtilities::removeCreatedProspectLists();
        $this->deleteProspectlistToCampaignRelationRecords();
        $this->deleteProspectlistToContactRelationRecords();
        SugarTestHelper::tearDown();
    }

    /**
     * If we create two default Target Lists for newsletter campaign and attach a contact to one of this target lists,
     * than when "Select Manage Subscriptions" for that contact - campaign should be listed once (not twice! as before)
     */
    public function testGetSubscriptionLists()
    {
        $oCampaign = SugarTestCampaignUtilities::createCampaign();
        $oCampaign->campaign_type = 'NewsLetter';
        $oCampaign->save();
        $oProspectList = SugarTestProspectListsUtilities::createProspectList(NULL, array(
            'list_type' => 'default'
        ));
        $oProspectList2 = SugarTestProspectListsUtilities::createProspectList(NULL, array(
            'list_type' => 'default'
        ));
        $oProspectList3 = SugarTestProspectListsUtilities::createProspectList(NULL, array(
            'list_type' => 'exempt'
        ));
        $oContact = SugarTestContactUtilities::createContact();
        $oContact2 = SugarTestContactUtilities::createContact();
        $this->createProspectlistToCampaignRelationRecord($oCampaign, $oProspectList);
        $this->createProspectlistToCampaignRelationRecord($oCampaign, $oProspectList2);
        $this->createProspectlistToCampaignRelationRecord($oCampaign, $oProspectList3);

        $this->createContactToProspectlistRelationRecord($oContact, $oProspectList);
        $this->createContactToProspectlistRelationRecord($oContact2, $oProspectList);

        $aResult = get_subscription_lists($oContact2);

        $this->assertInternalType('array', $aResult['unsubscribed']);
        $this->assertInternalType('array', $aResult['subscribed']);
        $this->assertArrayHasKey($oCampaign->name, $aResult['subscribed']);
        $this->assertArrayNotHasKey($oCampaign->name, $aResult['unsubscribed']);

    }

    private function createProspectlistToCampaignRelationRecord(Campaign $oCampaign, ProspectList $oProspectList)
    {
        if (!empty($oCampaign->id) and !empty($oProspectList->id))
        {
            $id = 'BUg54098' . mt_rand();
            $this->_aProspectlists_Campaigns[] = $id;
            $sDate = $GLOBALS['db']->convert(date('\'Y-m-d H:i:s\''), 'datetime');
            $GLOBALS['db']->query("INSERT INTO prospect_list_campaigns VALUES ('{$id}','{$oProspectList->id}', '{$oCampaign->id}', {$sDate}, 0)");
        }
    }

    private function deleteProspectlistToCampaignRelationRecords()
    {
        if (!empty($this->_aProspectlists_Campaigns))
        {
            $GLOBALS['db']->query("DELETE FROM prospect_list_campaigns WHERE id IN ('" . implode("','", $this->_aProspectlists_Campaigns) . "')");
        }
    }

    private function createContactToProspectlistRelationRecord(Contact $oContact, ProspectList $oProspectList)
    {
        if (!empty($oContact->id) and !empty($oProspectList->id))
        {
            $id = 'BUg54098' . mt_rand();
            $this->_aProspectlists_Prospects[] = $id;
            $sDate = $GLOBALS['db']->convert(date('\'Y-m-d H:i:s\''), 'datetime');
            $GLOBALS['db']->query("INSERT INTO prospect_lists_prospects VALUES ('{$id}','{$oProspectList->id}', '{$oContact->id}','Contacts',{$sDate}, 0)");
        }
    }

    private function deleteProspectlistToContactRelationRecords()
    {
        if (!empty($this->_aProspectlists_Campaigns))
        {
            $GLOBALS['db']->query("DELETE FROM prospect_lists_prospects WHERE id IN ('" . implode("','", $this->_aProspectlists_Prospects) . "')");
        }
    }
}
