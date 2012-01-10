<?php
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

require_once("data/BeanFactory.php");
require_once('modules/ExpressionEngine/FormulaHelper.php');

class ViewSelectRelatedField extends SugarView
{
    var $vars = array("tmodule", "selLink");

    function __construct()
    {
        parent::__construct();
        foreach($this->vars as $var)
        {
            if (!isset($_REQUEST[$var]))
                sugar_die("Required paramter $var not set in ViewRelFields");
            $this->$var = $_REQUEST[$var];
        }

    }

    function display() {
        $links = array();
        $rfields = array();

        //First, create a dummy bean to access the relationship info
        $focus = BeanFactory::newBean($this->tmodule);
        $focus->id = create_guid();

        $fields = FormulaHelper::cleanFields($focus->field_defs);
        //echo "<pre>" . print_r($fields, true) . "</pre>";

        //Next, get a list of all links and the related modules
        foreach($fields as $val)
        {
            $name = $val[0];
            $def = $focus->field_defs[$name];
            if ($val[1] == "relate" && $focus->load_relationship($name))
            {
                $relatedModule = $focus->$name->getRelatedModuleName();
                $label = empty($def['vname']) ? $name : translate($def['vname'], $this->tmodule);
                $links[$name] = "$relatedModule ($label)";
            }
        }

        //Preload the related fields from the first relationship
        if (!empty($links))
        {
            $link = isset($links[$this->selLink]) ? $this->selLink: key($links);
            $relatedModule = $focus->$link->getRelatedModuleName();
            $relatedBean = BeanFactory::getBean($relatedModule);
            $relatedFields = FormulaHelper::cleanFields($relatedBean->field_defs, false, true);
            foreach($relatedFields as $val)
            {
                $name = $val[0];
                $def = $relatedBean->field_defs[$name];
                $rfields[$name] = empty($def['vname']) ? $name : translate($def['vname'], $relatedModule);
                //Strip the ":" from any labels that have one
                if (substr($rfields[$name], -1) == ":")
                    $rfields[$name] = substr($rfields[$name], 0, strlen($rfields[$name]) -1);

            }
        }

        $this->ss->assign("rmodules", $links);
        $this->ss->assign("rfields", $rfields);
        $this->ss->assign("tmodule", $this->tmodule);
        $this->ss->assign("selLink", $this->selLink);
        $this->ss->assign("rollup_types", array(
            "rollupSum" => "Sum",
            "rollupMin" => "Minimum",
            "rollupMax" => "Maximum",
            "rollupAverage" => "Average",
        ));
        $this->ss->display('modules/ExpressionEngine/tpls/selectRelatedField.tpl');
    }
}