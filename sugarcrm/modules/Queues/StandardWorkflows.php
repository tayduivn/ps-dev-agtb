<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
//FILE SUGARCRM flav=int ONLY
global $current_language;
// setting mod strings differently since this object could be called from another bean
$this_mod_strings = return_module_language($current_language, 'Queues');

$standards = array ('none'			=> array (	'name'		=> $this_mod_strings['DOM_LBL_NONE'],
												'function'	=> 'doNothing();'),	
					'roundRobin'	=> array (	'name'		=> $this_mod_strings['LBL_WF_ROUNDROBIN'],
												'function'	=> 'roundRobin($beanId, $beanName);'),
					'leastBusy'		=> array (	'name'		=> $this_mod_strings['LBL_WF_LEASTBUSY'],
												'function'	=> 'leastBusy($beanId, $beanName);'),
					'manualPick'	=> array (	'name'		=> 'Manual Pick Distribution',
												'function'	=> 'manualPick();'
									   ),
			 );


				
?>
