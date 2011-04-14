<?php
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
require_once('include/SugarFields/Fields/Base/SugarFieldBase.php');

class SugarFieldFile extends SugarFieldBase {
   
	function getDetailViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex) {

        global $app_strings;
        if(!isset($displayParams['link'])) {
           $error = $app_strings['ERR_SMARTY_MISSING_DISPLAY_PARAMS'] . 'link';
           $GLOBALS['log']->error($error);	
           $this->ss->trigger_error($error);
           return;
        }
        
        if(!isset($displayParams['id'])) {
           $error = $app_strings['ERR_SMARTY_MISSING_DISPLAY_PARAMS'] . 'id';
           $GLOBALS['log']->error($error);	
           $this->ss->trigger_error($error);
           return;
        }        

        $this->setup($parentFieldArray, $vardef, $displayParams, $tabindex);
        return $this->fetch('include/SugarFields/Fields/File/DetailView.tpl');
    }
    
	public function save(&$bean, $params, $field, $properties, $prefix = ''){
		require_once('include/upload_file.php');
		$upload_file = new UploadFile($prefix . $field);

		//remove file
		if (isset($_REQUEST['remove_file_' . $field]) && $_REQUEST['remove_file_' . $field] == 1)
		{
			$upload_file->unlink_file($bean->$field);
			$bean->$field="";
		}
		
		$move=false;
		if (isset($_FILES[$prefix . $field]) && $upload_file->confirm_upload())
		{
    		$bean->$field = $upload_file->get_stored_file_name();
    		$bean->file_mime_type = $upload_file->mime_type;
			$bean->file_ext = $upload_file->file_ext;
			$move=true;
		}
 		
		if ($move) {
			if (empty($bean->id)) { 
				$bean->id = create_guid();
				$bean->new_with_id = true;
			}
        
			$upload_file->final_move($bean->id);
		}
	}
}
?>