<?php
//FILE SUGARCRM flav=pro ONLY
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

/**
 * Bug #54231
 * Check if ACL Roles are respected in the wireless edit view
 *
 * @author avucinic@sugarcrm.com
 * @ticket 54231
 */
class Bug54231Test extends Sugar_PHPUnit_Framework_OutputTestCase
{
	var $_beanFiles;
	var $_beanList;
	var $_app_list_strings_moduleList;
	var $_view_object_map;
	
	public function setUp()
	{
        SugarTestHelper::setUp('moduleList');
		global $current_user, $beanFiles, $beanList;
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('current_user');
		
		$this->_beanFiles = $GLOBALS['beanFiles'];
		$GLOBALS['beanFiles'] = array(
			'Opportunity' => 'modules/Opportunities/Opportunity.php'
		);
		
		$this->_beanList = $GLOBALS['beanList'];
		$GLOBALS['beanList'] = array(
			'Opportunities' => 'Opportunity'
		);

        if (isset($GLOBALS['app_list_strings']) && isset($GLOBALS['app_list_strings']['moduleList'])) {
            $this->_app_list_strings_moduleList = $GLOBALS['app_list_strings']['moduleList'];
        }
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
		$GLOBALS['beanFiles'] = $this->_beanFiles;
		$GLOBALS['beanList'] = $this->_beanList;
		$GLOBALS['app_list_strings']['moduleList'] = $this->_app_list_strings_moduleList;
	}


	/**
	 * Create a ViewWirelessedit, load some mock field defs, and check if ACL is respected
	 *
	 * @dataProvider bug54231DataProvider
	 */
	public function testACLRoles($fieldDefs, $detail)
	{
        $this->markTestIncomplete('Needs to be fixed by FRM team.');
		// Init the ViewWirelessedit
		$editView = new ViewWirelessedit();
		$editView->init(null, $this->_view_object_map);
		$editView->module = "Opportunities";

		// Set fields for wirelessedit.tpl
		$editView->ss->assign('fields', $fieldDefs);
		$editView->ss->assign('DETAILS', $detail);
		$editView->ss->display('include/SugarWireless/tpls/wirelessedit.tpl');

		foreach ($fieldDefs as $name => $value) {
			if ($value['acl'] > 1) {
                $this->expectOutputRegex("/value=\'" . $value['value'] . "\'/");
			} else {
                $this->expectOutputNotRegex("/value=\'" . $value['value'] . "\'/");
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
								'enabled' => true, 'boost' => 3,
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
	  									'enabled' => true, 'boost' => 3,
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
