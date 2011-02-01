<?php
require_once 'modules/Calls/Call.php';

class SugarTestCallUtilities
{
    private static $_createdCalls = array();

    private function __construct() {}

    public static function createCall($id = '') 
    {
        $time = mt_rand();
    	$call = new Call();
        $call->name = "SDizzle" . $time;
        $call->duration_hours = '0';
        $call->duration_minutes = '30';
        $call->date_start = $GLOBALS['timedate']->to_display_date(gmdate('Y-m-d'));
        $call->direction = 'Outbound';
        $call->status = 'Planned';
        if(!empty($id))
        {
            $call->new_with_id = true;
            $call->id = $id;
        }
        $call->save();
        self::$_createdCalls[] = $call;
        return $call;
    }

    public static function setCreatedCall($call_ids) {
    	foreach($call_ids as $call_id) {
    		$call = new Call();
    		$call->id = $call_id;
        	self::$_createdCalls[] = $call;
    	} // foreach
    } // fn
    
    public static function removeAllCreatedCalls() 
    {
        $call_ids = self::getCreatedCallIds();
        $GLOBALS['db']->query('DELETE FROM calls WHERE id IN (\'' . implode("', '", $call_ids) . '\')');
    }
    
    public static function getCreatedCallIds() 
    {
        $call_ids = array();
        foreach (self::$_createdCalls as $call) {
            $call_ids[] = $call->id;
        }
        return $call_ids;
    }
}
?>