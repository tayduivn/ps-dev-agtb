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


require_once('vendor/lessphp/lessc.inc.php');
require_once('include/api/SugarApiException.php');

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

    private $lessCompilerNames = array('bootstrap', 'sugar');

    function __construct($client = 'base', $themeName = null)
    {
        $this->myClient = $client;

        // Get user theme if the themeName isn't defined
        if (!$themeName || $themeName == '') {
            $themeName = $this->getUserTheme();
        }
        $this->myTheme = $themeName;
        $this->paths = $this->makePaths($client, $themeName);
    }

    /**
     * Get the user preferred theme
     * @return string themeName
     */
    private function getUserTheme() {
        if(isset($_COOKIE['sugar_user_theme']) && $_COOKIE['sugar_user_theme'] != '') {
            return $_COOKIE['sugar_user_theme'];
        }
        else if(isset($_SESSION['authenticated_user_theme']) && $_SESSION['authenticated_user_theme'] != '')	{
            return $_SESSION['authenticated_user_theme'];
        }
        else {
            global $sugar_config;
            return $sugar_config['default_theme'];
        }
    }

    /**
     * Checks the filesystem for a generated css file
     * This is useful on systems without a php memory cache
     * or if the memory cache is filled
     *
     * @param string $key Less compiler name
     * @return string Css cache filename
     */
    protected function retrieveThemeCacheFile($key)
    {
        $files = glob($this->getCssLocation($key, '*'));
        if (isset($files[0])) {
            // Looks like we found something
            return $files[0];
        } else {
            return false;
        }
    }

    /**
     * Deletes old CSS cache files, skipping the one we want to keep
     *
     * @param string $currCacheFile The full path of the current cache file
     */
    public function deleteStaleThemeCacheFiles($currCacheFile = '')
    {
        $files = glob($this->paths['cache'].'*.css');
        if ( !is_array($files) ) {
            return;
        }
        foreach ( $files as $fileName ) {
            if ( $fileName != $currCacheFile ) {
                unlink($fileName);
            }
        }
    }

    /**
     * Returns the css URLs of a theme
     * If not found in the cached folder, will generate them from /themes/ and /custom/themes/
     *
     * @return array of css file locations to include
     */
    public function getCSSURL()
    {
        $urls = array();
        $hashKey = $this->paths['hashKey'];

        //First check if the hash is cached so we don't have to load the metadata manually to calculate it
        $hashArray = sugar_cache_retrieve($hashKey);
        $hashArray = is_array($hashArray)? $hashArray : array();
        foreach ($this->lessCompilerNames as $compilerName) {
            if (!isset($hashArray[$compilerName])) $hashArray[$compilerName] = '';
            $hashArray[$compilerName] =  '';
        }

        //Now we expect 2 hash. 1 for twitter bootstrap css 1 for Sugar css
        foreach($hashArray as $key => $hash) {

            $file = $this->getCssLocation($key, $hash);

            // Check if same version exists on the system
            if (empty($hash) || !file_exists($file)) {
                //The hash may be inexistant or out-of-date

                //Look for new version
                $file = $this->retrieveThemeCacheFile($key);

                //We found a new version!
                if (!empty($file)) {
                    // Let's store the theme's hash into the cache so we can grab it next time
                    $hashArray[$key] = $this->getHashFromFileName($key, $file);
                    sugar_cache_put($hashKey, $key);

                //We have to recompile the theme
                } else {
                    // We compile expected theme by if we found variables.less in the file system (in /custom/themes or /themes)
                    $customThemeVars = $this->paths['custom'] . 'variables.less';
                    $baseThemeVars = $this->paths['base'] . 'variables.less';
                    if ($this->myTheme === 'default' || SugarAutoLoader::fileExists($customThemeVars) || SugarAutoLoader::fileExists($baseThemeVars)) {
                        $hashArray = $this->compileTheme();
                        foreach($hashArray as $subkey => $h) {
                            $urls[$subkey] =  $this->getCssLocation($subkey, $hashArray[$subkey]);
                        }
                    } else {
                        // Otherwise we grab the default theme
                        $clientDefaultTheme = new SidecarTheme($this->myClient, 'default');
                        $urls = $clientDefaultTheme->getCSSURL();
                    }
                    //Once here we have all the css locations so we can bypass the loop
                    break;
                }
            }
            $urls[$key] = $file;
        }
        return $urls;
    }

    /**
     * Returns a hash of 3 locations
     *  - the path of the base theme
     *  - the path of the customized theme
     *  - the path of the cached theme
     *
     * @param string $client
     * @param string $themeName
     * @return array Paths related to this client and theme
     */
    private function makePaths($client, $themeName)
    {
        return array(
            'base'   => 'styleguide/themes/clients/' . $client . '/' . $themeName . '/',
            'custom' => 'custom/themes/clients/' . $client . '/' . $themeName . '/',
            'cache'  =>  sugar_cached('themes/clients/' . $client . '/' . $themeName . '/'),
            'css'    =>  sugar_cached('themes/clients/' . $client . '/' . $themeName . '/' . $this->bootstrapCssName),
            'hashKey' => 'theme:'. $client . ':' . $themeName . ':' . $this->bootstrapCssName,
            'clients' => 'styleguide/less/clients/'
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
     * @param bool $split False to have a flat array
     * @return string plain text css
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
     *
     * @param bool $min True to minify the css
     * @return array of hashes of generated files
     */
    public function compileTheme($min = true)
    {
        // Get the theme definition
        $variables = $this->getThemeVariables();
        // Generate bootstrap.css
        $myCss = $this->compileCss($variables, $min);

        // Write bootstrap.css on the file system
        sugar_mkdir($this->paths['cache'], null, true);

        // Delete old css cache files
        $this->deleteStaleThemeCacheFiles();

        $hashes = array();
        foreach ($myCss as $key => $css) {
            $hashes[$key] = md5($css);
            $cacheFileName = $this->getCssLocation($key, $hashes[$key]);
            sugar_file_put_contents($cacheFileName, $css);
        }

        //Cache the hash in sugar_cache so we don't have to hit the filesystem for etag comparisons
        sugar_cache_put($this->paths['hashKey'], $hashes);

        return $hashes;
    }

    /**
     * Compiles the bootstrap.less file with custom variables
     *
     * @param array $variables to be given to lessphp compiler
     * @param bool $min minify or not the CSS
     * @return plain text CSS
     */
    public function compileCss($variables, $min = true)
    {
        $urls = $this->getCompilerLessFiles();

        $less = new lessc;
        if ($min === true) {
            $less->setFormatter('compressed');
        }
        //Relative path from /cache/themes/clients/PLATFORM/THEMENAME/bootstrap.css
        //              to   /styleguide/assets/
        if (!isset($variables['baseUrl'])) {
            $variables['baseUrl'] = '"../../../../../styleguide/assets"';
        }

        $less->setVariables($variables);

        try {
            $css = array();
            foreach ($urls as $key => $url) {
                $css[$key] = $less->compileFile($url);
            }
            return $css;

        } catch (exception $e) {
            throw new SugarApiExceptionError('lessc fatal error:<br />' . $e->getMessage());
        }
    }

    /**
     * Does a preg_match_all on a variables.less file and returns an array with varname/value
     *
     * @param string $regex
     * @param string $input contents of variables.less
     * @param bool $formatAsCollection if true, returns an array of objects, if false, returns a hash
     * @return array of variables matching the regex
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
     * @param string $path
     * @param bool $split Set to true if you want to split with hex/rgba/rel/bg
     * @return array of variables found
     */
    public function parseFile($path, $split = false) {

        $file = $path . 'variables.less';

        $output = array();
        $variablesLess = file_exists($file) ? file_get_contents($file) : null;

        if ($variablesLess) {
            if (!$split) {
                // Parses the mixins defs     @varName:      mixinName;
                $output = array_merge($output, $this->parseLessVars("/@([^:|@]+):(\s+)([^\#|@|\(|\"]*?);/", $variablesLess));
                // Parses the hex colors     @varName:      #aaaaaa;
                $output = array_merge($output, $this->parseLessVars("/@([^:|@]+):(\s+)(\#.*?);/", $variablesLess));
                // Parses the rgba colors     @varName:      rgba(0,0,0,0);
                $output = array_merge($output, $this->parseLessVars("/@([^:|@]+):(\s+)(rgba\(.*?\));/", $variablesLess));
                // Parses the related colors     @varName:      @relatedVar;
                $output = array_merge($output, $this->parseLessVars("/@([^:|@]+):(\s+)(@.*?);/", $variablesLess));
                // Parses the backgrounds     @varNamePath:      "./path/to/img.jpg";
                $output = array_merge($output, $this->parseLessVars("/@([^:|@]+Path):(\s+)(\".*?\");/", $variablesLess));
            } else {
                // Parses the mixins defs     @varName:      mixinName;
                $output['mixins'] = $this->parseLessVars("/@([^:|@]+):(\s+)([^\#|@|\(|\"]*?);/", $variablesLess, true);
                // Parses the hex colors     @varName:      #aaaaaa;
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
     * @param array $variables
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

    /**
     * Get the compiler less file from client folder or base folder
     *
     * @return array of less files to compile
     */
    private function getCompilerLessFiles() {
        $urls = array();

        foreach ($this->lessCompilerNames as $grouping) {
            $file = $this->paths['clients'] . $this->myClient . '/' . $grouping . '.less';
            $baseFile = $this->paths['clients'] . 'base' . '/' . $grouping . '.less';
            $urls[$grouping] = file_exists($file) ? $file : $baseFile;
        }

        return $urls;
    }

    /**
     * Get the location of a css file
     *
     * @param string $compilerName
     * @param string $hash
     * @return string file location
     */
    private function getCssLocation($compilerName, $hash) {
        return $this->paths['cache'] . $compilerName .'_' . $hash . ".css";
    }

    /**
     * Retrieves the hash from the file name
     *
     * @param string $compilerName
     * @param string $fileName
     * @return string hash
     */
    function getHashFromFileName($compilerName, $fileName) {
        $hash = str_replace("{$compilerName}_", '', pathinfo($fileName, PATHINFO_FILENAME));
        return $hash;
    }
}
