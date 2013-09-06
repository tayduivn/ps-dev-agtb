<?php
//FILE SUGARCRM flav=ent ONLY

/**
 * Define the before_save hook that will set the Opportunity currency to base
 */
$hook_array['before_save'][] = array(
    1,
    'setCurrencyToBase',
    'modules/Opportunities/OpportunityHooks.php',
    'OpportunityHooks',
    'setCurrencyToBase',
);
