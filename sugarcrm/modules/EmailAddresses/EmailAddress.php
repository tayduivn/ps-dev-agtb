<?php

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

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
