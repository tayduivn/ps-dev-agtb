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

/**
 * @ticket 50422
 */
class Bug50422Test extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var  Call */
    private $call;

    /** @var  Contact */
    private $contact;

    /** @var DeployedRelationships */
    private $relationships;

    /** @var OneToManyRelationship */
    private $relationship;

    protected function setUp()
    {
        parent::setUp();

        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user', array(true, 1));
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('app_list_strings');

        $this->relationships = new DeployedRelationships('Contacts');
        $definition = array(
            'lhs_module' => 'Contacts',
            'relationship_type' => 'one-to-many',
            'rhs_module' => 'Calls'
        );

        $this->relationship = RelationshipFactory::newRelationship($definition);
        $this->relationships->add($this->relationship);
        $this->relationships->save();
        $this->relationships->build();
        SugarTestHelper::setUp('relation', array('Contacts', 'Calls'));

        $this->call = SugarTestCallUtilities::createCall();
        $contact = $this->contact = SugarTestContactUtilities::createContact();
        $contact->salutation = 'Mr.';
        $contact->first_name = 'Bug50422Fn';
        $contact->last_name = 'Bug50422Ln';
        $contact->save();

        $relationshipName = $this->relationship->getName();
        $this->call->load_relationship($relationshipName);
        $this->call->$relationshipName->add($this->contact);
    }

    protected function tearDown()
    {
        SugarTestCallUtilities::removeAllCreatedCalls();
        SugarTestContactUtilities::removeAllCreatedContacts();

        if ($this->relationship && $this->relationships) {
            $this->relationships->delete($this->relationship->getName());
            $this->relationships->save();
        }

        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testRelateFullNameFormat()
    {
        /** @var User */
        global $current_user;
        $current_user->setPreference('default_locale_name_format', 's l, f');

        $call = $this->call;
        $relationshipName = $this->relationship->getName();
        $relateFieldName = $relationshipName . '_name';

        require_once 'include/ListView/ListViewData.php';
        $lvd = new ListViewData();
        $lvd->listviewName = $call->module_name;
        $response = $lvd->getListViewData(
            $call,
            'calls.id = ' . $call->db->quoted($call->id),
            -1,
            -1,
            array($relateFieldName)
        );

        $this->assertArrayHasKey('data', $response, 'Response doesn\'t contain data');
        $this->assertInternalType('array', $response['data'], 'Response data is not array');
        $this->assertEquals(1, count($response['data']), 'Response data should contain exactly 1 item');

        $relateFieldName = strtoupper($relateFieldName);
        $row = array_shift($response['data']);
        $this->assertInternalType('array', $row, 'Data row is not array');
        $this->assertArrayHasKey($relateFieldName, $row, 'Row doesn\'t contain contact name');
        $this->assertEquals('Mr. Bug50422Ln, Bug50422Fn', $row[$relateFieldName], 'Full name format is incorrect');
    }
}
