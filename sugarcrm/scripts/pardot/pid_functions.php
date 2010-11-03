<?php

function script_make_pid($filename){
	if(empty($filename)){
		$path = __FILE__;
		$break = explode('/', $path);
		$filename = $break[count($break) - 1];
	}
	
	$pid_file = "/tmp/{$filename}.pid";
	
	if(file_exists($pid_file)){
        $pid = file_get_contents($pid_file);
        if(check_pid($pid)) {
            // still running
            exit;
        } else {
            // not running any more so delete the pid file
            script_clear_pid($filename);
        }
		#trigger_error("pid file exists: {$pid_file}", E_USER_ERROR);
		#exit;
	}
	
	$pid_cmd = sprintf("echo %d > %s", posix_getpid(), $pid_file);
	shell_exec($pid_cmd);
}

function script_clear_pid($filename){
	if(empty($filename)){
		$path = __FILE__;
		$break = explode('/', $path);
		$filename = $break[count($break) - 1];
	}
	
	$pid_file = "/tmp/{$filename}.pid";
	
	$pid_cmd = sprintf("rm -rf %s", $pid_file);
	shell_exec($pid_cmd);
}

function check_pid($pid){
    $cmd = sprintf("ps %d", $pid);
    exec($cmd, $output, $result);

    if(count($output) >= 2){
        return true;
    }

    return false;
}
