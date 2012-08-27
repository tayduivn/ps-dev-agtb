<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
class QuotesSeedDataTest extends Sugar_PHPUnit_Framework_TestCase
{
	protected $quote_name;
	
	public function setUp()
	{
	    $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
	    SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
		global $sugar_demodata;
		$sugar_demodata['company_name_array'] = array();
		$query = 'SELECT * FROM accounts';
		$results = $GLOBALS['db']->limitQuery($query,0,10,true,"Error retrieving Accounts");
        while($row = $GLOBALS['db']->fetchByAssoc($results)) {
        	$sugar_demodata['company_name_array'][] = $row['name'];
        }
        
        $this->quote_name = 'Test Quote ' . mktime();
        
		$sugar_demodata['quotes_seed_data']['quotes'][0] = array(
			'name' => $this->quote_name,
			'quote_stage' => 'Draft',
			'date_quote_expected_closed' => '04/30/2012',
		    'description' => 'This is a test that should contain one product group with two products and a total of three items',
		         
		
		    'bundle_data' => array(
				0 => array (
				    'bundle_name' => 'Group 1',
				    'bundle_stage' => 'Draft',
				    'comment' => 'Three Computers',
				    'products' => array (
						1 => array('name'=>'TK 1000 Desktop', 'quantity'=>'1'),
						2 => array('name'=>'TK m30 Desktop', 'quantity'=>'2'),
					),
				),
			),
		);
	}

	public function tearDown() 
	{
		$sql = "SELECT * FROM quotes WHERE name = '{$this->quote_name}'";
		$results = $GLOBALS['db']->query($sql);
		$quote_id = '';
		
        while($row = $GLOBALS['db']->fetchByAssoc($results)) {
        	  $quote_id = $row['id'];
        }		
        
        $sql = "DELETE FROM quotes WHERE id = '{$quote_id}'";
        $GLOBALS['db']->query($sql);

        $sql = "DELETE FROM products WHERE quote_id = '{$quote_id}'";
        $GLOBALS['db']->query($sql);           
        
        $bundle_id = '';
        $sql = "SELECT bundle_id FROM product_bundle_quote WHERE quote_id = '{$quote_id}'";

        $results = $GLOBALS['db']->query($sql);
        while($row = $GLOBALS['db']->fetchByAssoc($results)) {
        	  $bundle_id = $row['bundle_id'];
        	  
        	  $sql = "DELETE FROM product_bundle_product WHERE bundle_id = '{$bundle_id}'";
        	  $GLOBALS['db']->query($sql);
        	  
        	  $sql = "DELETE FROM product_bundle_quote WHERE bundle_id = '{$bundle_id}'";
        	  $GLOBALS['db']->query($sql);
        }	        
        
        if(!empty($bundle_id)) {
        	$sql = "SELECT note_id FROM product_bundle_note WHERE bundle_id = '{$bundle_id}'";
	        $results = $GLOBALS['db']->query($sql);
	        while($row = $GLOBALS['db']->fetchByAssoc($results)) {  
	        	$note_id = $row['note_id'];
	        	
	        	$sql = "DELETE FROM product_bundle_notes WHERE id = '{$note_id}'";
        	    $GLOBALS['db']->query($sql);
	        }      	
	        
	        $sql = "DELETE FROM product_bundle_note WHERE bundle_id = '{$bundle_id}'";
	        $GLOBALS['db']->query($sql);
        }
        
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
	}
	
	public function testCreateSeedQuotes() 
	{
        require_once('install/seed_data/quotes_SeedData.php');
		$sql = "SELECT * FROM quotes WHERE name = '{$this->quote_name}'";
		$results = $GLOBALS['db']->query($sql); 
		$quote_created = false;   
        while($row = $GLOBALS['db']->fetchByAssoc($results)) {
        	  $quote_created = true;
        }	
        
        $this->assertTrue($quote_created);
	}
}
?>