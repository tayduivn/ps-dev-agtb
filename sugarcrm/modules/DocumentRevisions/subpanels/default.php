<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * Subpanel Layout definition for Leads
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
 */

// $Id: default.php 52533 2009-11-18 01:32:20Z clee $
$subpanel_layout = array(
	'top_buttons' => array(
        array('widget_class' => 'SubPanelTopCreateRevisionButton'),
	),

	'where' => '',


	'list_fields' => array(
		  'filename' => 
		  array (
		    'vname' => 'LBL_REV_LIST_FILENAME',
		    'widget_class' => 'SubPanelDetailViewLink',
		    'width' => '15%',
		    'default' => true,
		  ),
		  'revision' => 
		  array (
		    'vname' => 'LBL_REV_LIST_REVISION',
		    'width' => '5%',
		    'default' => true,
		  ),
		  'created_by_name' => 
		  array (
		    'vname' => 'LBL_REV_LIST_CREATED',
		    'width' => '25%',
		    'default' => true,
		  ),
		  'date_entered' => 
		  array (
		    'vname' => 'LBL_REV_LIST_ENTERED',
		    'width' => '10%',
		    'default' => true,
		  ),
		  'change_log' => 
		  array (
		    'vname' => 'LBL_REV_LIST_LOG',
		    'width' => '35%',
		    'default' => true,
		  ),
		  'del_button' => 
		  array (
		    'vname' => 'LBL_DELETE_BUTTON',
		    'widget_class' => 'SubPanelRemoveButton',
		    'width' => '5%',
		    'default' => true,
		  ),
		  'document_id' => 
		  array (
		    'usage' => 'query_only',
		  ),
	),
);
?>