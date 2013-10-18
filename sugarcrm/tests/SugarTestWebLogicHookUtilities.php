<?php
//FILE SUGARCRM flav=pro ONLY
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

require_once 'modules/WebLogicHooks/WebLogicHook.php';

class SugarTestWebLogicHookUtilities
{
    private static  $_createdWebLogicHooks = array();

    private function __construct() {}

    public static function createWebLogicHook($id = '', $attributes = array())
    {
    	$webLogicHook = new WebLogicHookMock();

    	foreach ($attributes as $attribute=>$value) {
    		$webLogicHook->$attribute = $value;
    	}

    	if(!empty($id))
        {
            $webLogicHook->new_with_id = true;
            $webLogicHook->id = $id;
        }

    	$webLogicHook->save();
        $GLOBALS['db']->commit();
        self::$_createdWebLogicHooks[] = $webLogicHook;
        return $webLogicHook;
    }

    public static function removeAllCreatedWebLogicHook()
    {
    	$hook_ids = self::getCreatedWebLogicHookIds();
        foreach (self::$_createdWebLogicHooks as $hook) {
            $hook->mark_deleted($hook->id);
        }
        $GLOBALS['db']->query('DELETE FROM web_logic_hooks WHERE id IN (\'' . implode("', '", $hook_ids) . '\')');
        WebLogicHookMock::$dispatchOptions = null;
    }

    public static function getCreatedWebLogicHookIds()
    {
    	$hook_ids = array();
        foreach (self::$_createdWebLogicHooks as $hook) {
            $hook_ids[] = $hook->id;
        }
        return $hook_ids;
    }
} 


class WebLogicHookMock extends WebLogicHook
{
    public static $dispatchOptions = null;

    protected function getActionArray()
    {
        return array(1, $this->name, 'tests/SugarTestWebLogicHookUtilities.php', __CLASS__, 'dispatchRequest', $this->id);
    }

    public function dispatchRequest($seed, $event, $arguments, $id)
    {
        self::$dispatchOptions = array(
            'seed' => $seed,
            'event' => $event,
            'arguments' => $arguments,
            'id' => $id,
        );
    }
}
