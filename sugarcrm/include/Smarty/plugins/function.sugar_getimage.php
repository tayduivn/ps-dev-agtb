<?php

/**
 * Smarty function plugin
 *
 * Type:     function
 * Name:     sugar_getimage
 * Purpose:  returns requested image or sprite
 * 
 * @param array: file, attributes
 * @param Smarty
 */
function smarty_function_sugar_getimage($params, &$smarty)
{
	if(!isset($params['file'])) {
		   $smarty->trigger_error($GLOBALS['app_strings']['ERR_MISSING_REQUIRED_FIELDS'] . 'file');
	}

	// get filename & extension
	$file_arr = explode('.', $params['file']);
	$filename = $file_arr[0];
	$ext = ".{$file_arr[1]}";

	// optional attributes
	$attr = '';
	if(isset($params['attributes'])) 
		$attr = $params['attributes'];

 	return SugarThemeRegistry::current()->getImage($filename, $attr, null, null, $ext);
}
?>
