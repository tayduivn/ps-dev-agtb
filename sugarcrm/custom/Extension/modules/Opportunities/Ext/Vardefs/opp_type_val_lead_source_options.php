<?php

/**
 *  Simple example of creating a dependent dropdown
 *  Sent dwheeler a comment about the fact that I have to define every option in the parent dropdown
 *  As you can see below, the options for '' and 'New Business' are to set the dropdown to the original 
 *    dropdown itself. I should be able to leave those two out, and it shoul do that. However, the 
 *    current behavior is that if the option exists in the parent dropdown, and it isn't set below,
 *    it will remove the child dropdown completely. Bad news bears.
 */


/*
$lead_source_existing = $GLOBALS['app_list_strings']['lead_source_dom']; 
unset($lead_source_existing['Cold Call']); 
unset($lead_source_existing['Self Generated']); 
unset($lead_source_existing['Public Relations']); 
unset($lead_source_existing['Word of mouth']);

$dictionary['Opportunity']['fields']['lead_source']['visibility_grid'] = array(
    'trigger' => 'opportunity_type',
    'values' => array(
        '' => $GLOBALS['app_list_strings']['lead_source_dom'],
        'Existing Business' => $lead_source_existing,
        'New Business' => $GLOBALS['app_list_strings']['lead_source_dom'],
    ),
);
*/
