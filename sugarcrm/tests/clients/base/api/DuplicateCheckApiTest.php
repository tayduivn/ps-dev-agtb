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

// need to make sure SugarApi is included when extending DuplicateCheckStrategy to avoid a fatal error
require_once('include/api/SugarApi.php');
require_once("clients/base/api/DuplicateCheckApi.php");
require_once("tests/SugarTestRestUtilities.php");
/**
 * @group api
 * @group duplicatecheck
 */
class DuplicateCheckApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $copyOfLeadsDuplicateCheckVarDef,
            $mockLeadsDuplicateCheckVarDef = array(
        'FilterDuplicateCheck' => array(
            'filter_template' => array(
                array(
                    '$and' => array(
                        array(
                            '$or' => array(
                                array(
                                    'status' => array(
                                        '$not_equals' => 'Converted',
                                    ),
                                ),
                                array(
                                    'status' => array(
                                        '$is_null' => '',
                                    ),
                                ),
                            ),
                        ),
                        array(
                            '$or' => array(
                                array(
                                    '$and' => array(
                                        array(
                                            'first_name' => array(
                                                '$starts' => '$first_name',
                                            ),
                                        ),
                                        array(
                                            'last_name' => array(
                                                '$starts' => '$last_name',
                                            ),
                                        ),
                                    ),
                                ),
                                array(
                                    'phone_work' => array(
                                        '$equals' => '$phone_work',
                                    ),
                                ),
                            ),
                        ),
                        array(
                            'account_name' => array(
                                '$equals' => '$account_name',
                            ),
                        ),
                    ),
                ),
            ),
            'ranking_fields'  => array(
                array(
                    'in_field_name'   => 'last_name',
                    'dupe_field_name' => 'last_name',
                ),
                array(
                    'in_field_name'   => 'first_name',
                    'dupe_field_name' => 'first_name',
                ),
            ),
        ),
    );

    private $api,
            $duplicateCheckApi,
            $convertedLead,
            $newLead,
            $newLead2,
            $newLeadFirstName  = "SugarLeadNewFirst",
            $newLeadLastName   = "SugarLeadLast",
            $newLead2FirstName = "SugarLeadNewFirst2", // different first name
            $newLead2LastName  = "SugarLeadLast"; // same last name

    public function setUp()
    {
        parent::setUp();

        SugarTestHelper::setUp('dictionary');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('app_strings');

        $this->copyOfLeadsDuplicateCheckVarDef = $GLOBALS["dictionary"]["Lead"]["duplicate_check"];
        $GLOBALS["dictionary"]["Lead"]["duplicate_check"] = $this->mockLeadsDuplicateCheckVarDef;

        $GLOBALS["current_user"] = SugarTestUserUtilities::createAnonymousUser();

        $this->api               = SugarTestRestUtilities::getRestServiceMock();
        $this->duplicateCheckApi = new DuplicateCheckApi();

        //make sure any left over test leads from failed tests are removed
        $GLOBALS['db']->query('DELETE FROM leads WHERE last_name LIKE (\'SugarLead%\')');

        //create test leads
        $this->convertedLead             = SugarTestLeadUtilities::createLead();
        $this->convertedLead->first_name = 'SugarLeadConvertFirst';
        $this->convertedLead->last_name  = 'SugarLeadLast';
        $this->convertedLead->status     = 'Converted';
        $this->convertedLead->save();

        $this->newLead             = SugarTestLeadUtilities::createLead();
        $this->newLead->first_name = $this->newLeadFirstName;
        $this->newLead->last_name  = $this->newLeadLastName;
        $this->newLead->save();

        $this->newLead2             = SugarTestLeadUtilities::createLead();
        $this->newLead2->first_name = $this->newLead2FirstName;
        $this->newLead2->last_name  = $this->newLead2LastName;
        $this->newLead2->status     = 'New'; // non-empty, non-Converted status
        $this->newLead2->save();
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        $GLOBALS["dictionary"]["Lead"]["duplicate_check"] = $this->copyOfLeadsDuplicateCheckVarDef;

        parent::tearDown();
    }

    /**
     * @dataProvider duplicatesProvider
     */
    public function testCheckForDuplicates($args, $expected, $message)
    {
        $args["module"] = "Leads";
        $results = $this->duplicateCheckApi->checkForDuplicates($this->api, $args);
        $actual  = count($results["records"]);
        self::assertEquals($expected, $actual, $message);
    }

    public function duplicatesProvider() {
        return array(
            array(
                array(
                    "first_name" => $this->newLeadFirstName,
                    "last_name"  => $this->newLeadLastName,
                ),
                2,
                "Two fields passed in; should match two Leads",
            ),
            array(
                array(
                    "first_name" => $this->newLead2FirstName,
                    "last_name"  => $this->newLead2LastName,
                ),
                1,
                "Two fields passed in; should match one Lead",
            ),
            array(
                array(
                    "first_name" => "",
                    "last_name"  => $this->newLeadLastName,
                ),
                2,
                "One of the two fields passed in is blank; should match two Leads",
            ),
            array(
                array(
                    "last_name" => $this->newLeadLastName,
                ),
                2,
                "Filter omits 'first_name' since field is not passed in; should match two Leads",
            ),
            array(
                array(
                    "last_name" => 'DO NOT MATCH ANY LAST NAMES',
                ),
                0,
                "No duplicate matches, should returns 0 results",
            ),
        );
    }

    public function testCheckForDuplicates_AllFilterArgumentsAreEmpty_ReturnsEmptyResultSet() {
        $GLOBALS["dictionary"]["Lead"]["duplicate_check"] = array(
            'FilterDuplicateCheck' => array(
                'filter_template' => array(
                    array(
                        'last_name' => array(
                            '$starts' => '$last_name',
                        ),
                    )
                )
            ),
        );

        $args = array(
            'module' => 'Leads',
            'last_name' => ''
        );
        $results = $this->duplicateCheckApi->checkForDuplicates($this->api, $args);
        self::assertEquals(array(), $results, 'When all arguments expected by the filter are empty, no records should be returned');
    }

    public function testCheckForDuplicates_EmptyBean()
    {
        $args = array(
            "module" => "FooModule"
        );

        $this->setExpectedException('SugarApiExceptionInvalidParameter');
        $this->duplicateCheckApi->checkForDuplicates($this->api, $args);
    }

    public function testCheckForDuplicates_NotAuthorized()
    {
        $args = array(
            "module" => "Leads"
        );
        //Setting access to be denied for read
        $_SESSION['ACL'] = array();
        $_SESSION['ACL'][$GLOBALS['current_user']->id][$args['module']]['module']['access']['aclaccess'] = ACL_ALLOW_DISABLED;
        // reset cached ACLs
        SugarACL::$acls = array();

        $this->setExpectedException('SugarApiExceptionNotAuthorized');
        $this->duplicateCheckApi->checkForDuplicates($this->api, $args);

        unset($_SESSION['ACL']);
    }

    public function testCheckForDuplicates_InvalidParameter()
    {
        $args = array(
            "module" => "Leads"
        );

        $this->setExpectedException('SugarApiExceptionInvalidParameter');
        $duplicateCheckApi = $this->getMock('DuplicateCheckApi', array('populateFromApi'));
        $duplicateCheckApi->expects($this->any())
                          ->method('populateFromApi')
                          ->will($this->returnValue(array()));
        $duplicateCheckApi->checkForDuplicates($this->api, $args);
    }
}
