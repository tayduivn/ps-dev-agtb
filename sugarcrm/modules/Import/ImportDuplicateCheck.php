<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2007 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id: ImportDuplicateCheck.php 31561 2008-02-04 18:41:10Z jmertic $
 * Description: Handles getting a list of fields to duplicate check and doing the duplicate checks
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 ********************************************************************************/

class ImportDuplicateCheck
{
    /**
     * Private reference to the bean we're dealing with
     */
    private $_focus;
    
    /** 
     * Constructor
     *
     * @param object $focus bean 
     */
    public function __construct(
        &$focus
        )
    {
        $this->_focus = &$focus;
    }
    
    /**
     * Returns an array of indices for the current module
     *
     * @return array
     */
    private function _getIndexVardefs()
    {
        $indexes = $this->_focus->getIndices();
        
        if ( $this->_focus->getFieldDefinition('email1') )
            $indexes[] = array(
                'name' => 'special_idx_email1',
                'type' => 'index',
                'fields' => array('email1')
                );
        if ( $this->_focus->getFieldDefinition('email2') )
            $indexes[] = array(
                'name' => 'special_idx_email2',
                'type' => 'index',
                'fields' => array('email2')
                );
        
        return $indexes;
    }
    
    /**
     * Returns an array with an element for each index
     *
     * @return array
     */
    public function getDuplicateCheckIndexes()
    {
        $super_language_pack = sugarArrayMerge(
            return_module_language($GLOBALS['current_language'], $this->_focus->module_dir), 
            $GLOBALS['app_strings']
            );
        
        $index_array = array();
        foreach ($this->_getIndexVardefs() as $index){
            if ($index['type'] == "index"){
                $labelsArray = array();
                foreach ($index['fields'] as $field){
                    if ($field == 'deleted') continue;
                    $fieldDef = $this->_focus->getFieldDefinition($field);
                    if ( isset($fieldDef['vname']) && isset($super_language_pack[$fieldDef['vname']]) )
                        $labelsArray[$fieldDef['name']] = $super_language_pack[$fieldDef['vname']];
                    else
                        $labelsArray[$fieldDef['name']] = $fieldDef['name'];
                }
                $index_array[$index['name']] = str_replace(":", "",implode(", ",$labelsArray));
            }
        }
        
        return $index_array;
    }
    
    /**
     * Checks to see if the given bean is a duplicate based off the given indexes
     *
     * @param  array $indexlist
     * @return bool true if this bean is a duplicate or false if it isn't
     */
    public function isADuplicateRecord(
        $indexlist
        )
    {
        // loop through var def indexes and compare with selected indexes
        foreach ( $this->_getIndexVardefs() as $index ) {
            // if we get an index not in the indexlist, loop
            if ( !in_array($index['name'],$indexlist) )
                continue;
            
            // This handles the special case of duplicate email checking
            if ( $index['name'] == 'special_idx_email1' || $index['name'] == 'special_idx_email2' ) {
                $emailAddress = new SugarEmailAddress();
                $email = $index['fields'][0];
                if ( $emailAddress->getCountEmailAddressByBean(
                        $this->_focus->$email,
                        $this->_focus,
                        ($index['name'] == 'special_idx_email1')
                        ) > 0 )
                    return true;
            }
            // Adds a hook so you can define a method in the bean to handle dupe checking
            elseif ( isset($index['dupeCheckFunction']) ) {
                $functionName = substr_replace($index['dupeCheckFunction'],'',0,9);
                if ( method_exists($this->_focus,$functionName) )
                    return $this->_focus->$functionName($index);
            }
            else {
                $index_fields = array('deleted' => '0');
                foreach($index['fields'] as $field){
                    if ($field == 'deleted') 
                        continue;
                    if (!in_array($field,$index_fields))
                        if (strlen($this->_focus->$field) > 0)
                            $index_fields[$field] = $this->_focus->$field;
                }
                
                // if there are no valid fields in the index field list, loop
                if ( count($index_fields) <= 1 )
                    continue;
                
                $newfocus = loadBean($this->_focus->module_dir);
                $result = $newfocus->retrieve_by_string_fields($index_fields,true);
                
                if ( !is_null($result) )
                    return true;
            }
        }
        return false;
    }
}
 
?>
