<?php

class Latin{
	
	function __construct($rome, $translationPath, $baseDir){
		$this->translationPath = $translationPath;
		$this->rome = $rome;
		$this->baseDir = realpath($baseDir);
		if(empty($this->startPath))$this->startPath = $this->baseDir;
		
	}
	
	function updateGit(){
		 if(!file_exists($this->translationPath)){
                        chdir(dirname($this->translationPath));
                        passthru("git clone git@github.com:sugarcrm/translations");
                }
		$this->cwd = getcwd();
		chdir($this->translationPath);
		passthru("git pull origin master");
	}
	
	function copyFiles($path){
		$dir = new DirectoryIterator($path);
		foreach ($dir as $fileInfo) {
    		if($fileInfo->isDot()) continue;
    		if($fileInfo->isDir()){
    			 $this->copyFiles($fileInfo->getPathname());
    		}else{
    			foreach($this->rome->config['builds'] as $flav=>$build){
    				
					if(empty($build['languages']))continue;
					foreach($build['languages'] as $lang){
	   					if(strpos($fileInfo->getFilename(), $lang. '.') !== false){
    						$path = $fileInfo->getPathname();
    						
    						//$config['file'] = $path;
	   						//$config['file'] = realpath($config['file']);
							//$config['file'] = str_replace($config['base_dir']. '/','', $config['file']);
							//if(is_file($config['base_dir'] .'/' .  $config['file'])){
								//$rome->setStartPath($config['base_dir']);
								//echo "Building " . $config['base_dir']  .'/' . $config['file'];
								//$this->rome->setOnlyOutput($flav);
								//$rome->buildFile($config['base_dir']  .'/' . $config['file']);
							//}
    						    						
    						//$path = str_replace($this->translationPath . '/', '' , $path);
    						$path = realpath($path);
    						$path = str_replace($this->baseDir . '/','', $path);
    						$this->rome->setOnlyOutput($flav);
    						$this->rome->buildFile($this->baseDir . '/' . $path, $this->startPath);
    					}
					}
    			}
    		}
		}	
		
	}
	
	function copyTranslations(){
		$this->updateGit();
		$tmp_path=realpath("$this->cwd" ."/". "$this->translationPath");
		$this->copyFiles($tmp_path);
		chdir($this->cwd);
	} 
	
	
}




?>
