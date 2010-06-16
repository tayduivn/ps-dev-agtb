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
 * $Id: phprint.php,v 1.29 2006/06/06 17:58:54 majed Exp $
 * Description: Main file and starting point for the application.  Calls the
 * theme header and footer files defined for the user as well as the module as
 * defined by the input parameters.
 ********************************************************************************/

require_once('include/utils.php');

clean_special_arguments();
// cn: set php.ini settings at entry points
setPhpIniSettings();

$query_string = "";
require_once('modules/ACL/ACLController.php');
foreach ($_GET as $key => $val) {
	if ($key != "print") {
		if (is_array($val)) {
			foreach ($val as $k => $v) {
				$query_string .= "{$key}[{$k}]=" . urlencode($v) . "&";
			}
		}
		else {
			$query_string .= "{$key}=" . urlencode($val) . "&";
		}
	}
}

$url = "{$_SERVER['PHP_SELF']}?{$query_string}";

?>
<html>
<head>
<script language="JavaScript">
function doNothing() {return true;}
window.onerror=doNothing;
</script>
<style type="text/css" media="all">
BODY { font-family: Arial, Helvetica, sans-serif; }
</style>
</head>

<body>
<a href="<?php echo $url; ?>"><< <?php echo $app_strings['LBL_BACK']; ?></a><br><br>
<?php
echo $page_arr[1];
?>
<br><br><a href="<?php echo $url; ?>"><< <?php echo $app_strings['LBL_BACK']; ?></a>
</body>
</html>