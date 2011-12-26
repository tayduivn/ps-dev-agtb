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

 
require_once 'include/ListView/ProcessView.php';


/**
 * Bug #46071
 * Record ID is shown instead of user name in workflow
 *
 * @author dkroman@sugarcrm.com
 * @ticket 46071
 */
class Bug46071Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $beanList = array();
	    $beanFiles = array();
	    require('include/modules.php');
	    $GLOBALS['beanList'] = $beanList;
	    $GLOBALS['beanFiles'] = $beanFiles;
        
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser(true, 1);
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }
	
    /**
     * @group 46071
     */
	public function testGetTriggerListDisplayTextGeneric() 
	{
        $workflow = new Bug46071Workflow;
        $workflow->save();

        $process_view = new ProcessView($workflow->get_workflow_type(), $workflow);
        $message = $process_view->get_trigger_list_display_text_generic($workflow);

        $this->assertContains($GLOBALS['current_user']->name, $message);
        
        $GLOBALS['db']->query("DELETE FROM workflow_triggershells WHERE id = '" . $workflow->id . "' ");
        $GLOBALS['db']->query("DELETE FROM expressions WHERE id = '" . $workflow->bug46071_expression->id . "' ");
	}
}


/**
 * Class was created to simulate work of function get_linked_beans in "WorkFlowTriggerShell"
 */
class Bug46071Workflow extends WorkFlowTriggerShell
{
    var $type = 'filter_rel_field';
    var $field = 'assigned_user_id';
    var $bug46071_expression = null;
 
    /**
     * Returns simulation an array of "beans" of related data.
     *
     * @param string $field_name relationship to be loaded, unused
     * @param string $bean name  class name of the related bean, unused
     * @param array $sort_array optional, unused
     * @param int $begin_index Optional, default 0, unused
     * @param int $end_index Optional, default -1, unused
     * @param int $deleted Optional, Default 0, 0  adds deleted=0 filter, 1  adds deleted=1 filter, unused
     * @param string $optional_where, Optional, default empty, unused
     *
     */
    function get_linked_beans($field_name,$bean_name, $sort_array = array(), $begin_index = 0, $end_index = -1,
                              $deleted=0, $optional_where="")
    {
        if ( !$this->bug46071_expression )
        {
            $this->bug46071_expression = new Expression();

            $this->bug46071_expression->lhs_module = 'Bugs';
            $this->bug46071_expression->operator = 'Equals';
            $this->bug46071_expression->exp_type = 'relate';
            $this->bug46071_expression->lhs_type = 'base_module';
            $this->bug46071_expression->lhs_field = 'assigned_user_id';
            $this->bug46071_expression->parent_type = 'expression';
            $this->bug46071_expression->rhs_value = $GLOBALS['current_user']->id;

            $this->bug46071_expression->save();
        }

        return array($this->bug46071_expression);
    }
}


?>