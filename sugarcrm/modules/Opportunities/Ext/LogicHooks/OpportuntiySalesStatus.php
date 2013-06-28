<?php
//FILE SUGARCRM flav=ent ONLY

/**
 * Define the before_save hook that will set the Opportunity Sales Status in Ent Only
 */
$hook_array['before_save'][] = array(
    1,
    'setSalesStatus',
    'modules/Opportunities/OpportunityHooks.php',
    'OpportunityHooks',
    'setSalesStatus',
);
