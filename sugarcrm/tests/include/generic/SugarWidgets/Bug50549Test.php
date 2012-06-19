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

require_once("include/generic/SugarWidgets/SugarWidgetFieldmultienum.php");
require_once("modules/Reports/Report.php");


class Bug50549Test extends Sugar_PHPUnit_Framework_TestCase {

    var $field;

    public function setUp() {
        global $beanList, $beanFiles;
        require('include/modules.php');
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['sugar_config']['default_language']);

        $this->field = new SugarWidgetFieldMultiEnum(new Bug50549MockReporter());
    }

    public function tearDown() {
        unset($GLOBALS['app_list_strings']);
        unset($GLOBALS['beanList']);
        unset($GLOBALS['beanFiles']);
    }

    /**
     * @dataProvider bug50549DataProvider
     */
    public function testQuery($layout_def, $expected) {
		switch ($layout_def['qualifier_name']) {
	    	case 'is_not':
	    		$function = "queryFilteris_not";
	    		break;
	    	case 'is': 
	    		$function = "queryFilteris";
	    		break;
	    	default:
	    		$function = "";
	    		break;
        }
        $this->assertContains($expected, $this->field->$function($layout_def), "Filter: '" . strtoupper($layout_def['qualifier_name']) . "' not adding carets.");
    }

    /**
     * Data provider for testColumnLabelsAreCorrectForMatrixReport()
     * @return array layout_def, expected
     */
    public function bug50549DataProvider() {
        return array(
            '0' => array( 
                array (
  					'name' => 'multi_c',
  					'table_key' => 'self',
			  		'qualifier_name' => 'is_not',
			  		'runtime' => 1,
			  		'input_name0' => 
			  		array (
			    		0 => 'B',
			  		),
			  		'column_name' => 'self:multi_c',
			  		'id' => 'rowid0',
			  		'table_alias' => 'cases_cstm',
			  		'column_key' => 'self:multi_c',
			  		'type' => 'multienum',
				),
                "<> '^B^'",
            ),
            '1' => array( 
                array (
  					'name' => 'multi_c',
  					'table_key' => 'self',
			  		'qualifier_name' => 'is',
			  		'runtime' => 1,
			  		'input_name0' => 
			  		array (
			    		0 => 'C',
			  		),
			  		'column_name' => 'self:multi_c',
			  		'id' => 'rowid0',
			  		'table_alias' => 'cases_cstm',
			  		'column_key' => 'self:multi_c',
			  		'type' => 'multienum',
				),
                "= '^C^'",
            ),
            '2' => array( 
                array (
  					'name' => 'multi_c2',
  					'table_key' => 'self',
			  		'qualifier_name' => 'is_not',
			  		'runtime' => 1,
			  		'input_name0' => 
			  		array (
			    		0 => 'B',
			  		),
			  		'column_name' => 'self:multi_c2',
			  		'id' => 'rowid0',
			  		'table_alias' => 'cases_cstm',
			  		'column_key' => 'self:multi_c2',
			  		'type' => 'multienum',
				),
                "<> 'B'",
            ),
            '3' => array( 
                array (
  					'name' => 'multi_c2',
  					'table_key' => 'self',
			  		'qualifier_name' => 'is',
			  		'runtime' => 1,
			  		'input_name0' => 
			  		array (
			    		0 => 'C',
			  		),
			  		'column_name' => 'self:multi_c2',
			  		'id' => 'rowid0',
			  		'table_alias' => 'cases_cstm',
			  		'column_key' => 'self:multi_c2',
			  		'type' => 'multienum',
				),
                "= 'C'",
            ),
        );
    }
}


class Bug50549MockReporter extends Report {

	var $all_fields = array (
		'self:multi_c' => array (
	  		'dependency' => '',
	  		'required' => false,
	  		'source' => 'custom_fields',
	  		'name' => 'multi_c',
	  		'vname' => 'LBL_MULTI',
	  		'type' => 'multienum',
	  		'massupdate' => '0',
	  		'default' => '^A^',
	  		'comments' => '',
	  		'help' => '',
	  		'importable' => 'true',
	  		'duplicate_merge' => 'disabled',
	  		'duplicate_merge_dom_value' => '0',
	  		'audited' => false,
	  		'reportable' => true,
	  		'unified_search' => false,
	  		'calculated' => false,
	  		'size' => '20',
	  		'options' => 'test_list',
	  		'studio' => 'visible',
	  		'isMultiSelect' => true,
			'id' => 'Casesmulti_c',
	  		'custom_module' => 'Cases',
	  		'module' => 'Cases',
	  		'real_table' => 'cases_cstm',
		),
		'self:multi_c2' => array (
	  		'dependency' => '',
	  		'required' => false,
	  		'name' => 'multi_c',
	  		'vname' => 'LBL_MULTI',
	  		'type' => 'multienum',
	  		'massupdate' => '0',
	  		'default' => '^A^',
	  		'comments' => '',
	  		'help' => '',
	  		'importable' => 'true',
	  		'duplicate_merge' => 'disabled',
	  		'duplicate_merge_dom_value' => '0',
	  		'audited' => false,
	  		'reportable' => true,
	  		'unified_search' => false,
	  		'calculated' => false,
	  		'size' => '20',
	  		'options' => 'test_list',
	  		'studio' => 'visible',
	  		'isMultiSelect' => true,
			'id' => 'Casesmulti_c',
	  		'custom_module' => 'Cases',
	  		'module' => 'Cases',
	  		'real_table' => 'cases_cstm',
		)
	);
	 
	public function getAttribute($name)
    {
        return $this;
    }
}


?>