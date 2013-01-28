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

class BeanDuplicateCheckTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @group duplicatecheck
     */
    public function testConstructor_MetadataCountIsZero_TheStrategyRemainsFalse() {
        $bean               = self::getMock("Lead");
        $metadata           = array();
        $beanDuplicateCheck = new BeanDuplicateCheck($bean, $metadata);

        $actual = $beanDuplicateCheck->getStrategy();
        self::assertFalse($actual, "The strategy should not have been changed from 'false'");
    }

    /**
     * @group duplicatecheck
     */
    public function testConstructor_MetadataCountIsTwo_TheStrategyRemainsFalse() {
        $bean               = self::getMock("Lead");
        $metadata           = array(
            'FilterDuplicateCheck' => array(
                'filter_template' => array(
                    array(
                        'account_name' => array(
                            '$starts' => '$account_name',
                        ),
                    ),
                ),
                'ranking_fields'  => array(
                    array(
                        'in_field_name'   => 'account_name',
                        'dupe_field_name' => 'account_name',
                    ),
                ),
            ),
            'ExtraDuplicateCheck'  => array(
                'filter_template' => array(
                    array(
                        'account_name' => array(
                            '$starts' => '$account_name',
                        ),
                    ),
                ),
                'ranking_fields'  => array(
                    array(
                        'in_field_name'   => 'account_name',
                        'dupe_field_name' => 'account_name',
                    ),
                ),
            ),
        );
        $beanDuplicateCheck = new BeanDuplicateCheck($bean, $metadata);

        $actual = $beanDuplicateCheck->getStrategy();
        self::assertFalse($actual, "The strategy should not have been changed from 'false'");
    }

    /**
     * @group duplicatecheck
     */
    public function testConstructor_TheStrategyDefinedInTheMetadataIsInvalid_TheStrategyRemainsFalse() {
        $bean               = self::getMock("Lead");
        $metadata           = array(
            'Foobar' => array(
                'filter_template' => array(
                    array(
                        'account_name' => array(
                            '$starts' => '$account_name',
                        ),
                    ),
                ),
                'ranking_fields'  => array(
                    array(
                        'in_field_name'   => 'account_name',
                        'dupe_field_name' => 'account_name',
                    ),
                ),
            ),
        );
        $beanDuplicateCheck = new BeanDuplicateCheck($bean, $metadata);

        $actual = $beanDuplicateCheck->getStrategy();
        self::assertFalse($actual, "The strategy should not have been changed from 'false'");
    }

    /**
     * @group duplicatecheck
     */
    public function testConstructor_MetadataCountIsOne_TheStrategyIsInitializedToTheStrategyDefinedInTheMetadata() {
        $bean               = self::getMock("Lead");
        $metadata           = array(
            'FilterDuplicateCheck' => array(
                'filter_template' => array(
                    array(
                        'account_name' => array(
                            '$starts' => '$account_name',
                        ),
                    ),
                ),
                'ranking_fields'  => array(
                    array(
                        'in_field_name'   => 'account_name',
                        'dupe_field_name' => 'account_name',
                    ),
                ),
            ),
        );
        $beanDuplicateCheck = new BeanDuplicateCheck($bean, $metadata);

        $expected = "FilterDuplicateCheck";
        $actual   = $beanDuplicateCheck->getStrategy();
        self::assertInstanceOf($expected,
                               $actual,
                               "The strategy should have been changed to an instance of '{$expected}'");
    }

    /**
     * @group duplicatecheck
     */
    public function testFindDuplicates_TheStrategyIsFalse_ReturnsNull() {
        $bean               = self::getMock("Lead");
        $metadata           = array(); // invalid metadata forces the strategy to remain false
        $beanDuplicateCheck = new BeanDuplicateCheck($bean, $metadata);

        $actual = $beanDuplicateCheck->findDuplicates();
        self::assertNull($actual, "BeanDuplicateCheck::findDuplicates should return null when the strategy is 'false'");
    }

    /**
     * @group duplicatecheck
     */
    public function testFindDuplicates_TheStrategyIsValid_TheFindDuplicatesMethodOnTheStrategyIsCalled() {
        $bean               = self::getMock("Lead");
        $metadata           = array(
            'DuplicateCheckMock' => array(
                'filter_template' => array(
                    array(
                        'account_name' => array(
                            '$starts' => '$account_name',
                        ),
                    ),
                ),
                'ranking_fields'  => array(
                    array(
                        'in_field_name'   => 'account_name',
                        'dupe_field_name' => 'account_name',
                    ),
                ),
            ),
        );
        $beanDuplicateCheck = new BeanDuplicateCheck($bean, $metadata);

        $actual = $beanDuplicateCheck->findDuplicates();
        self::assertTrue($actual, "DuplicateCheckMock::findDuplicates should return 'true'");
    }
}

// need to make sure SugarApi is included when extending DuplicateCheckStrategy to avoid a fatal error
require_once('include/api/SugarApi.php');
require_once('clients/base/api/FilterApi.php');

/**
 * Use the following class to test that DuplicateCheckStrategy::findDuplicates is called when it should be.
 */
class DuplicateCheckMock extends DuplicateCheckStrategy
{
    protected function setMetadata($metadata) {}

    public function findDuplicates() {
        return true;
    }

}
