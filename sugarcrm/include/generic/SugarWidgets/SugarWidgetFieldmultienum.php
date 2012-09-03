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


class SugarWidgetFieldMultiEnum extends SugarWidgetFieldEnum {
	public function queryFilternot_one_of(&$layout_def) {
        $col_name = $this->_get_column_select($layout_def) . " NOT LIKE " ;
        $query = array();
        foreach ($layout_def['input_name0'] as $val)
        {
            $query[] = $col_name . $this->reporter->db->quoted('%' . $this->encodeMultienumCustom($layout_def, $val) . '%');
        }
        return '('. implode(' AND ', $query) .')';
	}
        
    public function queryFilterone_of(&$layout_def) {
        $col_name = $this->_get_column_select($layout_def) . " LIKE " ;
        $query = array();
        foreach ($layout_def['input_name0'] as $val)
        {
            $query[] = $col_name . $this->reporter->db->quoted('%' . $this->encodeMultienumCustom($layout_def, $val) . '%');
        }
        return '('. implode(' OR ', $query) .')';
	}

	public function queryFilteris($layout_def) {
		$input_name0 = $layout_def['input_name0'];
		if (is_array($layout_def['input_name0'])) {
			$input_name0 = $layout_def['input_name0'][0];
		}

		// Bug 40022
		// IS filter doesn't add the carets (^) to multienum custom field values  
		$input_name0 = $this->encodeMultienumCustom($layout_def, $input_name0);
		
		return $this->_get_column_select($layout_def)." = ".$this->reporter->db->quoted($input_name0)."\n";
	}

	public function queryFilteris_not($layout_def) {
		$input_name0 = $layout_def['input_name0'];
		if (is_array($layout_def['input_name0'])) {
			$input_name0 = $layout_def['input_name0'][0];
		}

		// Bug 50549
		// IS NOT filter doesn't add the carets (^) to multienum custom field values  
		$input_name0 = $this->encodeMultienumCustom($layout_def, $input_name0);
		
		return $this->_get_column_select($layout_def)." <> ".$this->reporter->db->quoted($input_name0)."\n";
	}
	
    /**
     * Returns an OrderBy query for multi-select. We treat multi-select the same as a normal field because
     * the values stored in the database are in the format ^A^,^B^,^C^ though not necessarily in that order.
     * @param  $layout_def
     * @return string
     */
    public function queryOrderBy($layout_def) {
        return SugarWidgetReportField::queryOrderBy($layout_def);
    }
    
    /**
     * Function checks if the multienum field is custom, and escapes it with carets (^) if it is
     * @param array $layout_def field layout definition
     * @param string $value value to be escaped
     * @return string
     */
    private function encodeMultienumCustom($layout_def, $value) {
    	$field_def = $this->reporter->getFieldDefFromLayoutDef($layout_def);
    	// Check if it is a custom field
		if (!empty($field_def['source']) && ($field_def['source'] == 'custom_fields' || ($field_def['source'] == 'non-db' && !empty($field_def['ext2']) && !empty($field_def['id']))) && !empty($field_def['real_table']))
		{
			$value = encodeMultienumValue(array($value)); 
		}
		return $value;
    }
}
?>
