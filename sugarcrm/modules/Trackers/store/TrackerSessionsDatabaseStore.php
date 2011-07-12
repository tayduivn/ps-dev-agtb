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
/*********************************************************************************
 * $Id$
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
 
require_once('modules/Trackers/store/Store.php');
class TrackerSessionsDatabaseStore implements Store {
    
    public function flush($monitor) {

       $metrics = $monitor->getMetrics();
       
       if(isset($monitor->client_ip) && strlen($monitor->client_ip) > 20)
       {
          $monitor->client_ip = substr($monitor->client_ip, 0, 20);	  
       }
       
       $columns = array();
       $values = array();
       foreach($metrics as $name=>$metric) {
       	  if(!empty($monitor->$name)) {
       	  	 $columns[] = $name;
       	  	 if($metrics[$name]->_type == 'int' || $metrics[$name]->_type == 'double') {
                $values[] = $GLOBALS['db']->quote($monitor->$name);
             } else if ($metrics[$name]->_type == 'datetime') {
             	$values[] = ($GLOBALS['db']->dbType == 'oci8') ? db_convert("'".$monitor->$name."'",'datetime') : "'".$monitor->$name."'";
       	  	 } else {
                $values[] = "'".$GLOBALS['db']->quote($monitor->$name)."'";
             }     
       	  }
       } //foreach
       
       if(empty($values)) {
       	  return;
       }
       
       //BEGIN SUGARCRM flav=pro ONLY
       if($GLOBALS['db']->dbType == 'oci8'  &&  method_exists( $GLOBALS['db']->getHelper()  , 'getAutoIncrementSQL')) {
       	  $columns[] = 'id';
       	  if(method_exists($GLOBALS['db']->getHelper(), 'getAutoIncrementSQL'))
       	  {
       	  	$values[] = $GLOBALS['db']->getHelper()->getAutoIncrementSQL($monitor->table_name,'id');
       	  } else {
       	  	$values[] = $GLOBALS['db']->getHelper()->getAutoIncrement($monitor->table_name,'id');
       	  }
       }
       //END SUGARCRM flav=pro ONLY
       if ( !isset($monitor->round_trips) ) $monitor->round_trips = 0;
       if ( !isset($monitor->active) ) $monitor->active = 1;
       if ( !isset($monitor->seconds) ) $monitor->seconds = 0;
       if($monitor->round_trips == 1) {
		  $query = "INSERT INTO $monitor->table_name (" .implode("," , $columns). " ) VALUES ( ". implode("," , $values). ')';
		  $GLOBALS['db']->query($query);
       } else {
       	  $query = "UPDATE $monitor->table_name SET date_end = '{$monitor->date_end}' , seconds = {$monitor->seconds}, active = '{$monitor->active}', round_trips = $monitor->round_trips WHERE session_id = '{$monitor->session_id}'";
       	  $GLOBALS['db']->query($query);
       }
    }
}

?>
