<?php
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

class SugarTemplateUtilities{
	static $paths = array();
	static $webPaths = array();
	static $externalCache = array();
    static $disableCacheCheck = false;
	static $TEMPLATE_PATH;
	static $INSTANCE_PATH;

	//turn caching off - on by default
	function disableCache(){
		SugarTemplateUtilities::$disableCacheCheck = true;
	}
	//turn caching on - on by default
	function enableCache(){
		SugarTemplateUtilities::$disableCacheCheck = false;
	}
	//read/write functions
	function fopen($filename, $mode){
		$file = SugarTemplateUtilities::getFilePath($filename, $mode);
		return fopen($file, $mode);
	}

	function copy($source_file, $destination_file){
		//source can be either a template or instance , but destination must be an instance
		return copy(SugarTemplateUtilities::getFilePath($source_file), SugarTemplateUtilities::getFilePath($destination_file, 'instance'));
	}
	function link($target, $link){
		//source can be either a template or instance , but destination must be an instance
		return link(SugarTemplateUtilities::getFilePath($target), SugarTemplateUtilities::getFilePath($link, 'instance'));
	}
	function rename($old_name, $new_name){
		//source can be either a template or instance , but destination must be an instance
		return rename(SugarTemplateUtilities::getFilePath($old_name), SugarTemplateUtilities::getFilePath($new_name, 'instance'));
	}

	function move_uploaded_file($path, $new_path){
		//source is the proper path to the file , but destination must be an instance
		return move_uploaded_file($path, SugarTemplateUtilities::getFilePath($new_path, 'instance'));
	}

	function symlink($target, $link){
		//source can be either a template or instance , but destination must be an instance
		return symlink(SugarTemplateUtilities::getFilePath($target), SugarTemplateUtilities::getFilePath($link, 'instance'));
	}

	function dir($path){
		//sugar dir scans both the template and the instance
		return new SugarDir($path);
	}
	function opendir($path){
		//sugar dir scans both the template and the instance
		return new SugarDir($path);
	}
	function closedir($resource){
		//sugar dir closed them already
		return true;
	}
	function readdir($resource){
		//sugar dir as well as the normal resource implement read
		return $resource->read();
	}
	function rewinddir($resource){
		//sugar dir as well as the normal resource implement read
		return $resource->reset();
	}

	function scandir($path){
		//sugar dir as well as the normal resource implement read
		$dir = new SugarDir($path);
		return $dir->keys;
	}

	//write functions
	//ALL WRITE OPERATIONS MUST BE DONE ON THE INSTANCE
	function file_put_contents($filename,$data=null, $flags=null, $context = null ){
		return file_put_contents(SugarTemplateUtilities::getFilePath($filename, 'instance'),$data, $flags, $context);
	}

	function chgrp($filename, $group){
		//we can't modify the template so it has to be on the instance if not return true anyways if the file doesn't exist we'll create it properly
		$filename = SugarTemplateUtilities::getFilePath($filename, 'instance');
		if(file_exists($filename)){
			return chgrp($filename, $group);
		}
		return true;
	}

	function lchgrp($filename, $group){
		//we can't modify the template so it has to be on the instance if not return true anyways if the file doesn't exist we'll create it properly

		$filename = SugarTemplateUtilities::getFilePath($filename, 'instance');
		if(file_exists($filename)){
			return lchgrp($filename, $group);
		}
		return true;
	}

	function lchown($filename, $group){
		//we can't modify the template so it has to be on the instance if not return true anyways if the file doesn't exist we'll create it properly

		$filename = SugarTemplateUtilities::getFilePath($filename, 'instance');
		if(file_exists($filename)){
			return lchown($filename, $group);
		}
		return true;
	}

	function chmod($filename, $mode){
		//we can't modify the template so it has to be on the instance if not return true anyways if the file doesn't exist we'll create it properly

		$filename = SugarTemplateUtilities::getFilePath($filename, 'instance');
		if(file_exists($filename)){
			return chmod($filename, $mode);
		}
		return true;
	}

	function chown($filename, $user){
		//we can't modify the template so it has to be on the instance if not return true anyways if the file doesn't exist we'll create it properly
		$filename = SugarTemplateUtilities::getFilePath($filename, 'instance');
		if(file_exists($filename)){
			return chown($filename, $user);
		}
		return true;
	}

	function mkdir($filename, $mode=0770){
		// always pass in recursive = true as the directory structure may exist in the template, but it might not exist on the instance.
		$d = SugarTemplateUtilities::getFilePath($filename, 'instance');
		return file_exists($d) ? false : mkdir($d, $mode, true);
	}

	function rmdir($filename){
		//ALL WRITE OPERATIONS MUST BE DONE ON THE INSTANCE
		return rmdir(SugarTemplateUtilities::getFilePath($filename, 'instance'));
	}

	function unlink($filename){
		//ALL WRITE OPERATIONS MUST BE DONE ON THE INSTANCE
		return unlink(SugarTemplateUtilities::getFilePath($filename, 'instance'));
	}

	function tempnam($dir, $prefix){
		//ALL WRITE OPERATIONS MUST BE DONE ON THE INSTANCE
		return tempnam(SugarTemplateUtilities::getFilePath($dir, 'instance'), $prefix);
	}
	function touch($filename){
		//ALL WRITE OPERATIONS MUST BE DONE ON THE INSTANCE
		return touch(SugarTemplateUtilities::getFilePath($filename, 'instance'));
	}

	//read functions
	function file_get_contents($filename){
		return file_get_contents(SugarTemplateUtilities::getFilePath($filename));
	}

	function file($filename, $flags=null, $context=null){
		return file(SugarTemplateUtilities::getFilePath($filename),$flags,$context);
	}

	function parse_ini_file($filename, $process_sections = false){
		return parse_ini_file(SugarTemplateUtilities::getFilePath($filename), $process_sections);
	}
	function readfile($filename, $use_include_paths=null, $context=null){
		return readfile(SugarTemplateUtilities::getFilePath($filename), $use_include_paths, $context);
	}
	function readlink($filename ){
		return readlink(SugarTemplateUtilities::getFilePath($filename));
	}
	function getimagesize($filename){
		return getimagesize(SugarTemplateUtilities::getFilePath($filename));
	}


	//status functions
	function file_exists($filename){
		//if we are referring to the current directory get rid of the ./ so that ./mydir/ and mydir/ match up in our caching mechanism.
        if(substr($filename, 0,2) === './') $filename = substr($filename, 2);
         //if we start with a / or we have a :// it means that it's either a full path or a web path so nothing to figure out
        if(substr($filename, 0, 1) === '/' || substr($filename, 0, 6) === $GLOBALS['sugar_config']['cache_dir'])return file_exists($filename);

		//otherwise let's check the cached version
        $val =  SugarTemplateUtilities::getCachedPath($filename, true);
        return !empty($val);
    }


	function fileatime($filename){
		return fileatime(SugarTemplateUtilities::getFilePath($filename));
	}

	function filectime($filename){
		return filectime(SugarTemplateUtilities::getFilePath($filename));
	}
	function filemtime($filename){
		return filemtime(SugarTemplateUtilities::getFilePath($filename));
	}

	function filegroup($filename){
		return filegroup(SugarTemplateUtilities::getFilePath($filename));
	}

	function fileinode($filename){
		return fileinode(SugarTemplateUtilities::getFilePath($filename));
	}

	function fileowner($filename){
		return fileowner(SugarTemplateUtilities::getFilePath($filename));
	}

	function fileperms($filename){
		return fileperms(SugarTemplateUtilities::getFilePath($filename));
	}

	function filesize($filename){
		return filesize(SugarTemplateUtilities::getFilePath($filename));
	}

	function filetype($filename){
		return filetype(SugarTemplateUtilities::getFilePath($filename));
	}

	function is_dir($filename, $mode='default'){
		return is_dir(SugarTemplateUtilities::getFilePath($filename, $mode));
	}

	function is_file($filename, $mode='default'){
		return is_file(SugarTemplateUtilities::getFilePath($filename, $mode));
	}

	function is_executable($filename){
		return is_executable(SugarTemplateUtilities::getFilePath($filename));
	}
	function is_link($filename){
		return is_link(SugarTemplateUtilities::getFilePath($filename));
	}
	function is_readable($filename){
		return is_readable(SugarTemplateUtilities::getFilePath($filename));
	}
	function is_writable($filename){
		return !file_exists($filename) || is_writable(SugarTemplateUtilities::getFilePath($filename, 'instance'));
	}
	function is_writeable($filename){
		return !file_exists($filename) || is_writable(SugarTemplateUtilities::getFilePath($filename, 'instance'));
	}
	function is_uploaded_file($filename){
		return is_uploaded_file($filename);
	}

	function linkinfo($filename){
		return linkinfo(SugarTemplateUtilities::getFilePath($filename));
	}
	function lstat($filename){
		return lstat(SugarTemplateUtilities::getFilePath($filename));
	}

	function stat($filename){
		return stat(SugarTemplateUtilities::getFilePath($filename));
	}
	 function chdir($path){
                $path = str_replace(array(INSTANCE_PATH, TEMPLATE_PATH), '', $path . '/');

                SugarTemplateUtilities::$INSTANCE_PATH = preg_replace("'\/+'", '/', INSTANCE_PATH . '/'. $path . '/');
                SugarTemplateUtilities::$TEMPLATE_PATH = preg_replace("'\/+'", '/', TEMPLATE_PATH . '/'. $path . '/');
     }
/**
 * Enter description here...
 *
 * @param string $path - relative path to a file
 * @param boolean $exist_check - are we checking the existence of a file
 * @return string
 */
 function getCachedPath($path, $exist_check=false){
 				//should we update the external cache
                $update_cache = false;
                //the directory we cache the contents of a directory all at once to save on caching round trips
                $dir = dirname($path);
                // the file name we will check our cache to see if this file is on the TEMPLATE or the INSTANCE
                $cur = basename($path);
                //are we using external caching
                $eCache = false;
                //this is going to be the contents of the external cache
                $external = array();
                //we can't use caching until it has been loaded
                if(!empty($GLOBALS['sugar_config']) && defined('EXTERNAL_CACHE_DEBUG')){
					$eCache = true;
					$external = sugar_cache_retrieve($dir);
				}

                //determine whether file is from module builder or studio
                $MBS = false;
				$_path = strtolower($path);
                if(SugarTemplateUtilities::$disableCacheCheck ||  strpos(strtolower(SugarTemplateUtilities::$INSTANCE_PATH),'modulebuilder') !== false || strpos($_path,'modulebuilder') !== false  || strpos($_path,'studio') !== false || strpos($_path,'custom/extension') !== false){
                    $MBS = true;
                }
				//if we aren't using external cache then let's just do the file_exists check since there is no need to run down the directories
                  if(!$eCache || $path{0}=='.'){
                        if(file_exists(SugarTemplateUtilities::$INSTANCE_PATH . '/'. $path)){
                                return SugarTemplateUtilities::$INSTANCE_PATH . '/'. $path;
                        }else{
                        		//if we aren't checking for the existence of a file then if doesn't exist on the INSTANCE it must exist on the TEMPLATE
                                if(!$exist_check){
                                        return SugarTemplateUtilities::$TEMPLATE_PATH . '/'. $path;
                                }else{
                                		//other wise we check if it exists on the TEMPLATE and return that value
                                        return file_exists(SugarTemplateUtilities::$TEMPLATE_PATH . '/'. $path);
                                }
                        }

                }else if(empty($external) || $MBS ){
                        //process if directory is empty, or the file is from module builder or studio
                		//lets look up the contents of the directory and update the cache if we don't have this directory cached
                        $d = new SugarDir($dir);
                        //this will return an associative array containing file names to full paths for those files
                        $external = $d->getFullPaths();
                        //we should cache this so we don't have to do the lookup again
                        $update_cache = true;

                }
                if(!isset($external[$cur])){
                		//if we have this directory cached and the file isn't in it it probably means it doesn't exist
                		//this is an optimization to reduce file_exists calls so we are caching that the file does not exist
                        $external[$cur] = false;
                        $update_cache = true;
                }
                if( $update_cache && $eCache){
                		//if we have external cache turned on and
                        sugar_cache_put($dir, $external);
                }
               //if we aren't doing an existence check and we know the file does not exist we need to return the TEMPLATE path to the file since the system expects a file path
               if(!$exist_check && empty($external[$cur]) && $MBS){
               		return SugarTemplateUtilities::$INSTANCE_PATH . $path;
               }
               else  if(!$exist_check && empty($external[$cur])) {
	               	return SugarTemplateUtilities::$TEMPLATE_PATH . $path;
               }
               return $external[$cur];

        }


        function getFilePath($path, $mode='default'){
        		//if we are refuring to the current directory get rid of the ./ so that ./mydir/ and mydir/ match up in our caching mechanism.
                if(substr($path, 0,2) === './') $path = substr($path, 2);
                //if we start with a / or we have a :// it means that it's either a full path or a web path so nothing to figure out
                if(substr($path, 0, 1) === '/' || substr_count($path, '://') == 1)return $path;
                //if the directory is  the cachedirectory then it must be on the instance
                if(substr($path, 0, 6) === $GLOBALS['sugar_config']['cache_dir'])return SugarTemplateUtilities::$INSTANCE_PATH .'/'. $path;
                switch($mode){
                        case 'r+':
                        case 'w':
                        case 'wb':
                        case 'w+':
                        case 'a':
                        case 'a+':
                        case 'x':
                        case 'x+':
                        case 'instance':
                                //all writes need to go to the instance
                                        $full_path = SugarTemplateUtilities::$INSTANCE_PATH .$path;
                                break;
                        default:
                        		// Get the path from our caching mechanism. It will look it up if it's not in cache.
                                $full_path = SugarTemplateUtilities::getCachedPath($path);
                                //if it returned nothing it means that the file doesn't exist, but we must return a path so lets return the template path
                                if(empty($full_path))$full_path =  SugarTemplateUtilities::$TEMPLATE_PATH. $path;
                }
                return $full_path;
        }

	/**
	 * This will return a path to pass to the web browser for the file.
	 * Either a
	 *
	 * @param unknown_type $path
	 * @return unknown
	 */
	function getWebPath($path){
		if(!isset(SugarTemplateUtilities::$webPaths[$path] )){
			$fullpath = SugarTemplateUtilities::getFilePath($path);
			if(substr_count($fullpath, SugarTemplateUtilities::$TEMPLATE_PATH) > 0){
				SugarTemplateUtilities::$webPaths[$path] =  TEMPLATE_URL . '/'.$path;
			}else{
				SugarTemplateUtilities::$webPaths[$path] = $path;
			}
		}
		return SugarTemplateUtilities::$webPaths[$path];

	}

	/**
	 * This function is primarily used internally to clean up stringsand ensure that they aren't full paths
	 *
	 * @param unknown_type $path
	 * @return unknown
	 */
	function _getPath($path){
		if(!isset(SugarTemplateUtilities::$paths[$path] ))SugarTemplateUtilities::$paths[$path] = str_replace(array(SugarTemplateUtilities::$INSTANCE_PATH, SugarTemplateUtilities::$TEMPLATE_PATH, './'), '', $path);
		return SugarTemplateUtilities::$paths[$path];
	}

        function getRelativePath($path){
             $path = str_replace(array(SugarTemplateUtilities::$INSTANCE_PATH, SugarTemplateUtilities::$TEMPLATE_PATH), '', $path);
            return $path;
        }


}

/**
 * This class is to mimic the normal Dir opertaions, but it is able to look for contents on both the Template and the instance
 *
 *
 */
class SugarDir{

	var $contents = array();
	var $keys = array();
	var $index = 0;
	var $exists = false;
	function SugarDir($path){
                $path = SugarTemplateUtilities::getRelativePath($path);
		$this->exists = false;
		$template = false;
		//first we scan the template directory for files
		if(is_dir(SugarTemplateUtilities::$TEMPLATE_PATH . $path)){
			$this->exists = true;
			$template = dir(SugarTemplateUtilities::$TEMPLATE_PATH . $path);
			while($e = $template->read()){
				//no need to account for hidden files we shouldn't be touching them anyways
				if(substr($e, 0 , 1) == '.')continue;
				$this->contents[$e] = SugarTemplateUtilities::$TEMPLATE_PATH . $path . '/' . $e;
				$this->keys[] = $e;

			}
			$template->close();
		}
		//then we scan the instance directory
		if(is_dir(SugarTemplateUtilities::$INSTANCE_PATH. $path)){
			$this->exists = true;
			$instance = dir(SugarTemplateUtilities::$INSTANCE_PATH . $path);
			while($e = $instance->read()){
				//we shouldn't be touching hidden files
				if(substr($e, 0 , 1) == '.')continue;
				$this->contents[$e] = SugarTemplateUtilities::$INSTANCE_PATH . $path . '/' . $e;
				if(!isset($this->keys[$e])){
					$this->keys[] = $e;
				}
			}
			$instance->close();
		}

	}
	/**
	 * This is usually what is called to close a directory, but is not needed as we close the directory when we initially scan it
	 *
	 * @return true
	 */
	function close(){

		return true;
	}
	/**
	 * reset will set the pointer back to the first file in the list
	 *
	 * @return true
	 */
	function reset(){
		$this->index = 0;
		return true;
	}

	/**
	 * Returns the next file or directory in the list
	 *
	 * @return STRING FileName
	 */
	function read(){
		$value = false;
		if(isset($this->keys[$this->index])){
			$value = $this->keys[$this->index];
			$this->index++;
		}
		return $value;
	}

	/**
	 * returns a list of filenames and the full paths to those files
	 *
	 * @return ASSOCIATIVE ARRAY files
	 */
	function getFullPaths(){
		return $this->contents;
	}

	/**
	 * Enter description here...
	 *
	 * @param STRING $entry - the file or directory inside the current directory
	 * @return STRING the full path to that file or directory
	 */
	function getFullPath($entry){
		return 	$this->contents[$entry];
	}



}
?>
