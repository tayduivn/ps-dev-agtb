<?php
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
class ViewSidequickcreate extends SugarView{
	
	function ViewSidequickcreate(){
		parent::SugarView();
	}
	
	function preDisplay(){
		$this->ev = new EditView();
		$this->ev->ss =& $this->ss;
		$this->ev->populateBean = false;
		$this->ev->view = 'SideQuickCreate';
		$file = 'modules/'. $this->module.'/metadata/sidecreateviewdefs.php';
		if(file_exists('custom/'.$file)) {
			$this->ev->setup($this->module, null, 'custom/'.$file);
		} else if(file_exists($file)) {
			$this->ev->setup($this->module, null, $file);
		} else {
			return false;
		}
				
		return true;
	}
	
	function display(){
		$addedTeamField = $this->insertTeamField();
		
		$this->ev->process();
		
		if($addedTeamField) {
		   $this->ev->fieldDefs['team_id']['value'] = $GLOBALS['current_user']->team_id;
		   $this->ev->fieldDefs['team_set_id']['value'] = $GLOBALS['current_user']->team_set_id;
		}
		
		$contents = $this->getLeftFormHeader(translate('LBL_NEW_FORM_TITLE', $this->module))  . $this->ev->display(false, true) . '</span>';
		return $contents;
	}
    
    /**
     * Create HTML to display formatted form title of a form in the left pane
     *
     * @param  $title string to display as the title in the header
     * @return string HTML
     */
    function getLeftFormHeader(
        $title
        )
    {
        return <<<EOHTML
<h3><span>{$title}</span></h3>
<span id="newRecordForm">
EOHTML;
    }
    
    /**
     * Create HTML to display formatted form footer of form in the left pane.
     *
     * @return string HTML
     */
    function getLeftFormFooter() 
    {
        return "</td></tr></table>\n";
    }
    
	private function insertTeamField() {
		if(!empty($this->ev->defs) && !empty($this->ev->defs['panels']) && isset($GLOBALS['current_user'])) {
		   foreach($this->ev->defs['panels'] as $panel_id=>$panel) {
		   	  foreach($panel as $row=>$col) {
		   	  	 foreach($col as $id=>$entry) {
		   	  	 	$name = is_array($entry) ? $entry['name'] : $entry;
		   	  	 	if($name == 'team_name') {
		   	  	 	   return false;
		   	  	 	}
		   	  	 }
		   	  }
		   }

		   //Went through the panel defintions and no team_name field was found, so lets add it if
		   //the file is not already built
		   if(!file_exists("cache/modules/{$this->module}/form_SideQuickCreate_{$this->module}.tpl") || isset($GLOBALS['sugar_config']['developerMode'])) {
		   	   if(empty($this->ev->defs['templateMeta']['form']['hidden'])) {
			   	  $this->ev->defs['templateMeta']['form']['hidden'] = array();
			   }
			   
			   $this->ev->defs['templateMeta']['form']['hidden'][] = "<input type='hidden' name='team_id' value='{\$fields.team_id.value}'>";
			   $this->ev->defs['templateMeta']['form']['hidden'][] = "<input type='hidden' name='team_set_id' value='{\$fields.team_set_id.value}'>";
		   }
		   return true;
		}
		return false;
	}
}

?>