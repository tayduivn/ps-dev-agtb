<?php

class IBMDebug {
	public static function dpb($print = true, $die = false, $full = false){
		$backtrace = debug_backtrace();
		if(!$full){
			foreach($backtrace as $k => $a){
				if(isset($a['args'])){
					unset($backtrace[$k]['args']);
				}
				if(isset($a['object'])){
					unset($backtrace[$k]['object']);
				}
			}
		}
		if($print){
			var_dump($backtrace);
			if($die) die();
		}
		else{
			return $backtrace;
		}
	}
}
