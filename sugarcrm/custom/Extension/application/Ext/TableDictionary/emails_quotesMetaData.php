<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/





$dictionary['emails_quotes'] = array ('table' => 'emails_quotes',
	'fields' => array (
		array(	'name'		=> 'id',
				'type'		=> 'varchar',
				'len'		=> '36',
		),
		array(	'name'		=> 'email_id',
				'type'		=> 'varchar',	
				'len'		=> '36',
		),
		array(	'name'		=> 'quote_id',
				'type'		=> 'varchar',
				'len'		=> '36',
		),
		array(	'name'		=> 'date_modified',
				'type'		=> 'datetime'
		),
		array(	'name'		=> 'deleted',
				'type'		=> 'bool',
				'len'		=> '1',
				'default'	=> '0',
				'required'	=> true
		),
	),
	'indices' => array (
	    	array('name' =>'emails_quotespk', 'type' =>'primary', 'fields'=>array('id')),
			array('name' =>'idx_quote_email_email', 'type' =>'index', 'fields'=>array('email_id')),
			array('name' =>'idx_quote_email_quote', 'type' =>'index', 'fields'=>array('quote_id')),
	),
/* added to support InboundEmail */
	'relationships' => array (
		'emails_quotes' => array(
			'lhs_module'		=> 'Emails',
			'lhs_table'			=> 'emails',
			'lhs_key'			=> 'id',
			'rhs_module'		=> 'Quotes',
			'rhs_table'			=> 'quotes',
			'rhs_key'			=> 'id',
			'relationship_type'	=> 'many-to-many',
			'join_table'		=> 'emails_quotes',
			'join_key_lhs'		=> 'email_id',
			'join_key_rhs'		=> 'quote_id'
		)
	)
);

?>
