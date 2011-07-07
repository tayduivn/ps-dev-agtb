<?php 
$time = microtime(true);
require('Rome.php');
$config = getConfig();

$rome = new Rome();
$exculsive = !empty($config['exclusive']);
if(!empty($config['regions']))$rome->setRegions($config['regions'],$exculsive);
if(!empty($config['deployments']))$rome->setDeployments($config['deployments'], $exculsive);
if(!empty($config['flav']))$rome->setFlav($config['flav']);
elseif(!empty($config['skipFlavs']))$rome->setSkipFlavs($config['skipFlavs']);
if(!empty($config['ver']))$rome->setVersion($config['ver']);

if(!empty($config['build_dir']))$rome->setBuildDir($config['build_dir']);
if(!empty($config['dir']) && is_file($config['dir']) && empty($config['file'])){
	$config['file'] = $config['dir'];
	unset($config['dir']);
}
if(!empty($config['clean'])) {
    if ( !empty($config['file']) )
        foreach ( $config['builds'] as $build )
            $rome->remove($rome->getBuildDir().'/'.$build.'/'.$config['file']);
    elseif ( !empty($config['dir']) )
        foreach ( $config['builds'] as $build )
            $rome->remove($rome->getBuildDir().'/'.$build.'/'.$config['dir']);
    else
        $rome->remove($rome->getBuildDir());
}
if(!empty($config['ver']))$rome->setVersion($config['ver']);
if(!empty($config['cleanCache'])){
    foreach ( $config['builds'] as $build ) {
        $rome->remove($rome->getBuildDir() ."/$build/sugarcrm/cache/jsLanguage");
        $rome->remove($rome->getBuildDir() ."/$build/sugarcrm/cache/modules");
        $rome->remove($rome->getBuildDir() ."/$build/sugarcrm/cache/smarty");
        $rome->remove($rome->getBuildDir() ."/$build/sugarcrm/cache/Expressions");
        $rome->remove($rome->getBuildDir() ."/$build/sugarcrm/cache/themes");
        $rome->remove($rome->getBuildDir() ."/$build/sugarcrm/cache/blowfish");
        $rome->remove($rome->getBuildDir() ."/$build/sugarcrm/cache/dashlets");
    }
} 
if(!empty($config['base_dir'])){
	$config['base_dir'] = realpath($config['base_dir']);
	if(!empty($config['file'])){
		if(file_exists($config['file'])) {
			$config['file'] = realpath($config['file']);
		} else {
			$config['file'] = realpath($config['base_dir'] .'/' .$config['file']);
		}
		$config['file'] = str_replace($config['base_dir']. '/','', $config['file']);
		if(is_file($config['base_dir'] .'/' .  $config['file'])){
			$rome->setStartPath($config['base_dir']);
			echo "Building " . $config['base_dir']  .'/' . $config['file'];
			$rome->buildFile($config['base_dir']  .'/' . $config['file'], "");
		}else{
			echo "Build Stopped.  You entered an invalid file name: ".$config['base_dir']  .'/' . $config['file'];
		}
	}elseif(!empty($config['dir'])){
		if (file_exists($config['dir']))
		{
			$config['dir'] = realpath($config['dir']);
		} else {
            $config['dir'] = realpath($config['base_dir'] .'/' .$config['dir']);
        }
		$config['dir'] = str_replace($config['base_dir'],'', $config['dir']);
		if(is_dir($config['base_dir'] .'/' . $config['dir'])){
			$rome->setStartPath($config['base_dir']);
			echo "Building " . $config['base_dir'] .'/' . $config['dir'];
			$rome->build($config['base_dir'] .'/' . $config['dir']);
		}else{
			echo "Build Stopped.  You entered an invalid directory name: ".$config['base_dir']  .'/' . $config['dir'];
		}
	}
	else{ 
		echo "Building " . $config['base_dir'];
		$path = $config['base_dir'];
		$rome->build("$path");
	}
	if(!empty($config['latin'])){
		echo "\nImporting Languages\n\n";
		require_once('Latin.php');
		$latin = new Latin($rome, $config['languages']['gitPath'], $config['base_dir'], $config['ver']);
		$latin->copyTranslations();
	}
	
}else{
	$rome->throwException("No Base Directory To Build From", true);
}


$total = microtime(true) - $time;
echo "\n\n" . 'TOTAL TIME: ' . $total . "\n";


function getConfig(){
	//if (!isset($_SERVER['argv'])) {
	//	$argv = $_SERVER['argv'];
	//} 
	//$argv = $GLOBALS['argv'];
	
	global $argv;
	include('config/runtime.config.php');
	if(!isset($config))$config = array();
	if(isset($argv)){
		//array_shift($argv);
		foreach($argv as $arg){
			if(substr($arg, 0,2) == '--'){
				$arg = substr($arg, 2,strlen($arg));
			}
			if(substr($arg, 0,1) == '-'){
				$arg = substr($arg, 1,strlen($arg));
			}
			$params = explode('=', $arg);	
			if(count ($params) > 1){
				$config[$params[0]] = $params[1]; 
			}else{
				$config[$params[0]] = true;
			}
			
		}
	}
	$d = dir('config/builds');
	$flavs = array();
    while($e = $d->read()){
        $path = 'config/builds/' . $e;
        if(is_file($path) && substr($e, 0, 6) == 'config'){
        	array_push($flavs, substr($e,7,strpos($e,'.',8)-7));
        }
        
    }
	if (!empty($config['flav'])){
		$f_flag = 0; // 0- no match all pre-config flavs ; 1-match one flav
		foreach($flavs as $f){
			if($config['flav']==$f){
				$f_flag=1;	
				$config['builds'][] = $f;
			}
		}
		if(! $f_flag){
			echo "Build Stopped. You entered an invalid flav name:".$config['flav']."\n";
			exit (1);
		} 
	}else{
		foreach($flavs as $f){
			$config['builds'][] = $f;
		}   	
            
	}  
	return $config;

}
