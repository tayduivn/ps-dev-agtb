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

// $Id: license.php,v 1.16 2006/06/06 17:57:53 majed Exp $

if( !isset( $install_script ) || !$install_script ){
    die('Unable to process script directly.');
}

// setup session variables (and their defaults) if this page has not yet been submitted
if(!isset($_SESSION['license_submitted']) || !$_SESSION['license_submitted']){
    $_SESSION['setup_license_accept'] = false;
}

$checked = (isset($_SESSION['setup_license_accept']) && !empty($_SESSION['setup_license_accept'])) ? 'checked="on"' : '';

$license_file_name = "LICENSE.txt";
$fh = fopen( $license_file_name, 'r' ) or die( $mod_strings['ERR_LICENSE_NOT_FOUND'] );
$license_file = fread( $fh, filesize( $license_file_name ) );
fclose( $fh );

$out =<<<EOQ
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
   <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
   <meta http-equiv="Content-Style-Type" content="text/css">
   <title>{$mod_strings['LBL_WIZARD_TITLE']} {$next_step}</title>
   <link rel="stylesheet" href="install/install.css" type="text/css">
   <script type="text/javascript" src="install/license.js"></script>
</head>

<body onload="javascript:toggleNextButton();document.getElementById('defaultFocus').focus();">
<form action="install.php" method="post" name="setConfig" id="form">
  <table cellspacing="0" cellpadding="0" border="0" align="center" class="shell">
    <tr>
      <th width="400">{$mod_strings['LBL_STEP']} {$next_step}: {$mod_strings['LBL_LICENSE_ACCEPTANCE']}</th>
      <th width="200" height="30" style="text-align: right;"><a href="http://www.sugarcrm.com" target="_blank">
      	<IMG src="include/images/sugarcrm_login_65.png" alt="SugarCRM" border="0"></a>
      </th>
    </tr>

    <tr>
      <td colspan="2" width="600" style="background-position:right; background-repeat : no-repeat;">
	    <p><img src="include/images/logo_sugarportal_65.png" alt="SugarCRM" border="0"></p>
        <textarea cols="80" rows="20" readonly>{$license_file}</textarea>
      </td>
    </tr>
    <tr>
      <td align=left>
        <input type="checkbox" class="checkbox" name="setup_license_accept" id="defaultFocus" onClick='toggleNextButton();' {$checked} />
        <a href='javascript:void(0)' onClick='toggleLicenseAccept();toggleNextButton();'>{$mod_strings['LBL_LICENSE_I_ACCEPT']}</a>
      </td>
      <td align=right>
        <input type="button" class="button" name="print_license" value="{$mod_strings['LBL_LICENSE_PRINTABLE']}" 
        	onClick='window.open("install.php?page=licensePrint&language={$current_language}");' />
      </td>
    </tr>

    <tr>
      <td align="right" colspan="2">
        <hr>
        <input type="hidden" name="current_step" value="{$next_step}">
        <table cellspacing="0" cellpadding="0" border="0" class="stdTable">
          <tr>
            <td><input class="button" type="button" onclick="window.open('http://www.sugarcrm.com/forums/');" value="{$mod_strings['LBL_HELP']}" /></td>
            <td>
                <input class="button" type="button" value="{$mod_strings['LBL_BACK']}" onclick="document.getElementById('form').submit();" />
	            <input type="hidden" name="goto" value="{$mod_strings['LBL_BACK']}" />
            </td>
            <td><input class="button" type="submit" name="goto" value="{$mod_strings['LBL_NEXT']}" id="button_next" disabled="disabled" /></td>
          </tr>
        </table>
      </td>
    </tr>

  </table>
</form>
</body>
</html>
EOQ;

echo $out;
?>