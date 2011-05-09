<?php
/**
 * This script executes after the files are copied during the install.
 *
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2005 SugarCRM, Inc.; All Rights Reserved.
 *
 * $Id$
 */


function wrapMultiData($field)
{
    global $sugar_config;
    $ret = '';
    if($sugar_config['dbconfig']['db_type']== "mysql"){
        $ret = "CONCAT('^', $field, '^')";
    } else if($sugar_config['dbconfig']['db_type']== "mssql")
    {
        $ret = "'^'+cast($field as varchar)+'^'";
    }
    //BEGIN SUGARCRM flav=ent ONLY 
    else if($sugar_config['dbconfig']['db_type']== "oci8"){
        $ret = "concat('^', concat($field, '^')),";                 
    }
    //END SUGARCRM flav=ent ONLY 
    return $ret;
}

function getMultiEnumFields($fieldDefs)
{
    $ret = array();
    foreach($fieldDefs as $field => $def)
    {
        if (isset($def['type']) && $def['type']=='multienum' && (!isset($def['source']) || $def['source'] == 'db'))
        {
            $ret[] = $def['name'];
        }
    }
    return $ret;
}

function getCustomMultiEnumFields($fieldDefs)
{
    $ret = array();
    foreach($fieldDefs as $field => $def)
    {
        if (isset($def['type']) && $def['type']=='multienum' && isset($def['source']) && $def['source'] == 'custom_fields')
        {
            $ret[] = $def['name'];
        }
    }
    return $ret;
}

function getUpdateQuery($fields, $table)
{
    if (empty($fields))
        return "";
    
    $query = "UPDATE $table SET";
    foreach($fields as $field)
    {
        $query .= " {$field}=" . wrapMultiData($field) . ",";
    }
    $query = substr($query, 0, strlen($query) -1);
    
    return $query;
}



function upgrade_multienum_data(){
    require_once("include/SugarObjects/VardefManager.php");
    global $dictionary, $db, $moduleList, $beanList, $sugar_version;
    
    if ($_SESSION['current_db_version'] >= 550)
        return;
        
    
         
    foreach($moduleList as $module) 
    {
        if (empty($beanList[$module]))
            continue;
        $beanName = $beanList[$module];
        VardefManager::loadVardef($module, $beanName);
        if (empty($dictionary[$beanName]))
            continue;
        $def = $dictionary[$beanName];
        $query = "";
        if (!empty ($def['table']) && !empty($def['fields']))
        {
            $table = $def['table'];
            $fields = getMultiEnumFields($def['fields']);
            $customFields = getCustomMultiEnumFields($def['fields']);
            if (empty($fields) && empty($customFields))
                continue;
            $query  = getUpdateQuery($fields, $table);
            if (!empty($query))
            {
                $db->query($query);
            }
            $query = getUpdateQuery($customFields, $table . "_cstm");
            if (!empty($query))
            {
                $db->query($query);
            }
        }
    }   
}
    
?>
