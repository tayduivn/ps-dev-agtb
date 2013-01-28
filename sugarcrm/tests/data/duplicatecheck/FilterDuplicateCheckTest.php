<?php
/********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

class FilterDuplicateCheckTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $metadata;

    public function setUp() {
        $this->metadata = array(
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
        );
    }

    /**
     * @group duplicatecheck
     */
    public function testFindDuplicates_NoBeanId_AddFilterForEditsIsNotCalled() {
        $bean = self::getMock("Lead");

        $filterDuplicateCheckMock = self::getMock(
            "FilterDuplicateCheck",
            array(
                 "buildDupeCheckFilter",
                 "addFilterForEdits",
                 "callFilterApi",
                 "rankAndSortDuplicates",
            ),
            array(
                 $bean,
                 $this->metadata,
            )
        );

        $filterDuplicateCheckMock->expects(self::once())
            ->method("buildDupeCheckFilter")
            ->will(self::returnValue(true));

        // addFilterForEdits should never be called if the bean has no id
        $filterDuplicateCheckMock->expects(self::never())
            ->method("addFilterForEdits");

        $filterDuplicateCheckMock->expects(self::once())
            ->method("callFilterApi")
            ->will(self::returnValue(true));

        $filterDuplicateCheckMock->expects(self::once())
            ->method("rankAndSortDuplicates")
            ->will(self::returnValue(true));

        $duplicates = $filterDuplicateCheckMock->findDuplicates();
    }

    /**
     * @group duplicatecheck
     */
    public function testFindDuplicates_HasBeanId_AddFilterForEditsIsCalled() {
        $bean     = self::getMock("Lead");
        $bean->id = 1;

        $filterDuplicateCheckMock = self::getMock(
            "FilterDuplicateCheck",
            array(
                 "buildDupeCheckFilter",
                 "addFilterForEdits",
                 "callFilterApi",
                 "rankAndSortDuplicates",
            ),
            array(
                 $bean,
                 $this->metadata,
            )
        );

        $filterDuplicateCheckMock->expects(self::once())
            ->method("buildDupeCheckFilter")
            ->will(self::returnValue(true));

        // addFilterForEdits should be called if the bean has an id
        $filterDuplicateCheckMock->expects(self::once())
            ->method("addFilterForEdits");

        $filterDuplicateCheckMock->expects(self::once())
            ->method("callFilterApi")
            ->will(self::returnValue(true));

        $filterDuplicateCheckMock->expects(self::once())
            ->method("rankAndSortDuplicates")
            ->will(self::returnValue(true));

        $duplicates = $filterDuplicateCheckMock->findDuplicates();
    }

    /**
     * @group duplicatecheck
     */
    public function testFindDuplicates_RankAndSortDuplicatesReordersTheResults() {
        $bean               = self::getMock("Lead");
        $bean->last_name    = "Griffin";
        $bean->first_name   = "Pete";
        $bean->account_name = "Petoria";

        $filterDuplicateCheckMock = self::getMock(
            "FilterDuplicateCheck",
            array(
                 "callFilterApi",
            ),
            array(
                 $bean,
                 $this->metadata,
            )
        );

        $duplicate1 = array(
            "id"           => "1",
            "last_name"    => "Griffin",
            "first_name"   => "Peter",
            "status"       => "New",
            "account_name" => "Petoria",
        );

        $duplicate2 = array(
            "id"           => "2",
            "last_name"    => "Griffin",
            "first_name"   => "Pete",
            "status"       => "",
            "account_name" => "Petoria",
        );

        $results = array(
            "records" => array(
                $duplicate1,
                $duplicate2,
            ),
        );

        $filterDuplicateCheckMock->expects(self::once())
            ->method("callFilterApi")
            ->will(self::returnValue($results));

        $expected = array(
            "records" => array(
                $duplicate2,
                $duplicate1,
            ),
        );
        $actual   = $filterDuplicateCheckMock->findDuplicates();
        self::assertEquals($expected["records"][0]["id"],
                           $actual["records"][0]["id"],
                           "The duplicate records should have swapped places based on their duplicate_check_rank values.");
    }

    /**
     * @group duplicatecheck
     */
    public function testBuildDupeCheckFilter_ReplacesFirstName_ReplacesLastName_RemovesAccountName() {
        $bean             = self::getMock("Lead");
        $bean->last_name  = "Griffin";
        $bean->first_name = "Peter";
        $filterDuplicateCheckCaller = new FilterDuplicateCheckCaller($bean, $this->metadata);

        $expected = array(
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
                                            '$starts' => $bean->first_name,
                                        ),
                                    ),
                                    array(
                                        'last_name' => array(
                                            '$starts' => $bean->last_name,
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );
        $actual   = $filterDuplicateCheckCaller->buildDupeCheckFilterCaller();

        // compare the complete arrays
        self::assertEquals($expected,
            $actual,
            "The original filters were lost or the new filter is not constructed properly.");
    }

    /**
     * @group duplicatecheck
     */
    public function testAddFilterForEdits_AddsANotEqualsFilterToTheFilterArrayToPreventMatchesOnTheSpecifiedId() {
        $bean                       = self::getMock("Lead");
        $bean->id                   = "1";
        $filterDuplicateCheckCaller = new FilterDuplicateCheckCaller($bean, $this->metadata);
        $filter                     = $filterDuplicateCheckCaller->buildDupeCheckFilterCaller(); // need to build the filter first

        $expected = array(
            array(
                '$and' => array(
                    array(
                        'id' => array(
                            '$not_equals' => $bean->id,
                        ),
                    ),
                    $filter,
                ),
            ),
        );
        $actual   = $filterDuplicateCheckCaller->addFilterForEditsCaller($filter, $bean->id);

        // compare the complete arrays
        self::assertEquals($expected,
                           $actual,
                           "The original filters were lost or the new filter is not constructed properly.");

        // make sure the id filter was added
        self::assertEquals($expected[0]['$and'][0]["id"]['$not_equals'],
                           $actual[0]['$and'][0]["id"]['$not_equals'],
                           "The additional not-equals filter was not added.");
    }
}

// need to make sure SugarApi is included when extending FilterDuplicateCheck to avoid a fatal error
require_once('include/api/SugarApi.php');

class FilterDuplicateCheckCaller extends FilterDuplicateCheck
{
    public function buildDupeCheckFilterCaller() {
        return $this->buildDupeCheckFilter($this->filterTemplate);
    }

    public function addFilterForEditsCaller($filter, $id) {
        return $this->addFilterForEdits($filter, $id);
    }
}
