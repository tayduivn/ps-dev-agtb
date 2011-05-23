<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement 
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.  
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may 
 *not use this file except in compliance with the License. Under the terms of the license, You 
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or 
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or 
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit 
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the 
 *Software without first paying applicable fees is strictly prohibited.  You do not have the 
 *right to remove SugarCRM copyrights from the source code or user interface. 
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and 
 * (ii) the SugarCRM copyright notice 
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer 
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.  
 ********************************************************************************/
/*********************************************************************************
 * $Id$
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

$viewdefs = array (
  'Opportunities' => 
  array (
    'QuickCreate' => 
    array (
      'templateMeta' => 
      array (
        'maxColumns' => '2',
        'widths' => 
        array (
          0 => 
          array (
            'label' => '10',
            'field' => '30',
          ),
          1 => 
          array (
            'label' => '10',
            'field' => '30',
          ),
        ),
        'javascript' => '{$PROBABILITY_SCRIPT}',
		// BEGIN sadek - THESE HIDDEN FIELDS ARE NECESSARY TO MAKE SURE THAT THE "Primary Contact" IS FILLED IN ON THE OPP
		'form' => array(
        'hidden' =>
			array (
			  0 => '<input type="hidden" name="contact_role" value="Primary Decision Maker">',
			  1 => '<input type="hidden" name="contact_id_c" value="{$smarty.request.return_id}">',
			),
		),
		// END sadek - THESE HIDDEN FIELDS ARE NECESSARY TO MAKE SURE THAT THE "Primary Contact" IS FILLED IN ON THE OPP
      ),
      'panels' => 
      array (
        'DEFAULT' => 
        array (
          array (
            array (
              'name' => 'amount',
              'displayParams'=>array('required'=>true),
            ),
            array (
              'name' => 'account_name',
            ),
          ),
          array (
            array (
              'name' => 'currency_id',
            ),
            array (
              'name' => 'opportunity_type',
            ),            
          ),
          array (
			'',
            array (
              'name' => 'date_closed',
              'displayParams'=>array('required'=>true),
            ),            
          ),
          array (
             'next_step',
             'sales_stage',
          ),
          array (
             'lead_source',
             'probability',
          ),
        array (
            array (
              'name' => 'assigned_user_name',
            ),
            //BEGIN SUGARCRM flav=pro ONLY
            array (
              'name' => 'team_name',
            ),
            //END SUGARCRM flav=pro ONLY
        ),
        ),
      ),
    ),
  ),
);
?>
