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
 * to provide customization specific to the Contacts module.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('include/json_config.php');
require_once('include/MVC/View/views/view.edit.php');

class ITRequestsViewEdit extends ViewEdit {

    function ITRequestsViewEdit(){
        parent::ViewEdit();
    }

    /**
     * display
     *
     * We are overridding the display method to manipulate the sectionPanels.
     * If portal is not enabled then don't show the Portal Information panel.
     */
    function display() {
        if(is_null($this->bean->id)) {
            $this->bean->assigned_user_id = null;

            $this->ev->ss->assign("assignmessage", "<div style='border: 1px solid #4F8A10; background-color: #DFF2BF; padding: 5px 5px;'><h2>ITR Assignment:</h2>\n".
                "<strong>IT_User:</strong> internal issues with <strong>email</strong>, laptops, mice, monitors, phones, etc<br />\n".
                "<strong>internalsystems:</strong> sugarinternal related bugs and feature requests<br />\n".
                "<strong>operations:</strong> On Demand, Eval, Server Hardware and Data Center related issues</div><br />\n");
        }
        $this->ev->process();

        echo $this->ev->display();
    }

}

?>
