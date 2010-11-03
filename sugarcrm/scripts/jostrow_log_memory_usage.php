<?php
$jostrow_logfile = '/var/www/sugarinternal/logs/jostrow_log_peak_memory.log';

@register_shutdown_function('jostrow_log_peak_memory');

function jostrow_log_peak_memory() {
	global $jostrow_logfile;

	$str = date('Y-m-d H:i:s') . "\t" . $_SERVER['PWD'] . '/' . $_SERVER['SCRIPT_NAME'] . "\t" . memory_get_peak_usage() . "\n";

	$fp = @fopen($jostrow_logfile, 'ab');
	@fwrite($fp, $str);
	@fclose($fp);
}
