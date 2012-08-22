<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


require_once('include/lessphp/lessc.inc.php');
require_once('include/SugarTheme/SidecarTheme.php');

class ThemeApi extends SugarApi
{
    public function registerApiRest()
    {
        return array(
            'previewCSS' => array(
                'reqType' => 'GET',
                'path' => array('css'),
                'pathVars' => array(''),
                'method' => 'previewCSS',
                'shortHelp' => 'Generate the bootstrap.css file',
                'longHelp' => 'include/api/help/themePreview.html',
                'noLoginRequired' => true,
            ),
            'getCustomThemeVars' => array(
                'reqType' => 'GET',
                'path' => array('theme'),
                'pathVars' => array(''),
                'method' => 'getCustomThemeVars',
                'shortHelp' => 'Get the customizable variables of a custom theme',
                'longHelp' => 'include/api/help/themeGet.html',
                'noLoginRequired' => true,
            ),
            'updateCustomTheme' => array(
                'reqType' => 'POST',
                'path' => array('theme'),
                'pathVars' => array(''),
                'method' => 'updateCustomTheme',
                'shortHelp' => 'Update the customizable variables of a custom theme',
                'longHelp' => 'include/api/help/themePost.html',
            ),
        );
    }

    /**
     * Generate bootstrap.css
     * @param $api
     * @param $args
     * @return plain text/css or css file url
     */
    public function previewCSS($api, $args)
    {
        // Validating arguments
        $platform = isset($args['platform']) ? $args['platform'] : 'base';
        $themeName = isset($args['themeName']) ? $args['themeName'] : null;
        $minify = isset($args['min']) ? true : false;

        $theme = new SidecarTheme($platform, $themeName);

        // If `preview` is defined, it means that the call was made by the Theme Editor in Studio so we want to return
        // plain text/css
        if (isset($args['preview'])) {
            $variables = $theme->getThemeVariables(true);
            $variables = array_merge($variables, $args);
            $css = $theme->compileBootstrapCss($variables, $minify);

            header('Content-type: text/css');
            exit($css);
        } else {
            // Otherwise we just return the CSS Url so the application can load the CSS file.
            // getCSSURL method takes of generating bootstrap.css if it doesn't exist in cache.
            return $theme->getCSSURL();
        }

    }

    /**
     * Parses variables.less and returns a collection of objects {"name": varName, "value": value}
     * @param $api
     * @param $args
     * @return array
     */
    public function getCustomThemeVars($api, $args)
    {
        // Validating arguments
        $platform = isset($args['platform']) ? $args['platform'] : 'base';
        $themeName = isset($args['themeName']) ? $args['themeName'] : 'default';

        $theme = new SidecarTheme($platform, $themeName);
        $paths = $theme->getPaths();
        $variables = $theme->getThemeVariables($paths['custom'], true);

        return $variables;
    }

    /**
     * Updates variables.less with the values given in the request.
     * @param $api
     * @param $args
     * @return mixed|string
     * @throws SugarApiExceptionMissingParameter
     */
    public function updateCustomTheme($api, $args)
    {
        if(!is_admin($GLOBALS['current_user'])) {
            throw new SugarApiExceptionNotAuthorized();
        }

        if (empty($args)) {
            throw new SugarApiExceptionMissingParameter('Missing colors');
        }

        // Validating arguments
        $platform = isset($args['platform']) ? $args['platform'] : 'base';
        $themeName = isset($args['themeName']) ? $args['themeName'] : 'default';

        $theme = new SidecarTheme($platform, $themeName);

        // if reset=true is passed
        if (isset($args['reset']) && $args['reset'] == true) {
            $theme->resetDefault();

        } else {
            // else
            // Override the custom variables.less with the given vars
            $variables = array_diff_key($args, array('platform' => 0, 'themeName' => 0, 'reset' => 0 ));
            $theme->overrideThemeVariables($variables);
        }

        // Write the bootstrap.css file
        $theme->compileTheme(true);

        // saves the bootstrap.css URL in the portal settings
        $url = $GLOBALS['sugar_config']['site_url'] . '/' . $theme->getCSSURL();
        $GLOBALS ['system_config']->saveSetting($args['platform'], 'css', json_encode($url));

        return $url;
    }

}
