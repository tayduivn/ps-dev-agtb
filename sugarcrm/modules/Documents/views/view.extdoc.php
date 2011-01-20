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
 * to provide customization specific to the Calls module.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
require_once('include/Sugar_Smarty.php');
require_once('include/externalAPI/ExternalAPIFactory.php');


class DocumentsViewExtdoc extends SugarView
{
    var $options = array('show_header' => false, 'show_title' => false, 'show_subpanels' => false, 'show_search' => true, 'show_footer' => false, 'show_javascript' => false, 'view_print' => false,);

    public function init($bean, $view_object_map) {
        $this->seed = $bean;
    }

 	public function display(){

        if ( isset($_REQUEST['name_basic']) ) {
            $file_search = trim($_REQUEST['name_basic']);
        } else {
            $file_search = '';
        }
        
        // $apiName = 'LotusLiveDirect';
        $apiName = 'LotusLive';

        if ( ! EAPM::getLoginInfo($apiName) ) {
            $smarty = new Sugar_Smarty();
            echo $smarty->fetch('include/externalAPI/LotusLive/LotusLiveSignup.'.$GLOBALS['current_language'].'.tpl');
            return;
        }


        $api = ExternalAPIFactory::loadAPI($apiName);

        $quickCheck = $api->quickCheckLogin();
        if ( ! $quickCheck['success'] ) {
            $errorMessage = 'LotusLive' . translate('LBL_ERR_FAILED_QUICKCHECK','EAPM');
            $errorMessage .= '<br><button onclick="lastLoadedMenu=undefined;DCMenu.closeOverlay();">'.$GLOBALS['app_strings']['LBL_CANCEL_BUTTON_LABEL'].'</button> ';
            $errorMessage .= '<button onclick="window.open(\'index.php?module=EAPM&closeWhenDone=1&action=Save&record='.$api->eapmBean->id.'\',\'EAPM_CHECK_LOTUSLIVE\');">'.$GLOBALS['app_strings']['LBL_EMAIL_OK'].'</button>';
            
            echo $errorMessage;
            return;
        }

        $searchDataLower = $api->searchDoc($file_search,true);


        // In order to emulate the list views for the SugarFields, I need to uppercase all of the key names.
        $searchData = array();

        if ( is_array($searchDataLower) ) {
            foreach ( $searchDataLower as $row ) {
                $newRow = array();
                foreach ( $row as $key => $value ) {
                    $newRow[strtoupper($key)] = $value;
                }
                $searchData[] = $newRow;
            }
        }

        $displayColumns = array(
            'NAME' => array(
                'label' => 'LBL_LIST_DOCUMENT_NAME',
                'type' => 'varchar',
                'link' => true,
                ),
            'DATE_MODIFIED' => array(
                'label' => 'LBL_DATE',
                'type' => 'date',
                ),
        );

        $ss = new Sugar_Smarty();
        $ss->assign('data', $searchData);
        $ss->assign('displayColumns',$displayColumns);
        $ss->assign('DCSEARCH',$file_search);
        $ss->display('modules/Documents/tpls/view.extdoc.tpl');
 	}
}
