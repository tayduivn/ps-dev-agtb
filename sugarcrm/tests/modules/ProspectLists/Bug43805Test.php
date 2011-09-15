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


require_once "include/export_utils.php";
require_once "SugarTestProspectUtilities.php";

class Bug43805Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Contains created prospect lists' ids
     * @var Array
     */
    protected static $_createdProspectListsIds = array();

    /**
     * Instance of ProspectList
     * @var ProspectList
     */
    protected $_prospectList;

    /**
     * prospects array
     * @var Array
     */
    protected $_prospects = array();

    /**
     * Create prospect instance (with account)
     */
    public static function createProspect()
    {

		$prospect = SugarTestProspectUtilities::createProspect();
		
        $prospect->save();
        return $prospect;
       
    }

    /**
     * Create ProspectList instance
     * @param prospect instance to attach to prospect list
     */
    public static function createProspectList($prospect = null)
    {
        $prospectList = new ProspectList();
        $prospectList->name = "TargetList_code";
        $prospectList->save();
        self::$_createdProspectListsIds[] = $prospectList->id;

        if ($prospect instanceof Prospect) {
            self::attachProspectToProspectList($prospectList, $prospect);
        }

        return $prospectList;
    }

    /**
     *
     * Attach Prospect to prospect list
     * @param ProspectList $prospectList prospect list instance
     * @param prospect $prospect prospect instance
     */
    public static function attachProspectToProspectList($prospectList, $prospect)
    {
        $prospectList->load_relationship('prospects');
        $prospectList->prospects->add($prospect->id,array());
    }

    /**
     * Set up - create prospect list with 1 prospect
     */
    public function setUp()
    {
        global $current_user;
        $current_user = SugarTestUserUtilities::createAnonymousUser();;
        $this->_prospects[] = self::createProspect();
        $this->_prospectList = self::createProspectList($this->_prospects[0]);
        self::attachProspectToProspectList($this->_prospectList, $this->_prospects[0]);
    }

    /**
     * Clear all created data
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    public function tearDown()
    {
        SugarTestProspectUtilities::removeAllCreatedProspects();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        $this->_clearProspects();
    }

    /**
     * Test if Title exists within report
     */
    public function testTitleExistsExportList()
    {
        $content = export("ProspectLists", $this->_prospectList->id, true);
				
        $this->assertContains($this->_prospects[0]->title, $content, "Report should contain title of created Prospect");

    }

    private function _clearProspects()
    {
        $ids = implode("', '", self::$_createdProspectListsIds);
        $GLOBALS['db']->query('DELETE FROM prospect_list_campaigns WHERE prospect_list_id IN (\'' . $ids . '\')');
        $GLOBALS['db']->query('DELETE FROM prospect_lists_prospects WHERE prospect_list_id IN (\'' . $ids . '\')');
        $GLOBALS['db']->query('DELETE FROM prospect_lists WHERE id IN (\'' . $ids . '\')');
    }
}