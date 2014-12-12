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
class Bug18732Test extends Sugar_PHPUnit_Framework_TestCase
{
	public function testCurrencyLabel()
	{
		global $current_language;
		$_mod_strings = array();
        $this->_mod_strings = return_module_language($current_language, 'Contracts');
    	$dictionary = array();
    	require('modules/Contracts/vardefs.php');
    	// make sure the Contract vardef vname reference for currency name is correct
    	$label_name = $dictionary['Contract']['fields']['currency_name']['vname'];
    	$this->assertEquals($this->_mod_strings[$label_name], $this->_mod_strings['LBL_CURRENCY']);
    }	
}
