<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

//FILE SUGARCRM flav=pro ONLY

require_once 'include/MVC/View/views/view.edit.php';

class PdfManagerViewEdit extends ViewEdit
{
    public function PdfManagerViewEdit()
    {
        parent::ViewEdit();
    }

    public function display()
    {
    
        // Disable VCR Control
        $this->ev->showVCRControl = false;

        // Default Team as Global
        if ((empty($this->bean->id))  && !$this->ev->isDuplicate) {
            $this->bean->team_id = 1;
            $this->bean->team_set_id = 1;
        }
    
        // Load TinyMCE
        require_once 'include/SugarTinyMCE.php';
        $tiny = new SugarTinyMCE();
        $tiny->defaultConfig['apply_source_formatting']=true;
        $tiny->defaultConfig['cleanup_on_startup']=true;
        $tiny->defaultConfig['relative_urls']=false;
        $tiny->defaultConfig['convert_urls']=false;
        $ed = $tiny->getInstance('body_html');
        $this->ss->assign('tiny_script', $ed);

        // Load Fields for main module
        if (empty($this->bean->base_module)) {
            $modulesList = PdfManagerHelper::getAvailableModules();
            $this->bean->base_module = key($modulesList);
        }
        $fieldsForSelectedModule = PdfManagerHelper::getFields($this->bean->base_module, true);

        $this->ss->assign('fieldsForSelectedModule', $fieldsForSelectedModule);

        parent::display();
    }
}
