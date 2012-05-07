<?php
//FILE SUGARCRM flav=pro ONLY
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

/**
 * Bug50342Test.php
 * This test helps simulate the calls made to the get_entry_list method of SoapSugarUsers.php.  For the searching
 * and merging of campaign related data via the Word plugin, there is some special code made to query the relationships.
 * In particular, one quirk is the passage of token search terms surrounded by hash marks.  The
 * testSoapSugarUsersGetEntryListValidateQuery method attempts to test theses queries that had previously been failing
 * because they violated the column name checks in SugarSQLValidate.
 *
 * Another problem was in the Prospect.php file.  For the select_fields that the retrieveTargetList method was
 * processing, we would use the module name as the table name.  This fails because the module name contains uppercase
 * letters whereas the table names are lowercase.  The testRetrieveTargetList attempts to test for this issue.
 *
 * @author Collin Lee
 */

require_once('tests/SugarTestProspectUtilities.php');
require_once('include/SugarSQLValidate.php');
require_once('modules/Prospects/Prospect.php');

class Bug50342Test extends Sugar_PHPUnit_Framework_TestCase
{
    var $prospect;

    var $prospectList;

    var $campaign;

    public function setUp()
    {
        global $current_user, $beanList, $beanFiles;
        $beanList = array();
		$beanFiles = array();
		require('include/modules.php');
        $current_user = SugarTestUserUtilities::createAnonymousUser();;
        $this->prospect = SugarTestProspectUtilities::createProspect();
        $this->prospectList = new ProspectList();
        $this->prospectList->name = 'Bug50342Test';
        $this->prospectList->save();
        $this->prospectList->load_relationship('prospects');
        $this->prospectList->prospects->add($this->prospect->id,array());

        $this->campaign = new Campaign();
       	$this->campaign->name = 'Bug50342Test';
       	$this->campaign->campaign_type = 'Email';
       	$this->campaign->status = 'Active';
       	$timeDate = new TimeDate();
       	$this->campaign->end_date = $timeDate->to_display_date(date('Y')+1 .'-01-01');
       	$this->campaign->assigned_id = $current_user->id;
       	$this->campaign->team_id = '1';
       	$this->campaign->team_set_id = '1';
       	$this->campaign->save();

        $this->campaign->load_relationship('prospectlists');
        $this->campaign->prospectlists->add($this->prospectList->id);
    }

    public function tearDown()
    {
        SugarTestProspectUtilities::removeAllCreatedProspects();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        $GLOBALS['db']->query("DELETE FROM prospect_list_campaigns WHERE prospect_list_id = '{$this->prospectList->id}'");
        $GLOBALS['db']->query("DELETE FROM prospect_lists_prospects WHERE prospect_list_id = '{$this->prospectList->id}'");
        $GLOBALS['db']->query("DELETE FROM prospect_lists WHERE id = '{$this->prospectList->id}'");
        $GLOBALS['db']->query("DELETE FROM campaigns WHERE id = '{$this->campaign->id}'");
    }

    /**
     * testRetrieveTargetList
     * This method tests the Prospect.php retrieveTargetList method.  The previous errors here were that the query to
     * retrieve the related target list entries were using the module name in the table query, but the module name was
     * not in lowercase so these queries would fail because the table names were in lowercase.
     *
     */
    public function testRetrieveTargetList()
    {
        $query = '';
        $select_fields = array(
            'id', 'first_name', 'last_name'
        );
        $result = $this->prospect->retrieveTargetList($query, $select_fields);
        $this->assertNotEmpty($result['list'], 'Unable to get target lists data from prospect');
    }

    /**
     * getEntryListQueries
     *
     * These are some of the queries that may come in to the get_entry_list method from the Word plugin
     */
    public function getEntryListQueries()
    {
        return array(
            array("campaigns.id = '99353e9e-7887-b513-bb6d-4f381fb938d1' AND related_type = #contacts# campaignprospects.last_name ASC", 'contacts'),
            array("campaigns.id = '99353e9e-7887-b513-bb6d-4f381fb938d1' AND related_type = #users# campaignprospects.last_name ASC", 'users'),
            array("campaigns.id = '99353e9e-7887-b513-bb6d-4f381fb938d1' AND related_type = #prospects# campaignprospects.last_name ASC", 'prospects'),
            array("campaigns.id = '99353e9e-7887-b513-bb6d-4f381fb938d1' AND related_type = #leads# campaignprospects.last_name ASC", 'leads'),
            array("campaigns.id = '99353e9e-7887-b513-bb6d-4f381fb938d1' AND related_type = 'contacts' campaignprospects.last_name ASC", 'contacts'),
            array("campaigns.id = '99353e9e-7887-b513-bb6d-4f381fb938d1' AND related_type = 'users' campaignprospects.last_name ASC", 'users'),
            array("campaigns.id = '99353e9e-7887-b513-bb6d-4f381fb938d1' AND related_type = 'prospects' campaignprospects.last_name ASC", 'prospects'),
            array("campaigns.id = '99353e9e-7887-b513-bb6d-4f381fb938d1' AND related_type = 'leads' campaignprospects.last_name ASC", 'leads')
        );
    }

    /**
     * testSoapSugarUsersGetEntryListValidateQuery
     *
     * This method tests teh SoapSugarUsers.php call to SugarSQLValidate.php's validateQuery method.  The Plugin code
     * we have to perform mail merge searches on the contacts, users, leads or prospects would pass in SQL string with
     * a hash pattern for the object.
     * @param $sql String of the test SQL to simulate the Word plugin
     * @param $tableName String of the expected table name of the from query (the Prospect.php code will parse the related_type value)
     *
     *
     * @dataProvider getEntryListQueries
     */
    public function testSoapSugarUsersGetEntryListValidateQuery($sql, $tableName)
    {
       	$valid = new SugarSQLValidate();
        $this->assertTrue($valid->validateQueryClauses($sql), "SugarSQLValidate found Bad query: {$sql}");

        $mock = new Bug50342ProspectMock();
        $select = $mock->retrieveTargetList($sql, array('id', 'first_name', 'last_name'));
        $this->assertRegExp("/from\s+{$tableName}/i", $select, 'Incorrect from SQL clause: ' . $select);
    }

}


class Bug50342ProspectMock extends Prospect {

public function process_list_query($select, $offset, $limit, $max, $query)
{
    return $select;
}

}