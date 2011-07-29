<?php
//FILE SUGARCRM flav=pro ONLY
require_once 'modules/Expressions/Expression.php';

/**
 * $group bug38903
 */
class Bug38903Test extends Sugar_PHPUnit_Framework_TestCase
{
	public function setUp()
	{
	    require('include/modules.php');
	    $GLOBALS['beanList'] = $beanList;
	    $GLOBALS['beanFiles'] = $beanFiles;		
	}	
	
    public function testAccountNameExists()
    {
        //Reset moduleList, beanList and beanFiles
        global $beanList, $beanFiles, $moduleList;
        require('include/modules.php');
        
        $bean = new Expression();

        // just to remove php notice
        $_GET['opener_id'] = null;
        // wf condition: when a field in the target module changes to or from a specified value
        // module: Leads
        $options = strtolower($bean->get_selector_array(
            'field', null, 'Leads', false, 'normal_trigger', true, 'compare_specific', false));

        $this->assertRegExp('#<option value=\'account_name\'>account name</option>#', $options);
    }
}