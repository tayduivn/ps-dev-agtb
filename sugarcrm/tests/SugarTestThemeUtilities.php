<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
 
require_once 'include/SugarTheme/SugarTheme.php';
require_once 'include/dir_inc.php';

class SugarTestThemeUtilities
{
    private static  $_createdThemes = array();

    private function __construct() {}

    public static function createAnonymousTheme()
    {
        $themename = 'TestTheme'.mt_rand();

        sugar_mkdir("themes/$themename/images",null,true);
        sugar_mkdir("themes/$themename/css",null,true);
        sugar_mkdir("themes/$themename/js",null,true);
        sugar_mkdir("themes/$themename/tpls",null,true);

        sugar_file_put_contents("themes/$themename/css/style.css","h2 { display: inline; }");
        sugar_file_put_contents("themes/$themename/css/yui.css",".yui { display: inline; }");
        sugar_file_put_contents("themes/$themename/js/style.js",'var dog = "cat";');
        sugar_touch("themes/$themename/images/Accounts.gif");
        sugar_touch("themes/$themename/images/fonts.big.icon.gif");
        sugar_touch("themes/$themename/tpls/header.tpl");

        $themedef = "<?php\n";
        $themedef .= "\$themedef = array(\n";
        $themedef .= "'name'  => '$themename',";
        $themedef .= "'dirName'  => '$themename',";
        $themedef .= "'description' => '$themename',";
        $themedef .= "'version' => array('regex_matches' => array('.*')),";
        $themedef .= ");";
        sugar_file_put_contents("themes/$themename/themedef.php",$themedef);

        self::$_createdThemes[] = $themename;

        SugarThemeRegistry::buildRegistry();

        return $themename;
    }

    public static function createAnonymousOldTheme()
    {
        $themename = 'TestTheme'.mt_rand();

        sugar_mkdir("themes/$themename/images",null,true);
        sugar_mkdir("themes/$themename/css",null,true);
        sugar_mkdir("themes/$themename/js",null,true);
        sugar_mkdir("themes/$themename/tpls",null,true);

        sugar_file_put_contents("themes/$themename/css/style.css","h2 { display: inline; }");
        sugar_file_put_contents("themes/$themename/css/yui.css",".yui { display: inline; }");
        sugar_file_put_contents("themes/$themename/js/style.js",'var dog = "cat";');
        sugar_touch("themes/$themename/images/Accounts.gif");
        sugar_touch("themes/$themename/images/fonts.big.icon.gif");
        sugar_touch("themes/$themename/tpls/header.tpl");

        $themedef = "<?php\n";
        $themedef .= "\$themedef = array(\n";
        $themedef .= "'name'  => '$themename',";
        $themedef .= "'dirName'  => '$themename',";
        $themedef .= "'description' => '$themename',";
        $themedef .= "'version' => array('exact_matches' => array('5.5.1')),";
        $themedef .= ");";
        sugar_file_put_contents("themes/$themename/themedef.php",$themedef);

        self::$_createdThemes[] = $themename;

        SugarThemeRegistry::buildRegistry();

        return $themename;
    }

    public static function createAnonymousCustomTheme(
        $themename = ''
        )
    {
        if ( empty($themename) )
            $themename = 'TestThemeCustom'.mt_rand();

        create_custom_directory("themes/$themename/images/");
        create_custom_directory("themes/$themename/css/");
        create_custom_directory("themes/$themename/js/");

        sugar_touch("custom/themes/$themename/css/style.css");
        sugar_touch("custom/themes/$themename/js/style.js");
        sugar_touch("custom/themes/$themename/images/Accounts.gif");
        sugar_touch("custom/themes/$themename/images/fonts.big.icon.gif");

        $themedef = "<?php\n";
        $themedef .= "\$themedef = array(\n";
        $themedef .= "'name'  => 'custom $themename',";
        $themedef .= "'dirName'  => '$themename',";
        $themedef .= "'description' => 'custom $themename',";
        $themedef .= "'version' => array('regex_matches' => array('.*')),";
        $themedef .= ");";
        sugar_file_put_contents("custom/themes/$themename/themedef.php",$themedef);

        self::$_createdThemes[] = $themename;

        SugarThemeRegistry::buildRegistry();

        return $themename;
    }

    public static function createAnonymousChildTheme(
        $parentTheme
        )
    {
        $themename = 'TestThemeChild'.mt_rand();

        sugar_mkdir("themes/$themename/images",null,true);
        sugar_mkdir("themes/$themename/css",null,true);
        sugar_mkdir("themes/$themename/js",null,true);

        sugar_file_put_contents("themes/$themename/css/style.css","h3 { display: inline; }");
        sugar_file_put_contents("themes/$themename/css/yui.css",".yui { display: inline; }");
        sugar_file_put_contents("themes/$themename/js/style.js",'var bird = "frog";');

        $themedef = "<?php\n";
        $themedef .= "\$themedef = array(\n";
        $themedef .= "'name'  => '$themename',";
        $themedef .= "'dirName' => '$themename',";
        $themedef .= "'parentTheme' => '".$parentTheme."',";
        $themedef .= "'description' => '$themename',";
        $themedef .= "'version' => array('regex_matches' => array('.*')),";
        $themedef .= ");";
        sugar_file_put_contents("themes/$themename/themedef.php",$themedef);

        self::$_createdThemes[] = $themename;

        SugarThemeRegistry::buildRegistry();

        return $themename;
    }
    
    public static function createAnonymousRTLTheme() 
    {
        $themename = 'TestTheme'.mt_rand();
        
        sugar_mkdir("themes/$themename/images",null,true);
        sugar_mkdir("themes/$themename/css",null,true);
        sugar_mkdir("themes/$themename/js",null,true);
        sugar_mkdir("themes/$themename/tpls",null,true);
        
        sugar_file_put_contents("themes/$themename/css/style.css","h2 { display: inline; }");
        sugar_file_put_contents("themes/$themename/css/yui.css",".yui { display: inline; }");
        sugar_file_put_contents("themes/$themename/js/style.js",'var dog = "cat";');
        sugar_touch("themes/$themename/images/Accounts.gif");
        sugar_touch("themes/$themename/images/fonts.big.icon.gif");
        sugar_touch("themes/$themename/tpls/header.tpl");
        
        $themedef = "<?php\n";
        $themedef .= "\$themedef = array(\n";
        $themedef .= "'name'  => '$themename',";
        $themedef .= "'dirName'  => '$themename',";
        $themedef .= "'description' => '$themename',";
        $themedef .= "'directionality' => 'rtl',";
        $themedef .= "'version' => array('regex_matches' => array('.*')),";
        $themedef .= ");";
        sugar_file_put_contents("themes/$themename/themedef.php",$themedef);
        
        self::$_createdThemes[] = $themename;
        
        SugarThemeRegistry::buildRegistry();        
        
        return $themename;
    }

    public static function removeAllCreatedAnonymousThemes()
    {
        foreach (self::getCreatedThemeNames() as $name ) {
            if ( is_dir('themes/'.$name) )
                rmdir_recursive('themes/'.$name);
            if ( is_dir('custom/themes/'.$name) )
                rmdir_recursive('custom/themes/'.$name);
            if ( is_dir(sugar_cached('themes/').$name) )
                rmdir_recursive(sugar_cached('themes/').$name);
        }

        SugarThemeRegistry::buildRegistry();
    }

    public static function getCreatedThemeNames()
    {
        return self::$_createdThemes;
    }
}

