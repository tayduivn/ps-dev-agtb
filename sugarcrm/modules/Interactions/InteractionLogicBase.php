<?php
require_once('include/utils.php');

class InteractionLogicBase 
{
	public $module = '';
	
	public function updateInteraction(
        SugarBean $bean, 
        $event, 
        $arguments) 
    {
    }

	public function installHook(
        $file,
        $className
        )
    {
		check_logic_hook_file($this->module, "after_save", array(1, $this->module . " update interaction",  $file, $className, "updateInteraction"));
	}

    public function removeHook(
        $file,
        $className
        )
    {
		remove_logic_hook($this->module, "after_save", array(1, $this->module . " update interaction",  $file, $className, "updateInteraction"));        
    }
}
