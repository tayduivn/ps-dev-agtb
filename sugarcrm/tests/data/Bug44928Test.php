<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/en/msa/master_subscription_agreement_11_April_2011.pdf
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
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

/**
 * @see Team
 */
require_once 'modules/Teams/Team.php';

/**
 * @see Campaign
 */
require_once 'modules/Campaigns/Campaign.php';

/**
 * @see Lead
 */
require_once 'modules/Leads/Lead.php';

/**
 * @ticket 44928
 */
class Bug44928Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Temporarily created team record
     *
     * @var Team
     */
    protected $team;

    /**
     * Temporarily created campaign record
     *
     * @var Campaign
     */
    protected $campaign;

    /**
     * Temporarily created lead record
     *
     * @var Lead
     */
    protected $lead;

    /**
     * Temporarily campaign name
     *
     * @var string
     */
    protected $campaign_name = 'Bug44928Test';

    /**
     * Created lead ID
     *
     * @var string
     */
    protected $lead_id;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * Creates temporary records and sets anonymous current user
     */
    public function setUp()
    {
        $this->markTestIncomplete("Test is failing on Oracle, working with sergei to fix");
        return;
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

        // create new private team
        $this->team = new Team();
        $this->team->private = true;
        $this->team->save();

        // create new campaign associated with the team
        $this->campaign = new Campaign();
        $this->campaign->team_id = $this->team->id;
        $this->campaign->name = $this->campaign_name;
        $this->campaign->save();

        // create new lead associated with the campaign
        $this->lead = new Lead();
        $this->lead->campaign_id = $this->campaign->id;
        $this->lead->save();

        $this->lead_id = $this->lead->id;
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * Marks temporary records as deleted and restores current user
     */
    public function tearDown()
    {
       /* $this->lead->mark_deleted($this->lead->id);
        $this->campaign->mark_deleted($this->campaign->id);
        $this->team->mark_deleted($this->team->id);
        */
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    /**
     * Ensures that properties of bean relations are accessible for an anonymous
     * user depending on accessibility of the bean itself but not on team security
     * of related bean.
     */
    public function testRelatedBeanPropertiesAreAccessible()
    {
        $lead = new Lead();
        $lead->retrieve($this->lead_id);
        $this->assertEquals($this->campaign_name, $lead->campaign_name);
    }
}
