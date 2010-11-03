<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class EscalationHooks  {
/*
** @author: dtam
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #: 18747
** Description: autoset escalation type to case if from support or CA and not set to case, else if not bug set it to bug
*/
	function updateType(&$bean, $event, $arguments) {
	  if ($event == "before_save") {
	    // if source is not set to case case but from support or ca update it 
	    if ($bean->source == 'Sales' || $bean->source == 'Customer Advocacy') {
	      $bean->type_c = 'Case';
	    } else {
	      $bean->type_c = 'Bug';
	    }
	  }
	}
/* END SUGARINTERNAL CUSTOMIZATION */
}
