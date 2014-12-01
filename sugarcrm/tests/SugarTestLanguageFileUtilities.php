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

class SugarTestLanguageFileUtilities
{
    /**
     * @var array
     *
     * The languages that have been modified using {@link SugarTestLanguageFileUtilities::write}.
     */
    protected static $languages = array();

    private function __construct()
    {
    }

    /**
     * Backs up the state of files so that they can be restored during a test's tear down.
     *
     * @param string|array $files Paths to one or more files.
     */
    public static function backup($files = array())
    {
        if (!is_array($files)) {
            $files = array($files);
        }

        foreach ($files as $file) {
            SugarTestHelper::saveFile($file);
        }
    }

    /**
     * Deletes files from the filesystem so they won't interfere with tests.
     *
     * Most likely, these files should first be backed up so the tests can leave the environment exactly as they found
     * it.
     *
     * @param string|array $files Paths to one or more files.
     */
    public static function remove($files = array())
    {
        if (!is_array($files)) {
            $files = array($files);
        }

        foreach ($files as $file) {
            SugarAutoLoader::unlink($file, false);
        }
    }

    /**
     * Writes a string representing PHP code to [custom/]include/language/`$language`.lang.php.
     *
     * @param string $dir
     * @param string $language
     * @param string $contents
     */
    public static function write($dir, $language, $contents)
    {
        if (substr($dir, -1) !== '/') {
            $dir .= '/';
        }

        mkdir_recursive($dir);
        SugarAutoLoader::put("{$dir}{$language}.lang.php", "<?php\n{$contents}\n");

        if (!in_array($language, static::$languages)) {
            static::$languages[] = $language;
        }

        sugar_cache_clear("app_strings.{$language}");
        sugar_cache_clear("app_list_strings.{$language}");
    }

    /**
     * Clears the app strings and app list strings caches for all languages for which a file was written.
     */
    public static function clearCache()
    {
        foreach (static::$languages as $language) {
            sugar_cache_clear("app_strings.{$language}");
            sugar_cache_clear("app_list_strings.{$language}");
        }

        static::$languages = array();
    }
}
