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

require_once('modules/ModuleBuilder/parsers/relationships/OneToManyRelationship.php');

/**
 * Test building a one to many relationship
 *
 * @ticket 59889
 */
class CustomOneToManyTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');

    }
    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }


    public function testSelfReferencing()
    {
        $definition = array(
            'rhs_label' => 'Opportunities',
            'lhs_label' => 'Opportunities',
            'rhs_subpanel' => 'default',
            'lhs_module' => 'Opportunities',
            'rhs_module' => 'Opportunities',
            'relationship_type' => 'one-to-many',
            'readonly' => false,
            'deleted' => false,
            'relationship_only' => false,
            'for_activities' => false,
            'is_custom' => false,
            'from_studio' => false,
            'relationship_name' => 'opportunities_opportunities_2',
        );

        $testRel = new OneToManyRelationship($definition);
        $vardefs = $testRel->buildVardefs();
        $subPanel = $testRel->buildSubpanelDefinitions();
        $relData = $testRel->buildRelationshipMetaData();

        // print_r($vardefs);
        $this->assertEquals(4,count($vardefs['Opportunities']),"Doesn't have all four entries (link, link2, id, name)");
        
        $sortedDefs = array();
        foreach($vardefs['Opportunities'] as $def ) {
            if ( $def['type'] == 'link' ) {
                if ( isset($def['side']) && $def['side'] == 'right' ) {
                    $sortedDefs['link2'] = $def;
                } else {
                    $sortedDefs['link'] = $def;
                }
            } else {
                if ( isset($def['rname']) && $def['rname'] == 'id' ) {
                    $sortedDefs['id'] = $def;
                } else {
                    $sortedDefs['name'] = $def;
                }
            }
        }

        $this->assertEquals(4,count($sortedDefs),"Couldn't sort out the four entries only found: ".print_r(array_keys($sortedDefs),true));

        $this->assertEquals($sortedDefs['id']['name'],$sortedDefs['id']['id_name'],"ID's id_name is wrong!");
        $this->assertEquals($sortedDefs['id']['name'],$sortedDefs['link2']['id_name'],"Link2's id_name is wrong!");
        $this->assertEquals($sortedDefs['id']['name'],$sortedDefs['name']['id_name'],"Name's id_name is wrong!");
        $this->assertNotEquals($sortedDefs['id']['name'],$sortedDefs['link']['id_name'],"Link's id_name is the same as the right side id_name");

        // print_r($subPanel);
        $this->assertEquals($sortedDefs['link']['name'],$subPanel['Opportunities'][0]['get_subpanel_data'],"Subpanel is using the incorrect link");
        // print_r($relData);
    }

    public function testNormal()
    {
        $definition = array(
            'rhs_label' => 'Opportunities',
            'lhs_label' => 'Person Module',
            'rhs_subpanel' => 'default',
            'lhs_module' => 'Opportunities',
            'rhs_module' => 'bugs',
            'relationship_type' => 'one-to-many',
            'readonly' => false,
            'deleted' => false,
            'relationship_only' => false,
            'for_activities' => false,
            'is_custom' => false,
            'from_studio' => false,
            'relationship_name' => 'opportunities_bugs',
        );

        $testRel = new OneToManyRelationship($definition);
        $vardefs = $testRel->buildVardefs();
        $subPanel = $testRel->buildSubpanelDefinitions();
        $relData = $testRel->buildRelationshipMetaData();

        // print_r($vardefs);
        $this->assertEquals(3,count($vardefs['bugs']),"Doesn't have the three right entries (link2, id, name)");
        $this->assertEquals(1,count($vardefs['Opportunities']),"Doesn't have just one left entry)");
        
        $sortedDefs = array();
        foreach($vardefs['bugs'] as $def ) {
            if ( $def['type'] == 'link' ) {
                if ( isset($def['side']) && $def['side'] == 'right' ) {
                    $sortedDefs['link2'] = $def;
                } else {
                    $this->assertFalse(true,"The right hand side has a left-handed link field.");
                }
            } else {
                if ( isset($def['rname']) && $def['rname'] == 'id' ) {
                    $sortedDefs['id'] = $def;
                } else {
                    $sortedDefs['name'] = $def;
                }
            }
        }
        $sortedDefs['link'] = $vardefs['Opportunities'][0];

        $this->assertEquals(4,count($sortedDefs),"Couldn't sort out the four entries only found: ".print_r(array_keys($sortedDefs),true));

        $this->assertEquals($sortedDefs['id']['name'],$sortedDefs['id']['id_name'],"ID's id_name is wrong!");
        $this->assertEquals($sortedDefs['id']['name'],$sortedDefs['link2']['id_name'],"Link2's id_name is wrong!");
        $this->assertEquals($sortedDefs['id']['name'],$sortedDefs['name']['id_name'],"Name's id_name is wrong!");

        // print_r($subPanel);
        $this->assertEquals($sortedDefs['link']['name'],$subPanel['Opportunities'][0]['get_subpanel_data'],"Subpanel is using the incorrect link");
        // print_r($relData);

    }
}