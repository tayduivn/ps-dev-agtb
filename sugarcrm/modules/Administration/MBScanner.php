<?php
//FILE SUGARCRM flav=int ONLY
class MBScanner{
	
	static function findBuiltModules(){
		$list = array();
		$path = 'modules';
		if(!is_dir($path))return $list;
		$d = dir($path);
	
		while($e = $d->read()){
			$module_path = $path . '/'. $e;
			if(is_dir($module_path)){
				$f = dir($module_path);
				while($g = $f->read()){
					if(substr($g, 0, 1) == '.')continue;
					$file_path = $module_path . '/' . $g;
					if(is_file($file_path) && substr_count( $g, '_sugar.php') == 1){
						
						$list[] = $e;
					}
					
				}
			}
			
		}
		return $list;
	}
	
}

?>
