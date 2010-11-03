<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * SugarWidgetSubPanelTopSelectButton
 *
 * The contents of this file are subject to the SugarCRM Professional Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-professional-eula.html
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
 * by SugarCRM are Copyright (C) 2004-2007 SugarCRM, Inc.; All Rights Reserved.
 */



require_once('include/generic/SugarWidgets/SugarWidgetSubPanelTopButton.php');

class SugarWidgetSubPanelTopViewInteractionsButton extends SugarWidgetSubPanelTopButton
{
	//button_properties is a collection of properties associated with the widget_class definition. layoutmanager
	function SugarWidgetSubPanelTopViewInteractionsButton($button_properties=array())
	{
        $this->button_properties=$button_properties;
	}

	//widget_data is the collection of attributes assoicated with the button in the layout_defs file.
	function display(&$widget_data)
	{
		global $app_strings;
		$initial_filter = '';

		$this->title = $app_strings['LBL_VIEW_INTERACTIONS_BUTTON_TITLE'];
		$this->accesskey = $app_strings['LBL_VIEW_INTERACTIONS_BUTTON_KEY'];
		$this->value = $app_strings['LBL_VIEW_INTERACTIONS_BUTTON_LABEL'];

		if (is_array($this->button_properties)) {
			if( isset($this->button_properties['title'])) {
				$this->title = $app_strings[$this->button_properties['title']];
			}
			if( isset($this->button_properties['accesskey'])) {
				$this->accesskey = $app_strings[$this->button_properties['accesskey']];
			}
			if( isset($this->button_properties['form_value'])) {
				$this->value = $app_strings[$this->button_properties['form_value']];
			}
			if( isset($this->button_properties['module'])) {
				$this->module_name = $this->button_properties['module'];
			}
		}

		$subpanel_definition = $widget_data['subpanel_definition'];
		$button_definition = $subpanel_definition->get_buttons();

        $subpanel_name = $subpanel_definition->get_name();
		if (empty($this->module_name)) {
			$this->module_name = $subpanel_definition->get_module_name();
		}

		$return_module = $_REQUEST['module'];
		$return_id = $_REQUEST['record'];

		return '<form action="index.php">' . "\n"
			. ' <input type="button" name="view_interactions_button" id="view_interactions_button" class="button"' . "\n"
				. ' title="' . $this->title . '"'
			. ' accesskey="' . $this->accesskey . '"'
			. ' value="' . $this->value . "\"\n"
			. " onclick='window.open(\"index.php?to_pdf=1&module={$this->module_name}&action=relatedinteractions&related_module=$return_module&related_id=$return_id&parent_type=$return_module&parent_id=$return_id\","
            . "\"view_interactions\",\"width=600,height=400,resizable=1,scrollbars=1\");' /></form>\n";
	}
}
?>
