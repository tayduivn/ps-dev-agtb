<?php 
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
 
require_once('include/SugarFields/Fields/Relate/SugarFieldRelate.php');

class SugarFieldRelateTest extends Sugar_PHPUnit_Framework_TestCase
{
	public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
	}

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }
    
	public function testFormatContactNameWithFirstName()
	{
        $GLOBALS['current_user']->setPreference('default_locale_name_format','l f');
        
	    $vardef = array('name' => 'contact_name');
	    $value = 'John Mertic';
	    
	    $sfr = new SugarFieldRelate('relate');
	    
	    $this->assertEquals(
            $sfr->formatField($value,$vardef),
            'Mertic John'
            );
    }
    
    /**
     * @ticket 35265
     */
    public function testFormatContactNameWithoutFirstName()
	{
        $GLOBALS['current_user']->setPreference('default_locale_name_format','l f');
        
	    $vardef = array('name' => 'contact_name');
	    $value = 'Mertic';
	    
	    $sfr = new SugarFieldRelate('relate');
	    
	    $this->assertEquals(
            trim($sfr->formatField($value,$vardef)),
            'Mertic'
            );
    }
    
    /**
     * @ticket 35265
     */
    public function testFormatContactNameThatIsEmpty()
	{
        $GLOBALS['current_user']->setPreference('default_locale_name_format','l f');
        
	    $vardef = array('name' => 'contact_name');
	    $value = '';
	    
	    $sfr = new SugarFieldRelate('relate');
	    
	    $this->assertEquals(
            trim($sfr->formatField($value,$vardef)),
            ''
            );
    }
    
    public function testFormatOtherField()
	{
        $GLOBALS['current_user']->setPreference('default_locale_name_format','l f');
        
	    $vardef = array('name' => 'account_name');
	    $value = 'John Mertic';
	    
	    $sfr = new SugarFieldRelate('relate');
	    
	    $this->assertEquals(
            $sfr->formatField($value,$vardef),
            'John Mertic'
            );
    }
    
    /**
     * @group bug38548
    */
    public function testGetSearchViewSmarty(){
    	$vardef = array (
			'name' => 'assigned_user_id',
			'rname' => 'user_name',
			'id_name' => 'assigned_user_id',
			'vname' => 'LBL_ASSIGNED_TO_ID',
			'group'=>'assigned_user_name',
			'type' => 'relate',
			'table' => 'users',
			'module' => 'Users',
			'reportable'=>true,
			'isnull' => 'false',
			'dbType' => 'id',
			'audited'=>true,
			'comment' => 'User ID assigned to record',
            'duplicate_merge'=>'disabled'           
		);
		$displayParams = array();
		$sfr = new SugarFieldRelate('relate');
		$output = $sfr->getSearchViewSmarty(array(), $vardef, $displayParams, 0);
		$this->assertContains('name="{$Array.assigned_user_id', $output, 'Testing that the name property is in the form for thr assigned_user_id field');
		
		$vardef =  array (
				    'name' => 'account_name',
				    'rname' => 'name',
				    'id_name' => 'account_id',
				    'vname' => 'LBL_ACCOUNT_NAME',
				    'type' => 'relate',
				    'table' => 'accounts',
				    'join_name'=>'accounts',
				    'isnull' => 'true',
				    'module' => 'Accounts',
				    'dbType' => 'varchar',
				    'link'=>'accounts',
				    'len' => '255',
				   	 'source'=>'non-db',
				   	 'unified_search' => true,
				   	 'required' => true,
				   	 'importable' => 'required',
				     'required' => true,
				  );
		$displayParams = array();
		$sfr = new SugarFieldRelate('relate');
		$output = $sfr->getSearchViewSmarty(array(), $vardef, $displayParams, 0);
		$this->assertNotContains('name="{$Array.account_id', $output, 'Testing that the name property for account_id is not in the form.');
    }
}