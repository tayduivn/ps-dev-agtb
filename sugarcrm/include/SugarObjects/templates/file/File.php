<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
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
 
 require_once('include/SugarObjects/templates/basic/Basic.php');
 require_once('include/upload_file.php');
  require_once('include/formbase.php');
class File extends Basic{
	function File(){
		parent::Basic();
	}

	//Must overwrite the save operation for uploaded file.
	var $file_url;
	var $file_url_noimage;
	function save($check_notify=false){
		if (!empty($this->uploadfile))
			$this->filename = $this->uploadfile;
		return parent::save($check_notify);
		
 	}



	function fill_in_additional_detail_fields(){

		global $theme;
		global $current_language;
		global $timedate;
		global $app_list_strings;
		$this->uploadfile = $this->filename;
		$mod_strings = return_module_language($current_language, $this->object_name);
		global $img_name;
		global $img_name_bare;
		if (!$this->file_ext) {
			$img_name = SugarThemeRegistry::current()->getImageURL(strtolower($this->file_ext)."_image_inline.gif");
			$img_name_bare = strtolower($this->file_ext)."_image_inline";
		}
		//set default file name.
		if (!empty ($img_name) && file_exists($img_name)) {
			$img_name = $img_name_bare;
		} else {
			$img_name = "def_image_inline"; //todo change the default image.
		}
		$this->file_url_noimage = basename(UploadFile :: get_url($this->filename, $this->id));
		if(!empty($this->status_id)) {
	       $this->status = $app_list_strings['document_status_dom'][$this->status_id];
	    }

	}
	
	// need to override to have a name field created for this class
	function retrieve($id = -1, $encode=true) {
		$ret_val = parent::retrieve($id, $encode);
		$this->_create_proper_name_field();
		return $ret_val;
	}
	

	function _create_proper_name_field() {
		global $locale;
		$this->name = $this->document_name;
	}
}
?>
