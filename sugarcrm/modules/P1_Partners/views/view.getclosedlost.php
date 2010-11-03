<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
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
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************

 * Description:  TODO To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('include/MVC/View/SugarView.php');

class P1_PartnersViewGetClosedLost extends SugarView {

	public function __construct()
   	{
  		parent::SugarView();
   	}

    	function process() {
        	$this->display();
    	}

    	function display(){
		global $mod_strings, $app_strings, $app_list_strings;
		$this->ss->assign('MOD_OPPORTUNITIES', return_module_language($GLOBALS['current_language'],'Opportunities'));
		$opp = new Opportunity();
		/**** DROP DOWN DEFS ****/
                $oppfieldDefs = $opp->field_defs;

                foreach ($oppfieldDefs as $key => $values ) {
                        if(isset($oppfieldDefs[$key]['options']) && isset($app_list_strings[$oppfieldDefs[$key]['options']])){
                                unset($app_list_strings['sales_stage_dom']['Finance Closed']);
                                unset($app_list_strings['sales_stage_dom']['Sales Ops Closed']);
                                $oppfieldDefs[$key]['options'] = $app_list_strings[$oppfieldDefs[$key]['options']];
                        }
                        if ( isset($opp->$key) )
                                $oppfieldDefs[$key]['value'] = $opp->$key;
                }
                $this->ss->assign('opp_fields', $oppfieldDefs);

		$this->ss->display('modules/P1_Partners/tpls/getclosedlost.tpl');
	}
}
?>
