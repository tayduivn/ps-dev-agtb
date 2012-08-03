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

$the_string = <<<EOQ
document.write("<iframe frameborder='0' width='600' height='500' src='{$_SESSION['setup_site_url']}/index.php'></iframe>");
EOQ;
if( $fh = @fopen( 'sugarportal.js', "w" ) ){
    fputs( $fh, $the_string, strlen($the_string) );
    fclose( $fh );
}

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

<body>
  <table cellspacing="0" cellpadding="0" border="0" align="center" class="shell">
    <tr>
      <th width="400">{$mod_strings['LBL_STEP']} {$next_step}: {$mod_strings['LBL_SCRIPT_COPY']}</th>
      <th width="200" height="30" style="text-align: right;"><a href="http://www.sugarcrm.com" target="_blank">
      	<IMG src="include/images/sugarcrm_login_65.png" alt="SugarCRM" border="0"></a>
      </th>
    </tr>

    <tr>
      <td colspan="2" width="600" style="background-position:right; background-repeat : no-repeat;">
	    <p><img src="include/images/logo_sugarportal_65.png" alt="SugarCRM" border="0"></p>
        <h2>{$mod_strings['LBL_IMPORTANT']}</h2>
        <b>{$mod_strings['LBL_COPY_SCRIPT']}</b>
        <textarea cols="80" rows="4" readonly><script type='text/javascript' src='{$_SESSION['setup_site_url']}/sugarportal.js'></script></textarea>
        <br><i>{$mod_strings['LBL_SCRIPT_MOD']}</i> <br>[ {$_SESSION['setup_site_url']}/sugarportal.js ]
      </td>
    </tr>
    <tr>
      <td align="right" colspan="2">
        <hr>
        <form action="install.php" method="post" name="setConfig" id="form">
        <input type="hidden" name="current_step" value="{$next_step}">
        <table cellspacing="0" cellpadding="0" border="0" class="stdTable">
          <tr>
            <td><input class="button" type="button" onclick="window.open('http://www.sugarcrm.com/forums/');" value="{$mod_strings['LBL_HELP']}" /></td>
            <td>
                <input class="button" type="button" value="{$mod_strings['LBL_BACK']}" onclick="document.getElementById('form').submit();" />
	            <input type="hidden" name="goto" value="{$mod_strings['LBL_BACK']}" />
            </form>
			</td>
            <td>
				<form action="index.php" method="post" name="appform" id="appform">
                    <input type="hidden" name="default_user_name" value="admin">
                    <input class="button" type="submit" name="next" value="{$mod_strings['LBL_PERFORM_FINISH']}" />
		    	</form>
            </td>
          </tr>
        </table>
      </td>
    </tr>

  </table>
</body>
</html>
EOQ;

echo $out;
?>