<?php
/**
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
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */

 // $Id: listvardefoverride.php 53116 2009-12-10 01:24:37Z mitani $
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
//THIS IS TO FIX ANY VARDEFS IN CREATING LIST QUERIES (specifically relationships)
if(isset($this->field_defs['assigned_user_name'])){
	$this->field_defs['assigned_user_name'] =  array (
	    'name' => 'assigned_user_name',
	    'rname'=>'user_name',
	    'vname' => 'LBL_ASSIGNED_TO',
	    'type' => 'relate',
	    'reportable'=>false,
	    'source'=>'non-db',
	    'link'=>'assigned_user_link',
		'id_name' => 'assigned_user_id',
		'massupdate' => FALSE
	  );
}
if(isset($this->field_defs['created_by'])){
	$this->field_defs['created_by_name'] =  array (
	    'name' => 'created_by_name',
	    'rname'=>'user_name',
	    'vname' => 'LBL_CREATED',
	    'type' => 'relate',
	    'reportable'=>false,
	    'source'=>'non-db',
	    'link'=>'created_by_link'
	  );
}
if(isset($this->field_defs['modified_user_id'])){
	$this->field_defs['modified_user_name'] =  array (
	    'name' => 'modified_user_name',
	    'rname'=>'user_name',
	    'vname' => 'LBL_MODIFIED',
	    'type' => 'relate',
	    'reportable'=>false,
	    'source'=>'non-db',
	    'link'=>'modified_user_link'
	  );
	$this->field_defs['modified_by_name'] =  array (
	    'name' => 'modified_user_name',
	    'rname'=>'user_name',
	    'vname' => 'LBL_MODIFIED',
	    'type' => 'relate',
	    'reportable'=>false,
	    'source'=>'non-db',
	    'link'=>'modified_user_link'
	  );
}




?>
