<?php
require_once('include/utils.php');
require_once('include/utils/file_utils.php');

global $sugar_config;
$sugar_config_new = sugarArrayMerge(get_sugar_config_defaults(), $sugar_config);

if(is_writable("config.php")) {
   write_array_to_file("sugar_config", $sugar_config, "config.bak"); 		
   write_array_to_file("sugar_config", $sugar_config_new, "config.php");
}
?>
