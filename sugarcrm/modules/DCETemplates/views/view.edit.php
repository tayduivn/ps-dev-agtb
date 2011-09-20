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

class DCETemplatesViewEdit extends ViewEdit 
{    
 	public function __construct()
 	{
 		parent::ViewEdit();
 		$this->useForSubpanel = true;
 	}
 	
 	/**
	 * @see SugarView::display()
	 */
	public function display() 
	{
        $this->ev->process();
        if(isset($this->bean->template_name)){
            $this->ev->fieldDefs['sugar_version']['type']='readonly';
            $this->ev->fieldDefs['sugar_edition']['type']='readonly';
        }
        $focus = new Administration();
        $focus->retrieveSettings();
        if(isset($focus->settings['dce_templates_dir'])){
            $uploaddir=$focus->settings['dce_templates_dir'];
            if(!file_exists($uploaddir)){
                $error_msg=translate('ERR_CREATE_TEMPLATES_DIRECTORY', 'DCETemplates');
                echo "<span class='error'><b>$error_msg ($uploaddir)</b></span><br><br>";
            }
            // if no write access on the upload Template folder
            else if(!is_writable($uploaddir)){
                $error_msg=translate('ERR_WRITE_ACCESS', 'DCETemplates');
                echo "<span class='error'><b>$uploaddir $error_msg</b></span><br><br>";
            }
        }else{
            $error_msg=translate('ERR_SET_TEMPLATES_DIRECTORY', 'DCETemplates');
            echo "<span class='error'><b>$error_msg</b></span><br><br>";
        }
        
        $upload_size=ini_get("upload_max_filesize");
        $post_size=ini_get("post_max_size");
        //if less than 25M
        if(return_bytes($upload_size)<26214400 || return_bytes($post_size)<26214400){
            $error_msg=translate('ERR_PHP_INI_SIZE_PRB', 'DCETemplates');
            echo <<<ERR1
<span class='error'>
$error_msg<br>
upload_max_filesize = $upload_size <br>
post_max_size = $post_size <br><br>
</span>
ERR1;
        }
        echo $this->ev->display();
 	}
}