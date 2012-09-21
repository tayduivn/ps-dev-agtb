<?php
//BEGIN SUGARCRM flav=pro ONLY
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


/*
* This test check if prosect adds correctly to prospects_list
* @ticket 53288
*/

require_once('modules/ProspectLists/ProspectList.php');
//require_once('modules/Prospects/Prospect.php');

class Bug53288Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $_oProspectList;
    protected $_oProspect;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_list_strings');
        $this->_oProspect = SugarTestProspectUtilities::createProspect();
        $this->createProspectList();
    }

    public function tearDown()
    {
        SugarTestProspectListsUtilities::removeProspectsListToProspectRelation($this->_oProspectList->id, $this->_oProspect->id);
        SugarTestProspectUtilities::removeAllCreatedProspects();
        SugarTestProspectListsUtilities::removeProspectLists($this->_oProspectList->id);
        $_REQUEST = array();
        SugarTestHelper::tearDown();
    }

    public function testAddProspectsToProspectList()
    {
        $_REQUEST['prospect_list_id'] = $this->_oProspectList->id;
        $_REQUEST['prospect_id'] = $this->_oProspect->id;
        $_REQUEST['prospect_ids'] = array($this->_oProspect->id);
        $_REQUEST['return_type'] = 'addtoprospectlist';
        require_once('include/generic/Save2.php');
        $res = $GLOBALS['db']->query("SELECT * FROM prospect_lists_prospects WHERE prospect_list_id='{$this->_oProspectList->id}' AND related_id='{$this->_oProspect->id}'");
        $row = $GLOBALS['db']->fetchByAssoc($res);
        $this->assertInternalType('array', $row);
    }

    protected function createProspectList()
    {
        $this->_oProspectList = new ProspectList();
        $this->_oProspectList->name = "Bug53288Test_ProspectListName";
        $this->_oProspectList->save();
    }

}
