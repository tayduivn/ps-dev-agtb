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

 * Description: Controller for the Import module
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 ********************************************************************************/
require_once('modules/Interactions/Interaction.php');
require_once('modules/Accounts/Account.php');
require_once('modules/Contacts/Contact.php');
require_once('modules/LeadAccounts/LeadAccount.php');
require_once('modules/LeadContacts/LeadContact.php');
require_once('include/MVC/View/views/view.popup.php');

class InteractionsViewRelatedInteractions extends ViewPopup 
{	
    /**
     * Constructor
     */
 	public function __construct()
    {
 		parent::SugarView();
    }
    
 	/** 
     * @see SugarView::display()
     */
    public function display()
    {
        global $mod_strings, $theme;
        
        $relatedModule = $_REQUEST['parent_type'];
        $relatedId = $_REQUEST['parent_id'];
        
        insert_popup_header($theme);
        
        $focus = new Interaction;
        
        // build listview to show interaction records
        require_once('include/ListView/ListViewFacade.php');
        $lvf = new ListViewFacade($focus, $focus->module_dir, 0);
    
        $params = array();
        if(!empty($_REQUEST['orderBy'])) {
            $params['orderBy'] = $_REQUEST['orderBy'];
            $params['overrideOrder'] = true;
            if(!empty($_REQUEST['sortOrder'])) $params['sortOrder'] = $_REQUEST['sortOrder'];
        }
        $relatedFocus = loadBean($relatedModule);
        $relatedFocus->retrieve($relatedId);
        $query_parts = $relatedFocus->getInteractionsQuery();
        $params['custom_where'] = str_replace('WHERE','AND',$query_parts['where']);
        
        // Fixes Bug 28788 - Somehow the listview doesn't get display on the first load in a browser session,
        // this forces that to happen
        $GLOBALS['displayListView'] = true;
        
        $lvf->lv->mergeduplicates = false;
        $lvf->setup('', '', $params, $mod_strings, 0, -1, '', strtoupper($focus->object_name), array(), 'id');
        $lvf->display($mod_strings['LBL_MODULE_NAME']);
    }
}
?>
