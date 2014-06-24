<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

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
        $this->shell_object->parent_id = 'Bug52584Test';

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
