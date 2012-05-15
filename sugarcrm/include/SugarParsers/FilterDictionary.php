<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/********************************************************************************
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

class FilterDictionary
{
    /**
     * Where is this stored at
     *
     * @var string
     */
    protected $cacheDir;

    public function __construct()
    {
        $this->cacheDir = sugar_cached('include/SugarParsers/Filters/');
    }

    public function resetCache()
    {
        $dictFile = $this->cacheDir . 'FilterDictionary.php';
        @unlink($dictFile);
    }

    public function loadDictionaryFromStorage()
    {
        $dictFile = $this->cacheDir . 'FilterDictionary.php';
        if (!file_exists($dictFile)) {
            // No stored service dictionary, I need to build them
            $this->buildAllDictionaries();
        }

        // create the variable just in case.
        $filterDictionary = array();

        // load the cache file
        require($dictFile);

        // return the variable from the cache file
        return $filterDictionary;
    }

    protected function saveDictionaryToStorage($storageData)
    {
        if (!is_dir($this->cacheDir)) {
            sugar_mkdir($this->cacheDir, null, true);
        }

        sugar_file_put_contents($this->cacheDir . 'FilterDictionary.php', '<' . "?php\n\$filterDictionary = " . var_export($storageData, true) . ";\n");

    }

    protected function buildAllDictionaries()
    {
        $globPaths = array(array('glob' => 'include/SugarParsers/Filter/*.php', 'custom' => false),
            array('glob' => 'custom/include/SugarParsers/Filter/*.php', 'custom' => true),
        );

        $filterRegistry = array();

        foreach ($globPaths as $path) {
            $files = glob($path['glob'], GLOB_NOSORT);

            if (!is_array($files)) {
                // No matched files, skip to the next glob
                continue;
            }
            foreach ($files as $file) {
                // Strip off the directory, then the .php from the end
                $fileClass = "";
                if($path['custom'] === true) {
                    $fileClass = "Custom_";
                }
                $fileClass .= 'SugarParsers_Filter_' . substr(basename($file), 0, -4);

                require_once($file);
                if (!(class_exists($fileClass)
                    && is_subclass_of($fileClass, 'SugarParsers_Filter_AbstractFilter'))
                ) {
                    // Either the class doesn't exist, or it's not a subclass of SugarApi, regardless, we move on
                    continue;
                }

                /* @var $obj SugarParsers_Filter_AbstractFilter */
                $obj = new $fileClass();
                $variables = $obj->getVariables();

                foreach($variables as $var) {
                    $filterRegistry[$var] = array('class' => $fileClass, 'file' => $file);
                }
            }
        }

        $this->saveDictionaryToStorage($filterRegistry);
    }
}
