<?php
require_once('include/workflow/workflow_utils.php');
require_once('modules/Expressions/Expression.php');
require_once('include/workflow/glue.php');

class Bug43572Test extends Sugar_PHPUnit_Framework_TestCase
{
    function testGlueDate()
    {

        $condition = new Expression();
        $condition->lhs_field = 'date_closed';
        $condition->exp_type = 'date';
        $condition->operator = 'Less Than';
        $condition->ext1 = 172800;
        $glueWorkflow = new WorkFlowGlue();
        $actualCondition = $glueWorkflow->glue_date('future', $condition, true);
        $expectedConditionChunk = preg_quote('strtotime($focus->date_closed) < (time() + 172800)', '~');
        $matched = preg_match("~$expectedConditionChunk~i", $actualCondition);
        $this->assertEquals(1, $matched);

    }
}