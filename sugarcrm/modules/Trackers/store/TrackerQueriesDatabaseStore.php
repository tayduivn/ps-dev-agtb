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

class TrackerQueriesDatabaseStore implements Store {

    public function flush($monitor) {

       $metrics = $monitor->getMetrics();
       $columns = array();
       $values = array();
       foreach($metrics as $name=>$metric) {
       	  if(!empty($monitor->$name)) {
       	  	 $columns[] = $name;
       	  	 if($metrics[$name]->_type == 'int') {
       	  	    $values[] = intval($monitor->$name);
       	  	 } else if($metrics[$name]->_type == 'double') {
                $values[] = floatval($monitor->$name);
             } else if ($metrics[$name]->_type == 'datetime') {
             	$values[] = $GLOBALS['db']->convert($GLOBALS['db']->quoted($monitor->$name), "datetime");
       	  	 } else {
                $values[] = $GLOBALS['db']->quoted($monitor->$name);
             }
           }
       } //foreach

       if(empty($values)) {
       	  return;
       }

       $id = $GLOBALS['db']->getAutoIncrementSQL($monitor->table_name,'id');
       if(!empty($id)) {
       	  $columns[] = 'id';
       	  $values[] = $id;
       }

       if($monitor->run_count == 1) {
       	  if($GLOBALS['db']->dbType == 'oci8') {
       	  	  //BEGIN SUGARCRM flav=pro ONLY
	          $query = "INSERT INTO $monitor->table_name (" .implode("," , $columns). " ) VALUES ( ". implode("," , $values). ')';

			  $lob_fields = array();
			  $lob_field_type = array();
			  $lobs = array();

			  //Add text as the lob field
			  $lob_fields['text'] = ":". 'text';
			  $lob_field_type['text'] = OCI_B_CLOB;

			  $query .= " RETURNING ".implode(",", array_keys($lob_fields)).' INTO '.implode(",", array_values($lob_fields));

			  $stmt = oci_parse($GLOBALS['db']->database, $query);
			  $err = oci_error($GLOBALS['db']->database);
			  if ($err != false){
			      $GLOBALS['log']->error($query.">>".$err['code'].":".$err['message']);
			      return;
			  }

			  foreach ($lob_fields as $key=>$descriptor) {
			    $newlob = OCINewDescriptor($GLOBALS['db']->database, OCI_D_LOB);
			    OCIBindByName($stmt, $descriptor, $newlob, -1, $lob_field_type[$key]);
			    $lobs[$key] = $newlob;
			  }

			  oci_execute($stmt,OCI_DEFAULT);
			  $err = oci_error($stmt);
			  if ($err != false){
				  $GLOBALS['log']->fatal($query.">>".$err['code'].":".$err['message']);
				  return;
			  } else {
				  foreach ($lobs as $key=>$lob){
				        $val = $monitor->$key;
				        if (empty($val)) $val=" ";
				        $lob->save($val);
				  }
				  oci_commit($GLOBALS['db']->database);
			  }

			  // free all the lobs.
			  foreach ($lobs as $lob){
			    $lob->free();
			  }
			  oci_freecursor($stmt);
			  //END SUGARCRM flav=pro ONLY
       	  } else {
	          $query = "INSERT INTO $monitor->table_name (" .implode("," , $columns). " ) VALUES ( ". implode("," , $values). ')';
		      $GLOBALS['db']->query($query);
       	  }
       } else {
       	  $query = "UPDATE $monitor->table_name set run_count={$monitor->run_count}, sec_avg={$monitor->sec_avg}, sec_total={$monitor->sec_total}, date_modified='{$monitor->date_modified}' where query_hash = '{$monitor->query_hash}'";
          $GLOBALS['db']->query($query);
       }
    }
}

?>
