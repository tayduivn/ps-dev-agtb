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

// $Id: Charts.php 34583 2008-04-22 23:51:17Z awu $
function create_chart($chartName,$xmlFile,$width="800",$height="400") {
	$html ='<OBJECT classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
 codebase="https://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0"
 WIDTH="'.$width.'" HEIGHT="'.$height.'" id="'.$chartName.'" ALIGN="">';
	$html .='<PARAM NAME=movie VALUE="'.getSWFPath('include/charts/'.$chartName.'.swf','filename='.$xmlFile).'">';
	$html .='<PARAM NAME=bgcolor VALUE=#FFFFFF>';
	$html .='<PARAM NAME=wmode VALUE=transparent>';
	$html .= '<PARAM NAME=quality VALUE=high>';
	$html .='<EMBED src="' . getSWFPath('include/charts/'.$chartName.'.swf','filename='.$xmlFile).'" wmode="transparent" quality=high bgcolor=#FFFFFF  WIDTH="'.$width.'" HEIGHT="'.$height.'" NAME="'.$chartName.'" ALIGN=""
 TYPE="application/x-shockwave-flash" PLUGINSPAGE="https://www.macromedia.com/go/getflashplayer">';
	$html .='</EMBED>';
	$html .='</OBJECT>';
return $html;
}


function generate_graphcolor($input,$instance) {
	if ($instance <20) {
	$color = array(
	"0xFF0000",
	"0x00FF00",
	"0x0000FF",
	"0xFF6600",
	"0x42FF8E",
	"0x6600FF",
	"0xFFFF00",
	"0x00FFFF",
	"0xFF00FF",
	"0x66FF00",
	"0x0066FF",
	"0xFF0066",
	"0xCC0000",
	"0x00CC00",
	"0x0000CC",
	"0xCC6600",
	"0x00CC66",
	"0x6600CC",
	"0xCCCC00",
	"0x00CCCC");
	$out = $color[$instance];
	} else {
	$out = "0x" . substr(md5($input), 0, 6);

	}
	return $out;
}

function save_xml_file($filename,$xml_file) {
	global $app_strings;

	if (!$handle = sugar_fopen($filename, 'w')) {
		$GLOBALS['log']->debug("Cannot open file ($filename)");
		return;
	}

	if (fwrite($handle,$xml_file) === FALSE) {
		$GLOBALS['log']->debug("Cannot write to file ($filename)");
		return false;
	}

	$GLOBALS['log']->debug("Success, wrote ($xml_file) to file ($filename)");

	fclose($handle);
	return true;

}

function get_max($numbers) {
    $max = max($numbers);
    if ($max < 1) return $max;
    $base = pow(10, floor(log10($max)));
    return ceil($max/$base) * $base;
}

// retrieve the translated strings.
global $current_language;
$app_strings = return_application_language($current_language);

if(isset($app_strings['LBL_CHARSET']))
{
	$charset = $app_strings['LBL_CHARSET'];
}
else
{
	global $sugar_config;
	$charset = $sugar_config['default_charset'];
}
?>
