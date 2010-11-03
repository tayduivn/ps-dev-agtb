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

 * Description: This file is used to override the default Meta-data EditView behavior
 * to provide customization specific to the Calls module.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('include/MVC/View/views/view.detail.php');

class TouchpointsViewDetail extends ViewDetail 
{
 	public function __construct()
    {
 		parent::ViewDetail();
 	}
    
    public function preDisplay()
    {
        if ( isset($_REQUEST['offset']) && (!isset($this->bean->scrubbed) || !$this->bean->scrubbed) )
            SugarApplication::redirect("index.php?module=Touchpoints&action=ScrubView&record={$_REQUEST['record']}&return_action=index");
        
        parent::preDisplay();
    }
 	
 	public function display()
    {
        global $current_user;
        
        if ( is_admin($current_user) || is_admin_for_module($current_user,'Leads') || $current_user->check_role_membership('Leads Admin Role')){
            if( $this->bean->scrubbed != 0 )
				$this->ss->assign('SHOW_RESCRUB',true);
			else
				$this->ss->assign('SHOW_RESCRUB',false);
		}
        else
        	$this->ss->assign('SHOW_RESCRUB',false);

		if( $this->bean->scrubbed == 0 )
			$this->ss->assign('SHOW_SCRUB',true);
		else
			$this->ss->assign('SHOW_SCRUB',false);

 		$this->ss->assign('RAW_DATA_ARRAY', $this->bean->raw_data_array);
        
 		parent::display();
 	}
}
?>
