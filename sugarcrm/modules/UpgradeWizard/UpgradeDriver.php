<?php
/**
 * Upgrader main driver class
 * @api
 */
abstract class UpgradeDriver
{
    /**
     * If upgrade is successful
     * @var bool
     */
    public $success = true;
    /**
     * Execution context
     * zip - ZIP file
     * temp_dir - temporary directory
     * source_dir - Sugar source dir
     * new_source_dir - directory where new Sugar source files are stored
     * admin - Admin user
     * log - Log file
     * CLI:
     * php - PHP binary
     * Shadow:
     * pre_template - old template
     * post_template - new template
     * @var array
     */
    public $context;

    /**
     * Upgrade manifest
     * @var array
     */
    public $manifest;

    /**
     * Loaded $sugar_config
     * @var array
     */
    public $config;

    /**
     * Version being upgraded
     * @var sring
     */
    public $from_version;
    /**
     * Flavor being upgraded
     * @var sring
     */
    public $from_flavor;
    /**
     * Version to which we upgrade
     * @var sring
     */
    public $to_version;
    /**
     * Flavor to which we upgrade
     * @var sring
     */
    public $to_flavor;

    /**
     * Upgrade state
     * - old_version - Old version & flavor
     * - old_modules - Pre-upgrade module list
     * - stages - Stages success
     * - scripts - Scripts execution status
     * - files_to_delete - Files that upgrade scripts requested to be deleted
     * @var array
     */
    public $state = array();

    /**
     * Current stage
     * @var string
     */
    public $current_stage;

    const STATE_FILE = "upgrade_state";

    public $sugar_initialized = false;

    /**
     * Launches the next stage
     * @param string $stage
     */
    abstract public function runStage($stage);

    /**
     * Copy data files
     */
    protected function commit()
    {
    	$this->manifest = $this->dataInclude("{$this->context['temp_dir']}/manifest.php", 'manifest');
        if(empty($this->manifest['copy_files']['from_dir'])) {
            return false;
        }
        $zip_from_dir = $this->context['temp_dir']."/".$this->manifest['copy_files']['from_dir'];
        $target_dir = $this->context['source_dir'];
        $files = $this->findFiles($zip_from_dir);
        foreach($files as $file) {
            $this->log("Copying $file");
            $this->ensureDir(dirname("$target_dir/$file"));
            if(!copy("$zip_from_dir/$file", "$target_dir/$file")) {
                return $this->error("Failed to copy: $file");
            }
        }
        return true;
    }

    /**
     * Load stored state
     */
    protected function loadState()
    {
        $statefile = $this->cacheDir('upgrades/').self::STATE_FILE;
        if(file_exists($statefile)) {
            $state = array();
            include $statefile;
            $this->state = $state;
        }
    }

    /**
     * Save stored state
     */
    protected function saveState()
    {
        $statefile = $this->cacheDir('upgrades/').self::STATE_FILE;
        $data = "<?php \$state = ".var_export($this->state, true).";";
        file_put_contents($statefile, $data);
    }

    /**
     * Clean Sugar cache directories:
     * Rebuild autoloader cache
     * Clean smarty cache
     * modules cache
     * themes cache
     * jsLanguage cache
     */
    public function cleanCaches()
    {
        if(is_callable(array('SugarAutoLoader', 'buildCache'))) {
            SugarAutoLoader::buildCache();
        } else {
            // delete dangerous files manually
            @unlink("cache/file_map.php");
        }
        $this->cleanDir($this->cacheDir("smarty"));
        $this->cleanDir($this->cacheDir("modules"));
        $this->cleanDir($this->cacheDir("jsLanguage"));
        $this->cleanDir($this->cacheDir("Expressions"));
        $this->cleanDir($this->cacheDir("themes"));
    }

    /**
     * Execution will start here
     * This function must form context, create a class and run it
     */
    static public function start()
    {
        die("Must override this function in a driver");
    }

    public function __construct($context)
    {
        $this->context = $context;
        $this->loadConfig();
    	$this->context['temp_dir'] = $this->cacheDir("upgrades/temp");
        $this->ensureDir($this->context['temp_dir']);
        $this->loadState();
    }

    /**
     * Display error
     * @param string $msg
     * @returns false
     */
    public function error($msg)
    {
        $this->log("ERROR: $msg");
        $this->fail();
        return false;
    }

    /**
     * Log message
     * @param string $msg
     */
    public function log($msg)
    {
        if(empty($this->fp)) {
            $this->fp = @fopen($this->context['log'], 'a+');
        }
        if(empty($this->fp)) {
            die("Cannot open logfile: {$this->context['log']}");
        }

        fwrite($this->fp, date('r').' [Upgrader] - '.$msg."\n");
    }

    /**
     * Ensure directory exists
     * @param string $dir
     * @throws Exception
     */
    public function ensureDir($dir)
    {
        if(!is_dir($dir)) {
            mkdir($dir, 0770, true);
        }
        if(!is_dir($dir)) {
            throw new Exception("Unable to create directory: $dir");
        }
    }

    /**
     * Remove all files in the directory
     * @param string $dir
     */
    public function cleanDir($dir)
    {
        $files = $this->findFiles($dir);
        foreach($files as $file) {
            unlink("$dir/$file");
        }
    }

    /**
     * Copy directory to directory
     * @param string $path
     * @param string $pathto
     * @return boolean
     */
    public function copyDir($path, $pathto)
    {
        if(!is_dir($path)) {
            return copy($path, $pathto);
        } else {
            $this->ensureDir($pathto);
            $status = true;
            $d = dir( $path );
            if($d === false) {
                return false;
            }
            while(false !== ($f = $d->read())) {
                if( $f == "." || $f == ".." ) {
                    continue;
                }
                $status &= $this->copyDir( "$path/$f", "$pathto/$f" );
            }
            $d->close();
            return $status;
        }
    }

    /**
     * Remove directory with all files in it
     * @param string $path
     * @return boolean
     */
    public function removeDir($path)
    {
        if(is_file( $path)) {
        	return unlink($path);
        }
        if(!is_dir($path)){
            $this->log("Directory does not exist: $path, ignoring delete request");
        	return false;
        }

        $status = true;

        $d = dir($path);

        while(($f = $d->read()) !== false){
        	if( $f == "." || $f == ".." ){
        		continue;
        	}
        	if(is_file("$path/$f")) {
        	    unlink("$path/$f");
        	} else {
        	    $status &= $this->removeDir("$path/$f");
        	}
        	if(!$status) {
        	    return false;
        	}
        }
        $d->close();
        if(@rmdir($path) === FALSE){
        	$this->log("Failed to remove directory: $path");
        	return false;
        }
        return $status;
    }

    /**
     * Load sugar config
     */
    protected function loadConfig()
    {
        $sugar_config = array();
        include($this->context['source_dir']."/config.php");
        if(file_exists($this->context['source_dir']."/config_override.php")) {
            include($this->context['source_dir']."/config_override.php");
        }
        $GLOBALS['sugar_config'] = $sugar_config;
        // by-ref so we can modify it
        $this->config =& $GLOBALS['sugar_config'];
    }

    /**
     * Load version file from path
     * @param string $path
     * @return array
     */
    protected function loadVersion($path)
    {
        if(!defined('sugarEntry')) define('sugarEntry', true);
        include("$path/sugar_version.php");
        $sugar_flavor = strtolower($sugar_flavor);
        return array($sugar_version, $sugar_flavor);
    }

    /**
     * Return path in cache directory
     * @param string $dir
     * @return string
     */
    public function cacheDir($dir)
    {
        return rtrim($this->config['cache_dir'], '/')."/".$dir;
    }

    /**
     * Verify upgrade package
     * @param string $zip ZIP filename
     * @param string $dir Temp dir to use for zip files
     */
    protected function verify($zip, $dir)
    {
        // Check the user
        $this->initSugar();
        if(empty($GLOBALS['current_user']) || empty($GLOBALS['current_user']->id) || !$GLOBALS['current_user']->isAdmin()) {
            return $this->error("{$this->context['admin']} is not a valid admin user");
        }

        // Create target dir
        $unzip_dir = $this->context['temp_dir'];
        $this->cleanDir($unzip_dir);
        // unzip file
        $zip = new ZipArchive;
        $res = $zip->open($this->context['zip']);
        if($res !== true) {
            return $this->error(sprintf("ZIP Error(%d): Status(%s): Arhive(%s): Directory(%s)", $res, $zip->status, $this->context['zip'], $unzip_dir));
        }
        $res = $zip->extractTo($unzip_dir);
        if($res !== true) {
        	return $this->error(sprintf("ZIP Error(%d): Status(%s): Arhive(%s): Directory(%s)", $res, $zip->status, $this->context['zip'], $unzip_dir));
        }
        unset($zip);

        // load manifest
        if(!file_exists("$unzip_dir/manifest.php")) {
            $this->cleanDir($unzip_dir);
            return $this->error("Package does not contain manifest.php");
        }
        // validate manifest
        list($this->from_version, $this->from_flavor) = $this->loadVersion($this->context['source_dir']);
        $res = $this->validateManifest("$unzip_dir/manifest.php");
        if($res !== true) {
            return $this->error($res);
        }
        $this->log("**** Upgrade checks passed");
        return true;
    }

    /**
     * Check if the data file does not have some prohibited constructs
     * @param string $file Filename
     */
    public function checkDataFile($file)
    {
        $tokens = @token_get_all(file_get_contents($file));
        $checkFunction = false;
        foreach($tokens as $index=>$token) {
            if(is_string($token)) {
                if($token == "`") {
                    return $this->error("Backtick is not allowed");
                }
                if($checkFunction && $token == '(') {
                    return $this->error("Functions are not allowed");
                }
                if($token == '$' && !empty($tokens[$index+1][0]) &&  $tokens[$index+1][0] == T_VARIABLE) {
                    return $this->error("Variable vars are not allowed");
                }
            } else {
                switch($token[0]) {
                    case T_WHITESPACE: continue;
                    case T_EVAL:
                    case T_EXIT:
                        return $this->error("{$token[1]}() is not allowed");
                    case T_STRING:
                    case T_VARIABLE:
                        $checkFunction = true;
                        break;
                    case T_OBJECT_OPERATOR:
                    case T_DOUBLE_COLON:
                        return $this->error("Object access is not allowed");
                    case T_REQUIRE_ONCE:
                    case T_REQUIRE:
                    case T_INCLUDE_ONCE:
                    case T_INCLUDE:
                        return $this->error("Includes are not allowed");
                    default:
                        $checkFunction = false;
                }
            }
        }
        return true;
    }

    /**
     * Include file with data
     * @param string $file
     * @param string $name name of the data array
     */
    protected function dataInclude($file, $name)
    {
        $this->log("Loading file $file");
        if(!$this->checkDataFile($file)) {
            return $this->error("Bad data file: $file");
        }
        include $file;
        return $$name;
    }

    /**
     * Load language strings
     * @return array
     */
    protected function loadStrings()
    {
        if(isset($this->config['default_language'])) {
            $lang = $this->config['default_language'];
        } else {
            $lang = 'en_us';
        }
        $mod_strings = array();
        include dirname(__FILE__)."/language/$lang.lang.php";
        $this->mod_strings = $GLOBALS['mod_strings'] = $mod_strings;
        return $mod_strings;
    }

    protected function validateManifest($file)
    {
        // takes a manifest.php manifest array and validates contents
        $this->log('validating manifest.php file');
        $manifest = $this->dataInclude($file, 'manifest');
        $this->loadStrings();

        if(!isset($manifest['type'])) {
        	return $this->mod_strings['ERROR_MANIFEST_TYPE'];
        }

        if($manifest['type'] != 'patch') {
        	return sprintf($this->mod_strings['ERROR_PACKAGE_TYPE'], $manifest['type']);
        }

        if(isset($manifest['acceptable_sugar_versions'])) {
        	$version_ok = false;
        	$matches_empty = true;
        	if(!empty($manifest['acceptable_sugar_versions']['exact_matches'])) {
        		$matches_empty = false;
        		foreach($manifest['acceptable_sugar_versions']['exact_matches'] as $match) {
        			if($match == $this->from_version) {
        				$version_ok = true;
        				break;
        			}
        		}
        	}
        	if(!$version_ok && !empty($manifest['acceptable_sugar_versions']['regex_matches'])) {
        		$matches_empty = false;
        		foreach($manifest['acceptable_sugar_versions']['regex_matches'] as $match) {
        			if(preg_match("/$match/i", $this->from_version)) {
        				$version_ok = true;
        				break;
        			}
        		}
        	}

        	if(!$matches_empty && !$version_ok) {
        		return sprintf($this->mod_strings['ERROR_VERSION_INCOMPATIBLE'], $this->from_version);
        	}
        }

        if(!empty($manifest['acceptable_sugar_flavors'])) {
        	$flavor_ok = false;
        	foreach($manifest['acceptable_sugar_flavors'] as $match) {
        		if(strtolower($match) == $this->from_flavor) {
        			$flavor_ok = true;
        			break;
        		}
        	}
        	if(!$flavor_ok) {
        		return sprintf($this->mod_strings['ERROR_FLAVOR_INCOMPATIBLE'], $this->from_flavor);
        	}
        }

        return true;
    }

    /**
     * Fail current stage
     */
    public function fail()
    {
        $this->success = false;
        if(!empty($this->current_stage)) {
            $this->state['stage'][$this->current_stage] = 'failed';
            $this->saveState();
        }
        return false;
    }

    /**
     * Initialize Sugar environment
     */
    protected function initSugar()
    {
        if($this->sugar_initialized) {
            return;
        }
        if(!defined('sugarEntry')) define('sugarEntry', true);

        global $beanFiles, $beanList, $objectList, $moduleList, $modInvisList, $sugar_config, $locale, $sugar_version, $sugar_flavor, $db, $locale;
        include('include/entryPoint.php');
        $GLOBALS['current_language'] = $this->config['default_language'];
        if(empty($GLOBALS['current_language'])) {
            $GLOBALS['current_language'] = 'en_us';
        }
        $this->db = $GLOBALS['db'] = DBManagerFactory::getInstance();
        $GLOBALS['log']	= LoggerManager::getLogger('SugarCRM');
        // Load user
        $GLOBALS['current_user'] = new User();
        $user_id = $this->db->getOne("select id from users where user_name = " . $this->db->quoted($this->context['admin']), false);
        $GLOBALS['current_user']->retrieve($user_id);
        // Prepare DB
        if($this->config['dbconfig']['db_type'] == 'mysql'){
        	//Change the db wait_timeout for this session
        	$now_timeout = $this->db->getOne("select @@wait_timeout");
        	$this->log('Wait Timeout before change ***** '.$now_timeout);
        	$this->db->query("set wait_timeout=28800");
        	$now_timeout = $this->db->getOne("select @@wait_timeout");
        	$this->log('Wait Timeout after change ***** '.$now_timeout);
        }
        // stop trackers
		$trackerManager = TrackerManager::getInstance();
        $trackerManager->pause();
        $trackerManager->unsetMonitors();
        $this->sugar_initialized = true;
    }

    /**
     * Sorting function for scripts order
     * @param int $a
     * @param int $b
     * @return number
     */
    public function sortByOrder($a, $b)
    {
        return $a->order - $b->order;
    }

    /**
     * Get files to be executed on this stage
     * The sources are:
     * - upgrade/scripts/
     * - custom/upgrade/scripts/
     * - modules/MODULENAME/upgrade/scripts/
     * - custom/modules/MODULENAME/upgrade/scripts/
     * @param string $dir Sugar directory
     * @param string $stage
     * @return array
     */
    protected function getScripts($dir, $stage)
    {
        $dirs = array("$dir/upgrade/scripts/", "$dir/custom/upgrade/scripts/");
        foreach(array("$dir/modules/", "$dir/custom/modules/") as $moduledir) {
            if(!is_dir($moduledir)) continue;
            try {
                foreach (new FilesystemIterator($moduledir, FilesystemIterator::KEY_AS_FILENAME|FilesystemIterator::SKIP_DOTS) as $filename => $fileInfo) {
                    if(!$fileInfo->isDir()) continue;
                    if(file_exists($moduledir.$filename."/upgrade/scripts/")) {
                        $dirs[] = $moduledir.$filename."/upgrade/scripts/";
                    }
                }
            } catch(Exception $e) {
                // ignore Iterator exceptions
                $this->log("FilesystemIterator: ".$e->getMessage());
            }
        }
        $results = array();
        $this->log("Checking for scripts: ".var_export($dirs, true));
        foreach($dirs as $dirname) {
            if(!file_exists($dirname.$stage)) continue;
            try {
                foreach(new FilesystemIterator($dirname.$stage, FilesystemIterator::SKIP_DOTS) as $fileInfo) {
                    if(!$fileInfo->isFile() || $fileInfo->getExtension() != "php") continue;

                    include_once $fileInfo->getPathName();
                    $scriptname = $fileInfo->getBasename(".php");
                    $classname = "SugarUpgrade".preg_replace('/^\d+_/', "", $scriptname); // strip numeric prefix, add SugarUpgrade
                    if(!class_exists($classname)) continue;
                    // add class to results
                    $results[$scriptname] = new $classname($this);
                }
            } catch(Exception $e) {
                // ignore Iterator exceptions
                $this->log("FilesystemIterator: ".$e->getMessage());
            }
        }
        $cnt = count($results);
        $this->log("Found $cnt scripts");
        uasort($results, array($this, "sortByOrder"));
        return $results;
    }

    /**
     * Find files in directory
     * @param string $dir
     */
    public function findFiles($dir)
    {
        $dirlen = strlen(rtrim($dir, '/'))+1;
        $names = array();
        foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir,
                FilesystemIterator::SKIP_DOTS)) as $pathname => $fileInfo) {
            if(!$fileInfo->isFile()) continue;
            // strip dir/ from the name
            $names[] = substr($pathname, $dirlen);
        }
        return $names;
    }

    /**
     * Are we running in Windows?
     * @return boolean
     */
    public function isWindows()
    {
        return strtoupper(substr(PHP_OS, 0, 3)) == 'WIN';
    }

    /**
     * Create file and assign default permissions
     * @param string $path
     * @return boolean
     */
    public function createFile($path)
    {
        touch($path);
        if(!file_exists($path)) {
            $this->error("Could not create file: $path");
            return false;
        }
        if($this->isWindows()) {
            return true;
        }
        if(!empty($this->config['default_permissions']['file_mode'])){
        	chmod($filename, $this->config['default_permissions']['file_mode']);
        }
        if(!empty($this->config['default_permissions']['user'])){
        	chown($filename, $this->config['default_permissions']['user']);
        }
        if(!empty($this->config['default_permissions']['group'])){
        	chgrp($filename, $this->config['default_permissions']['group']);
        }
        return true;
    }

    /**
     * Create file and put data into it
     * @param string $filename
     * @param mixed $data
     * @return boolean
     */
    public function putFile($filename, $data)
    {
        $this->createFile($file);
        return file_put_contents($filename, $data);
    }

/**
 * Implodes some parts of version with specified delimiter, beta & rc parts are removed all time
 *
 * @example ('6.5.6') returns 656
 * @example ('6.5.6beta2') returns 656
 * @example ('6.5.6rc3') returns 656
 * @example ('6.6.0.1') returns 6601
 * @example ('6.5.6', 3, 'x') returns 65x
 * @example ('6', 3, '', '.') returns 6.0.0
 *
 * @param string $version like 6, 6.2, 6.5.0beta1, 6.6.0rc1, 6.5.7 (separated by dot)
 * @param int $size number of the first parts of version which are requested
 * @param string $lastSymbol replace last part of version by some string
 * @param string $delimiter delimiter for result
 * @return string
 */
    public function implodeVersion($version, $size = 0, $lastSymbol = '', $delimiter = '')
    {
        preg_match('/^\d+(\.\d+)*/', $version, $parsedVersion);
        if (empty($parsedVersion)) {
            return '';
        }

        $parsedVersion = $parsedVersion[0];
        $parsedVersion = explode('.', $parsedVersion);

        if ($size == 0) {
            $size = count($parsedVersion);
        }

        $parsedVersion = array_pad($parsedVersion, $size, 0);
        $parsedVersion = array_slice($parsedVersion, 0, $size);
        if ($lastSymbol !== '') {
            array_pop($parsedVersion);
            array_push($parsedVersion, $lastSymbol);
        }

        return implode($delimiter, $parsedVersion);
    }

    public function fileToDelete($file)
    {
        if(!isset($this->state['files_to_delete'])) {
            $this->state['files_to_delete'] = array();
        }
        if(is_array($file)) {
            $this->state['files_to_delete'] = array_merge($this->state['files_to_delete'], $file);
        } else {
            $this->state['files_to_delete'][] = $file;
        }
    }

    /**
     * Get package manifest
     * @return array
     */
    protected function getManifest()
    {
        return $this->dataInclude("{$this->context['temp_dir']}/manifest.php", 'manifest');
    }

    /**
     * PHP error handler for upgrade scripts, to log PHP errors
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param string $errline
     * @param array $errcontext
     */
    public function scriptErrorHandler($errno, $errstr, $errfile, $errline, $errcontext)
    {
        $this->log("PHP: [$errno] $errstr in $errfile at $errline");
    }

    /**
     * Run individual upgrade script
     * @param UpgradeScript $script
     */
    protected function runScript(UpgradeScript $script)
    {
        set_error_handler(array($this, 'scriptErrorHandler'), E_ALL & ~E_STRINCT & ~E_DEPRECATED);
        ob_start();
        try {
            $script->run($this);
        } catch(Exception $e) {
            $this->error("Exception: ".$e->getMessage());
        }
        $out = ob_get_clean();
        if($out) {
            $this->log("OUTPUT: $out");
        }
        restore_error_handler();
    }

    /**
     * Run set of scripts
     * @param string $stage
     * @return boolean
     */
    protected function runScripts($stage)
    {
    	$mod_strings = $this->loadStrings();
    	$this->manifest = $this->getManifest();
    	if(!empty($this->manifest['copy_files']['from_dir'])) {
    	    $this->context['new_source_dir'] = $this->context['temp_dir']."/".$this->manifest['copy_files']['from_dir'];
    	}
    	$scripts = $this->getScripts($this->context['new_source_dir'], $stage);
    	$this->to_version = $this->manifest['version'];
    	if(!empty($this->manifest['flavor'])) {
    	    $this->to_flavor = $this->manifest['flavor'];
    	} else {
    	    $this->to_flavor = $this->from_flavor;
    	}

    	foreach($scripts as $name => $script) {
    	    if(!empty($this->state['scripts'][$name]) && $this->state['scripts'][$name] == 'done') {
    	        $this->log("Skipping script $name - already done");
    	        continue;
    	    }
    	    $this->log("Starting script $name");
    	    $this->state['scripts'][$name] = 'started';
    	    $this->saveState();
    	    $this->runScript($script);
    	    $this->log("Finished script $name");
    	    // Just in case some script did something wrong, go back to proper dir
    	    chdir($this->context['source_dir']);
    		if(!$this->success) {
        	    // script called $this->fail
    		    $this->state['scripts'][$name] = 'failed';
    		    $this->saveState();
    		    return false;
    		} else {
    		    $this->state['scripts'][$name] = 'done';
    		    $this->saveState();
    		}
    	}
    	return true;
    }

    /**
     * Check if $is flavor is of flavor $flav or above
     * @param string $is
     * @param string $flav
     * @return boolean
     */
    protected function isFlavor($is, $flav)
    {
        switch($flav) {
            case 'pro':
                if($is == 'pro') {
                    return true;
                }
            case 'corp':
                if($is == 'corp') {
                    return true;
                }
            case 'ent':
                if($is == 'ent') {
                    return true;
                }
            case 'ult':
                if($is == 'ult') {
                    return true;
                }
                return false;
        }
        return true;
    }

    /**
     * Check if we're upgrading to certain flavor or up
     * @param string $flav
     * @return boolean
     */
    public function toFlavor($flav)
    {
        return $this->isFlavor($this->to_flavor, $flav);
    }

    /**
     * Check if we're upgrading from certain flavor or up
     * @param string $flav
     * @return boolean
     */
    public function fromFlavor($flav)
    {
        return $this->isFlavor($this->from_flavor, $flav);
    }

    /**
     * Save config.php
     */
    public function saveConfig()
    {
        ksort($this->config);
        write_array_to_file("sugar_config", $this->config, $this->context['source_dir']."/config.php");
    }

    protected $stages = array('unpack', 'pre', 'commit', 'post', 'cleanup');

    /**
     * Run one step in the upgrade
     * @param string $stage
     * @return boolean|string true if done, false if error, otherwise next step
     */
    protected function runStep($stage)
    {
        if($stage) {
        	$stage_num = array_search($stage, $this->stages);
        } else {
        	$stage = $this->stages[0];
        	$stage_num = 0;
        }
        if($stage_num === false) {
        	return false;
        }
        if(!$this->runStage($stage)) {
            return false;
        }
        if(++$stage_num >= count($this->stages)) {
            return true;
        } else {
            return $this->stages[$stage_num];
        }
    }

    /**
     * Run given stage
     * @param string $stage stage on which we're running
     * @return boolean
     */
    public function run($stage)
    {
        // TODO: re-run from given state/script
        ini_set('memory_limit',-1);
        if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
            ini_set('error_reporting', E_ALL & ~E_STRICT & ~E_DEPRECATED);
        } else {
            ini_set('error_reporting', E_ALL & ~E_STRICT);
        }
        $this->log("Stage $stage staring");
        try {
            $this->current_stage = $stage;
            $this->state['stage'][$stage] = 'started';
            $this->saveState();
            switch($stage) {
                case "unpack":
                    // Verify package
                    if(!$this->verify($this->context['zip'], $this->context['temp_dir'])) {
                        $this->error("Package verificaition failed");
                        return false;
                    }
                    break;
                case "pre":
                    // Run pre-upgrade
                    // TODO: pre-script are currently taken from old envt.
                    // We need to consider how to take them from new envt instead.
                    list($this->from_version, $this->from_flavor) = $this->loadVersion($this->context['source_dir']);
                    $this->state['old_version'] = array($this->from_version, $this->from_flavor);
                    $this->saveState();
    	            $this->initSugar();
                    if(!$this->runScripts("pre")) {
                        $this->error("Pre-upgrade stage failed!");
                        return false;
                    }
                    break;
                case "commit":
                    // Run copy files
                    if(!$this->commit()) {
                        $this->error("Commit stage failed!");
                        return false;
                    }
                    break;
                case "post":
                    // Run post-upgrade
    	            $this->initSugar();
                    $this->cleanCaches();
    	            list($this->from_version, $this->from_flavor) = $this->state['old_version'];
                    if(!$this->runScripts("post")) {
                        $this->error("Post-upgrade stage failed!");
                        return false;
                    }
                    $this->saveConfig();
                    $this->cleanCaches();
                    break;
                case "cleanup":
                    // Remove temp files
                    $this->removeTempFiles();
                    break;
                default:
                    $this->error("Wrong stage: $stage");
                    return false;
            }
        } catch(Exception $e) {
            $this->error("Exception: ".$e->getMessage());
            return false;
        }
        $this->state['stage'][$stage] = 'done';
        $this->saveState();
        $this->log("Stage $stage done");
        return true;
    }

    /**
     * Remove temp files for upgrader
     */
    public function removeTempFiles()
    {
        $this->removeDir($this->context['temp_dir']);
    }
}

abstract class UpgradeScript
{
    /**
     * Script updates core files
     * Should not be run on db-only updates
     */
    const UPGRADE_CORE = 1;
    /**
     * Script updates DB data
     * Should be run on all updates
     */
    const UPGRADE_DB = 2;
    /**
     * Script updates customization or config files
     * Should not be run on db-only updates, but should be run on shadow upgrades
     */
    const UPGRADE_CUSTOM = 4;
    /**
     * Script does unknown updates
     * Should always be run
     */
    const UPGRADE_ALL = 0xFF;

    /**
     * Sorting order, lower is first
     * @var int
     */
    public $order = 9999;

    /**
     * Version where this script appears
     * @var string
     */
    public $version = "6.7.0";
    public $type = self::UPGRADE_ALL;
    /**
     * Upgrade driver
     * @var UpgradeDriver
     */
    public $upgrader;

    public function __construct($upgrader)
    {
        $this->upgrader = $upgrader;
    }

    abstract public function run();

    public function __get($name)
    {
        return $this->upgrader->$name;
    }

    public function __call($name, $args)
    {
        if(is_callable(array($this->upgrader, $name))) {
            return call_user_func_array(array($this->upgrader, $name), $args);
        }
        throw new Exception("Can not call unknown method $name");
    }
}

