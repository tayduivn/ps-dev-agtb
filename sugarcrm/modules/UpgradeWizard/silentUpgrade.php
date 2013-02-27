<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
function build_argument_string($arguments=array()) {
   if(!is_array($arguments)) {
   	  return '';
   }

   $argument_string = '';
   $count = 0;
   foreach($arguments as $arg) {
   	   if($count != 0)
   	   {
   	   	  //If current directory or parent directory is specified, substitute with full path
   	   	  if($arg == '.')
   	   	  {
   	   	  	 $arg = getcwd();
   	   	  } else if ($arg == '..') {
   	   	  	 $dir = getcwd();
			 $arg = substr($dir, 0, strrpos($dir, DIRECTORY_SEPARATOR));
   	   	  }
          $argument_string .= ' ' . escapeshellarg($arg);
   	   }
   	   $count++;
   }

   return $argument_string;
}

//Bug 52872. Dies if the request does not come from CLI.
$sapi_type = php_sapi_name();
if (substr($sapi_type, 0, 3) != 'cli') {
    die("This is command-line only script");
}
//End of #52872

if(defined('PHP_BINDIR')) {
    $php_path = PHP_BINDIR."/";
} else {
    $php_path = '';
}

$php_file = $argv[0];
$p_info = pathinfo($php_file);
$php_dir = (isset($p_info['dirname']) && $p_info['dirname'] != '.') ?  $p_info['dirname'] . DIRECTORY_SEPARATOR : '';

//Make sure that the php executable really exists; if not, just default back assuming the executable is set
if(!file_exists($php_path . 'php')) {
    $php_path = '';
}

for($step=1;$step<=3;$step++) {
    $step_cmd = $php_path."php -f {$php_dir}silentUpgrade_step{$step}.php " . build_argument_string($argv);
    passthru($step_cmd, $output);
    if($output != 0) {
	    echo "***************         Step {$step} failed         ***************: $output\n";
	    exit(1);
    } else {
        echo "***************         Step {$step} OK\n";
    }
}

echo "***************         SUCCESS!\n";
exit(0);