<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
require_once ("modules/ModuleBuilder/Module/StudioModuleFactory.php");

class ViewDepDropdown extends SugarView
{
    protected $vars = array("editModule", "field", "parentList", "list");

    function display ()
    {
        $this->ss = new Sugar_Smarty();
        foreach($this->vars as $var)
        {
            if(isset($_REQUEST[$var])) {
                $this->$var = $_REQUEST[$var];
                $this->ss->assign($var, $_REQUEST[$var]);
            }
        }

        $mapping = empty($_REQUEST['mapping']) ? array() : json_decode(html_entity_decode($_REQUEST['mapping']), true);

        $this->ss->assign("mapping", $mapping);

        $sm = StudioModuleFactory::getStudioModule($_REQUEST['targetModule']);
        $fields = $sm->getFields();
        if (!empty($fields[$this->parentList]) && !empty($fields[$this->parentList]['options']))
            $this->parentList = $fields[$this->parentList]['options'];
        $this->ss->assign("parent_list_options", translate($this->parentList));
        $parentOptions = translate($this->parentList);
        $parentOptionsArray = array();
        if(!is_array($parentOptions)) {
            $parentOptions = array();
        }
        foreach($parentOptions as $value => $label)
        {
            $parentOptionsArray[] = array("value" => $value, "label" => $label);
        }
        $this->ss->assign("parentOptions",  json_encode(translate($this->parentList)));
        $child = !empty($_REQUEST["childList"]) ? $_REQUEST["childList"] : "industry_dom";
        $this->ss->assign("child_list_options",  translate($child));
        $childOptions = translate("industry_dom");
        $childOptionsArray = array();
        foreach($childOptions as $value => $label)
        {
            $childOptionsArray[] = array("value" => $value, "label" => $label);
        }
        $this->ss->assign("childOptions",  json_encode($childOptionsArray));
        $this->ss->display("modules/ModuleBuilder/tpls/depdropdown.tpl");
    }


}
