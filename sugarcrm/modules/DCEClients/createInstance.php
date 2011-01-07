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
define('MKDIRMODE', 0755);
function process_create_instance($template_path, $instance_path, $template_url, $cwd=''){
	if(substr($template_path, -1, 1) != '/'){
		$template_path .= '/';
	}
	if(substr($instance_path, -1, 1) != '/'){
		$instance_path .= '/';
	}
	$instance_name =  basename($instance_path);
	if(file_exists($instance_path . 'ini_setup.php'))return false;

	$structure = array(
	sugar_cached(''),
	sugar_cached('dashlets'),
	sugar_cached('dynamic_fields'),
	sugar_cached('modules'),
    sugar_cached('import'),
	rtrim($GLOBALS['sugar_config']['upload_dir'], '/\\'),
	sugar_cached("xml"),
	sugar_cached('smarty'),
	'custom',
	'data',
	'data/upload',
	'include',
	'include/javascript',
	);
	$copy = array(
		'index.php'=>true,
		'install.php'=>true,
		'soap.php'=>true,
		'cron.php'=>true,
        'config.php'=>false,
        'sugar_version.php'=>false,
        'vcal_server.php'=>true,
	);
	// create the directory structure
	if(!file_exists($instance_path)){
		if(!mkdir($instance_path,MKDIRMODE, true)){
			print('<br>Could Not Create Directory:'. $instance_path);
			return false;
		}
	}

	foreach($structure as $struct){
		if(!file_exists($instance_path .$struct))if(!mkdir($instance_path. $struct,MKDIRMODE, true)){
			print('<br>Could Not Create Directory:'. $instance_path. $struct);
			return false;
		}
	}
	//copy required files
	foreach($copy as $file=>$entry_point){
		if(!$entry_point){
			if(!file_exists($template_path. $file)){
				if($file == 'config.php'){
					$fp = fopen($instance_path . $file, 'a');
					fclose($fp);
					continue;

				}else{
					print('<br>Invalid Template - Missing File : ' . $template_path. $file);
					return false;
				}
			}
			copy($template_path. $file , $instance_path . $file);

		}else{
			if(!file_exists($template_path. $file)){
				print('<br>Invalid Template - Missing File : ' . $template_path. $file);
				return false;
			}
			$contents = file_get_contents($template_path. $file );
			$contents = preg_replace('/\<\?php/', '<?php' . "\nrequire_once('ini_setup.php');\n",$contents, 1 );
			$fp = fopen($instance_path . $file,'w');
			fwrite($fp, $contents);
			fclose($fp);
		}
	    chmod($instance_path.$file, MKDIRMODE);
	}
	if(empty($cwd))$cwd = getcwd();
	$template_url = str_replace('/index.php', '', $template_url);
	$ini_setup = <<<EOQ
<?PHP
	define('INSTANCE_PATH', '$instance_path');
	define('TEMPLATE_PATH', '$template_path');
	define('TEMPLATE_URL', '$template_url');
	require_once('$cwd/SugarTemplateUtilities.php');
	SugarTemplateUtilities::\$TEMPLATE_PATH = TEMPLATE_PATH;
	SugarTemplateUtilities::\$INSTANCE_PATH = INSTANCE_PATH;
EOQ;
	$fp = fopen($instance_path . '/ini_setup.php', 'w');
	fwrite($fp, $ini_setup);
	fclose($fp);
    chmod($instance_path . '/ini_setup.php', MKDIRMODE);

	$fp = fopen($instance_path . '/.htaccess', 'w');
	fwrite($fp , 'RedirectMatch 301 /' . $instance_name. '/(?!cache)(?!custom)(.*)\.(gif|jpg|png|css|js|swf|ico) ' . $template_url . '/$1.$2');
	fclose($fp);
    chmod($instance_path . '/.htaccess', MKDIRMODE);
	return true;
}
?>