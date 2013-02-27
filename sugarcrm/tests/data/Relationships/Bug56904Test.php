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

require_once('data/Link2.php');

/**
 * @ticket 56904
 */
class Bug56904Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Ensures that relationships for all related beans are removed and return
     * value is calculated based on the related beans remove results
     *
     * @param array $results
     * @param bool $expected
     * @dataProvider getRemoveResults
     */
    public function testAllRelationsAreRemoved(array $results, $expected)
    {
        $relationship = $this->getRelationshipMock($results);
        $link         = $this->getLinkMock(count($results));

        $result = $relationship->removeAll($link);
        if ($expected)
        {
            $this->assertTrue($result);
        }
        else
        {
            $this->assertFalse($result);
        }
    }

    /**
     * Creates mock of SugarRelationship object which will return specified
     * results on on consecutive SugarRelationship::remove() calls
     *
     * @param array $results
     * @return SugarRelationship
     */
    protected function getRelationshipMock(array $results)
    {
        $mock = $this->getMockForAbstractClass('SugarRelationship');
        $mock->expects($this->exactly(count($results)))
            ->method('remove')
            ->will(
                call_user_func_array(array($this, 'onConsecutiveCalls'), $results)
            );
        return $mock;
    }

    /**
     * Creates mock of Link2 object with specified number of related beans
     *
     * @param int $count
     * @return Link2
     */
    protected function getLinkMock($count)
    {
        if ($count > 0)
        {
            $bean  = new SugarBean();
            $bean->id = 'Bug56904Test';
            $beans = array_fill(0, $count, $bean);
        }
        else
        {
            $beans = array();
        }

        $mock = $this->getMock('Link2', array('getSide', 'getFocus', 'getBeans'), array(), '', false);
        $mock->expects($this->any())
            ->method('getSide')
            ->will($this->returnValue(REL_LHS));
        $mock->expects($this->any())
            ->method('getFocus')
            ->will($this->returnValue(new SugarBean()));
        $mock->expects($this->any())
            ->method('getBeans')
            ->will($this->returnValue($beans));
        return $mock;
    }

    /**
     * Provides results that should be returned by SugarRelationship::remove()
     * calls and expected result of SugarRelationship::removeAll()
     *
     * @return array
     */
    public static function getRemoveResults()
    {
        return array(
            array(
                array(), true,
            ),
            array(
                array(true), true,
            ),
            array(
                array(false), false,
            ),
            array(
                array(true, false), false,
            ),
            array(
                array(false, true), false,
            ),
            array(
                array(false, false), false,
            ),
            array(
                array(true, true), true,
            ),
        );
    }
}
