<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2007 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
class cleanup50{
	function findAndRemove($file,$path, $contains,$not=array() ){
		$removed = 0;
		$cur = $path . '/' . $file;
		if(file_exists($cur)){
			$del = true;
			$contents = file_get_contents($cur);
			if(!empty($contains)){
				$del = false;
				if(substr_count($contents, $contains) > 0){
					$del = true;
					foreach($not as $str=>$count){
						if(substr_count($contents, $str) >= $count ){
							$del = false;
						}
					}
				}
			}
			if($del){
				unlink($cur);
				$removed++;
			}
		}
		if(!file_exists($path))return $removed;
		$d = dir($path);
		while($e = $d->read()){
			$next = $path . '/'. $e;
			if(substr($e, 0, 1) != '.' && is_dir($next)){
				$removed += cleanup50::findAndRemove($file, $next, $contains, $not);
			}
		}
		return $removed;
	}

	function findAndRename($from, $to, $path){
		$renamed = 0;
		$to_path = $path . '/' . $to;
		foreach($from as $file=>$to_file){
			$cur = $path .'/' . $file;
			if(file_exists($cur) && substr_count($cur, $to) == 0){
				if(!file_exists($to_path))sugar_mkdir($to_path);
				rename($cur, $path . '/' . $to . '/' . $to_file);
				$renamed++;
			}
		}
		if(!file_exists($path))return $renamed;
		$d = dir($path);
		while($e = $d->read()){
			$next = $path . '/'. $e;
			if(substr($e, 0, 1) != '.' && is_dir($next)){
				$renamed += cleanup50::findAndRename($from, $to, $next);
			}
		}
		return $renamed;

	}
	function delete($dir){
		if(is_file($dir)){
			return unlink($dir);
		}
		$d = dir($dir);
		while($e = $d->read()){
			if($e != '.' && $e != '..'){
				$next = $dir . '/' . $e;
				cleanup50::delete($next);
			}
		}
		return rmdir($dir);
	}
	function removeSVN($svn_folder, $path){
		$removed = 0;
		if(!file_exists($path))return $renamed;
		$d = dir($path);
		while($e = $d->read()){
			$next = $path . '/'. $e . '/' . $svn_folder . '/.svn';
			if(substr($e, 0, 1) != '.' && is_dir($next)){
				cleanup50::delete($next);
			}
		}
		return $removed;

	}
}
/*
echo 'Removed ' . cleanup50::findAndRemove('Popup.php', 'modules', ' new Popup_Picker()',array(' new '=>2)) . ' Popup.php files<br>';
echo 'Moved ' . cleanup50::findAndRename(array('layout_defs.php'=>'subpaneldefs.php', 'subpanels'=>'subpanels'), 'metadata', 'modules') . ' layout_defs.php to metadata/subpaneldefs.php AND subpanels to metadata/subpanels <br>';
echo 'Moved  Custom ' . cleanup50::findAndRename(array('layout_defs.php'=>'subpaneldefs.php', 'subpanels'=>'subpanels'), 'metadata', 'custom/modules') . ' layout_defs.php to metadata/subpaneldefs.php AND subpanels to metadata/subpanels<br>';;
*/
//removing echo
cleanup50::findAndRemove('Popup.php', 'modules', ' new Popup_Picker()',array(' new '=>2));
cleanup50::findAndRename(array('layout_defs.php'=>'subpaneldefs.php', 'subpanels'=>'subpanels'), 'metadata', 'modules');
cleanup50::findAndRename(array('layout_defs.php'=>'subpaneldefs.php', 'subpanels'=>'subpanels'), 'metadata', 'custom/modules');
//cleanup50::removeSVN('metadata/subpanels/', 'modules');

?>
