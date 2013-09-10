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

require_once('modules/ModuleBuilder/parsers/relationships/ActivitiesRelationship.php');

/**
 * Bug #56425
 * see duplicate modules name in Report's Related Modules box
 *
 * @author mgusev@sugarcrm.com
 * @ticked 56425
 * @ticket 42169
 */
class Bug56425Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Test asserts genereated labels for activities
     *
     * @param array $definition
     * @param array $expected
     * @dataProvider getDefinitions
     * @group 56425
     * @group 42169
     * @return void
     */
    public function testBuildLabels($definition, $expected)
    {
        ActivitiesRelationship56425::reset($definition['lhs_module']);
        $relationship = new ActivitiesRelationship56425($definition);
        $labels = $relationship->buildLabels();
        foreach ($labels as $label) {
            $this->assertArrayHasKey($label['module'], $expected, 'Incorrect label was generated');
            $this->assertEquals($expected[$label['module']], $label['display_label'], 'Labels are incorrect');
            unset($expected[$label['module']]);
        }
        $this->assertEmpty($expected, 'Not all labels were generated');
    }

    /**
     * Method returns definition for relationship & expected result
     *
     * @return array
     */
    public function getDefinitions()
    {
        return array(
            array(
                array(
                    'rhs_label' => 'Activities',
                    'rhs_module' => 'Users',
                    'lhs_module' => 'Contacts',
                    'relationship_name' => 'users_contacts_relationship'
                ),
                array(
                    'Contacts' => 'Activities:Users',
                    'Users' => 'Activities:Contacts'
                )
            ),
            array(
                array(
                    'rhs_label' => 'Activities 123',
                    'rhs_module' => 'Users',
                    'lhs_module' => 'Contacts',
                    'relationship_name' => 'users_contacts_relationship'
                ),
                array(
                    'Contacts' => 'Activities 123:Users',
                    'Users' => 'Activities 123:Contacts'
                )
            ),
            array(
                array(
                    'rhs_module' => 'Users',
                    'lhs_module' => 'Contacts',
                    'relationship_name' => 'users_contacts_relationship'
                ),
                array(
                    'Contacts' => 'Users',
                    'Users' => 'Contacts'
                )
            ),
            array(
                array(
                    'lhs_module' => 'lhs_module',
                    'lhs_label' => 'lhs_label',
                    'rhs_module' => 'rhs_module',
                    'rhs_label' => 'rhs_label',
                ),
                array(
                    'lhs_module' => 'rhs_label:rhs_module',
                    'rhs_module' => 'rhs_label:lhs_module'
                )
            )
        );
    }
}

class ActivitiesRelationship56425 extends ActivitiesRelationship
{
    static public function reset($module)
    {
        self::$labelsAdded = array(
            $module => true
        );

    }
}
