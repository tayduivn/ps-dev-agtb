<?php
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
/*
 * Created on Feb 20, 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
require_once('include/MVC/Controller/SugarController.php');
class DCETemplatesController extends SugarController{
    function DCETemplatesController(){
        parent::SugarController();
    }
    function action_save(){
        parent::pre_save();
        if(!isset($this->bean->zip_name) || !isset($this->bean->id) ){
            $focus = new Administration();
            $focus->retrieveSettings();
            $uploaddir=$focus->settings['dce_templates_dir'];
            if(!isset($uploaddir)){
                die('<font color="red">'.translate('ERR_SET_TEMPLATES_DIRECTORY', 'DCETemplates').'</font>');
            }
            if(!file_exists($uploaddir))
            {
                die('<font color="red">'.translate('ERR_CREATE_TEMPLATES_DIRECTORY', 'DCETemplates').'</font>');
            }
            $uploadfile = $uploaddir .'/'. $_REQUEST['template_file'];
            if (file_exists($uploadfile)){
                $this->action = 'editview';
                $this->errors[] = translate('ERR_UPLOAD_FILE_EXIST', 'DCETemplates');
                $this->process();
                return false;
            }else{
                $success=rename($_REQUEST['uploadTmpDir'] .'/'. $_REQUEST['template_file'],$uploadfile);//Move file from tmp directory
                rmdir_recursive($_REQUEST['uploadTmpDir']);
                if(!$success){
                    $this->action = 'editview';
                    $this->errors[] = translate('ERR_UPLOAD_COPY_IMPOSSIBLE', 'DCETemplates');
                    $this->process();
                    return false;
                }
                $this->bean->zip_name = basename($_REQUEST['template_file']);
            }
        }
        parent::action_save();
    }
     
    function action_convertTemplate(){
     require_once('modules/DCETemplates/convertTemplate.php');   
    }   
}
?>