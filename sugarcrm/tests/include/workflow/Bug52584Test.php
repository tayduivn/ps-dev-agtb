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

require_once('include/workflow/glue.php');
/**
 * Bug #52584
 * Time triggered workflow isn't working when condition checkes a calculated field.
 *
 * @author myarotsky@sugarcrm.com
 * @ticket 52584
 */
class Bug52584Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var object
     */
    private $shell_object;

    /**
     * @var object
     */
    private $focus;

    /**
     * @var string
     */
    private $field;

    public function setUp()
    {
        $this->field = 'a';
        $this->value = 'test';

        $this->shell_object = new stdClass();
        $this->shell_object->field = $this->field;

        $this->focus = new stdClass();
        $this->focus->{$this->field} = $this->value;
        $this->focus->fetched_row[$this->field] = $this->value;
    }

    /**
     * Condition Return TRUE If Value Not Changes
     * @group 52584
     */
    public function testConditionReturnTrueIfValueNotChanges()
    {
        $this->assertTrue($this->getConditionResult());
    }
    /**
     * Condition Return FALSE If Value Is Changes
     * @group 52584
     */
    public function testConditionReturnFalseIfValueIsChanges()
    {
        $this->focus->{$this->field} = 'new value';
        $this->assertFalse($this->getConditionResult());
    }

    private function getConditionResult()
    {
        $glue = new WorkFlowGlue();
        $ret = $glue->glue_normal_compare_any_time($this->shell_object);
        $focus = $this->focus;
        return eval("return $ret;");
    }
}
