<?php
if (! defined ( 'sugarEntry' ) || ! sugarEntry)
    die ( 'Not A Valid Entry Point' ) ;
/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */

require_once ('modules/ModuleBuilder/parsers/ModuleBuilderParser.php') ;

class ParserLabel extends ModuleBuilderParser
{

    function ParserLabel ($moduleName, $packageName = '' )
    {
        $this->moduleName = $moduleName;
        if (!empty($packageName))
            $this->packageName = $packageName ;
    }

    /**
     * Takes in the request params from a save request and processes
     * them for the save.
     * @param REQUEST $params       Labels as "label_".System label => Display label pairs
     * @param string $language      Language key, for example 'en_us'
     */
    function handleSave ($params , $language)
    {
        $labels = array ( ) ;
        foreach ( $params as $key => $value )
        {
            if (preg_match ( '/^label_/', $key ) && strcmp ( $value, 'no_change' ) != 0)
            {
                $labels [ strtoupper(substr ( $key, 6 )) ] = SugarCleaner::cleanHtml(from_html($value),false);
            }
        }

        // Are we are in Module builder?
        if (!empty($this->packageName)) {
            return self::addLabels ( $language, $labels, $this->moduleName, "custom/modulebuilder/packages/{$this->packageName}/modules/{$this->moduleName}/language" ) ;
        } else {
            // Default the return value
            $result = false;
            if (!empty($labels)) {
                $result =  self::addLabels($language, $labels, $this->moduleName);
            }

            return $result;
        }
    }

    /**
     * Gets custom strings for this module. If $ext is true, will look in the
     * extension language file. Otherwise will look in the custom lang file.
     * 
     * @param string $language The language to get the strings for
     * @param boolean $ext Whether to use the extension file
     * @return array
     */
    protected function getCustomModStrings($language, $ext = false)
    {
        if ($ext) {
            $file = "custom/modules/".$this->moduleName."/Ext/Language/".$language.".lang.ext.php";
        } else {
            $file = "custom/modules/".$this->moduleName."/language/".$language.".lang.php";
        }

        if (is_file($file)) {
            include $file;
        }

        return isset($mod_strings) ? $mod_strings : array();
    }

    /*
     * Remove a label from the language pack for a module
     * @param string $language      Language key, for example 'en_us'
     * @param string $label         The label to remove
     * @param string $labelvalue    The value of the label to remove
     * @param string $moduleName    Name of the module to which to add these labels
     * @param string $basepath      base path of the language file
     * @param string $forRelationshipLabel      whether this is a relationship label
     */
    static function removeLabel($language, $label, $labelvalue, $moduleName, $basepath = null, $forRelationshipLabel = false) {
        $GLOBALS [ 'log' ]->debug ( "ParserLabel->removeLabels($language, \$label, \$labelvalue, $moduleName, $basepath );" ) ;
        if (is_null ( $basepath ))
        {
            $deployedModule = true ;
            $basepath = "custom/modules/$moduleName/language" ;
            if($forRelationshipLabel){
            	$basepath = "custom/modules/$moduleName/Ext/Language" ;
            }
            if (! is_dir ( $basepath ))
            {
                $GLOBALS ['log']->debug("$basepath is not a directory.");
                return false;
            }
        }

        $filename = "$basepath/$language.lang.php" ;
        if($forRelationshipLabel){
        	$filename = "$basepath/$language.lang.ext.php" ;
     	}

        $dir_exists = is_dir ( $basepath ) ;

        $mod_strings = array ( ) ;

        if ($dir_exists)
        {
            if (file_exists ($filename))
            {
                // obtain $mod_strings
                include ($filename) ;
            } else {
                $GLOBALS ['log']->debug("file $filename does not exist.");
                return false;
            }
        } else {
            $GLOBALS ['log']->debug("directory $basepath does not exist.");
            return false ;
        }

        $changed = false ;

        if (isset($mod_strings[$label]) && $mod_strings[$label]==$labelvalue) {
            unset($mod_strings[$label]);
            $changed = true;
        }

        if ($changed)
        {
            if (! write_array_to_file ( "mod_strings", $mod_strings, $filename )) {
                $GLOBALS [ 'log' ]->fatal ( "Could not write $filename" ) ;
            } else {
                // if we have a cache to worry about, then clear it now
                if ($deployedModule) {
                    $GLOBALS ['log']->debug ( "PaserLabel->addLabels: clearing language cache" ) ;
                    $cache_key = "module_language." . $language . $moduleName ;
                    sugar_cache_clear ( $cache_key ) ;
                    LanguageManager::clearLanguageCache ( $moduleName, $language ) ;
                }
            }
        }

        return true ;
    }

    /*
     * Add a set of labels to the language pack for a module, deployed or undeployed
     * @param string $language      Language key, for example 'en_us'
     * @param array $labels         The labels to add in the form of an array of System label => Display label pairs
     * @param string $moduleName    Name of the module to which to add these labels
     * @param string $packageName   If module is undeployed, name of the package to which it belongs
     */
    static function addLabels ($language , $labels , $moduleName , $basepath = null, $forRelationshipLabel = false)
    {
        $GLOBALS [ 'log' ]->debug ( "ParserLabel->addLabels($language, \$labels, $moduleName, $basepath );" ) ;
        $GLOBALS [ 'log' ]->debug ( "\$labels:" . print_r ( $labels, true ) ) ;

        $deployedModule = false ;
        if (is_null ( $basepath ))
        {
            $deployedModule = true ;
            $basepath = "custom/modules/$moduleName/language" ;
            if($forRelationshipLabel){
                $basepath = "custom/modules/$moduleName/Ext/Language" ;
            }
            if (! is_dir ( $basepath ))
            {
                mkdir_recursive($basepath);
            }
        }

        $filename = "$basepath/$language.lang.php" ;
        if($forRelationshipLabel){
            $filename = "$basepath/$language.lang.ext.php" ;
        }
        $dir_exists = is_dir ( $basepath ) ;

        $mod_strings = array ( ) ;
        $changed = false;
        
        if ($dir_exists) {
            if (file_exists ( $filename )) {
                // obtain $mod_strings
                include ($filename) ;
            }

            foreach ( $labels as $key => $value ) {
                if (! isset ( $mod_strings [ $key ] ) || strcmp ( $value, $mod_strings [ $key ] ) != 0) {
                    $mod_strings [$key] = to_html(strip_tags(from_html($value))); // must match encoding used in view.labels.php
                    $changed = true ;
                }
            }
        } else {
            $changed = true;
        }

        if ($changed) {
            $GLOBALS [ 'log' ]->debug ( "ParserLabel->addLabels: writing new mod_strings to $filename" ) ;
            $GLOBALS [ 'log' ]->debug ( "ParserLabel->addLabels: mod_strings=".print_r($mod_strings,true) ) ;
            if (! write_array_to_file ( "mod_strings", $mod_strings, $filename )) {
                $GLOBALS [ 'log' ]->fatal ( "Could not write $filename" ) ;
            } else {
                // The write was successful, remove this string from other
                // files to make sure this new string trumps all
                
                // We need to scrub the following paths for existing mod_strings
                // Yes, custom/modules is built by module installer, but there
                // are times in the request that this build hasn't happened
                // yet. This handles that case.
                $paths = array(
                    "custom/Extension/modules/{$moduleName}/Ext/Language",
                    "custom/modules/{$moduleName}/Ext/Language",
                );

                $exts = array();
                foreach ($paths as $path) {
                    $exts = array_merge($exts, glob("$path/{$language}.*.php"));
                }

                foreach ($exts as $ext) {
                    // This will determine whether we need to rewrite the ext file
                    $changed = false;
                    require $ext;
                    foreach ($labels as $key => $value) {
                        if (isset($mod_strings[$key]) && $mod_strings[$key] != $value) {
                            // Remove this string from the ext and resave the ext
                            unset($mod_strings[$key]);
                            $changed = true;
                        }
                    }

                    if ($changed) {
                        $write  = "<?php\n// WARNING: The contents of this file are auto-generated.\n";
                        // We can't use var_export here since multiple files
                        // can be structured differently. This is dirty, yes,
                        // but necessary.
                        foreach ($mod_strings as $k => $v) {
                            $write .= "\$mod_strings['$k'] = " . var_export($v, 1) . ";\n";
                        }

                        SugarAutoLoader::put($ext, $write, true);
                    }
                }

                // if we have a cache to worry about, then clear it now
                if ($deployedModule) {
                    SugarCache::cleanOpcodes();
                    $GLOBALS [ 'log' ]->debug ( "PaserLabel->addLabels: clearing language cache" ) ;
                    $cache_key = "module_language." . $language . $moduleName ;
                    sugar_cache_clear ( $cache_key ) ;
                    LanguageManager::clearLanguageCache ( $moduleName, $language ) ;
                    MetaDataManager::refreshLanguagesCache($language);
                }
            }
        }

        return true ;
    }

    /**
     * Takes in the request params from a save request and processes
     * them for the save.
     * @param $metadata
     * @param string $language      Language key, for example 'en_us'
     */
    function handleSaveRelationshipLabels ($metadata , $language)
        {
        foreach ( $metadata as $definition )
            {
        	$labels = array();
        	$labels[$definition [ 'system_label' ]] = $definition [ 'display_label' ];
        	self::addLabels ( $language, $labels, $definition [ 'module' ],null,true );
            }
        }

    function addLabelsToAllLanguages($labels)
            {
    	$langs = get_languages();
    	foreach($langs as $lang_key => $lang_display)
        {
    		self::addLabels($lang_key, $labels, $this->moduleName);
        }
    }
}

?>
