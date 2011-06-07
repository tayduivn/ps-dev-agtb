<?php

/*

Modification information for LGPL compliance

2011-06-01 15:46:10 -0700 (Wed, 1 Jun 2011) - majed - translate and truncate function


*/


/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {sugar_translate} function plugin
 *
 * Type:     function<br>
 * Name:     sugar_translate_truncate_ellipses<br>
 * Purpose:  calls the translate function and replaces long labels with ellipses

 * @param array
 * @param Smarty
 */
function smarty_function_sugar_translate_truncate_ellipses($params, $smarty,$length = 16)
{
    //call translate function
    require_once('include/Smarty/plugins/function.sugar_translate.php');
    $value =  smarty_function_sugar_translate($params, &$smarty);
    //if returned value is longer than allowed length, then replace with ellipses
    if(strlen($value)>$length){
        $first_end = $length/2;
        $second_end = $length/4;
        $value = substr($value,0,$first_end).'... '.substr($value,-$second_end);
    }
    return $value;


}
