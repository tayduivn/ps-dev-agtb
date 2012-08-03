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
ini_set("max_execution_time", "901");
ini_set("memory_limit", "100M");
class TemplateConverter{

static $requireFunctions = array(
	T_REQUIRE_ONCE=>1,
	T_REQUIRE=>1,
	T_INCLUDE=>1,
	T_INCLUDE_ONCE=>1,

);

static  $functionList = array(
	'chgrp',
	'chmod',
	'chown',
	'file_exists',
	'file_get_contents',
	'file_put_contents',
	'file',
	'fileatime',
	'filectime',
	'filegroup',
	'fileinode',
	'filemtime',
	'fileowner',
	'fileperms',
	'filesize',
	'filetype',
	'fopen',
	'is_dir',
	'is_executable',
	'is_file',
	'is_link',
	'is_readable',
	'is_uploaded_file',
	'is_writable',
	'is_writeable',
	'lchgrp',
	'lchown',
	'linkinfo',
	'lstat',
	'mkdir',
	'parse_ini_file',
	'readfile',
	'readlink',
	'rmdir',
	'stat',
	'tempnam',
	'touch',
	'unlink',
	'getimagesize',

	//multiple files per function call
	'copy',
	'link',
	'rename',
	'symlink',

	//Second param
	'move_uploaded_file',


//directory functions
	//should only use relative paths
	'chdir',
	//should not be used
	//	'chroot',
	'dir',
	'opendir',

	//NOT NEEDED HAVE RESOURCES
	//'feof',
	//'fclose',
	//'fflush',
	//'fgetc',
	//'fgetscsv',
	//'fgets',
	//'fgetss',
	//'flock',
	//'fpassthru',
	//'fputcsv',
	//'fputs',
	//'fread',
	//'fscanf',
	//'fseek',
	//'fstat',
	//'ftell',
	//'ftruncate',
	//'fwrite',
	//'rewind',
	'closedir',
	'readdir',
	'rewinddir',
	'scandir',


	//SHOULD NOT BE USED
	//'fnmatch',


	//Maybe
	//'basename',
	//'glob',
	//'dirname',
	//'disk_free_space',
	//'disk_total_space',
	//'pathinfo',
	//'realpath',

);
function generateRegEx(){
	static $reg = array();
	if(!empty($reg))return $reg;
	foreach(TemplateConverter::$functionList as $function){
			$reg['keys'][] = '/([^a-zA-Z\_\.0-9\$])(' . $function . '[ ]*\()/i';
			$reg['values'][] = '$1SugarTemplateUtilities::$2';

	}
	/*foreach(TemplateConverter::$requireFunctions as $function){
			$reg['keys'][] = '/\s*' . $function . '[\s]*[\( ])([^;]*?)(\){0,1}\s*;)/i';
			$reg['values'][] = '$1SugarTemplateUtilities::getFilePath($2)$3';

	}
	*/
	return $reg;
}




function generateRevertRequireRegEx(){
	static $reg = array();
	if(!empty($reg))return $reg;
	foreach(TemplateConverter::$requireFunctions as $function){
			$reg[] = '/(' . $function . '[\s]*[\( ])SugarTemplateUtilities::getFilePath\((.*)\)(\s*;)/i';

	}
	return $reg;
}

function displayProgress(){
	static $count = 0;
	static $row = 0;
	echo '.';
	$count++;
	if($count == 100){
	echo '<br>';
	$count = 0;
	$row++;
	}
	flush();
}
function convertDir($path, $to=true){
	static $startPath = '';
	if(empty($startPath))$startPath = $path;
	if(!is_dir($path) || file_exists($path . '/templateConverter.php'))return;
	$d = dir($path);
	while($e = $d->read()){
		if(substr($e, 0, 1) == '.')continue;
		$next = $path . '/' . $e;
		if(is_dir($next)){
			TemplateConverter::convertDir($next, $to);
		}else if(TemplateConverter::isPHPFile($next)){
			if($to){
				TemplateConverter::convertFile($next);
			}else{
				TemplateConverter::revertFile($next);
			}
		}
	}

	if($path == $startPath)echo 'DONE';
    return true;
}

function isPHPFile($next){
	return is_file($next) && substr($next, -4) == '.php';
}

function convertFile($path){
//	TemplateConverter::displayProgress($path);
//excluding silent upgrade file from conversion
if(strpos(strtolower($path), 'silentupgrade')!==false) return;
	$status = '';
	$statusDepth = 0;
	$contents = file_get_contents($path);
	$tokens = token_get_all($contents);
	$newContents = '';
	$isSet = false;
	$isMember = false;
	$isFunction = false;
	foreach($tokens as $index=>$token){
		if(is_string($token)){
			if($status == 'require'){
				switch($token){
					case ';':
						$status = '';
						$statusDepth = 0;
						$newContents .= ')'. $token;
						break;
					case '"':
						if($statusDepth == 0){
								$newContents .= 'SugarTemplateUtilities::getFilePath(';
								$statusDepth = 1;
						}
						$newContents .= $token;
						break;
					case '(':
						$newContents .= $token;
						if($statusDepth == 0){
							for($j = $index + 1;!is_string($tokens[$j]) && $tokens[$j][0] ==T_WHITESPACE;$j++){

							}
							if(is_string($tokens[$j]) || $tokens[$j][1] != 'SugarTemplateUtilities'){
								$newContents .= 'SugarTemplateUtilities::getFilePath(';
							}else{
								$status = '';
								$statusDepth = 0;
							}
						}
						$statusDepth++;
						break;
					case ')':
						$statusDepth--;
					default:
						$newContents .= $token;
				}
			}else{
				$newContents .= $token;
			}
		}else{
			if($status == 'require' && $token[0] != T_WHITESPACE && $statusDepth == 0){

				if($token[1] == 'SugarTemplateUtilities'){
					$status = '';
					$statusDepth = 0;
				}else{
					for($j = $index + 1;!is_string($tokens[$j]) && $tokens[$j][0] ==T_WHITESPACE;$j++){

					}

					if(is_string($tokens[$j]) || $tokens[$j][1] != 'SugarTemplateUtilities'){
						$statusDepth = 1;

						$newContents .= 'SugarTemplateUtilities::getFilePath(';
					}else{
						$status = '';
						$statusDepth = 0;
					}
				}
			}
			if(!$isMember && !$isFunction){
				if(!empty(TemplateConverter::$requireFunctions[$token[0]])){
					$status = 'require';
					$statusDepth = 0;
				}
				if( in_array($token[1], TemplateConverter::$functionList)){
					$newContents .= 'SugarTemplateUtilities::';
				}
			}else{
				$isMember = false;
				if($isFunction && $token[0] != T_WHITESPACE){
					$isFunction = false;
				}
			}

			if($token[0] == T_FUNCTION){
				$isFunction = true;
			}
			if($token[0] == T_OBJECT_OPERATOR || $token[0] == T_PAAMAYIM_NEKUDOTAYIM){
				$isMember = true;
			}


			$newContents .= $token[1];

		}
	}

	file_put_contents($path, $newContents);
}

function revertFile($path){
//	TemplateConverter::displayProgress($path);
	$status = '';
	$statusDepth = 0;
	$contents = file_get_contents($path);
	$tokens = token_get_all($contents);
	$newContents = '';
	foreach($tokens as $index=>$token){
		if($status == 'skipNext'){
			$status = '';
			$statusDepth = 0;
			continue;
		}
		if(is_string($token)){
			if($status == 'require'){
				switch($token){
					case '(':
						if($statusDepth > 0)$newContents .= $token;
						$statusDepth++;
						break;
					case ')':
						$statusDepth--;
						if($statusDepth > 0)$newContents .= $token;
						break;
					default;
						if($statusDepth > 0)$newContents .= $token;
				}
				if($statusDepth == 0){
					$status = '';
				}
				continue;
			}else{
				$newContents .= $token;
			}
		}else{
			if($status == 'require'){
				if($statusDepth > 0)$newContents .= $token[1];
				continue;
			}
			if(strcmp('SugarTemplateUtilities', $token[1]) == 0){
				if(!empty($tokens[$index + 2]) &&  !is_string($tokens[$index + 2]) && strcmp('getFilePath', $tokens[$index + 2][1]) == 0){
					$status = 'require';
				}else{
					$status = 'skipNext';
				}
				continue;
			}else{
				$newContents .= $token[1];
			}


		}
	}
	file_put_contents($path, $newContents);
}


}

//echo '<div style="font-family: courier, monospace">';
//TemplateConverter::convertFile('../include/utils.php');

//TemplateConverter::convertFile('../install/install_utils.php');
//TemplateConverter::convertFile('../include/utils.php');
if(!empty($argv[1])){
	$_GET['TEMPLATE_PATH'] = $argv[1];
    echo "using following path:". $argv[1];
}
if(!empty($argv[2]) && ($argv[2] == 1 || $argv[2] == 'true') ) {
    $_GET['revert'] = true;
}
if(!empty($argv[3]) && ($argv[3] == 1 || $argv[3] == 'true') ) {
    $_GET['CONVERT_FILE_ONLY'] = true;
}

if(empty($_GET['TEMPLATE_PATH'])){
	$TEMPLATE_PATH = getcwd();
	$paths = explode('/', $TEMPLATE_PATH);
	unset($paths[count($paths) - 1]);
	unset($paths[count($paths) - 1]);
	$TEMPLATE_PATH = implode('/', $paths);
    $_GET['TEMPLATE_PATH'] = $TEMPLATE_PATH;
    echo "
            Template Path was not provided, using following path instead: $TEMPLATE_PATH
          ";
}else{
	$TEMPLATE_PATH = $_GET['TEMPLATE_PATH'];
}

/*
echo <<<EOQ
<form>
	<input type='text' name='TEMPLATE_PATH' size='60' value='$TEMPLATE_PATH'><input type='submit' value='Convert'>
</form>
EOQ;
*/
//check to see if this is for one file only first
if(isset($_GET['CONVERT_FILE_ONLY']) && $_GET['CONVERT_FILE_ONLY']){
    TemplateConverter::convertFile($_GET['TEMPLATE_PATH']);
}else if(!empty($_GET['TEMPLATE_PATH'])){
//echo "Converting\n";
    $success = false;
    $success = TemplateConverter::convertDir( $_GET['TEMPLATE_PATH'], empty($_GET['revert']));
}
//echo '</div>';
?>
