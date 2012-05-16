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
 *Portions created by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights
 *Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id: 
 * Description:
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc. All Rights
 * Reserved. Contributor(s): ______________________________________..
 * *******************************************************************************/

if( !isset( $install_script ) || !$install_script ){
    die($mod_strings['ERR_NO_DIRECT_SCRIPT']);
}

///////////////////////////////////////////////////////////////////////////////
////	PREFILL $sugar_config VARS
if(empty($sugar_config['upload_dir'])) {
    $sugar_config['upload_dir'] = 'cache/upload/';
}
if(empty($sugar_config['upload_maxsize'])) {
	$sugar_config['upload_maxsize'] = 8192000;
}
if(empty($sugar_config['upload_badext'])) {
	$sugar_config['upload_badext'] = array('php', 'php3', 'php4', 'php5', 'pl', 'cgi', 'py', 'asp', 'cfm', 'js', 'vbs', 'html', 'htm');
}
if(empty($sugar_config['date_formats'])) {
	$sugar_config['date_formats'] = array(		'Y-m-d'=>'2006-12-23',
		'd-m-Y' => '23-12-2006',
      	'm-d-Y'=>'12-23-2006',
		'Y/m/d'=>'2006/12/23',
		'd/m/Y' => '23/12/2006',
		'm/d/Y'=>'12/23/2006',
		'Y.m.d' => '2006.12.23',
		'd.m.Y' => '23.12.2006',
		'm.d.Y' => '12.23.2006'
	);
}
if(empty($sugar_config['time_formats'])) {
	$sugar_config['time_formats'] = array(      'H:i'=>'23:00', 'h:ia'=>'11:00pm', 'h:iA'=>'11:00PM',
      'H.i'=>'23.00', 'h.ia'=>'11.00pm', 'h.iA'=>'11.00PM' );
}
if(empty($sugar_config['languages'])) {
	// language installation will add to this array
	$sugar_config['languages'] = array('en_us' => 'US English');
}
if(empty($sugar_config['default_currencies'])) {
	$sugar_config['default_currencies'] = array(
		'AUD' => array(	'name'		=> 'Austrailian Dollars',
						'iso4217'	=> 'AUD',
						'symbol'	=> '$'),
		'BRL' => array(	'name'		=> 'Brazilian Reais',
						'iso4217'	=> 'BRL',
						'symbol'	=> 'R$'),
		'GBP' => array(	'name'		=> 'British Pounds',
						'iso4217'	=> 'GBP',
						'symbol'	=> '£'),
		'CAD' => array(	'name'		=> 'Candian Dollars',
						'iso4217'	=> 'CAD',
						'symbol'	=> '$'),
		'CNY' => array(	'name'		=> 'Chinese Yuan',
						'iso4217'	=> 'CNY',
						'symbol'	=> '元'),
		'EUR' => array(	'name'		=> 'Euro',
						'iso4217'	=> 'EUR',
						'symbol'	=> '€'),
		'FRF' => array(	'name'		=> 'French Franc',
						'iso4217'	=> 'FRF',
						'symbol'	=> '₣'),
		'HKD' => array(	'name'		=> 'Hong Kong Dollars',
						'iso4217'	=> 'HKD',
						'symbol'	=> '$'),
		'INR' => array(	'name'		=> 'Indian Rupees',
						'iso4217'	=> 'INR',
						'symbol'	=> '₨'),
		'KRW' => array(	'name'		=> 'Korean Won',
						'iso4217'	=> 'KRW',
						'symbol'	=> '₩'),
		'YEN' => array(	'name'		=> 'Japanese Yen',
						'iso4217'	=> 'YEN',
						'symbol'	=> '¥'),
		'MXM' => array(	'name'		=> 'Mexican Pesos',
						'iso4217'	=> 'MXM',
						'symbol'	=> '$'),
		'SGD' => array(	'name'		=> 'Singaporean Dollars',
						'iso4217'	=> 'SGD',
						'symbol'	=> '$'),
		'THB' => array(	'name'		=> 'Thai Baht',
						'iso4217'	=> 'THB',
						'symbol'	=> '฿'),
		'USD' => array(	'name'		=> 'US Dollars',
						'iso4217'	=> 'USD',
						'symbol'	=> '$'),
	);	
}

////	END PREFILL $sugar_config VARS
///////////////////////////////////////////////////////////////////////////////
require_once('include/utils/zip_utils.php');
require_once('include/utils/file_utils.php');
require_once('include/upload_file.php');
require_once('include/dir_inc.php');

///////////////////////////////////////////////////////////////////////////////
////    PREP VARS FOR LANG PACK
    $base_upgrade_dir       = $sugar_config['upload_dir'] . "upgrades";
    $base_tmp_upgrade_dir   = $base_upgrade_dir."/temp";
///////////////////////////////////////////////////////////////////////////////    

///////////////////////////////////////////////////////////////////////////////
////    HANDLE FILE UPLOAD AND PROCESSING
$errors = array();
$uploadResult = '';
if(isset($_REQUEST['languagePackAction']) && !empty($_REQUEST['languagePackAction'])) {
    switch($_REQUEST['languagePackAction']) {
        case 'upload':
            $file = new UploadFile('language_pack');
    
            if($file->confirm_upload()) { // check for a real file
                if(strpos($file->mime_type, 'zip') !== false) { // only .zip files
                    if(langPackFinalMove($file)) { // move file to sugar upload_dir
                        $uploadResult = $mod_strings['LBL_LANG_SUCCESS'];
                        $result = langPackUnpack();
                    } else {
                        $errors[] = $mod_strings['ERR_LANG_UPLOAD_3'];   
                    }
                } else {
                    $errors[] = $mod_strings['ERR_LANG_UPLOAD_2'];
                }
            } else {
                $errors[] = $mod_strings['ERR_LANG_UPLOAD_1'];
            }
            
            if(count($errors) > 0) {
            	foreach($errors as $error) {
	            	$uploadResult .= $error."<br />";
            	}
            }
            
            break; // end 'validate'
        case 'commit':
            commitLanguagePack();
            break;
        case 'remove':
            removeLanguagePack();
            break;
        case 'uninstall':
        	uninstallLanguagePack();
        	break;
        default:
            break;                   
    }
}
////    END HANDLE FILE UPLOAD AND PROCESSING
///////////////////////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////////////////////////////
////    PRELOAD DISPLAY DATA
$upload_max_filesize = ini_get('upload_max_filesize');
$upload_max_filesize_bytes = return_bytes($upload_max_filesize);
$fileMaxSize ='';
define('SUGARCRM_MIN_UPLOAD_MAX_FILESIZE_BYTES', 6 * 1024 * 1024);
if($upload_max_filesize_bytes < constant('SUGARCRM_MIN_UPLOAD_MAX_FILESIZE_BYTES')) {
    $GLOBALS['log']->debug("detected upload_max_filesize: $upload_max_filesize");
    $fileMaxSize = '<p class="error">'.$mod_strings['ERR_UPLOAD_MAX_FILESIZE']."</p>\n";
}
$availableLanguagePacks = getLangPacks();
$installedLanguagePacks = getInstalledLangPacks();
$dateFormat = get_select_options_with_id($sugar_config['date_formats'], isset($_SESSION['default_date_format']) ? $_SESSION['default_date_format'] : 'Y-m-d');
$timeFormat = get_select_options_with_id($sugar_config['time_formats'], isset($_SESSION['default_time_format']) ? $_SESSION['default_time_format'] : 'Y-m-d');
$languages  = get_select_options_with_id(get_languages(), isset($_SESSION['default_language']) ? $_SESSION['default_language'] : 'en_us');
$nameFormat = isset($_SESSION['default_locale_name_format']) ? $_SESSION['default_locale_name_format'] : 's f l';
$defaultCurrencyName = isset($_SESSION['default_currency_name']) ? $_SESSION['default_currency_name'] : 'US Dollar';
$defaultCurrencySymbol = isset($_SESSION['default_currency_symbol']) ? $_SESSION['default_currency_symbol'] : '$';
$defaultCurrencyIso = isset($_SESSION['default_currency_iso4217']) ? $_SESSION['default_currency_iso4217'] : 'USD';
$separator = isset($_SESSION['default_number_grouping_seperator']) ? $_SESSION['default_number_grouping_seperator'] : ',';
$decimal = isset($_SESSION['default_decimal_seperator']) ? $_SESSION['default_decimal_seperator'] : '.';
$getNameJs = $locale->getNameJs($mod_strings['LBL_LOCALE_NAME_FIRST'], $mod_strings['LBL_LOCALE_NAME_LAST'], $mod_strings['LBL_LOCALE_NAME_SALUTATION']);
$charsets = get_select_options_with_id($locale->getCharsetSelect(), isset($_SESSION['default_export_charset']) ? $_SESSION['default_export_charset'] : 'ISO-8859-1');
$exportDelimiter = (isset($_SESSION['export_delimiter'])) ? $_SESSION['export_delimiter'] : ',';

// default currencies    
$currencySelect = '';
$currencyDefs = "var currencyDefs = new Object;\r";
foreach($sugar_config['default_currencies'] as $iso4217 => $currency) {
	$currencyDefs .= "currencyDefs.{$iso4217} = new Object;\r";
	$currencyDefs .= "currencyDefs.{$iso4217}.name = '{$currency['name']}';\r";
	$currencyDefs .= "currencyDefs.{$iso4217}.symbol = '{$currency['symbol']}';\r";
	$currencyDefs .= "currencyDefs.{$iso4217}.iso4217 = '{$currency['iso4217']}';\r";
	
	$selected = '';
	if($iso4217 == $defaultCurrencyIso) {
		$selected = ' SELECTED';
	}
	$currencySelect .= "<option value='{$iso4217}'{$selected}> {$currency['name']} </option>";
}
////    PRELOAD DISPLAY DATA
///////////////////////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////////////////////////////
////    BEING PAGE OUTPUT
$disabled = "";
$result = "";
$out =<<<EOQ
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
   <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
   <meta http-equiv="Content-Script-Type" content="text/javascript">
   <meta http-equiv="Content-Style-Type" content="text/css">
   <title>{$mod_strings['LBL_WIZARD_TITLE']} {$next_step}</title>
   <link rel="stylesheet" href="install/install.css" type="text/css">
   <script type="text/javascript" src="install/installCommon.js"></script>
</head>

<body onLoad="setFocus();">
{$fileMaxSize}
  <table cellspacing="0" width="100%" cellpadding="0" border="0" align="center" class="shell">
    <tr>
      <th width="400">{$mod_strings['LBL_STEP']} {$next_step}: {$mod_strings['LBL_LOCALE_TITLE']} & {$mod_strings['LBL_LANG_TITLE']}</th>
      <th width="200" height="30" style="text-align: right;"><a href="http://www.sugarcrm.com" target=
      "_blank"><IMG src="include/images/sugarcrm_login_65.png" alt="SugarCRM" border="0"></a></th>
    </tr>

    <tr>
		<td colspan="2">
			<p>{$mod_strings['LBL_LANG_1']}</p>
			<table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" class="StyleDottedHr">
				<tr>
		    		<th colspan="2" align="left">{$mod_strings['LBL_LANG_TITLE']}</th>
		    	</tr>
				<tr>
					<td colspan="2">
			        <form name="the_form" enctype="multipart/form-data" 
			            action="install.php" method="post">
			            <input type="hidden" name="current_step" value="{$next_step}">
			            <input type="hidden" name="goto" value="{$mod_strings['LBL_CHECKSYS_RECHECK']}">
			            <input type="hidden" name="languagePackAction" value="upload">
			        
			        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabForm">
			            <tr>
			                <td>
			                    <table width="450" border="0" cellspacing="0" cellpadding="0">
			                        <tr>
			                            <td>
			                                {$mod_strings['LBL_LANG_UPLOAD']}:<br />
			                                <input type="file" name="language_pack" size="40" />
			                            </td>
			                            <td valign="bottom">
			                                <input type=button value="{$mod_strings['LBL_LANG_BUTTON_UPLOAD']}" 
			                                    onClick="document.the_form.language_pack_escaped.value = escape( document.the_form.language_pack.value );
			                                             document.the_form.submit();"
			                                />
			                                <input type=hidden name="language_pack_escaped" value="" />
			                            </td>
			                        </tr>
			                    </table>
			                </td>
			            </tr>
			            <tr>
			            	<td>
			            		{$uploadResult}
			            	</td>
			            </tr>
			        </table>
			        </form>
			      </td>
			    </tr>
				<tr>
					<td colspan=2>
						{$result}
					</td>
				</tr>
				<!--// Available Upgrades //-->
				<tr>
					<td align="left" colspan="2">
						<hr>
						<table cellspacing="0" cellpadding="0" border="0" class="stdTable">
							{$availableLanguagePacks}
						</table>
					</td>
				</tr>
				<!--// INSTALLED Upgrades //-->
				<tr>
					<td align="left" colspan="2">
						<hr>
						<table cellspacing="0" cellpadding="0" border="0" class="stdTable">
							{$installedLanguagePacks}
						</table>
					</td>
				</tr>
				
				
				
				<form action="install.php" method="post" name="theForm" id="theForm">
				<tr>
		    		<th colspan="2" align="left">{$mod_strings['LBL_LOCALE_TITLE']}</th>
		    	</tr>
				<tr>
					<td colspan="2">
						{$mod_strings['LBL_LOCALE_DESC']}
						<hr>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<b>{$mod_strings['LBL_LOCALE_UI']}</b>
					</td>
				</tr>
				<tr>	
					<td>
						{$mod_strings['LBL_LOCALE_DATEF']}:
					</td>
					<td>
						<select name="default_date_format">{$dateFormat}</select>
					</td>
				</tr>
				<tr>
					<td>
						{$mod_strings['LBL_LOCALE_TIMEF']}:
					</td>
					<td>
						<select name="default_time_format">{$timeFormat}</select>
					</td>
				</tr>
				<tr>
					<td>
						{$mod_strings['LBL_LOCALE_LANG']}:
					</td>
					<td>
						<select name="default_language">{$languages}</select>
					</td>
				</tr>
				<tr>
					<td>
						{$mod_strings['LBL_LOCALE_NAMEF']}:
					</td>
					<td>
						<input onkeyup="setPreview();" onkeydown="setPreview();" id="default_locale_name_format" name="default_locale_name_format" value="{$nameFormat}">&nbsp;<input name="no_value" id="nameTarget" value="" disabled>
						<br />
						{$mod_strings['LBL_LOCALE_NAMEF_DESC']}
					</td>
				</tr>


				<tr>
					<td colspan="2">
						<b>{$mod_strings['LBL_LOCALE_EXPORT_TITLE']}</b>
					</td>
				</tr>
				<tr>
					<td>
						{$mod_strings['LBL_LOCALE_EXPORT']}:
					</td>
					<td>
						<select name="default_export_charset">{$charsets}</select>
					</td>
				</tr>
				<tr>
					<td>
						{$mod_strings['LBL_LOCALE_EXPORT_DELIMITER']}:
					</td>
					<td>
						<input type="text" name="export_delimiter" value="{$exportDelimiter}">
					</td>
				</tr>



				<tr>
					<td colspan="2">
						<b>{$mod_strings['LBL_LOCALE_CURRENCY']}</b>
					</td>
				</tr>
				<tr>
					<td>
						{$mod_strings['LBL_LOCALE_CURR_DEFAULT']}:
					</td>
					<td>
						<select id='currency' onchange='fillCurrency(this.value);' name='currency'>{$currencySelect}</select>
						<input type="text" disabled id="symbol" name="symbol" value="{$defaultCurrencySymbol}" size="2" style="text-align:center">
						<input type="text" disabled id="iso4217" name="iso4217" value="{$defaultCurrencyIso}" size="3" style="text-align:center">
						<input type="hidden" id="default_currency_name" name="default_currency_name" value="{$defaultCurrencyName}">
						<input type="hidden" id="default_currency_symbol" name="default_currency_symbol" value="{$defaultCurrencySymbol}">
						<input type="hidden" id="default_currency_iso4217" name="default_currency_iso4217" value="{$defaultCurrencyIso}">
					</td>
				</tr>
				<tr>
					<td>
						{$mod_strings['LBL_LOCALE_CURR_1000S']}:
					</td>
					<td>
						<input name="default_number_grouping_seperator" value="{$separator}">
					</td>
				</tr>
				<tr>
					<td>
						{$mod_strings['LBL_LOCALE_CURR_DECIMAL']}:
					</td>
					<td>
						<input name="default_decimal_seperator" value="{$decimal}">
					</td>
				</tr>
				<tr>
					<td align="right" colspan="2">
						<hr>
						<input type="hidden" name="current_step" value="{$next_step}">
						<table cellspacing="0" cellpadding="0" border="0" class="stdTable">
							<tr>
								<td>
									<input class="button" type="button" onclick="window.open('http://www.sugarcrm.com/forums/');" value="{$mod_strings['LBL_HELP']}" />
								</td>
								<td>
									<input class="button" type="button" name="Back" value="{$mod_strings['LBL_BACK']}" onclick="document.getElementById('theForm').submit();" />
									<input type="hidden" name="goto" value="{$mod_strings['LBL_BACK']}" />
								</td>
								<td>
									<input class="button" type="submit" name="goto" value="{$mod_strings['LBL_NEXT']}" id="defaultFocus" {$disabled} />
								</td>
							</tr>
						</table>
						</form>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<br>

<script language="Javascript" type="text/javascript">
	{$getNameJs}
	function fillCurrency(keyIso) {
		{$currencyDefs}
		document.getElementById('symbol').value = currencyDefs[keyIso].symbol;
		document.getElementById('iso4217').value = currencyDefs[keyIso].iso4217;
		
		document.getElementById('default_currency_symbol').value = currencyDefs[keyIso].symbol;
		document.getElementById('default_currency_iso4217').value = currencyDefs[keyIso].iso4217;
		document.getElementById('default_currency_name').value = currencyDefs[keyIso].name;
	}
	
	fillCurrency('{$defaultCurrencyIso}');
</script>

</body>
</html>
EOQ;

echo $out;

unlinkTempFiles('','');
////    END PAGEOUTPUT
///////////////////////////////////////////////////////////////////////////////
?>