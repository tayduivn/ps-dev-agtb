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
            if ( empty($vardef['docDirectUrl']) ) {
                $vardef['docDirectUrl'] = 'doc_direct_url';
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
        $fakeDisplayParams = array();
        $this->fillInOptions($vardef,$fakeDisplayParams);

		require_once('include/upload_file.php');
		$upload_file = new UploadFile($prefix . $field . '_file');

		//remove file
		if (isset($_REQUEST['remove_file_' . $field]) && $params['remove_file_' . $field] == 1)
		{
			$upload_file->unlink_file($bean->$field);
			$bean->$field="";
		}
		
		$move=false;
		if (isset($_FILES[$prefix . $field . '_file']) && $upload_file->confirm_upload())
		{
    		$bean->$field = $upload_file->get_stored_file_name();
            $GLOBALS['log']->fatal("IKEA: Set filename: ".__LINE__.":(".$bean->$field.")");
    		$bean->file_mime_type = $upload_file->mime_type;
			$bean->file_ext = $upload_file->file_ext;
			$move=true;
		}

        if (isset($params['isDuplicate']) && $params['isDuplicate'] == true && $params['isDuplicate'] != 'false' ) {
            // It's a duplicate
            $old_id = $params['relate_id'];
        }

        if (empty($bean->id)) { 
            $bean->id = create_guid();
            $bean->new_with_id = true;
        }

        $GLOBALS['log']->fatal("IKEA: OLD ID: ".$old_id);
        $GLOBALS['log']->fatal("IKEA: PARAMS: ".print_r($params,true));
 		
		if ($move) {
            $GLOBALS['log']->fatal("IKEA: Moving the file ({$bean->filename})");
			$upload_file->final_move($bean->id);
            $upload_file->upload_doc($bean, $bean->id, $params[$prefix . $vardef['docType']], $bean->$field, $bean->mime_type);
        } else if ( ! empty($old_id) ) {
            // It's a duplicate, I think
            $GLOBALS['log']->fatal("IKEA: It's a duplicate old id ($old_id) / isDuplicate ({$params['isDuplicate']})");

            if ( empty($params[$prefix . $vardef['docUrl'] ]) ) {
                $GLOBALS['log']->fatal("IKEA: It's a locally stored file, ($prefix{$vardef['docUrl']}) is empty");
                $upload_file->duplicate_file($old_id, $bean->id, $bean->$field);
            } else {
                $GLOBALS['log']->fatal("IKEA: It's a remotely stored file, lets copy manually");
                $docType = $vardef['docType'];
                $bean->$docType = $params[$prefix . $field . '_old_doctype'];
            }
		} else if ( !empty($params[$prefix . $field . '_remoteName']) ) {
            // We ain't moving, we might need to do some remote linking
            $GLOBALS['log']->fatal("IKEA: Remotely linking the file");
            $displayParams = array();
            $this->fillInOptions($vardef,$displayParams);
            
            if ( isset($params[$prefix . $vardef['docId']])
                 && ! empty($params[$prefix . $vardef['docId']])
                 && isset($params[$prefix . $vardef['docType']]) 
                 && ! empty($params[$prefix . $vardef['docType']])
                ) {
                $GLOBALS['log']->fatal("IKEA: Set filename: ".__LINE__.":(".$bean->$field.")");
                $bean->$field = $params[$prefix . $field . '_remoteName'];
            }
        } else {
            $GLOBALS['log']->fatal("IKEA: Not doing a thing");
        }
        
        if ( empty($bean->$field) ) {
            $GLOBALS['log']->fatal("The $field is empty, clearing out the lot");
            // Looks like we are emptying this out
            $clearFields = array('docId', 'docType', 'docUrl', 'docDirectUrl');
            foreach ( $clearFields as $clearMe ) {
                if ( ! isset($vardef[$clearMe]) ) {
                    continue;
                }
                $clearField = $vardef[$clearMe];
                $bean->$clearField = '';
            }
        }
	}
}
