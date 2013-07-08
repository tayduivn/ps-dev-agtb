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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once("modules/ActivityStream/clients/base/api/ActivitiesApi.php");
require_once("data/SugarACL.php");

class ActivitiesApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $api;

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp("current_user");
        $this->api       = SugarTestRestUtilities::getRestServiceMock();
        $this->api->user = $GLOBALS['current_user'];
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testListActivities_HomePage_PostActivityMessageWithSpecialChars_SpecialCharsReturnedDecoded()
    {
        $records   = array(
            array(
                'comment_count' => 0,
                'last_comment'  => json_encode(array()),
                'date_modified' => "2013-12-25 13:00:00",
                'date_entered'  => "2013-12-25 13:00:00",
                'activity_type' => 'post',
                'first_name'    => 'Davey',
                'last_name'     => 'Crockett',
                'data'          => json_encode(
                    array(
                        'value' => 'aaa &lt; bbb &gt; ccc',
                        'tags'  =>
                        array(),
                    )
                ),
            ),
        );
        $records[] = array(); // Need One Bogus Record that Formatter will POP

        $expected = array(
            'records'     => array(
                array(
                    'comment_count'   => 0,
                    'last_comment'    => array(),
                    'date_modified'   => '2013-12-25T13:00:00+00:00',
                    'date_entered'    => '2013-12-25T13:00:00+00:00',
                    'activity_type'   => 'post',
                    'first_name'      => 'Davey',
                    'last_name'       => 'Crockett',
                    'data'            => array(
                        'value' => "aaa < bbb > ccc",
                        'tags'  => array(),
                    ),
                    'created_by_name' => 'Davey Crockett',
                ),
            ),
            'next_offset' => -1,
            'args'        => array(),
        );

        $sugarQueryMock = $this->getMock("SugarQuery", array("execute"));
        $sugarQueryMock->expects($this->once())
            ->method("execute")
            ->will($this->returnValue($records));

        $activitiesApi = new TestActivitiesApi();
        $actual        = $activitiesApi->exec_formatResult($this->api, array(), $sugarQueryMock, null);

        $this->assertEquals(
            $expected,
            $actual,
            "Expected Encoded Special Characters in Activity Message to be properly decoded by Result Formatter"
        );
    }

    public function testListActivities_HomePage_MultipleModuleTypes_UserHasMixedFieldAccess_AppropriateFieldChangesReturned()
    {
        $records = array(
            array(
                'comment_count' => 0,
                'last_comment'  => json_encode(array()),
                'date_modified' => "2013-12-25 13:00:00",
                'date_entered'  => "2013-12-25 13:00:00",
                'activity_type' => 'update',
                'first_name'    => 'Davey',
                'last_name'     => 'Crockett',
                'fields'        => json_encode(array('first_name', 'last_name', 'lead_source', 'city')),
                'data'          => json_encode(
                    array(
                        'object'  => array(
                            'type'   => 'Lead',
                            'module' => 'Leads',
                            'name'   => 'Davey Crockett',
                        ),
                        'changes' => array(
                            'lead_source' => array(
                                'field_name' => 'lead_source',
                                'before'     => 'xxx',
                                'after'      => 'yyy',
                            ),
                        ),
                    )
                ),
            ),
            array(
                'comment_count' => 0,
                'last_comment'  => json_encode(array()),
                'date_modified' => "2013-12-25 13:00:00",
                'date_entered'  => "2013-12-25 13:00:00",
                'activity_type' => 'update',
                'first_name'    => 'Jim',
                'last_name'     => 'Bowie',
                'fields'        => json_encode(array('opt_out')),
                'data'          => json_encode(
                    array(
                        'object'  => array(
                            'type'   => 'Contact',
                            'module' => 'Contacts',
                            'name'   => 'Jim Bowie',
                        ),
                        'changes' => array(
                            'opt_out' => array(
                                'field_name' => 'opt_out',
                                'before'     =>  false,
                                'after'      =>  true,
                            ),
                        ),
                    )
                ),
            ),
        );
        $records[] = array(); // Need One Bogus Record that Formatter will POP

        $expected = array(
            'records' => array(
                array(
                    'comment_count'   => 0,
                    'last_comment'    => array(),
                    'date_modified'   => '2013-12-25T13:00:00+00:00',
                    'date_entered'    => '2013-12-25T13:00:00+00:00',
                    'activity_type'   => 'update',
                    'first_name'    => 'Davey',
                    'last_name'     => 'Crockett',
                    'data'            => array(
                        'object'  => array(
                            'type'   => 'Lead',
                            'module' => 'Leads',
                            'name'   => 'Davey Crockett',
                        ),

                        'changes' => array(              // User Has Access to lead_source field - Change Data Expected
                            'lead_source' => array(
                                'field_name' => 'lead_source',
                                'before'     => 'xxx',
                                'after'      => 'yyy',
                            ),
                        ),
                    ),
                    'created_by_name' => 'Davey Crockett',
                ),
                array(
                    'comment_count'   => 0,
                    'last_comment'    => array(),
                    'date_modified'   => '2013-12-25T13:00:00+00:00',
                    'date_entered'    => '2013-12-25T13:00:00+00:00',
                    'activity_type'   => 'update',
                    'first_name'      => 'Jim',
                    'last_name'       => 'Bowie',
                    'data'            => array(
                        'object'  => array(
                            'type'   => 'Contact',
                            'module' => 'Contacts',
                            'name'   => 'Jim Bowie',
                        ),
                        'changes' => array(),               // User Has No Access to opt_out field - No Change Data Expected
                    ),
                    'created_by_name' => 'Jim Bowie',
                ),
            ),
            'next_offset' => -1,
            'args'        => array(),
        );

        $sugarQueryMock = $this->getMock("SugarQuery", array("execute"));
        $sugarQueryMock->expects($this->once())
            ->method("execute")
            ->will($this->returnValue($records));

        // Inject SugarACL checkFieldList()
        $aclLead                     = new TestSugarACLStatic();
        $aclLead->return_value       = array('lead_source' => true);  //User Has Field Level Access to Leads::lead_source field
        $aclContact                  = new TestSugarACLStatic();
        $aclContact->return_value    = array('opt_out' => false);     //User Does Not Have Field Level Access to Contacts::opt_out field
        SugarACL::resetACLs();
        SugarACL::$acls['Leads']    = array($aclLead);
        SugarACL::$acls['Contacts'] = array($aclContact);

        $activitiesApi = new TestActivitiesApi();
        $actual        = $activitiesApi->exec_formatResult($this->api, array(), $sugarQueryMock, null);

        $this->assertEquals($expected, $actual, "Expected Activities Records with Field Access Applied correctly across Modules");
    }

    public function testListActivities_ListView_UserHasFieldAccess_FieldChangesReturned()
    {
        $records   = array(
            array(
                'comment_count' => 0,
                'last_comment'  => json_encode(array()),
                'date_modified' => "2013-12-25 13:00:00",
                'date_entered'  => "2013-12-25 13:00:00",
                'activity_type' => 'update',
                'first_name'    => 'John',
                'last_name'     => 'Doe',
                'fields'        => json_encode(array('first_name', 'last_name', 'lead_source', 'city')),
                'data'          => json_encode(
                    array(
                        'object'  => array(
                            'type'   => 'Lead',
                            'module' => 'Leads',
                            'name'   => 'John Doe',
                        ),
                        'changes' => array(
                            'lead_source' => array(
                                'field_name' => 'lead_source',
                                'before'     => 'xxx',
                                'after'      => 'yyy',
                            ),
                        ),
                    )
                ),
            ),
        );
        $records[] = array(); // Need One Bogus Record that Formatter will POP

        $expected = array(
            'records'     => array(
                array(
                    'comment_count'   => 0,
                    'last_comment'    => array(),
                    'date_modified'   => '2013-12-25T13:00:00+00:00',
                    'date_entered'    => '2013-12-25T13:00:00+00:00',
                    'activity_type'   => 'update',
                    'first_name'      => 'John',
                    'last_name'       => 'Doe',
                    'data'            => array(
                        'object'  => array(
                            'type'   => 'Lead',
                            'module' => 'Leads',
                            'name'   => 'John Doe',
                        ),
                        'changes' => array(
                            'lead_source' => array(
                                'field_name' => 'lead_source',
                                'before'     => 'xxx',
                                'after'      => 'yyy',
                            ),
                        ),
                    ),
                    'created_by_name' => 'John Doe',
                ),
            ),
            'next_offset' => -1,
            'args'        => array(),
        );

        $sugarQueryMock = $this->getMock("SugarQuery", array("execute"));
        $sugarQueryMock->expects($this->once())
            ->method("execute")
            ->will($this->returnValue($records));

        // Inject SugarACL checkFieldList()
        $acl                     = new TestSugarACLStatic();
        $acl->return_value       = array('lead_source' => true);  // User Has Field Level Access to Leads::lead_source field
        SugarACL::resetACLs();
        SugarACL::$acls['Leads'] = array($acl);

        $activitiesApi = new TestActivitiesApi();
        $actual        = $activitiesApi->exec_formatResult($this->api, array(), $sugarQueryMock, null);

        $this->assertEquals($expected, $actual, "Expected Activities Records with Changed Fields Listed");
    }

    public function testListActivities_ListView_UserDoesNotHaveFieldAccess_FieldChangesNotReturned()
    {
        $records   = array(
            array(
                'comment_count' => 0,
                'last_comment'  => json_encode(array()),
                'date_modified' => "2013-12-25 13:00:00",
                'date_entered'  => "2013-12-25 13:00:00",
                'activity_type' => 'update',
                'first_name'    => 'John',
                'last_name'     => 'Doe',
                'fields'        => json_encode(array('first_name', 'last_name', 'lead_source', 'city')),
                'data'          => json_encode(
                    array(
                        'object'  => array(
                            'type'   => 'Lead',
                            'module' => 'Leads',
                            'name'   => 'John Doe',
                        ),
                        'changes' => array(
                            'lead_source' => array(
                                'field_name' => 'lead_source',
                                'before'     => 'xxx',
                                'after'      => 'yyy',
                            ),
                        ),
                    )
                ),
            ),
        );
        $records[] = array(); // Need One Bogus Record that Formatter will POP

        $expected = array(
            'records'     => array(
                array(
                    'comment_count'   => 0,
                    'last_comment'    => array(),
                    'date_modified'   => '2013-12-25T13:00:00+00:00',
                    'date_entered'    => '2013-12-25T13:00:00+00:00',
                    'activity_type'   => 'update',
                    'first_name'      => 'John',
                    'last_name'       => 'Doe',
                    'data'            => array(
                        'object'  => array(
                            'type'   => 'Lead',
                            'module' => 'Leads',
                            'name'   => 'John Doe',
                        ),
                        'changes' => array(),
                    ),
                    'created_by_name' => 'John Doe',
                ),
            ),
            'next_offset' => -1,
            'args'        => array(),
        );

        $sugarQueryMock = $this->getMock("SugarQuery", array("execute"));
        $sugarQueryMock->expects($this->once())
            ->method("execute")
            ->will($this->returnValue($records));

        // Inject SugarACL checkFieldList()
        $acl                     = new TestSugarACLStatic();
        $acl->return_value       = array('lead_source' => false);  //User Has No Field Level Access to lead_source field
        SugarACL::resetACLs();
        SugarACL::$acls['Leads'] = array($acl);

        $activitiesApi = new TestActivitiesApi();
        $actual        = $activitiesApi->exec_formatResult($this->api, array(), $sugarQueryMock, null);

        $this->assertEquals($expected, $actual, "Expected Activities Records without data for Changed Fields");
    }

    public function testListActivities_RecordView_UserDoesNotHaveFieldAccess_FieldChangesNotReturned()
    {
        $records   = array(
            array(
                'comment_count' => 0,
                'last_comment'  => json_encode(array()),
                'date_modified' => "2013-12-25 13:00:00",
                'date_entered'  => "2013-12-25 13:00:00",
                'activity_type' => 'update',
                'first_name'    => 'John',
                'last_name'     => 'Doe',
                'fields'        => json_encode(array('first_name', 'last_name', 'lead_source')),
                'data'          => json_encode(
                    array(
                        'object'  => array(
                            'type'   => 'Lead',
                            'module' => 'Leads',
                            'name'   => 'John Doe',
                        ),
                        'changes' => array(
                            'lead_source' => array(
                                'field_name' => 'lead_source',
                                'before'     => 'xxx',
                                'after'      => 'yyy',
                            ),
                            'first_name' => array(
                                'field_name' => 'first_name',
                                'before'     => 'Johnathan',
                                'after'      => 'John',
                            ),
                            'last_name' => array(
                                'field_name' => 'last_name',
                                'before'     => 'Dough',
                                'after'      => 'Doe',
                            ),
                        ),
                    )
                ),
            ),
        );
        $records[] = array(); // Need One Bogus Record that Formatter will POP

        $expected = array(
            'records'     => array(
                array(
                    'comment_count'   => 0,
                    'last_comment'    => array(),
                    'date_modified'   => '2013-12-25T13:00:00+00:00',
                    'date_entered'    => '2013-12-25T13:00:00+00:00',
                    'activity_type'   => 'update',
                    'first_name'      => 'John',
                    'last_name'       => 'Doe',
                    'fields'          => '["first_name","last_name","lead_source"]',
                    'data'            => array(
                        'object'  => array(
                            'type'   => 'Lead',
                            'module' => 'Leads',
                            'name'   => 'John Doe',
                        ),
                        'changes' => array(),
                    ),
                    'created_by_name' => 'John Doe',
                ),
            ),
            'next_offset' => -1,
            'args'        => array(),
        );

        $sugarQueryMock = $this->getMock("SugarQuery", array("execute"));
        $sugarQueryMock->expects($this->once())
            ->method("execute")
            ->will($this->returnValue($records));

        // Inject SugarACL checkFieldList()
        $acl = new TestSugarACLStatic();
        //User Has Field Level Access to lead_source, first_name and last_name fields
        $acl->return_value  = array(
            'lead_source' => false,
            'first_name'  => false,
            'last_name'   => false,
        );
        SugarACL::resetACLs();
        SugarACL::$acls['Leads'] = array($acl);

        $lead = SugarTestLeadUtilities::createLead();

        $activitiesApi = new TestActivitiesApi();
        $actual        = $activitiesApi->exec_formatResult($this->api, array(), $sugarQueryMock, $lead);

        $this->assertEquals($expected, $actual, "Expected Activities Records without data for Changed Fields");
    }

    public function testListActivities_RecordView_UserHasFieldAccess_FieldChangesReturned()
    {
        $records   = array(
            array(
                'comment_count' => 0,
                'last_comment'  => json_encode(array()),
                'date_modified' => "2013-12-25 13:00:00",
                'date_entered'  => "2013-12-25 13:00:00",
                'activity_type' => 'update',
                'first_name'    => 'John',
                'last_name'     => 'Doe',
                'fields'        => json_encode(array('first_name', 'last_name', 'lead_source')),
                'data'          => json_encode(
                    array(
                        'object'  => array(
                            'type'   => 'Lead',
                            'module' => 'Leads',
                            'name'   => 'John Doe',
                        ),
                        'changes' => array(
                            'lead_source' => array(
                                'field_name' => 'lead_source',
                                'before'     => 'xxx',
                                'after'      => 'yyy',
                            ),
                            'first_name' => array(
                                'field_name' => 'first_name',
                                'before'     => 'Johnathan',
                                'after'      => 'John',
                            ),
                            'last_name' => array(
                                'field_name' => 'last_name',
                                'before'     => 'Dough',
                                'after'      => 'Doe',
                            ),
                        ),
                    )
                ),
            ),
        );
        $records[] = array(); // Need One Bogus Record that Formatter will POP

        $expected = array(
            'records'     => array(
                array(
                    'comment_count'   => 0,
                    'last_comment'    => array(),
                    'date_modified'   => '2013-12-25T13:00:00+00:00',
                    'date_entered'    => '2013-12-25T13:00:00+00:00',
                    'activity_type'   => 'update',
                    'first_name'      => 'John',
                    'last_name'       => 'Doe',
                    'fields'          => '["first_name","last_name","lead_source"]',
                    'data'            => array(
                        'object'  => array(
                            'type'   => 'Lead',
                            'module' => 'Leads',
                            'name'   => 'John Doe',
                        ),
                        'changes' => array(
                            'lead_source' => array(
                                'field_name' => 'lead_source',
                                'before'     => 'xxx',
                                'after'      => 'yyy',
                            ),
                            'first_name' => array(
                                'field_name' => 'first_name',
                                'before'     => 'Johnathan',
                                'after'      => 'John',
                            ),
                            'last_name' => array(
                                'field_name' => 'last_name',
                                'before'     => 'Dough',
                                'after'      => 'Doe',
                            ),
                        ),
                    ),
                    'created_by_name' => 'John Doe',
                ),
            ),
            'next_offset' => -1,
            'args'        => array(),
        );

        $sugarQueryMock = $this->getMock("SugarQuery", array("execute"));
        $sugarQueryMock->expects($this->once())
            ->method("execute")
            ->will($this->returnValue($records));

        // Inject SugarACL checkFieldList()
        $acl = new TestSugarACLStatic();
        //User Has Field Level Access to lead_source, first_name and last_name fields
        $acl->return_value  = array(
           'lead_source' => true,
           'first_name'  => true,
           'last_name'   => true,
        );
        SugarACL::resetACLs();
        SugarACL::$acls['Leads'] = array($acl);

        $lead = SugarTestLeadUtilities::createLead();

        $activitiesApi = new TestActivitiesApi();
        $actual        = $activitiesApi->exec_formatResult($this->api, array(), $sugarQueryMock, $lead);

        $this->assertEquals($expected, $actual, "Expected Activities Records with all data for Changed Fields");
    }
}

class TestActivitiesApi extends ActivitiesApi
{
    public function exec_formatResult(ServiceBase $api, array $args, SugarQuery $query, SugarBean $bean = null)
    {
        return $this->formatResult($api, $args, $query, $bean);
    }
}

class TestSugarACLStatic extends SugarACLStatic
{
    public $return_value = null;

    public function checkFieldList($module, $field_list, $action, $context)
    {
        return $this->return_value;
    }
}
