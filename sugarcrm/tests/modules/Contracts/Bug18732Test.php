<?php 
//FILE SUGARCRM flav=pro ONLY
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