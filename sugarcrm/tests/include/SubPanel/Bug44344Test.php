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
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('include/SubPanel/SubPanelTilesTabs.php');

/**
 * Bug #44344
 * Custom relationships under same module only show once in subpanel tabs
 *
 * @ticket 44344
 */
class Bug44344Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $account;
    private $subPanel;
    private $group_label;

    public function setUp()
    {
        global $beanList, $beanFiles;
        require('include/modules.php');

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser(true, 1);
        $GLOBALS['current_user']->setPreference('max_tabs', '7');

        // create vardef to add new relation account - cases
        $this->addNewRelationships();

        // add new tabgroup whit cases module
        unset($GLOBALS['tabStructure']);
        $this->group_label = 'LBL_GROUPTAB_'.mktime();
        $GLOBALS['tabStructure'][$this->group_label] = array(
            'label' => $this->group_label,
            'modules' => array('Cases')
        );

        $this->account = SugarTestAccountUtilities::createAccount();
    }

    public function tearDown()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        unset($this->account);

        unset($GLOBALS['tabStructure']);
        unset($this->subPanel, $this->group_label);
        unset($GLOBALS['dictionary']["accounts_cases_10000"]);

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);

        unset($GLOBALS['beanFiles']);
        unset($GLOBALS['beanList']);
    }

    /**
     * add new relation accounts_cases_10000 (Account to Cases: one-to-many)
     * @return void
     */
    private function addNewRelationships()
    {
        $GLOBALS['dictionary']["accounts_cases_10000"] = array (
            'true_relationship_type' => 'one-to-many',
            'from_studio' => true,
            'relationships' =>
            array (
                'accounts_cases_10000' =>
                array (
                    'lhs_module' => 'Accounts',
                    'lhs_table' => 'accounts',
                    'lhs_key' => 'id',
                    'rhs_module' => 'Cases',
                    'rhs_table' => 'cases',
                    'rhs_key' => 'id',
                    'relationship_type' => 'many-to-many',
                    'join_table' => 'accounts_cases_10000_c',
                    'join_key_lhs' => 'accounts_cases_10000accounts_ida',
                    'join_key_rhs' => 'accounts_cases_10000cases_idb',
                ),
            ),
            'table' => 'accounts_cases_10000_c',
            'fields' =>
            array (
                0 => array ('name' => 'id', 'type' => 'varchar', 'len' => 36),
                1 => array ('name' => 'date_modified', 'type' => 'datetime'),
                2 => array ('name' => 'deleted', 'type' => 'bool', 'len' => '1', 'default' => '0', 'required' => true),
                3 => array ('name' => 'accounts_cases_10000accounts_ida', 'type' => 'varchar', 'len' => 36),
                4 => array ('name' => 'accounts_cases_10000cases_idb', 'type' => 'varchar', 'len' => 36),
            ),
            'indices' =>
            array (
                0 => array ('name' => 'accounts_cases_10000spk', 'type' => 'primary', 'fields' => array (0 => 'id')),
                1 => array ('name' => 'accounts_cases_10000_ida1', 'type' => 'index', 'fields' => array (0 => 'accounts_cases_10000accounts_ida')),
                2 => array ('name' => 'accounts_cases_10000_alt', 'type' => 'alternate_key', 'fields' => array (0 => 'accounts_cases_10000cases_idb')),
            ),
        );
    }

    /**
     * generate mock layout_defs for SubPanelDefinitions object
     * add two subpanels: cases (default relation) and accounts_cases_10000 (test created relation)
     * @return array
     */
    private function getLayoutDefs()
    {
        $layout_defs = array();

        $layout_defs["subpanel_setup"]['cases'] = array(
            'order' => 100,
            'sort_order' => 'desc',
            'sort_by' => 'case_number',
            'module' => 'Cases',
            'subpanel_name' => 'ForAccounts',
            'get_subpanel_data' => 'cases',
            'add_subpanel_data' => 'case_id',
            'title_key' => 'LBL_CASES_SUBPANEL_TITLE',
            'top_buttons' => array(
                array('widget_class' => 'SubPanelTopButtonQuickCreate'),
                array('widget_class' => 'SubPanelTopSelectButton', 'mode'=>'MultiSelect')
            ),
        );

        $layout_defs["subpanel_setup"]['accounts_cases_10000'] = array (
            'order' => 100,
            'module' => 'Cases',
            'subpanel_name' => 'default',
            'sort_order' => 'asc',
            'sort_by' => 'id',
            'title_key' => 'LBL_ACCOUNTS_CASES_FROM_CASES_TITLE',
            'get_subpanel_data' => 'accounts_cases_10000',
        );
        return $layout_defs;
    }

    /**
     * @group 44344
     * @outputBuffering enabled
     */
    public function testSubPanelTilesTabsGetTabs()
    {
        $tabs = array('cases', 'accounts_cases_10000');
        $this->subPanel = new SubPanelTilesTabs($this->account, '', $this->getLayoutDefs());

        // get tabs by selected group ($this->group_label)
        $returned_tabs = $this->subPanel->getTabs($tabs, true, $this->group_label);

        foreach ( $tabs as $tab )
        {
            $this->assertContains($tab, $returned_tabs);
        }
        // just to suppress output remove when proper code is in place.
        $this->expectOutputRegex('/groupTabs/');
    }
}
