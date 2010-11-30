<?php
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
require_once('include/SugarFields/Fields/Base/SugarFieldBase.php');

class SugarFieldRelate extends SugarFieldBase {
    
    function getDetailViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex) {
        $nolink = array('Users', 'Teams');
        if(in_array($vardef['module'], $nolink)){
            $this->ss->assign('nolink', true);
        }else{
            $this->ss->assign('nolink', false);
        }
        $this->setup($parentFieldArray, $vardef, $displayParams, $tabindex);
        return $this->fetch('include/SugarFields/Fields/Relate/DetailView.tpl');
    }
    
    function getEditViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex) {
        if(!empty($vardef['function']['returns']) && $vardef['function']['returns'] == 'html'){
            return parent::getEditViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex);
        }
        
        $call_back_function = 'set_return';
        if(isset($displayParams['call_back_function'])) {
            $call_back_function = $displayParams['call_back_function'];
        }
        $form_name = 'EditView';
        if(isset($displayParams['formName'])) {
            $form_name = $displayParams['formName'];
        }
        
        //Special Case for accounts; use the displayParams array and retrieve
        //the key and copy indexes.  'key' is the suffix of the field we are searching
        //the Account's address with.  'copy' is the suffix we are copying the addresses
        //form fields into.
        if(isset($vardef['module']) && preg_match('/Accounts/si',$vardef['module']) 
           && isset($displayParams['key']) && isset($displayParams['copy'])) {
            
            if(isset($displayParams['key']) && is_array($displayParams['key'])) {
              $database_key = $displayParams['key'];    
            } else {
              $database_key[] = $displayParams['key'];
            }
            
            if(isset($displayParams['copy']) && is_array($displayParams['copy'])) {
                $form = $displayParams['copy'];
            } else {
                $form[] = $displayParams['copy'];
            }
            
            if(count($database_key) != count($form)) {
              global $app_list_strings;
              $this->ss->trigger_error($app_list_strings['ERR_SMARTY_UNEQUAL_RELATED_FIELD_PARAMETERS']);
            } //if
            
            $copy_phone = isset($displayParams['copyPhone']) ? $displayParams['copyPhone'] : true;
            
            $field_to_name = array();
            $field_to_name['id'] = $vardef['id_name'];
            $field_to_name['name'] = $vardef['name'];
            $address_fields = array('_address_street', '_address_city', '_address_state', '_address_postalcode', '_address_country');
            $count = 0;
            foreach($form as $f) {
                foreach($address_fields as $afield) {
                    $field_to_name[$database_key[$count] . $afield] = $f . $afield;
                }
                $count++;
            }

            $popup_request_data = array(
                'call_back_function' => $call_back_function,
                'form_name' => $form_name,
                'field_to_name_array' => $field_to_name,
            );
                
            if($copy_phone) {
              $popup_request_data['field_to_name_array']['phone_office'] = 'phone_work';
            }               
        } elseif(isset($displayParams['field_to_name_array'])) {
            $popup_request_data = array(
                'call_back_function' => $call_back_function,
                'form_name' => $form_name,
                'field_to_name_array' => $displayParams['field_to_name_array'],
            );  
        } else {
            $popup_request_data = array(
                'call_back_function' => $call_back_function,
                'form_name' => $form_name,
                'field_to_name_array' => array(
                          'id' => (empty($displayParams['idName']) ? $vardef['id_name'] : ($displayParams['idName'] . '_' . $vardef['id_name'])) ,
                          ((empty($vardef['rname'])) ? 'name' : $vardef['rname']) => (empty($displayParams['idName']) ? $vardef['name'] : $displayParams['idName']),
                    ),
                );
        }
        $json = getJSONobj();
        $displayParams['popupData'] = '{literal}'.$json->encode($popup_request_data). '{/literal}';
        if(!isset($displayParams['readOnly'])) {
           $displayParams['readOnly'] = '';
        } else {
           $displayParams['readOnly'] = $displayParams['readOnly'] == false ? '' : 'READONLY';  
        }
        $this->setup($parentFieldArray, $vardef, $displayParams, $tabindex);
        return $this->fetch('include/SugarFields/Fields/Relate/EditView.tpl'); 
    }
    
    function getPopupViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex){
    	$displayParams['clearOnly'] = true;
    	return $this->getSearchViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex);
    }
    
    function getSearchViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex) {
        $call_back_function = 'set_return';
        if(isset($displayParams['call_back_function'])) {
            $call_back_function = $displayParams['call_back_function'];
        }
        $form_name = 'search_form';
        if(isset($displayParams['formName'])) {
            $form_name = $displayParams['formName'];
        }
        if(!empty($vardef['rname']) && $vardef['rname'] == 'user_name'){
        	$displayParams['useIdSearch'] = true;
        }
        //Special Case for accounts; use the displayParams array and retrieve
        //the key and copy indexes.  'key' is the suffix of the field we are searching
        //the Account's address with.  'copy' is the suffix we are copying the addresses
        //form fields into.
        if(isset($vardef['module']) && preg_match('/Accounts/si',$vardef['module']) 
           && isset($displayParams['key']) && isset($displayParams['copy'])) {
            
            if(isset($displayParams['key']) && is_array($displayParams['key'])) {
              $database_key = $displayParams['key'];    
            } else {
              $database_key[] = $displayParams['key'];
            }
            
            if(isset($displayParams['copy']) && is_array($displayParams['copy'])) {
                $form = $displayParams['copy'];
            } else {
                $form[] = $displayParams['copy'];
            }
            
            if(count($database_key) != count($form)) {
              global $app_list_strings;
              $this->ss->trigger_error($app_list_strings['ERR_SMARTY_UNEQUAL_RELATED_FIELD_PARAMETERS']);
            } //if
            
            $copy_phone = isset($displayParams['copyPhone']) ? $displayParams['copyPhone'] : true;
            
            $field_to_name = array();
            $field_to_name['id'] = $vardef['id_name'];
            $field_to_name['name'] = $vardef['name'];
            $address_fields = array('_address_street', '_address_city', '_address_state', '_address_postalcode', '_address_country');
            $count = 0;
            foreach($form as $f) {
                foreach($address_fields as $afield) {
                    $field_to_name[$database_key[$count] . $afield] = $f . $afield;
                }
                $count++;
            }

            $popup_request_data = array(
                'call_back_function' => $call_back_function,
                'form_name' => $form_name,
                'field_to_name_array' => $field_to_name,
            );
                
            if($copy_phone) {
              $popup_request_data['field_to_name_array']['phone_office'] = 'phone_work';
            }               
        } elseif(isset($displayParams['field_to_name_array'])) {
            $popup_request_data = array(
                'call_back_function' => $call_back_function,
                'form_name' => $form_name,
                'field_to_name_array' => $displayParams['field_to_name_array'],
            );  
        } else {
            $popup_request_data = array(
                'call_back_function' => $call_back_function,
                'form_name' => $form_name,
                'field_to_name_array' => array(
                          'id' => $vardef['id_name'],
                          ((empty($vardef['rname'])) ? 'name' : $vardef['rname']) => $vardef['name'],
                    ),
                );
        }
        $json = getJSONobj();
        $displayParams['popupData'] = '{literal}'.$json->encode($popup_request_data). '{/literal}';
        if(!isset($displayParams['readOnly'])) {
           $displayParams['readOnly'] = '';
        } else {
           $displayParams['readOnly'] = $displayParams['readOnly'] == false ? '' : 'READONLY';  
        }
        $this->setup($parentFieldArray, $vardef, $displayParams, $tabindex);
        return $this->fetch('include/SugarFields/Fields/Relate/SearchView.tpl'); 
    }    
    
    function formatField($rawField, $vardef) {
    	if ('contact_name' == $vardef['name']){
    	    $default_locale_name_format = $GLOBALS['current_user']->getPreference('default_locale_name_format');
    	    $default_locale_name_format = trim(preg_replace('/s/i', '', $default_locale_name_format));
            $new_field = '';
    	    $names = array();
            $temp = explode(' ', $rawField);
            if ( !isset($temp[1]) ) {
                $names['f'] = '';
                $names['l'] = $temp[0];
            }
            elseif ( !empty($temp) ) {
                $names['f'] = $temp[0];
                $names['l'] = $temp[1];
            }
            for($i=0;$i<strlen($default_locale_name_format);$i++){
        	    $new_field .= array_key_exists($default_locale_name_format{$i}, $names) ? $names[$default_locale_name_format{$i}] : $default_locale_name_format{$i};
            }
    	}
    	else  $new_field = $rawField;
    	
        return $new_field;
    }
}
?>