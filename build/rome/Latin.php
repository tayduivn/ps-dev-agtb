<?php

class Latin{
	
	function __construct($rome, $translationPath){
		$this->translationPath = $translationPath;
		$this->rome = $rome;
		
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
    						$path = str_replace($this->translationPath . '/', '' , $path);
    						$this->rome->setOnlyOutput($flav);
    						$this->rome->buildFile($path);
    					}
					}
    			}
    		}
		}	
		
	}
	
	function copyTranslations(){
		$this->updateGit();
		$this->copyFiles($this->translationPath);
		chdir($this->cwd);
	} 
	
	
}




?>
