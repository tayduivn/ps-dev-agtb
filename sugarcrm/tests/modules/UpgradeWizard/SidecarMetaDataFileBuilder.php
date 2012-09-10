<?php
//FILE SUGARCRM flav=pro || flav=ent || flav=sales ONLY
/**
 * This class moves test metadata files into legacy locations to test the upgrade
 * routine. Will back up any existing legacy and sidecar files, and restore them,
 * as needed.
 */
class SidecarMetaDataFileBuilder {
    /**
     * The backed up files on the host machine. Contains both legacy and sidecar
     * backups.
     * 
     * @var array
     */
    private $backedup = array();
    
    /**
     * The files created for testing.
     * 
     * @var array
     */
    private $created  = array();
    
    /**
     * The file suffix to use when creating a backup
     * 
     * @var string
     */
    private $backupSuffix = '_unittest.bak';
    
    /**
     * The list of test files to make
     * 
     * @var array
     */
    private $filesToMake = array(
        //BEGIN SUGARCRM flav=pro || flav=sales ONLY
        array(
            'module'      => 'Accounts', 'view' => 'edit', 'type' => 'mobile', 
            'testpath'    => 'tests/modules/UpgradeWizard/metadata/Accountswirelessedit.php',
            'legacypath'  => 'custom/history/modules/Accounts/metadata/wireless.editviewdefs.php_1341122961', 
            'sidecarpath' => 'custom/history/modules/Accounts/clients/mobile/views/edit/edit.php_1341122961',
        ),
        array(
            'module'      => 'Accounts', 'view' => 'detail', 'type' => 'mobile', 
            'testpath'    => 'tests/modules/UpgradeWizard/metadata/Accountswirelessdetail.php',
            'legacypath'  => 'custom/working/modules/Accounts/metadata/wireless.detailviewdefs.php', 
            'sidecarpath' => 'custom/working/modules/Accounts/clients/mobile/views/detail/detail.php',
        ),
        array(
            'module'      => 'Bugs', 'view' => 'list', 'type' => 'mobile', 
            'testpath'    => 'tests/modules/UpgradeWizard/metadata/Bugswirelesslist.php',
            'legacypath'  => 'custom/modules/Bugs/metadata/wireless.listviewdefs.php', 
            'sidecarpath' => 'custom/modules/Bugs/clients/mobile/views/list/list.php',
        ),
        array(
            'module'      => 'Bugs', 'view' => 'search', 'type' => 'mobile', 
            'testpath'    => 'tests/modules/UpgradeWizard/metadata/Bugswirelesssearch.php',
            'legacypath'  => 'custom/working/modules/Bugs/metadata/wireless.searchdefs.php', 
            'sidecarpath' => 'custom/working/modules/Bugs/clients/mobile/views/search/search.php',
        ),
        //END SUGARCRM flav=pro || flav=sales ONLY
        //BEGIN SUGARCRM flav=ent ONLY
        array(
            'module'      => 'Cases', 'view' => 'edit', 'type' => 'portal', 
            'testpath'    => 'tests/modules/UpgradeWizard/metadata/Casesportaledit.php',
            'legacypath'  => 'custom/working/portal/modules/Cases/metadata/editviewdefs.php', 
            'sidecarpath' => 'custom/working/modules/Cases/clients/portal/views/edit/edit.php',
        ),
        array(
            'module'      => 'Cases', 'view' => 'detail', 'type' => 'portal', 
            'testpath'    => 'tests/modules/UpgradeWizard/metadata/Casesportaldetail.php',
            'legacypath'  => 'custom/portal/modules/Cases/metadata/detailviewdefs.php_1341122961', 
            'sidecarpath' => 'custom/history/modules/Cases/clients/portal/views/detail/detail.php_1341122961',
        ),
        array(
            'module'      => 'Bugs', 'view' => 'list', 'type' => 'portal', 
            'testpath'    => 'tests/modules/UpgradeWizard/metadata/Bugsportallist.php',
            'legacypath'  => 'custom/portal/modules/Bugs/metadata/listviewdefs.php', 
            'sidecarpath' => 'custom/modules/Bugs/clients/portal/views/list/list.php',
        ),
        array(
            'module'      => 'Bugs', 'view' => 'search', 'type' => 'portal', 
            'testpath'    => 'tests/modules/UpgradeWizard/metadata/Bugsportalsearch.php',
            'legacypath'  => 'custom/portal/modules/Bugs/metadata/searchformdefs.php', 
            'sidecarpath' => 'custom/modules/Bugs/clients/portal/views/search/search.php',
        ),
        //END SUGARCRM flav=ent ONLY
    );

    /**
     * Builds the test files by moving them into their legacy locations. Will also 
     * back up any existing files that need to be backed up
     */
    public function buildFiles() {
        foreach ($this->filesToMake as $filedata) {
            $this->_backupExistingFile($filedata, 'legacy');
            $this->_backupExistingFile($filedata, 'sidecar');
            $this->_installTestFile($filedata);
        }
    }
    
    /**
     * Tears down the test files that were created and restores all backed up files
     */
    public function teardownFiles() {
        foreach ($this->created as $file) {
            // Kill the file we made for testing
            unlink($file);
        }
        
        // Now handle backups
        foreach ($this->backedup as $type => $backedup) {
            foreach ($backedup as $backup => $restore) {
                rename($backup, $restore);
            }
        }
    }
    
    /**
     * Gets files of type $path that are made by this object. Used in the unit test
     * for checking existence of sidecar files after the upgrade and for getting
     * the list of legacy files that were converted.
     * 
     * If $path is null, will return the array of files to make
     * 
     * @param string|null $path
     * @param bool $asArrays If true, returns each filename as an array
     * @return array
     */
    public function getFilesToMake($path = null, $asArrays = false) {
        if (!$path) {
            return $this->filesToMake;
        }
        $return = array();
        $index = $path . 'path';
        foreach ($this->filesToMake as $filedata) {
            if (isset($filedata[$index])) {
                $return[] = $asArrays ? array($filedata[$index]) : $filedata[$index];
            }
        }
        
        return $return;
    }
    
    /**
     * Used in the unit test for metadata upgrading, builds a list of files for
     * a given module, view and type
     * 
     * @param array|string $view If an array, gets all files of view type in the array
     * @param string $path The path type to get
     * @return array
     */
    public function getFilesToMakeByView($view, $path = 'sidecar') {
        $return = array();
        $index = $path . 'path';
        foreach ($this->filesToMake as $file) {
            if (is_array($view) && in_array($file['view'], $view)) {
                $return[] = array('module' => $file['module'], 'view' => $file['view'], 'type' => $file['type'], 'filepath' => $file[$index]);
            } else {
                if ($file['view'] == $view) {
                    $return[] = array('module' => $file['module'], 'view' => $file['view'], 'type' => $file['type'], 'filepath' => $file[$index]);
                }
            }
        }
        
        return $return;
    }
    
    /**
     * Utility method to back up existing files of a given type (legacy or sidecar)
     * 
     * @param array $filedata
     * @param string $type
     */
    protected function _backupExistingFile($filedata, $type) {
        $path = $type . 'path';
        if (file_exists($filedata[$path])) {
            if (rename($filedata[$path], $filedata[$path] . $this->backupSuffix)) {
                $this->backedup[$type][$filedata[$path] . $this->backupSuffix] = $filedata[$path];
            }
        } 
    }
    
    /**
     * Utility method to actually install a test file
     * 
     * @param array $filedata
     */
    protected function _installTestFile($filedata) {
        if (file_exists($filedata['testpath'])) {
            $dir = dirname($filedata['legacypath']);
            if (!is_dir($dir)) {
                mkdir_recursive($dir);
            }
            
            if (copy($filedata['testpath'], $filedata['legacypath'])) {
                $this->created[] = $filedata['legacypath'];
            }
        }
    }
}