<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************

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
	$sugar_config['date_formats'] = array(		'Y-m-d'=>'2010-12-23',
		'd-m-Y' => '23-12-2010',
      	'm-d-Y'=>'12-23-2010',
		'Y/m/d'=>'2010/12/23',
		'd/m/Y' => '23/12/2010',
		'm/d/Y'=>'12/23/2010',
		'Y.m.d' => '2010.12.23',
		'd.m.Y' => '23.12.2010',
		'm.d.Y' => '12.23.2010'
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
	$sugar_config['default_currencies'] = $locale->getDefaultCurrencies();
}

////	END PREFILL $sugar_config VARS
///////////////////////////////////////////////////////////////////////////////
require_once('include/utils/zip_utils.php');

require_once('include/upload_file.php');


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
            	// cn: bug 9072 - apache sometimes detects zip as binary MIME type
                if((strpos($file->mime_type, 'binary') && strtolower($file->file_ext) == 'zip') || (strpos($file->mime_type, 'zip') !== false)) { // only .zip files
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
            $sugar_config = commitLanguagePack();
            break;
        case 'uninstall': // leaves zip file in "uploaded" state
        	$sugar_config = uninstallLanguagePack();
        	break;
        case 'remove':
            removeLanguagePack();
            break;
        default:
            break;                   
    }
}
////    END HANDLE FILE UPLOAD AND PROCESSING
///////////////////////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////////////////////////////
////    PRELOAD DISPLAY DATA

$availableLanguagePacks = getLangPacks();
$installedLanguagePacks = getInstalledLangPacks();
$dateFormat = get_select_options_with_id($sugar_config['date_formats'], isset($_SESSION['default_date_format']) ? $_SESSION['default_date_format'] : 'm/d/Y');
$timeFormat = get_select_options_with_id($sugar_config['time_formats'], isset($_SESSION['default_time_format']) ? $_SESSION['default_time_format'] : 'h:ia');
$languages  = get_select_options_with_id(get_languages(), isset($_SESSION['default_language']) ? $_SESSION['default_language'] : 'en_us');
$nameFormat = isset($_SESSION['default_locale_name_format']) ? $_SESSION['default_locale_name_format'] : 's f l';
$defaultCurrencyName = isset($_SESSION['default_currency_name']) ? $_SESSION['default_currency_name'] : 'US Dollar';
$defaultCurrencySymbol = isset($_SESSION['default_currency_symbol']) ? $_SESSION['default_currency_symbol'] : '$';
$defaultCurrencyIso = isset($_SESSION['default_currency_iso4217']) ? $_SESSION['default_currency_iso4217'] : 'USD';
$separator = isset($_SESSION['default_number_grouping_seperator']) ? $_SESSION['default_number_grouping_seperator'] : ',';
$decimal = isset($_SESSION['default_decimal_seperator']) ? $_SESSION['default_decimal_seperator'] : '.';
$getNameJs = $locale->getNameJs($mod_strings['LBL_LOCALE_NAME_FIRST'], $mod_strings['LBL_LOCALE_NAME_LAST'], $mod_strings['LBL_LOCALE_NAME_SALUTATION']);
$getNumberJs = $locale->getNumberJs();
$charsets = get_select_options_with_id($locale->getCharsetSelect(), isset($_SESSION['default_export_charset']) ? $_SESSION['default_export_charset'] : 'CP1252');
$charsetsEmail = get_select_options_with_id($locale->getCharsetSelect(), isset($_SESSION['default_email_charset']) ? $_SESSION['default_email_charset'] : 'ISO-8859-1');
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
$signficantDigits = (isset($_SESSION['default_currency_significant_digits']) && !empty($_SESSION['default_currency_significant_digits'])) ? $_SESSION['default_currency_significant_digits'] : 2;
$sigDigits = '';
for($i=0; $i<=6; $i++) {
	$sigDigitsSelected = ($signficantDigits == $i) ? ' SELECTED' : '';
	$sigDigits .= "<option value='{$i}'{$sigDigitsSelected}>{$i}</option>";
}

$errs = '';
if(isset($validation_errors)) {
	if(count($validation_errors) > 0) {
		$errs  = '<div id="errorMsgs">';
		$errs .= "<p>{$mod_strings['LBL_SYSOPTS_ERRS_TITLE']}</p>";
		$errs .= '<ul>';

		foreach($validation_errors as $error) {
			$errs .= '<li>' . $error . '</li>';
		}

		$errs .= '</ul>';
		$errs .= '</div>';
	}
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
   <title>{$mod_strings['LBL_WIZARD_TITLE']}{$mod_strings['LBL_LOCALE_TITLE']}</title>
   <link REL="SHORTCUT ICON" HREF="$icon">
   <link rel="stylesheet" href="$css" type="text/css">
   <script type="text/javascript" src="$common"></script>
</head>

<body onLoad="document.getElementById('defaultFocus').focus();">
  <table cellspacing="0" width="100%" cellpadding="0" border="0" align="center" class="shell">
      <tr><td colspan="2" id="help"><a href="{$help_url}" target='_blank'>{$mod_strings['LBL_HELP']} </a></td></tr>
    <tr>
      <th width="500">
		<p><img src="$sugar_md" alt="SugarCRM" border="0"></p>
		{$mod_strings['LBL_LOCALE_TITLE']}</th>
      <th width="200" style="text-align: right;"><a href="http://www.sugarcrm.com" target=
      "_blank"><IMG src="$loginImage" width="145" height="30" alt="SugarCRM" border="0"></a>
      </th>
    </tr>

    <tr>
		<td colspan="2">

			<table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" class="StyleDottedHr">				
				<form action="install.php" method="post" name="theForm" id="theForm">
				<tr>
		    		<th colspan="2" align="left">{$mod_strings['LBL_CUSTOMIZE_LOCALE']}</th>
		    	</tr>
				<tr>
					<td colspan="2">
						{$mod_strings['LBL_LOCALE_DESC']}
						{$errs}
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
EOQ;



//hide this in typical mode
if(isset($_SESSION['install_type'])  && !empty($_SESSION['install_type'])  && strtolower($_SESSION['install_type'])=='custom'){
$out .=<<<EOQ
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
                        <input onkeyup="setNamePreview();" onkeydown="setNamePreview();" id="default_locale_name_format" name="default_locale_name_format" value="{$nameFormat}">&nbsp;<span id='nameTargetDiv'></span><input type='hidden' name="no_value" id="nameTarget" value="" disabled>
                        <br />
                        {$mod_strings['LBL_LOCALE_NAMEF_DESC']}
                    </td>
                </tr>

				<tr>
					<td colspan="2">
						<br><b>{$mod_strings['LBL_EMAIL_CHARSET_TITLE']}</b>
					</td>
				</tr>
				<tr>
					<td>
						{$mod_strings['LBL_EMAIL_CHARSET_DESC']}:
					</td>
					<td>
						<select name="default_email_charset">{$charsetsEmail}</select>
					</td>
				</tr>


				<tr>
					<td colspan="2">
						<br><b>{$mod_strings['LBL_LOCALE_EXPORT_TITLE']}</b>
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

EOQ;
}

$out .=<<<EOQ
				<tr>
					<td colspan="2">
						<br><b>{$mod_strings['LBL_LOCALE_CURRENCY']}</b>
					</td>
				</tr>
				<tr>
					<td>
						{$mod_strings['LBL_LOCALE_CURR_DEFAULT']}:
					</td>
					<td nowrap>
						<select id='currency' onchange='fillCurrency(this.value,true); setDigits();' name='currency'>{$currencySelect}</select>
						<span id="symbol_span" name="symbol">{$defaultCurrencySymbol}</span>&nbsp;&nbsp;
                        <span id="iso4217_span" name="iso4217">{$defaultCurrencyIso}</span>

                        <input type="hidden" disabled id="symbol" name="symbol" value="{$defaultCurrencySymbol}" size="2" style="text-align:center">
						<input type="hidden" disabled id="iso4217" name="iso4217" value="{$defaultCurrencyIso}" size="3" style="text-align:center">
						<input type="hidden" id="default_currency_name" name="default_currency_name" value="{$defaultCurrencyName}">
						<input type="hidden" id="default_currency_symbol" name="default_currency_symbol" value="{$defaultCurrencySymbol}">
						<input type="hidden" id="default_currency_iso4217" name="default_currency_iso4217" value="{$defaultCurrencyIso}">
					</td>
				</tr>
				<tr>
					<td>
						{$mod_strings['LBL_LOCALE_CURR_SIG_DIGITS']}:
					</td>
					<td>
						<select id='sigDigits' onchange='setDigits(this.value);' name='default_currency_significant_digits'>{$sigDigits}</select>
					</td>
				</tr>
				<tr>
					<td>
						{$mod_strings['LBL_LOCALE_CURR_1000S']}:
					</td>
					<td>
						<input onkeyup="setDigits();" onkeydown="setDigits();" id="default_number_grouping_seperator" name="default_number_grouping_seperator" value="{$separator}">
					</td>
				</tr>
				<tr>
					<td>
						{$mod_strings['LBL_LOCALE_CURR_DECIMAL']}:
					</td>
					<td>
						<input onkeyup="setDigits();" onkeydown="setDigits();" id="default_decimal_seperator" name="default_decimal_seperator" value="{$decimal}">
					</td>
				</tr>
				<tr>
					<td>
						<i>{$mod_strings['LBL_LOCALE_CURR_EXAMPLE']}</i>:
					</td>
					<td>
						<input type="hidden" disabled id="sigDigitsExample" name="sigDigitsExample">
                        <span id="sigDigitsSpan" ></span>
					</td>
				</tr>
            </table>
        </td>
    </tr>
	<tr>
		<td align="right" colspan="2">
			<hr>
			<input type="hidden" name="current_step" value="{$next_step}">
			<table cellspacing="0" cellpadding="0" border="0" class="stdTable">
				<tr>
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
<br>

<script language="Javascript" type="text/javascript">
EOQ;



//hide this in typical mode
if(isset($_SESSION['install_type'])  && !empty($_SESSION['install_type'])  && strtolower($_SESSION['install_type'])=='custom'){
$out .=<<<EOQ
	{$getNameJs}
EOQ;
}

$out .=<<<EOQ
	{$getNumberJs}
	
	function fillCurrency(keyIso,resetDefaults) {
		{$currencyDefs}
        document.getElementById('symbol_span').innerHTML = '&nbsp;'+currencyDefs[keyIso].symbol;
        document.getElementById('iso4217_span').innerHTML = '&nbsp;'+currencyDefs[keyIso].iso4217;
        document.getElementById('symbol').value = currencyDefs[keyIso].symbol;
        document.getElementById('iso4217').value= currencyDefs[keyIso].iso4217;
	
		document.getElementById('default_currency_symbol').value = currencyDefs[keyIso].symbol;
		document.getElementById('default_currency_iso4217').value = currencyDefs[keyIso].iso4217;
		document.getElementById('default_currency_name').value = currencyDefs[keyIso].name;
        //if defaults should be reset
        if(resetDefaults){
            setCurrFromDD();
        }
	}
	
	fillCurrency('{$defaultCurrencyIso}',false);

	function setDigits(){
        setSigDigits();
        document.getElementById('sigDigitsSpan').innerHTML = document.getElementById('sigDigitsExample').value;
    } 
	setDigits();
    
    function setCurrFromDD(){
     ddVal = document.getElementById('currency').value;
     if(ddVal == "CHF" || ddVal == "EUD" || ddVal == "BRL"){
        document.getElementById('sigDigits').value = '2';   
        document.getElementById('default_number_grouping_seperator').value = '.';
        document.getElementById('default_decimal_seperator').value = ',';        
     }else{
        document.getElementById('sigDigits').value = '2';   
        document.getElementById('default_number_grouping_seperator').value = ',';
        document.getElementById('default_decimal_seperator').value = '.';        
     } 
     
     
        
    }
EOQ;



//hide this in typical mode
if(isset($_SESSION['install_type'])  && !empty($_SESSION['install_type'])  && strtolower($_SESSION['install_type'])=='custom'){
$out .=<<<EOQ
    function setNamePreview(){
        setPreview();
        document.getElementById('nameTargetDiv').innerHTML = document.getElementById('nameTarget').value;
    }
    setNamePreview();
EOQ;
}

$out .=<<<EOQ
</script>

</body>
</html>
EOQ;

echo $out;

unlinkTempFiles('','');
////    END PAGEOUTPUT
///////////////////////////////////////////////////////////////////////////////
?>