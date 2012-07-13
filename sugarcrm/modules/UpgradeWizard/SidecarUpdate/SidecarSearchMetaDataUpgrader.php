<?php
// This will need to be pathed properly when packaged
require_once 'SidecarAbstractMetaDataUpgrader.php';

class SidecarSearchMetaDataUpgrader extends SidecarAbstractMetaDataUpgrader
{
    /**
     * Handles the actual upgrading for search metadata. This process is much
     * simpler in that no manipulation of defs is necessary. We simply move the 
     * file contents into place in the new structure.
     * 
     * @return bool
     */
    public function upgrade() {
        if (file_exists($this->fullpath)) {
            // Save the new file and report it
            return $this->handleSave();
        }
        
        return false;
    }
    
    /**
     * Does nothing for search since search is simply a file move.
     */
    public function convertLegacyViewDefsToSidecar() {}
    
    /**
     * Simply gets the current file contents
     * 
     * @return string
     */
    public function getNewFileContents() {
        return file_get_contents($this->fullpath);
    }
}