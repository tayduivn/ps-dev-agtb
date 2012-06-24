<?php
require_once 'modules/ModuleBuilder/parsers/MetaDataFiles.php';

class SidecarMetaDataUpgrader
{
    /**
     * Listing of modules that need to be upgraded
     * 
     * @var array
     */
    protected $upgradeModules = array();
    
    /**
     * List of modules that are deployed 
     * 
     * @var array
     */
    protected $deployedModules = array();
    
    /**
     * The types of metadata files to be upgraded
     *
     * @var array
     */
    protected $fileTypes = array('custom', 'history', 'working',);

    /**
     * The list of paths for both portal and wireless viewdefs under the legacy
     * system.
     *
     * @var array
     */
    protected $legacyFilePaths = array(
        'portal' => array(
            'custom'  => 'custom/portal/',
            'history' => 'custom/portal/',
            'working' => 'custom/working/portal/',
        ),
        'wireless' => array(
            'custom'  => 'custom/',
            'history' => 'custom/history/',
            'working' => 'custom/working/',
        ),
    );

    /**
     * Maps of old metadata file names
     *
     * @var array
     */
    protected $legacyMetaDataFileNames = array(
        //BEGIN SUGARCRM flav=pro || flav=sales ONLY
        'wireless' => array(
            MB_WIRELESSEDITVIEW       => 'wireless.editviewdefs' ,
            MB_WIRELESSDETAILVIEW     => 'wireless.detailviewdefs' ,
            MB_WIRELESSLISTVIEW       => 'wireless.listviewdefs' ,
            MB_WIRELESSBASICSEARCH    => 'wireless.searchdefs' ,
            // Advanced is unneeded since it shares with basic
            //MB_WIRELESSADVANCEDSEARCH => 'wireless.searchdefs' ,
        ),
        //END SUGARCRM flav=pro || flav=sales ONLY
        //BEGIN SUGARCRM flav=ent ONLY
        'portal' => array(
            MB_PORTALEDITVIEW         => 'editviewdefs',
            MB_PORTALDETAILVIEW       => 'detailviewdefs',
            MB_PORTALLISTVIEW         => 'listviewdefs',
            MB_PORTALSEARCHVIEW       => 'searchformdefs',
        ),
        //END SUGARCRM flav=ent ONLY
    );
    
    /**
     * Legacy metadata variable mapping
     * 
     * @var array
     */
    protected $legacyMetaDataVars = array(
        'wireless.editviewdefs'   => 'viewdefs',
        'wireless.detailviewdefs' => 'viewdefs',
        'wireless.listviewdefs'   => 'listViewDefs',
        'wireless.searchdefs'     => 'searchdefs',
        'editviewdefs'            => 'viewdefs',
        'detailviewdefs'          => 'viewdefs',
        'listviewdefs'            => 'viewdefs',
        'searchformdefs'          => 'viewdefs',
    );

    /**
     * Translated legacyFilePaths with the module embedded
     *
     * @var array
     */
    protected $filePaths = array();

    /**
     * Listing of actual metadata files, by client
     *
     * @var array
     */
    protected $files = array();
    
    /**
     * List of Sidecar*MetaDataUpgrader classes that map to a view type
     * 
     * @var array
     */
    protected $upgraderClassMap = array(
        'list'   => 'List',
        'edit'   => 'Grid',
        'detail' => 'Grid',
        'search' => 'Search',
    );
    
    /**
     * Sets the list of files that need to be upgraded. Will look in directories 
     * contained in $legacyFilePaths and will also attempt to identify custom
     * modules that are found within modules/
     */
    public function setFilesToUpgrade() 
    {
        $this->setPortalFilesToUpgrade();
        $this->setMobileFilesToUpgrade();
    }
    
    /**
     * Sets the listing of customized portal module metadata to upgrade
     */
    public function setPortalFilesToUpgrade()
    {
        $this->setUpgradeFiles('portal');
    }
    
    /**
     * Sets the listing of customized mobile module metadata to upgrade. Will 
     * also scrape custom modules (deployed and undeployed) looking for all custom
     * modules and their respective metadata to upgrade.
     */
    public function setMobileFilesToUpgrade()
    {
        $metatype = 'wireless';
        $this->setUpgradeFiles($metatype);
        
        // Get custom modules. We need both DEPLOYED and UNDEPLOYED
        // Undeployed will be those in packages that are NOT in builds but are
        // also in modules
        
        require_once 'modules/ModuleBuilder/MB/ModuleBuilder.php';
        $mb = new ModuleBuilder();
        
        // Set the packages and modules in place
        $mb->getPackages();
        
        // Set the core app module path for checking deployment
        $modulepath = 'modules/';
        
        // Handle module list making. We need to look for metadata in three places:
        // - modules/
        // - custom/modulebuilder/packages/<PACKAGENAMES>/modules/<MODULENAME>/metadata
        // - custom/modulebuilder/builds/<PACKAGENAMES>/SugarModules/modules/<PACKAGEKEY>_<MODULENAME>/metadata
        //
        // The first path will be handled if we don't send the packagename and deployed status
        // The second path will be handled by history types with a package name and undeployed status
        // The last path will be handdled by base types with a package name and undeployed status
        // 
        foreach ($mb->packages as $packagename => $package) {
            $buildpath = $package->getBuildDir() . '/SugarModules/modules/';
            foreach ($package->modules as $module => $mbmodule) {
                $appModulePath = $modulepath . $package->key . '_' . $module;
                $mbbModulePath = $buildpath . $package->key . '_' . $module;
                $packagePath   = $package->getPackageDir() . '/modules/' . $module;
                $deployed = file_exists($appModulePath) && file_exists($mbbModulePath);
                
                // For deployed modules we need to get 
                if ($deployed) {
                    // Reset the module name to the key_module name format
                    $module = $package->key . '_' . $module;
                    
                    // Get the metadata directory
                    $metadatadir = "$appModulePath/metadata/";
                    
                    // Get our upgrade files as base files since these are regular metadata
                    $files = $this->getUpgradeableFilesInPath($metadatadir, $module, $metatype);
                    $this->files = array_merge($this->files, $files);
                    
                    // For deployed modules we still need to handle package dir metadata
                    $metadatadir = "$mbbModulePath/metadata/";
                    
                    // Get our upgrade files as undeployed base type wireless client
                    $files = $this->getUpgradeableFilesInPath($metadatadir, $module, $metatype, 'base', $packagename, false);
                    $this->files = array_merge($this->files, $files);
                } else {
                    // Handle undeployed history metadata
                    $metadatadir = "$packagePath/metadata/";
                    
                    // Get our upgrade files
                    $files = $this->getUpgradeableFilesInPath($metadatadir, $module, $metatype, 'history', $packagename, false);
                    $this->files = array_merge($this->files, $files);
                }
            }
        }
    }
    
    /**
     * Checks to see if a module is deployed
     * 
     * @param sting $module The name of the module
     * @return bool
     */
    public function isModuleDeployed($module) {
        if (empty($this->deployedModules)) {
            $dirs = glob('modules/*', GLOB_ONLYDIR);
            foreach ($dirs as $dir) {
                $this->deployedModules[$dir] = $dir;
            }
            
            sort($this->deployedModules);
        }
        
        return isset($this->deployedModules[$module]);
    }
    
    /**
     * Processes the entire upgrade process of old to new metadata styles
     */
    public function upgrade() 
    {
        // Set the upgrade file list
        $this->setFilesToUpgrade();
        
        // Traverse the files and start parsing and moving
        foreach ($this->files as $file) {
            // Get the appropriate upgrade class name for this view type
            $class = $this->getUpgraderClass($file['viewtype']);
            if ($class) {
                if (!class_exists($class, false)) {
                    $classfile = $class . '.php';
                    require_once $classfile;
                }
                $upgrader = new $class($this, $file);
                $upgrader->upgrade();
                $upgrader->cleanupLegacyFiles();
            }
        }
    }
    
    /**
     * Gets all files for a client that need to be upgraded. This is OOTB install
     * only! Custom modules are handled differently inside the call for mobile
     * file setting.
     * 
     * @param $client
     */
    protected function setUpgradeFiles($client) 
    {
        // Hit the legacy paths list to start the ball rolling 
        if (isset($this->legacyFilePaths[$client]) && is_array($this->legacyFilePaths[$client])) {
            foreach ($this->legacyFilePaths[$client] as $type => $path) {
                // Get the modules from inside the path
                $dirs = glob($path . 'modules/*', GLOB_ONLYDIR);
                if (!empty($dirs)) {
                    foreach ($dirs as $dirpath) {
                        // Get the module to list it in case it needs to be upgraded
                        $module = basename($dirpath);
                        
                        // Get the metadata directory
                        $metadatadir = "$dirpath/metadata/";
                        
                        // Get our upgrade files
                        $files = $this->getUpgradeableFilesInPath($metadatadir, $module, $client, $type);
                        $this->files = array_merge($this->files, $files);
                    }
                }
            }
        }
    }
    
    /**
     * Gets all metadata files that need to be upgraded for a module
     * 
     * @param string $path    The path to scan for metadata files
     * @param string $module  The module name, used for indexing
     * @param string $client  The client, also used for indexing
     * @param string $type    The type (custom, history, working, base)
     * @param string $package The name of the package for this module if custom
     * @param boolean $deployed Marker to determine if a custom module is deployed or not
     * @return array
     */
    protected function getUpgradeableFilesInPath($path, $module, $client, $type = 'base', $package = null, $deployed = true) 
    {
        $return = array();
        if (file_exists($path)) {
            // The second * is to pick up history files
            $files = glob($path . '*.php*'); 
            
            // And if we have any, match them against what we are looking for
            if (!empty($files)) {
                foreach ($files as $file) {
                    $timestamp = null;
                    // Handle history file handling different
                    $history = is_numeric(substr($file, -4));
                    
                    // In the case of undeployed modules, type may be set to base
                    // If it is, and there is a history file, set type to history
                    // This is primarily for saving new defs using the MetaDataFiles
                    // class to get the correct name of the metadata file
                    if ($history && !$deployed && $type == 'base') {
                        $type = 'history';
                    }
                    
                    // Only hit history files for history types with a timestamp
                    // Unless we are looking at undeployed modules
                    if (($history && $type != 'history') || (!$history && $type == 'history') && $deployed) {
                        continue;
                    }
                    
                    if ($history) {
                        $parts = explode(':', str_replace('.php_', ':', $file));
                        $filename  = basename($parts[0]);
                        $timestamp = $parts[1];
                    } else {
                        $filename = basename($file, '.php');
                    }
                    
                    if (in_array($filename, $this->legacyMetaDataFileNames[$client])) {
                        // Success! We have a full file path. Add this module to the stack
                        $this->addUpgradeModule($module);
                        
                        $return[] = array(
                            'client'    => $client,
                            'module'    => $module,
                            'type'      => $type,
                            'basename'  => $filename,
                            'timestamp' => $timestamp,
                            'fullpath'  => $file,
                            'package'   => $package,
                            'deployed'  => $deployed,
                            'viewtype'  => $this->getViewTypeFromFilename($filename),
                        );
                    }
                }
            }
        }
        
        return $return;
    }
    
    /**
     * Adds a module name to the list of upgradeable modules, for reporting
     * 
     * @param string $module The module name
     */
    protected function addUpgradeModule($module) 
    {
        if (empty($this->upgradeModules[$module])) {
            $this->upgradeModules[$module] = $module;
        }
    }
    
    /**
     * Gets a view type from a filename
     * 
     * @param string $filename The name of the file to get the view type from
     * @return string
     */
    protected function getViewTypeFromFilename($filename)
    {
        if (strpos($filename, 'list') !== false) {
            return 'list';
        }
        
        if (strpos($filename, 'edit') !== false) {
            return 'edit';
        }
        
        if (strpos($filename, 'detail') !== false) {
            return 'detail';
        }
        
        if (strpos($filename, 'search') !== false) {
            return 'search';
        }
        
        return '';
    }
    
    /**
     * Gets the class name for the upgrader that will carry out the upgrade
     * 
     * @param string $viewtype The view type (list, edit, detail)
     * @return string
     */
    protected function getUpgraderClass($viewtype) {
        if (isset($this->upgraderClassMap[$viewtype])) {
            return 'Sidecar' . $this->upgraderClassMap[$viewtype] . 'MetaDataUpgrader';
        }
        
        return false;
    }
}
