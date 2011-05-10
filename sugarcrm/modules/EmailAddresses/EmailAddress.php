<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
/*********************************************************************************
 * $Id: Delete.php,v 1.22 2006/01/17 22:50:52 majed Exp $
 * Description:
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc. All Rights
 * Reserved. Contributor(s): ______________________________________..
 *********************************************************************************/



/**
 * Stub class, exists only to allow Link class easily use the SugarEmailAddress class
 */
class EmailAddress extends SugarEmailAddress 
{
	var $disable_row_level_security = true;

	function EmailAddress() 
	{
		parent::SugarEmailAddress();	
	}
	
	function save($id = '', $module = '', $new_addrs=array(), $primary='', $replyTo='', $invalid='', $optOut='', $in_workflow=false) 
	{
		if ( func_num_args() > 1 ) {
		    parent::save($id, $module, $new_addrs, $primary, $replyTo, $invalid, $optOut, $in_workflow);
		}
		else {
		    SugarBean::save($id);
		}
	}
}