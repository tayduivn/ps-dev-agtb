<?php
if(!defined('sugarEntry'))define('sugarEntry', true);
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
/*********************************************************************************
 * $Id: licensePrint.php,v 1.6 2006/06/06 17:57:53 majed Exp $
 * Description:  printable license page.
 ********************************************************************************/
require_once("install/language/{$_GET['language']}.lang.php");
require_once("include/utils.php");

// cn: set php.ini settings at entry points
setPhpIniSettings();

$license_file_name = "LICENSE.txt";
$fh = fopen( $license_file_name, 'r' ) or die( "License file not found!" );
$license_file = fread( $fh, filesize( $license_file_name ) );
fclose( $fh );

$out =<<<EOQ
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
   <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
   <meta http-equiv="Content-Style-Type" content="text/css">
   <title>{$mod_strings['LBL_LICENSE_TITLE_2']}</title>
   <link rel="stylesheet" href="install/install.css" type="text/css">
   <script type="text/javascript" src="install/license.js"></script>
</head>

<body>
<form>
  <table cellspacing="0" cellpadding="0" border="0" align="center" class="shell">
    <tr>
      <td>
        <input type="button" name="close_windows" value=" {$mod_strings['LBL_CLOSE']} " onClick='window.close();' />
        <input type="button" name="print_license" value=" {$mod_strings['LBL_PRINT']} " onClick='window.print();' />
      </td>
    </tr>
    <tr>
    </tr>
    <tr>
      <td>
        <pre>
            {$license_file}
        </pre>
      </td>
    </tr>
  </table>
</form>
</body>
</html>
EOQ;
echo $out;
?>