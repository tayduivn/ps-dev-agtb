<?php 
require_once('include/SugarFields/Fields/Phone/SugarFieldPhone.php');

class Bug43942Test extends Sugar_PHPUnit_Framework_TestCase
{
	public function test_phone_without_formatting()
	{
	    $sugarField = new SugarFieldPhone('Phone');
	    $bean = new Contact();
	    
	    $params = array('phone_work'=>'+1(800)999-2222');
	    $field = 'phone_work';
	    $properties = array('name' => 'phone_work', 
	                        'vname' => 'LBL_OFFICE_PHONE', 
	                        'type' => 'phone',
						    'dbType' => 'varchar',
						    'len' => 100,
						    'audited'=> 1,
						    'unified_search'=> 1,
						    'comment' => 'Work phone number of the contact',
						    'merge_filter' => 'enabled',
						    'calculated' => '',
	    			  );
	    
	    $sugarField->save($bean, $params, $field, $properties);
	    $this->assertEquals($bean->phone_work, '+1(800)999-2222', "Assert that '+1(800)999-2222' is not formatted");
	    
	    $params = array('phone_work'=>'');
	    $sugarField->save($bean, $params, $field, $properties);
	    $this->assertEquals($bean->phone_work, '', "Assert that saving empty value works as expected");
	}	
	
}

?>