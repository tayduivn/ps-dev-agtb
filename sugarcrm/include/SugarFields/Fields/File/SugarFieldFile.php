<?php
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
require_once('include/SugarFields/Fields/Base/SugarFieldBase.php');

class SugarFieldFile extends SugarFieldBase {
    private function fillInOptions(&$vardef,&$displayParams) {
        if ( isset($vardef['allowEapm']) && $vardef['allowEapm'] == true ) {
            if ( empty($vardef['docType']) ) {
                $vardef['docType'] = 'doc_type';
            }
            if ( empty($vardef['docId']) ) {
                $vardef['docId'] = 'doc_id';
            }
            if ( empty($vardef['docUrl']) ) {
                $vardef['docUrl'] = 'doc_url';
            }
        } else {
            $vardef['allowEapm'] = false;
        }

        // This is needed because these aren't always filled out in the edit/detailview defs
        if ( !isset($vardef['fileId']) ) {
            if ( isset($displayParams['id']) ) {
                $vardef['fileId'] = $displayParams['id'];
            } else {
                $vardef['fileId'] = 'id';
            }
        }
    }


	function getDetailViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex) {
        $this->fillInOptions($vardef,$displayParams);
        return parent::getDetailViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex);
    }
    
	function getEditViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex) {
        $this->fillInOptions($vardef,$displayParams);
        return parent::getEditViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex);
    }
    
	public function save(&$bean, $params, $field, $vardef, $prefix = ''){
        $GLOBALS['log']->fatal('IKEA: Im trying to save');

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
            $GLOBALS['log']->fatal('IKEA: It is looking good so far');
		}
 		
		if ($move) {
			if (empty($bean->id)) { 
				$bean->id = create_guid();
				$bean->new_with_id = true;
			}
        
			$upload_file->final_move($bean->id);
            $GLOBALS['log']->fatal('IKEA: Calling upload_doc, doc_type:'.$params[$prefix . $vardef['docType']]);
            $upload_file->upload_doc($bean, $bean->id, $params[$prefix . $vardef['docType']], $bean->$field, $bean->mime_type);
		} else if ( !empty($params[$prefix . $vardef['name'] . '_remoteName']) ) {
            // We ain't moving, we might need to do some remote linking
            $displayParams = array();
            $this->fillInOptions($vardef,$displayParams);

            $GLOBALS['log']->fatal('IKEA: Params: '.print_r($params,true));
            $GLOBALS['log']->fatal('IKEA: vardef: '.print_r($vardef,true));
            
            if ( isset($params[$prefix . $vardef['docId']])
                 && ! empty($params[$prefix . $vardef['docId']])
                 && isset($params[$prefix . $vardef['docType']]) 
                 && ! empty($params[$prefix . $vardef['docType']])
                ) {
                $bean->filename = $params[$prefix . $vardef['name'] . '_remoteName'];
            }
        }
	}
}
?>