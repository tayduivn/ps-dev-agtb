<?php

require_once('modules/Calendar/Calendar.php');
require_once('modules/Calendar/CalendarDisplay.php');
require_once("modules/Calendar/CalendarGrid.php");
require_once("modules/Calendar/utils.php");

global $cal_strings, $app_strings, $app_list_strings, $current_language, $timedate, $sugarConfig;

$cal_strings = return_module_language($current_language, 'Calendar');


?>
