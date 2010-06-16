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
 * $Id: view.edit.php 
 * Description: This file is used to override the default Meta-data EditView behavior
 * to provide customization specific to the Contacts module.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('include/MVC/View/views/view.list.php');

class DCEInstancesViewDCEUpgradeStep1 extends ViewList 
{
    /**
	 * @see SugarView::preDisplay()
	 */
	public function preDisplay()
	{
        parent::preDisplay();
        $_REQUEST['massupdate']='false';
        $this->lv->showMassupdateFields=false;      
    }
    
    /**
	 * @see SugarView::display()
	 */
	public function display()
	{
        parent::display();
        echo $this->_getNextBtn();
        echo $this->_getJS();
    }
    
    /**
	 * @see ViewList::listViewPrepare()
	 */
	public function listViewPrepare()
	{
        $this->options['show_title']=false;
        echo get_module_title($GLOBALS['mod_strings']['LBL_MODULE_NAME'], $GLOBALS['mod_strings']['LBL_MODULE_NAME']." ".$GLOBALS['mod_strings']['LBL_DCEUPGRADE_STEP_1_TITLE'], false); 
        
        parent::listViewPrepare();
        $this->lv->delete=false;
        $this->lv->export=false;
    }
    
    /**
	 * @see ViewList::prepareSearchForm()
	 */
	public function prepareSearchForm()
	{
        $this->searchForm = null;
        $view = 'dceupgrade_search';
        $this->headers = true;

        $this->use_old_search = false;
        require_once('include/SearchForm/SearchForm2.php');

        if (file_exists('modules/'.$this->module.'/metadata/searchdefs.php'))
        {
            require_once('modules/'.$this->module.'/metadata/searchdefs.php');
        }
        if(file_exists('modules/'.$this->module.'/metadata/SearchFields.php'))
            require_once('modules/'.$this->module.'/metadata/SearchFields.php');
            
        $this->searchForm = new SearchForm($this->seed, $this->module, $this->action);
        $this->searchForm->showCustom=array('dceupgrade_search');
        $this->searchForm->showBasic=false;
        $this->searchForm->showAdvanced=false;
        $this->searchForm->showSavedSearchesOptions=false;
        $this->searchForm->parsedView='dceupgrade_search';
        $this->searchForm->setup($searchdefs, $searchFields, 'include/SearchForm/tpls/SearchFormGeneric.tpl', 'dceupgrade_search', $this->listViewDefs);
        $this->searchForm->lv = $this->lv;
        // set where to have an empty list view when arriving on the page
    }
    
    /**
	 * @see ViewList::processSearchForm()
	 */
	public function processSearchForm()
	{
        $overwriteWhere=true;
        if(isset($_REQUEST['query']))
        {
            // we have a query
            if(!empty($_SERVER['HTTP_REFERER']) && preg_match('/action=EditView/', $_SERVER['HTTP_REFERER'])) { // from EditView cancel
                $this->searchForm->populateFromArray($this->storeQuery->query);
            }
            else {
                $this->searchForm->populateFromRequest();
            } 
            if(isset($this->searchForm->searchFields['upgrade_searchForm'])){
                //don't search this field
                $this->searchForm->searchFields['upgrade_searchForm']=null;
                $overwriteWhere=false;
            }
            $where_clauses = $this->searchForm->generateSearchWhere(true, $this->seed->module_dir);
            if (count($where_clauses) > 0 )$this->where = '('. implode(' ) AND ( ', $where_clauses) . ')';
            $GLOBALS['log']->info("List View Where Clause: $this->where");
        }
        //Overwrite the where if the search don't come from the upgrade wizard in order to have an empty listview
        if($overwriteWhere){
            $this->where="1=2";
        }
        echo $this->searchForm->display($this->headers);
    }
    
    /**
     * Returns Next Button used in this view
     */
    private function _getNextBtn()
    {
        $hrefInfo="&action=DCEUpgradeStep2&return_module={$this->module}&return_action={$_REQUEST['action']}";
        return <<<EONEXTBTN
<input id='nextBtn' class='button' type='button' onclick='sListView.send_form(true, "{$this->module}", "index.php", "{$GLOBALS['app_strings']['LBL_LISTVIEW_NO_SELECTED']}", "{$this->module}","{$hrefInfo}");' value="{$GLOBALS['app_strings']['LBL_NEXT_BUTTON_LABEL']}">
EONEXTBTN;
    }
    
    /**
     * Returns JS used in this view
     */
    private function _getJS()
    {
        global $mod_strings;
    
        return <<<EOJAVASCRIPT
<script type="text/javascript">
<!--
document.getElementById('search_form_submit').onclick = function(){
    if(check_form('search_form')){
        SUGAR.savedViews.setChooser();
    }else{
        return false;
    }
}
star=document.createElement("span");
star.innerHTML="<font color='red'>* </font>";
dcetemplate_name_dceupgrade=document.getElementById('dcetemplate_name_dceupgrade');
dcetemplate_name_dceupgrade.parentNode.insertBefore(star,dcetemplate_name_dceupgrade);
addToValidate('search_form', 'dcetemplate_name_dceupgrade', 'text',true, "{$mod_strings['LBL_TEMPLATE']}");
-->
</script>

EOJAVASCRIPT;
    }
}