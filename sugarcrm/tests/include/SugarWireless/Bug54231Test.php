<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/en/msa/master_subscription_agreement_11_April_2011.pdf
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

/**
 * Bug #54231
 * Check if ACL Roles are respected in the wireless edit view
 *
 * @author avucinic@sugarcrm.com
 * @ticket 54231
 */
class Bug54231Test extends Sugar_PHPUnit_Framework_TestCase
{
	var $_view_object_map;
	
	public function setUp()
	{
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('current_user');

		$GLOBALS['beanFiles'] = array(
			'Opportunity' => 'modules/Opportunities/Opportunity.php'
		);

		$GLOBALS['beanList'] = array(
			'Opportunities' => 'Opportunity'
		);

		$GLOBALS['app_list_strings']['moduleList'] = array(
			'Opportunities' => 'Opportunities'
		);
		
		$this->_view_object_map = array(
			'wireless_module_registry' => array(
				'Opportunities' => array(
					'disable_create' => true
				),
			),
  		'remap_action' => 'wirelessedit',
		);
	}

	public function tearDown()
	{
        SugarTestHelper::tearDown();
	}


	/**
	 * Create a ViewWirelessedit, load some mock field defs, and check if ACL is respected
	 *
	 * @dataProvider bug54231DataProvider
	 */
	public function testACLRoles($fieldDefs, $detail)
	{
		// Init the ViewWirelessedit
		$editView = new ViewWirelessedit();
		$editView->init(null, $this->_view_object_map);
		$editView->module = "Opportunities";

		// Set fields for wirelessedit.tpl
		$editView->ss->assign('fields', $fieldDefs);
		$editView->ss->assign('DETAILS', $detail);

		ob_start();
		$editView->ss->display('include/SugarWireless/tpls/wirelessedit.tpl');
		$view = ob_get_contents();
		ob_end_clean();

		foreach ($fieldDefs as $name => $value) {
			if ($value['acl'] > 1) {
				$this->assertContains("value='" . $value['value'] . "'", $view);
			} else {
				$this->assertNotContains("value='" . $value['value'] . "'", $view);
			}
		}
	}
	
	
    /**
	
     * Data provider for testACLRoles()
	
     * @return array fieldDefs, array detail
	
     */
    public function bug54231DataProvider() {
        return array(
        	0 => array(
				// fieldDef array
				array(
					'amount' => array(
  						'name' => 'amount',
  						'vname' => 'LBL_AMOUNT',
  						'type' => 'currency',
  						'dbType' => 'double',
  						'comment' => 'Unconverted amount of the opportunity',
  						'importable' => 'required',
  						'duplicate_merge' => '1',
  						'required' => true,
  						'options' => 
	  						array(
  								'=' => 'Equals',
  								'not_equal' => 'Does Not Equal',
  								'greater_than' => 'Greater Than',
  								'greater_than_equals' => 'Greater Than Or Equal To',
  								'less_than' => 'Less Than',
  								'less_than_equals' => 'Less Than Or Equal To',
  								'between' => 'Is Between',
  							),
  						'enable_range_search' => true,
  						'value' => 'TestAmount',
  						'acl' => 1,
  					),
  					'name' => array(
						'name' => 'name',
						'vname' => 'LBL_OPPORTUNITY_NAME',
						'type' => 'name',
						'dbType' => 'varchar',
						'len' => '50',
						'unified_search' => true,
						'full_text_search' => 
							array(
								'boost' => 3,
							),
						'comment' => 'Name of the opportunity',
						'merge_filter' => 'selected',
						'importable' => 'required',
						'required' => true,
						'value' => 'TestName',
						'acl' => 4,
					)
				),
				// detail array
				array(
					'amount' => array(
    					'id' => '76aeb087-ec27-423f-057f-500ea309721f',
    					'field' => 'amount',
    					'required' => false,
    					'detail_only' => false,
    					'displayParams' => 
    						array(
    							'readOnly' => false,
    						),
    					'label' => 'Opportunity Amount:',
    					'value' => 'TestAmount',
    					'vardef' => 
	    					array(
		    					'name' => 'amount',
		    					'vname' => 'LBL_AMOUNT',
		    					'type' => 'currency',
		    					'dbType' => 'double',
		    					'comment' => 'Unconverted amount of the opportunity',
		    					'importable' => 'required',
		    					'duplicate_merge' => '1',
		    					'required' => true,
		    					'options' => 'numeric_range_search_dom',
		    					'enable_range_search' => true,
	    					),
    					'type' => 'currency',
    					'readOnly' => false,
    					'customCode' => NULL,
    				),
	    			'name' => array(
	  					'id' => '76aeb087-ec27-423f-057f-500ea309721f',
	  					'field' => 'name',
	  					'required' => true,
	  					'detail_only' => false,
	  					'displayParams' => 
	  						array(
	  							'required' => true,
	  							'wireless_edit_only' => true,
	  							'readOnly' => false,
	  						),
	  					'label' => 'Opportunity Name:',
	  					'value' => 'TestName',
	  					'vardef' => 
	  						array(
	  							'name' => 'name',
	  							'vname' => 'LBL_OPPORTUNITY_NAME',
	  							'type' => 'name',
	  							'dbType' => 'varchar',
	  							'len' => '50',
	  							'unified_search' => true,
	  							'full_text_search' => 
	  								array(
	  									'boost' => 3,
	  								),
	  							'comment' => 'Name of the opportunity',
	  							'merge_filter' => 'selected',
	  							'importable' => 'required',
	  							'required' => true,
	  					),
	  					'type' => 'name',
	  					'readOnly' => false,
	  					'customCode' => NULL,
					),
				),
  			),
        );
    }
}
