<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
//FILE SUGARCRM flav=int ONLY
if($_SERVER['SERVER_ADDR'] != $_SERVER['REMOTE_ADDR']) { // make sure this script only gets executed locally
	header('Location: index.php?action=Login&module=Users');
	return;
}






$GLOBALS['log'] = LoggerManager::getLogger('SugarCRM');
error_reporting(E_ALL ^ E_NOTICE);

class SecurityAudit {
	var $appRoot;
	var $absoluteRoot;
	var $curlControl;
	var $docRoot;
//	var $arrayActions;
	var $arrayAllFiles;
	var $arrayFileNames;
	var $arrayHackUrls;
	var $arrayHackUrlsWithRecs;
	var $arrayHackUrlsNonModule;
	var $arrayIds;
	var $arrayModules;
	var $arrayModulesPhp;	// actual module class
	var $db;
	var $httpRoot;
	var	$modulesRoot;
	var $pattern;
	var $returnLimit = '5';
	var $reverseDepth = -1000;
	
	function SecurityAudit() {
		// do something here
		global $sugar_config;
		$cfg = $sugar_config;
		/* setup Database bits */
		$db = new DBManagerFactory();
		$db->setDatabaseHost($cfg['dbconfig']['db_host_name']);
		$db->setUserName($cfg['dbconfig']['db_user_name']);
		$db->setUserPassword($cfg['dbconfig']['db_password']);
		$db->setDatabaseName($cfg['dbconfig']['db_name']);
		$db->setDatabaseType($cfg['dbconfig']['db_type']);
		$this->db 					= $db;

		$this->appDir 				= "/sugarcrm/";
		$this->docRoot				= $_SERVER['DOCUMENT_ROOT'];
		$this->absoluteRoot			= $this->docRoot.$this->appDir;
		$this->buildDirectoryPath($this->absoluteRoot);
		
		$this->setModulesRoot("modules/");
		$this->setHttpRoot();
		$this->setArrayModules();
//		$this->setArrayActions();
		$this->setArrayIds();
		$this->getHackUrls();
		
		$this->setArrayHackUrlsNonModule();
		$this->setCurlControl();
		
		_pp($this->arrayAllFiles);
	}
	
	function setHttpRoot() {
		global $sugar_config;
		//$httpRoot = str_replace($_SERVER['PHP_SELF'],'',$_SERVER['SCRIPT_URI']);
		$httpRoot = $sugar_config['site_url'];
		$this->httpRoot = $httpRoot;
	}
	
	function setModulesRoot($modulesRoot) {
		$this->modulesRoot = $modulesRoot;
	}
	
	function setArrayIds() {
		$query = 'SELECT DISTINCT id FROM ';
	
		foreach($this->getArrayModules() as $k=>$mod) {
			if(is_file("modules/".$mod."/".$mod)) {
				require_once("modules/".$mod."/".$mod);
			} elseif(is_file("modules/".$mod."/".$mod)) {
				require_once("modules/".$mod."/".$mod);
			} else {
				continue;
			}
			
			$ids[$mod][0] = null;
			$GLOBALS['log']->debug("[SECURITYAUDIT][QUERY] ".$query.strtolower($mod).' LIMIT '.$this->returnLimit);
			$result = $this->db->query($query.strtolower($mod).' LIMIT '.$this->returnLimit);
			while($data = $this->db->fetchByAssoc($result)) {
				$ids[$mod][] = $data['id'];
			}
		}
		
		$this->arrayIds = $ids;
	}
	
	function setArrayModules() {
		$modulesPath = $this->docRoot.$this->appDir.$this->modulesRoot;
		//echo $modulesPath."<br>";
		if(is_dir($modulesPath)) {
			$dir = opendir($modulesPath);
		
			while(($pointer = readdir($dir)) !== false) {
				if(is_dir($modulesPath.$pointer) && $pointer != '.' && $pointer != '..' && $pointer != 'CVS') {
					$this->arrayModules[] = $pointer;
					
					// now get actual module class file
					$modDir = opendir($modulesPath.$pointer); // in a /modules/[x]/ dir
					while(($phpFile = readdir($modDir)) !== false) {
						if(substr($pointer, 5) == substr($modulesPath.$pointer.$phpFile, 5)) {
							$this->arrayModulesPhp[] = $phpFile;
						}
					}
				}
			}
		}
		else {
			die("modulesRoot is not a directory.");
		}
		
		sort($this->arrayModules);
	}
	
/*	function setArrayActions() {
		$this->arrayActions = array('index','DetailView','EditView','ListView','Login','Logout','Menu','Popup','Save',);
	}
*/
	function getArrayModules() {
		if(!empty($this->arrayModules)) {
			return $this->arrayModules;	
		} else {
			$this->setArrayModules();
			return $this->arrayModules;
		}
	}

	function getHackUrls() {
		
		foreach($this->arrayAllFiles as $k =>$file) {
			
			
		}
		
/*		$indexMod = 0;
		$indexAct = 0;
		$indexRec = 0;
		$url = array();
		$urlNoIds = array();
		
		$urlRoot = $this->httpRoot."/index.php?module=";

		foreach($this->arrayModules as $k2 => $module) {
			foreach($this->arrayActions as $k => $action) {
				$moduleRecs = $this->arrayIds[$module];
				$urlNoIds[$indexMod][$indexAct] = $module."||".$urlRoot.$module."&action=".$action;
				
				//echo $module." has ".count($moduleRecs)." record ids<br>";
				if($moduleRecs[0] != null) {
					foreach($moduleRecs as $k3 => $record) {
						$url[$indexMod][$indexAct][$indexRec] =  $module."||".$urlRoot.$module."&action=".$action."&record=".$record;
						$indexRec++;
					}
				}
				$indexAct++;
				$indexRec = 0;
			}
			$indexMod++;
			$indexAct = 0;
			//echo $url[$indexAct][$indexMod][$indexRec];
		}
		$this->arrayHackUrls = $urlNoIds;
		$this->arrayHackUrlsWithRecs = $url;
		_pp($this->arrayHackUrls);
		_ppd($this->arrayHackUrlsWithRecs);*/
	}
	
	function curlThisUrl($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url); // set url 
		curl_setopt($ch, CURLOPT_FAILONERROR, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); // return into a variable
		curl_setopt($ch, CURLOPT_TIMEOUT, 30); // times out after 29s
		$result = curl_exec($ch);
		curl_close($ch);
		
		return $result;
	}

	function setCurlControl() {
		$controlResult = $this->curlThisUrl($this->httpRoot."/index.php");
		$this->curlControl = substr($controlResult, $this->reverseDepth); // return last 1000 chars.	
	}	
	
	

	function buildDirectoryPath($path) {
		$d = dir($path);
    	while($entry = $d->read()){        
	    	if($entry != '..' && $entry != '.'){
        		if(is_dir($path. '/'. $entry)){
	            	$this->buildDirectoryPath($path. '/'. $entry);        
            	} else {
            		
            		if((substr($path.'/'.$entry, -3) == 'php' || (substr($path."/".$entry, 3) == 'log')) ) { 
            		/*|| substr($path.'/'.$entry, -4) == 'html'*/
            			$this->buildFilePath($path, $entry);
            		}
				}
	    	}
    	}
	}

	function buildFilePath($path, $file){
		global $sugar_config;
		_pp($path);
        $this->arrayAllFiles[] = array('path'=>$path, 'relPath'=>str_replace($this->absoluteRoot, '', $path), 'file'=>$file);
       // print_r($this->arrayAllFiles);
	}

	function setArrayHackUrlsNonModule() {
		foreach($this->arrayAllFiles as $k => $set) {
			$this->arrayHackUrlsNonModule[] = $this->httpRoot.$set['relPath'].'/'.$set['file'];
		}
		
	}
	
	
	function doModuleAudit($withRecs=false) {
		$badHack = array();
		$badReturn = 0;
		$goodReturn = 0;
		$idxMod = 0;
		$idxAct = 0;
		$idxRec = 0;
		$loop = array();
		
		
		// start output buffer
		ob_start();
		$startTime = microtime(true);
		echo "<pre>";
		echo "Starting at: ".date('H:i:s')."<br>";
		ob_flush();
		flush();
		
		if($withRecs) {
			$loop = $this->arrayHackUrlsWithRecs;
		} else {
			$loop = $this->arrayHackUrls;
		}
		
		// with IDs has 1 more degree of array dimension
		foreach($loop as $k => $url) { // 2 levels of array depth
			$loopTime = microtime(true);
			foreach($url as $l => $serial) {
				// we are going to build URLs with action and record IDs
				if(isset($serial[0])) {
					foreach($serial as $m => $serialId) {
						$modPlusUrl = explode('||', $serialId);
						$module = $modPlusUrl[0];
						$curlMe = $modPlusUrl[1];
						$hackResult = $this->curlThisUrl($curlMe);
						if(	$this->checkAgainstKnownGood($hackResult) ) {
							$goodReturn++;
						} else {
							$badReturn++;
							echo "<a href='".$curlMe."' target='_blank'>".$curlMe."</a><br>";
							ob_flush();
							flush();
						}
					} // end 'with IDs' foreach;
					
				} else {
					$modPlusUrl = explode('||', $serial);
					$module = $modPlusUrl[0];
					$curlMe = $modPlusUrl[1];
					$hackResult = $this->curlThisUrl($curlMe);
					if(	$this->checkAgainstKnownGood($hackResult) ) {
						$goodReturn++;
					} else {
						$badReturn++;
						echo "<a href='".$curlMe."' target='_blank'>".$curlMe."</a><br>";
						ob_flush();
						flush();
					}
				}
			}
			echo "<br>    Module ".$module." took: ". microtime(true) - $loopTime." s";
			ob_flush();
			flush();
		}
		
		echo "<br><br>Total Time: ".microtime(true) - $startTime;
		echo "<br>Tested ".$goodReturn." secure/good URLs";
		echo "<br>Found ".$badReturn." insecure/bad URLs";
		
		
		echo "</pre>";
		ob_end_flush();
		ob_clean();
		
	}
	
	function doPhpHtmlAudit() {
        global $timedate;
		// start output buffer
		ob_start();
		$badHack = array();
		$returned = '';
		$startTime = $timedate->getNow()->ts;
		$idx = 0;
		$goodReturn = 0;
		
		echo "<pre>";
		echo "Starting at: ".date('H:i:s', $startTime)."<br>";
		ob_flush();
		flush();
		
		foreach($this->arrayHackUrlsNonModule as $k => $url) {
			$returned = $this->curlThisUrl($url);
			if(	$this->checkAgainstKnownGood($returned) ) {
				$goodReturn++;
			} else {
				echo ".";
				$badHack[] = $url;
				ob_flush();
				flush();
			}
			$idx++;
		}

		echo "<br>totalTime: ".date('H:i:s', (mktime() - $startTime))."<br>";
		
		print_r($badHack);
		
		echo "<br>Checked ".$idx."files";
		echo "<br>Found ".$goodReturn." good security points.";
		
		echo "</pre>";
		ob_end_flush();
		//ob_clean();		
	}
	
	function checkHtaccess() {
	// check the .htaccess files exist and that httpd.conf allowOverride is on	
	}
	
	function checkAgainstKnownGood($check) {
		if(	(trim($check) == "") || 
			(substr_count($check, 'Unable to process script directly.') > 0) ||
			(substr_count($check, '<html><body></body></html>') > 0) || 
			(substr_count($check, '<td scope="row">Password:</td>') > 0) ||
			(substr_count($check, 'Warning: main') > 0) || 
			(substr_count($check, 'No Access') > 0)
		) {
			// safe/secure return
			return true;
		} else {
			// something unexpected, possible entry point
			return false;
		}
	}
} // end class


$audit = new SecurityAudit();
//$audit->getFileList("/opt/lampp/htdocs/sugarcrm","/opt/lampp/htdocs/sugarcrm");

//$audit->doPhpHtmlAudit(	);
//$audit->doModuleAudit(true);

echo "<pre>";

//print_r($GLOBALS);
//print_r($_SERVER);
//print_r($audit->arrayModules);
//print_r($audit->arrayActions);
//print_r($audit->arrayIds);
//print_r($audit->arrayHackUrls);
//print_r($audit->arrayHackUrlsWithRecs);
//print_r($audit->arrayAllFiles);
//print_r($audit->arrayHackUrlsNonModule);
echo "</pre>";

//print_r($audit->arrayAllFiles);
//$audit->curlThisUrl("http://127.0.0.1/sugarcrm/index.php?module=Tasks&action=Popup&record=130b3985-5228-55e1-4400-432b6b39961a");

?>
