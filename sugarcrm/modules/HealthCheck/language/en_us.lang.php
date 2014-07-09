<?php

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

$mod_strings = array(
    'LBL_MODULE_NAME' => 'Health Check',
    'LBL_MODULE_NAME_SINGULAR' => 'Health Check',
    'LBL_MODULE_TITLE' => 'Health Check',
    'LBL_LOGFILE' => 'Log file',
    'LBL_BUCKET' => 'Bucket',
    'LBL_FLAG' => 'Flag',
    'LBL_LOGMETA' => 'Log meta',
    'LBL_ERROR' => 'Error',

    'LBL_SCAN_101_LOG' => '%s has studio history',
    'LBL_SCAN_102_LOG' => '%s has extensions: %s',
    'LBL_SCAN_103_LOG' => '%s has custom vardefs',
    'LBL_SCAN_104_LOG' => '%s has custom layoutdefs',
    'LBL_SCAN_105_LOG' => '%s has custom viewdefs',

    'LBL_SCAN_201_LOG' => '% is not a stock module',

    'LBL_SCAN_301_LOG' => '%s to be run as BWC',
    'LBL_SCAN_302_LOG' => 'Unknown file views present - %s is not MB module',
    'LBL_SCAN_303_LOG' => 'Non-empty form file %s - %s is not MB module',
    'LBL_SCAN_304_LOG' => 'Unknown file %s - %s is not MB module',
    'LBL_SCAN_305_LOG' => 'Bad vardefs - key %s, name %s',
    'LBL_SCAN_306_LOG' => 'Bad vardefs - relate field %s has empty `module`',
    'LBL_SCAN_307_LOG' => 'Bad vardefs - link %s refers to invalid relationship',
    'LBL_SCAN_308_LOG' => 'Vardef HTML function in %s',
    'LBL_SCAN_309_LOG' => 'Bad md5 for %s',
    'LBL_SCAN_310_LOG' => 'Unknown file %s/%s',

    'LBL_SCAN_401_LOG' => 'Vendor files inclusion found, for files that have been moved to vendor/:\r\n%s',
    'LBL_SCAN_402_LOG' => 'Bad module %s - not in beanList and not in filesystem',
    'LBL_SCAN_403_LOG' => 'Logic hook after_ui_frame detected',
    'LBL_SCAN_404_LOG' => 'Logic hook after_ui_footer detected',
    'LBL_SCAN_405_LOG' => 'Incompatible Integration - %s %s',
    'LBL_SCAN_406_LOG' => '%s has custom views',
    'LBL_SCAN_407_LOG' => '%s has custom views in module dir',
    'LBL_SCAN_408_LOG' => 'Extension dir %s detected',
    'LBL_SCAN_409_LOG' => 'Found customCode %s in %s',
    'LBL_SCAN_410_LOG' => 'Max fields - Found more than %s fields (%s) in %s',
    'LBL_SCAN_411_LOG' => 'Found \'get_subpanel_data\' with \'function:\' value in %s',
    'LBL_SCAN_412_LOG' => 'Bad subpanel link %s in %s',
    'LBL_SCAN_413_LOG' => 'Unknown widget class detected: %s for %s',
    'LBL_SCAN_414_LOG' => 'Unknown fields handled by CRYS-36, so no more checks here',
    'LBL_SCAN_415_LOG' => 'Bad hook file in %s: %s',
    'LBL_SCAN_416_LOG' => 'By-ref parameter in hook file %s function %s',
    'LBL_SCAN_417_LOG' => 'Incompatible module %s',
    'LBL_SCAN_418_LOG' => 'Found subpanel with link to non-existing module: %s',
    'LBL_SCAN_419_LOG' => 'Bad vardefs - key %s, name %s',
    'LBL_SCAN_420_LOG' => 'Bad vardefs - relate field %s has empty `module`',
    'LBL_SCAN_421_LOG' => 'Bad vardefs - link %s refers to invalid relationship',
    'LBL_SCAN_422_LOG' => 'Vardef HTML function in %s',
    'LBL_SCAN_423_LOG' => 'Bad vardefs - %s refers to bad subfield %s',
    'LBL_SCAN_424_LOG' => 'Inline HTML found in %s on line %s',
    'LBL_SCAN_425_LOG' => 'Found "echo" in %s on line %s',
    'LBL_SCAN_426_LOG' => 'Found "print" in %s on line %s',
    'LBL_SCAN_427_LOG' => 'Found "die/exit" in %s on line %s',
    'LBL_SCAN_428_LOG' => 'Found "print_r" in %s on line %s',
    'LBL_SCAN_429_LOG' => 'Found "var_dump" in %s on line %s',
    'LBL_SCAN_430_LOG' => 'Found output buffering (%s) in %s on line %s',

    'LBL_SCAN_501_LOG' => 'Missing file: %s',
    'LBL_SCAN_502_LOG' => 'md5 mismatch for %s, expected %s',
    'LBL_SCAN_503_LOG' => 'Custom module with the same name as new Sugar7 module: %s',
    'LBL_SCAN_504_LOG' => 'Field type missing in module %s: %s',
    'LBL_SCAN_505_LOG' => 'Type change in %s for field %s: from %s to %s',
    'LBL_SCAN_506_LOG' => '$this usage in %s',
    'LBL_SCAN_507_LOG' => 'Bad vardefs - %s refers to bad subfield %s',
    'LBL_SCAN_508_LOG' => 'Inline HTML found in %s on line %s',
    'LBL_SCAN_509_LOG' => 'Found "echo" in %s on line %s',
    'LBL_SCAN_510_LOG' => 'Found "print" in %s on line %s',
    'LBL_SCAN_511_LOG' => 'Found "die/exit" in %s on line %s',
    'LBL_SCAN_512_LOG' => 'Found "print_r" in %s on line %s',
    'LBL_SCAN_513_LOG' => 'Found "var_dump" in %s on line %s',
    'LBL_SCAN_514_LOG' => 'Found output buffering (%s) in %s on line %s',
    'LBL_SCAN_515_LOG' => 'Script failure: %s',

    'LBL_SCAN_901_LOG' => 'Instance already upgraded to Sugar 7',
    'LBL_SCAN_999_LOG' => 'Unknown failure, please consult support',
);
