<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-professional-eula.html
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

/*********************************************************************************
 * Description:  Contains a variety of utility functions used to display UI
 * components such as form headers and footers.  Intended to be modified on a per
 * theme basis.
 ********************************************************************************/


require_once('include/lessphp/lessc.inc.php');
require_once("include/SugarTheme/SugarTheme.php");

/**
 * Class that provides tools for working with a theme.
 * @api
 */
class SidecarTheme
{
    private $myClient;
    private $myTheme;
    private $paths;
    private $bootstrapCssName = 'bootstrap.css';

    function __construct($client = 'base', $themeName = 'default')
    {
        $this->myClient = $client;
        $this->myTheme = $themeName;

        $this->paths = $this->makePaths($client, $themeName);
    }

    /**
     * Returns the bootstrap.css URL of a theme
     * If not found in the cached folder, will generate it from /themes/ and /custom/themes/
     *
     * @return string url of css file to include
     */
    public function getCSSURL()
    {
        $cacheCSS = $this->paths['css'];

        // Check if file exists on the system
        // otherwise we have to generate the corresponding bootstrap.css with custom theme, default theme and base theme
        if (!file_exists($cacheCSS)) {
            // We compile it if a we have the custom theme in the file system in /custom/themes or /themes
            $customThemeVars = $this->paths['custom'] . 'variables.less';
            $baseThemeVars = $this->paths['base'] . 'variables.less';
            if ( file_exists($customThemeVars) || file_exists($baseThemeVars) ) {
                $this->compileTheme();
            }
            else {
                // Otherwise we compile the default theme if it exists
                $clientDefaultTheme = new SidecarTheme($this->myClient, 'default');
                $customThemeVars = $clientDefaultTheme->paths['custom'] . 'variables.less';
                $baseThemeVars = $clientDefaultTheme->paths['base'] . 'variables.less';

                if ( file_exists($customThemeVars) || file_exists($baseThemeVars) ) {
                    $cacheCSS = $clientDefaultTheme->paths['css'];
                    if (!file_exists($cacheCSS)) {
                        $clientDefaultTheme->compileTheme();
                    }
                }
                else {
                    // Finally we compile the base default theme if we still have nothing
                    $baseDefaultTheme = new SidecarTheme('base', 'default');
                    $cacheCSS = $baseDefaultTheme->paths['css'];
                    if (!file_exists($cacheCSS)) {
                        $baseDefaultTheme->compileTheme();
                    }
                }

            }
        }
        return $cacheCSS;
    }

    /**
     * Returns a hash of 3 locations
     *  - the path of the base theme
     *  - the path of the customized theme
     *  - the path of the cached theme
     *
     * @param $client
     * @param $themeName
     * @return array
     */
    private function makePaths($client, $themeName)
    {
        return array(
            'base'   => 'themes/clients/' . $client . '/' . $themeName . '/',
            'custom' => 'custom/themes/clients/' . $client . '/' . $themeName . '/',
            'cache'  =>  sugar_cached('themes/clients/' . $client . '/' . $themeName . '/'),
            'css'    =>  sugar_cached('themes/clients/' . $client . '/' . $themeName . '/' . $this->bootstrapCssName),
        );
    }

    /**
     * Getter for paths
     * @return array
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * Parse the variables.less definition of the theme
     *
     * @return plain text css
     */
    public function getThemeVariables($split = false) {
        $desiredTheme = $this->paths;
        $clientDefault = $this->makePaths($this->myClient, 'default');
        $baseDefault = $this->makePaths('base', 'default');

        // Crazy override from :
        // - the base/default base theme
        // - the base/default custom theme
        // - the client/default base theme
        // - the client/default custom theme
        // - the client/themeName base theme
        // - the client/themeName custom theme
        $variables = $this->parseFile($baseDefault['base'], $split);
        $variables = array_merge($variables, $this->parseFile($baseDefault['custom'], $split));
        $variables = array_merge($variables, $this->parseFile($clientDefault['base'], $split));
        $variables = array_merge($variables, $this->parseFile($clientDefault['custom'], $split));
        $variables = array_merge($variables, $this->parseFile($desiredTheme['base'], $split));
        $variables = array_merge($variables, $this->parseFile($desiredTheme['custom'], $split));

        return $variables;
    }

    /**
     * Compile Less files and write /cache/themes/clients/$client/$themeName/bootstrap.css
     */
    public function compileTheme($min = true)
    {
        // Get the theme definition
        $variables = $this->getThemeVariables();
        // Generate bootstrap.css
        $myCss = $this->compileBootstrapCss($variables, $min);

        // Write bootstrap.css on the file system
        sugar_mkdir($this->paths['cache'], null, true);
        sugar_file_put_contents($this->paths['css'], $myCss);
    }

    /**
     * Compiles the bootstrap.less file with custom variables
     * @param $variables to be given to lessphp compiler
     * @param bool $min minify or not the CSS
     * @return plain text CSS
     */
    public function compileBootstrapCss($variables, $min = true)
    {
        if (file_exists('styleguide/' . $this->myClient . '/less/config.less'))
            $url = 'styleguide/less/clients/' . $this->myClient . '/less/config.less';
        else
            $url = 'styleguide/less/clients/base/config.less';

        $less = new lessc($url);
        if ($min === true) {
            $less->setFormatter('compressed');
        }
        $variables['baseUrl'] = '"../../../../../styleguide/assets"';

        try {
            $css = $less->parse($variables);
            return $css;

        } catch (exception $e) {
            throw new SugarApiExceptionError('lessc fatal error:<br />' . $e->getMessage());
        }
    }

    /**
     * Does a preg_match_all on a variables.less file and returns an array with varname/value
     * @param $regex
     * @param $input contents of variables.less
     * @param bool $formatAsCollection if true, returns an array of objects, if false, returns a hash
     * @return array
     */
    public function parseLessVars($regex, $input, $formatAsCollection = false)
    {
        $output = array();
        preg_match_all($regex, $input, $match, PREG_PATTERN_ORDER);
        foreach ($match[1] as $key => $lessVar) {
            if ($formatAsCollection) {
                $output[] = array('name' => $lessVar, 'value' => $match[3][$key]);
            } else {
                $output[$lessVar] = $match[3][$key];
            }
        }
        return $output;
    }


    /**
     * Parses a less file and returns the array of variables
     * @param $path
     * @param $split Set to true if you want to split with hex/rgba/rel/bg
     * @return array
     */
    public function parseFile($path, $split = false) {

        $file = $path . 'variables.less';

        $output = array();
        $variablesLess = file_exists($file) ? file_get_contents($file) : null;

        if ($variablesLess) {
            if (!$split) {
                // Parses the hex colors     @varName:      #aaaaaa;
                $output = array_merge($output, $this->parseLessVars("/@([^:|@]+):(\s+)(\#.*?);/", $variablesLess));
                // Parses the rgba colors     @varName:      rgba(0,0,0,0);
                $output = array_merge($output, $this->parseLessVars("/@([^:|@]+):(\s+)(rgba\(.*?\));/", $variablesLess));
                // Parses the related colors     @varName:      @relatedVar;
                $output = array_merge($output, $this->parseLessVars("/@([^:|@]+):(\s+)(@.*?);/", $variablesLess));
                // Parses the backgrounds     @varNamePath:      "./path/to/img.jpg";
                $output = array_merge($output, $this->parseLessVars("/@([^:|@]+Path):(\s+)(\".*?\");/", $variablesLess));
            } else {
                $output['hex'] = $this->parseLessVars("/@([^:|@]+):(\s+)(\#.*?);/", $variablesLess, true);
                // Parses the rgba colors     @varName:      rgba(0,0,0,0);
                $output['rgba'] = $this->parseLessVars("/@([^:|@]+):(\s+)(rgba\(.*?\));/", $variablesLess, true);
                // Parses the related colors     @varName:      @relatedVar;
                $output['rel'] = $this->parseLessVars("/@([^:|@]+):(\s+)(@.*?);/", $variablesLess, true);
                // Parses the backgrounds     @varNamePath:      "./path/to/img.jpg";
                $output['bg'] = $this->parseLessVars("/@([^:|@]+Path):(\s+)\"(.*?)\";/", $variablesLess, true);
            }
        }
        return $output;
    }

    /**
     * Override variables.less with the base/default one + the variales passed in arguments
     * @param $variables
     */
    public function overrideThemeVariables($variables) {
        // /themes/clients/base/default
        $baseDefaultTheme = new SidecarTheme('base', 'default');
        $baseDefaultThemePaths = $baseDefaultTheme->getPaths();

        // /themes/clients/$client/$themeName
        $baseTheme = $this->paths['base'] . 'variables.less';
        $customTheme = $this->paths['custom'] . 'variables.less';

        $contents = file_get_contents($baseDefaultThemePaths['base'] . 'variables.less');

        foreach ($variables as $lessVar => $lessValue) {
            // override the variables
            $lessValue = html_entity_decode($lessValue);
            $contents = preg_replace("/@$lessVar:(.*);/", "@$lessVar: $lessValue;", $contents);
        }
        $contents = str_replace('\n', '', $contents);

        // overwrite the theme
        sugar_mkdir($this->paths['custom'], null, true);
        sugar_file_put_contents($customTheme, $contents);
    }

    /**
     * Reset the base/default theme.
     */
    public function resetDefault() {
        $baseDefault = $this->makePaths('base', 'default');
        $variables = $this->parseFile($baseDefault['base']);
        $this->overrideThemeVariables($variables);
    }

}
