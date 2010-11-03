<?php
require_once('modules/Interactions/InteractionLogicBase.php');
require_once('modules/Interactions/Interaction.php');
require_once('modules/Touchpoints/ScrubHelper.php');

class TouchpointsInteraction extends InteractionLogicBase 
{
    public $module = "Touchpoints";

    public function updateInteraction(
        SugarBean $bean, 
        $event, 
        $arguments
        )
    {
        // commented out so it would never run but just incase the function runs again.
        // jwhitcraft 6.8.10
        /*$GLOBALS['log']->info('processing updateInteraction() logic hook for Touchpoints');
        
        $interactionFocus = new Interaction;
        $interactionFocus->retrieve_by_string_fields(
            array(
                'source_id' => $bean->id,
                'source_module' => $bean->module_dir
                ), 
            false);
        if ( !empty($interactionFocus->id) ) {
            $interactionFocus->score = $bean->score;
            $interactionFocus->name = $bean->get_summary_text();
            $interactionFocus->save(false);
        }*/
    }
}