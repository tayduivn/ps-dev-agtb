<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
/**
 * Stub class, exists only to allow Link class easily use the SugarEmailAddress class
 */
class EmailAddress extends SugarEmailAddress
{
	var $disable_row_level_security = true;

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