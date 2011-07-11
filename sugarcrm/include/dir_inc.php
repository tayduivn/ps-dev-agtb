<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
// $Id: dir_inc.php 55277 2010-03-15 13:43:46Z jmertic $

function copy_recursive( $source, $dest ){
	//BEGIN SUGARCRM flav=int ONLY
	if (strpos($source,".svn") !== false or strpos($dest,".svn") !== false) {
		return true;
	}
	//END SUGARCRM flav=int ONLY


    if( is_file( $source ) ){
        return( copy( $source, $dest ) );
    }
    if( !sugar_is_dir($dest, 'instance') ){
        sugar_mkdir( $dest );
    }

    $status = true;

    $d = dir( $source );
    if($d === false) {
    	return false;
    }
    while(false !== ($f = $d->read())) {
        if( $f == "." || $f == ".." ) {
            continue;
        }
        $status &= copy_recursive( "$source/$f", "$dest/$f" );
    }
    $d->close();
    return( $status );
}

function mkdir_recursive($path, $check_is_parent_dir = false) {
	if(sugar_is_dir($path, 'instance')) {
		return(true);
	}
	if(sugar_is_file($path, 'instance')) {
		print("ERROR: mkdir_recursive(): argument $path is already a file.\n");
		return false;
	}

	$path = clean_path($path);
	$path = str_replace(clean_path(getcwd()), '', $path);
	$thePath = '';
	$dirStructure = explode("/", $path);
	$status = true;
	foreach($dirStructure as $dirPath) {
		$thePath .= '/'.$dirPath;
		$mkPath = getcwd().'/'.$thePath;

		if(!is_dir($mkPath )) {
			$status = $status & sugar_mkdir($mkPath);
		}
	}
	return $status;

}

function rmdir_recursive( $path ){
    if( is_file( $path ) ){
        return( unlink( $path ) );
    }
    if( !is_dir( $path ) ){
        $GLOBALS['log']->error( "ERROR: rmdir_recursive(): argument $path is not a file or a dir.\n" );
        return( false );
    }

    $status = true;

    $d = dir( $path );
    
    if(!is_object($d))
    {
       $GLOBALS['log']->error( "ERROR: rmdir_recursive dir method on $path resulted in non-object");
       return( false );
    }    
    
    while(($f = $d->read()) !== false){
        if( $f == "." || $f == ".." ){
            continue;
        }
        $status &= rmdir_recursive( "$path/$f" );
    }
    $d->close();
    rmdir( $path );
    return( $status );
}

function findTextFiles( $the_dir, $the_array ){
    if(!is_dir($the_dir)) {
		return $the_array;
	}
	$d = dir($the_dir);
    while (false !== ($f = $d->read())) {
        if( $f == "." || $f == ".." ){
            continue;
        }
        if( is_dir( "$the_dir/$f" ) ){
            // i think depth first is ok, given our cvs repo structure -Bob.
            $the_array = findTextFiles( "$the_dir/$f", $the_array );
        }
        else {
            switch( mime_content_type( "$the_dir/$f" ) ){
                // we take action on these cases
                case "text/html":
                case "text/plain":
                    array_push( $the_array, "$the_dir/$f" );
                    break;
                // we consciously skip these types
                case "application/pdf":
                case "application/x-zip":
                case "image/gif":
                case "image/jpeg":
                case "image/png":
                case "text/rtf":
                    break;
                default:
                    debug( "no type handler for $the_dir/$f with mime_content_type: " . mime_content_type( "$the_dir/$f" ) . "\n" );
            }
        }
    }
    return( $the_array );
}

function findAllFiles( $the_dir, $the_array, $include_dirs=false, $ext='', $exclude_dir=''){
	// jchi  #24296
	if(!empty($exclude_dir)){
		$exclude_dir = is_array($exclude_dir)?$exclude_dir:array($exclude_dir);
		foreach($exclude_dir as $ex_dir){
			if($the_dir == $ex_dir){
				  return $the_array;
			}
		}
	}
	//end
    if(!is_dir($the_dir)) {
		return $the_array;
	}
	$d = dir($the_dir);
    while (false !== ($f = $d->read())) {
        if( $f == "." || $f == ".." ){
            continue;
        }

        if( is_dir( "$the_dir/$f" ) ) {
			// jchi  #24296
			if(!empty($exclude_dir)){
				//loop through array to compare directories..
				foreach($exclude_dir as $ex_dir){
					if("$the_dir/$f" == $ex_dir){
						  continue 2;
					}
				}
			}
			//end

			if($include_dirs) {
				$the_array[] = clean_path("$the_dir/$f");
			}
            $the_array = findAllFiles( "$the_dir/$f/", $the_array, $include_dirs, $ext);
        } else {
			if(empty($ext) || preg_match('/'.$ext.'$/i', $f)){
				$the_array[] = clean_path("$the_dir/$f");
			}
        }


    }
    rsort($the_array);
    return $the_array;
}

function findAllFilesRelative( $the_dir, $the_array ){
    if(!is_dir($the_dir)) {
		return $the_array;
	}
    $original_dir   = getCwd();
    if(is_dir($the_dir)){
    	chdir( $the_dir );
    	$the_array = findAllFiles( ".", $the_array );
    	if(is_dir($original_dir)){
    		chdir( $original_dir );
    	}
    }
    return( $the_array );
}

/*
 * Obtain an array of files that have been modified after the $date_modified
 *
 * @param the_dir			the directory to begin the search
 * @param the_array			array to hold the results
 * @param date_modified		the date to use when searching for files that have
 * 							been modified
 * @param filter			optional regular expression filter
 *
 * return					an array containing all of the files that have been
 * 							modified since date_modified
 */
function findAllTouchedFiles( $the_dir, $the_array, $date_modified, $filter=''){
    if(!is_dir($the_dir)) {
		return $the_array;
	}
	$d = dir($the_dir);
    while (false !== ($f = $d->read())) {
        if( $f == "." || $f == ".." ){
            continue;
        }
        if( is_dir( "$the_dir/$f" ) ){
            // i think depth first is ok, given our cvs repo structure -Bob.
            $the_array = findAllTouchedFiles( "$the_dir/$f", $the_array, $date_modified, $filter);
        }
        else {
        	$file_date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s', filemtime("$the_dir/$f"))) - date('Z'));

        	if(strtotime($file_date) > strtotime($date_modified) && (empty($filter) || preg_match($filter, $f))){
        		 array_push( $the_array, "$the_dir/$f");
        	}
        }
    }
    return $the_array;
}
