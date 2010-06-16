<?php
class SugarTestTrackerUtility
{
    private static $_trackerSettings = array();
    
    private function __construct() {}
    
    public static function setup()
    {
        require('modules/Trackers/config.php');
        foreach($tracker_config as $entry) {
            if(isset($entry['bean'])) {
                $GLOBALS['tracker_' . $entry['name']] = false;
            } //if
        } //foreach
        
        $result = $GLOBALS['db']->query("SELECT category, name, value from config WHERE category = 'tracker' and name != 'prune_interval'");
        while($row = $GLOBALS['db']->fetchByAssoc($result)){
            self::$_trackerSettings[$row['name']] = $row['value'];
            $GLOBALS['db']->query("DELETE FROM config WHERE category = 'tracker' AND name = '{$row['name']}'");
        }
    }
    
    public static function restore()
    {
        foreach(self::$_trackerSettings as $name=>$value) {
            $GLOBALS['db']->query("INSERT INTO config (category, name, value) VALUES ('tracker', '{$name}', '{$value}')");
        }
    }
}
?>
