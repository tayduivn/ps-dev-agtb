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
 *Portions created by SugarCRM are Copyright (C) 2007 SugarCRM, Inc.; All Rights Reserved.
 /*********************************************************************************

 * Description:  ajax call to sort and return list, used in browse tab list form of Full Text Search 
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('modules/KBDocuments/SearchUtils.php');


    $node_depth = isset($_REQUEST['PARAMT_depth']) ? $_REQUEST['PARAMT_depth'] : 0;
    $n_id = isset($_REQUEST['PARAMN_id_'.$node_depth]) ? $_REQUEST['PARAMN_id_'.$node_depth] : '';
    $zero_node =  isset($_REQUEST['PARAMN_id_0']) ? $_REQUEST['PARAMN_id_0'] : '';
    $sortCol = '';
    
    //if we do not get the node id, then cancel this call, we cannot proceed
    if(empty($n_id )){
        return;   
    }
    
    $search_str = ' kbdocuments.deleted =0';
    //if node id is untagged, then create query for all untagged articles
    if($n_id == 'UNTAGGED_NODE_ID'){
    $search_str .= " and kbdocuments.id NOT IN
                                (select kbdocument_id from kbdocuments_kbtags where deleted = 0)";      
        
    }else{
        //create query for articles under this tag
    $search_str .= " and kbdocuments.id
                        IN (
                            SELECT kbd.id
                            FROM kbdocuments kbd, kbdocuments_kbtags kbd_kt
                            WHERE kbd.id = kbd_kt.kbdocument_id
                            AND kbd.deleted = 0
                            AND kbd_kt.deleted = 0
                            AND kbd_kt.kbtag_id = '$n_id'
                        )";        
    
    }
    
    //check to see if sortCol has been specified
    if( isset($_REQUEST['sortCol']) && !empty($_REQUEST['sortCol'])) {
        //if sorcol has been set to PAGINATE, then this is a pagination and requires
        //reversing the sort order so listview data can process correctly
        if($_REQUEST['sortCol']=='PAGINATE'){
            if(isset($lvso) && !empty($lvso)){
                $lvso = (strcmp(strtolower($lvso), 'asc') == 0)?'DESC':'ASC';
            }
        }else{
            //this is a normal sort column command, override sort order 
            //with currently selected column (if this call is from sort event)
            $sortCol = $_REQUEST['sortCol'];
        }
    }
   //Set Request Object parameter so that Sort order will happen in get_fts_list method
   $_REQUEST['KBDocuments2_KBDOCUMENT_ORDER_BY'] = $sortCol;
       
   //if set to 'all tags', pass in query 'where' clause into method that returns list for admins
   if(!empty($zero_node) && strtolower($zero_node) == 'all_tags'){
   		$results = get_admin_fts_list($search_str,false,true);
   }else{   
        $results = get_fts_list($search_str,false,true);
   }

echo $results;

?>
