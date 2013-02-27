<?php
//FILE SUGARCRM flav=pro ONLY
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

require_once 'include/SubPanel/SubPanelDefinitions.php';

/**
 * Dependent Fields do not display in the Subpanel of a Related Module unless the Field(s) they Depend on are also in
 * the Subpanel Display
 *
 * @ticket 59047
 */
class Bug59047Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function testDependentFieldsAreExpanded()
    {
        $bean = new SugarBean();
        $bean->field_defs = array(
            'field_1' => array(
                'dependency' => 'equals($field_2,"test")',
            ),
            'field_2' => array(),
            'field_3' => array(
                'dependency' => 'equals($non_existing_field,"test")',
            ),
            'field_4' => array(
                'dependency' => 'equals($field_5,"test")',
            ),
            'field_5' => array(),
        );

        $definition = array(
            'list_fields' => array(
                'field_1' => array(),
                'field_3' => array(),
                'field_5' => array(),
            ),
        );

        $subPanel = new Bug59047Test_SubPanel();
        $subPanel->template_instance = $bean;
        $subPanel->set_panel_definition($definition);

        $list_fields = $subPanel->panel_definition['list_fields'];

        // ensure that "field_1" is marked as non-sortable
        $this->assertFalse($list_fields['field_1']['sortable']);

        // ensure that "field_2" is added to the definition and marked as "query only"
        $this->assertArrayHasKey('field_2', $list_fields);
        $this->assertEquals('query_only', $list_fields['field_2']['usage']);

        // ensure that "non_existing_field" is not added to the definition
        $this->assertArrayNotHasKey('non_existing_field', $list_fields);

        // ensure that "field_5" is not marked as "query only" since it's explicitly defined
        $this->assertArrayHasKey('field_5', $list_fields);
        $this->assertArrayNotHasKey('usage', $list_fields['field_5']);
    }
}

class Bug59047Test_SubPanel extends aSubPanel
{
    public function __construct()
    {
    }

    public function set_panel_definition(array $definition)
    {
        parent::set_panel_definition($definition);
    }
}
