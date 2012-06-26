<?php
// Get the meta data files handler for the new setup
require_once 'modules/ModuleBuilder/parsers/MetaDataFiles.php';

abstract class SidecarAbstractMetaDataUpgrader
{
    /**
     * The upgrader that triggers the upgrade process
     * 
     * @var SidecarMetaDataUpgrader
     */
    protected $upgrader;
    
    /**
     * File path to the file
     * 
     * @var string
     */
    protected $filepath;
    
    /**
     * The name of the module that this object will be getting view defs for
     *
     * @var string
     */
    protected $module;

    /**
     * The view client to work on (portal|wireless)
     *
     * @var string
     */
    protected $client;

    /**
     * The type of file this is (working, custom, history
     * 
     * @var string
     */
    protected $type;
    
    /**
     * The base name of the file
     * 
     * @var sring
     */
    protected $basename;
    
    /**
     * The name of the file
     * 
     * @var string
     */
    protected $filename;
    
    /**
     * The timestamp of the save for history types
     * 
     * @var string
     */
    protected $timestamp;
    
    /**
     * The fullpath to the filename
     * 
     * @var string 
     */
    protected $fullpath;
    
    /**
     * The name of the package that this module belongs to for custom modules
     * 
     * @var string 
     */
    protected $package;
    
    /**
     * The type of view we are working on (list, edit, detail, search)
     * @var 
     */
    protected $viewtype;
    
    /**
     * The legacy style view defs
     * 
     * @var array
     */
    protected $legacyViewdefs = array();
    
    /**
     * The sidecar style view defs
     * 
     * @var array
     */
    protected $sidecarViewdefs = array();
    
    /**
     * Deployed state of the module. This has an effect on how the filenaming is
     * handled
     * 
     * @var bool
     */
    protected $deployed = true;    
    
    /**
     * Mapping of viewdef vars for a client and viewtype
     * @var array
     */
    protected $variableMap = array(
        'portal'   => array(
            'list'   => 'viewdefs',
            'edit'   => 'viewdefs',
            'detail' => 'viewdefs',
        ),
        'wireless' => array(
            'list'   => 'listViewDefs',
            'edit'   => 'viewdefs',
            'detail' => 'viewdefs',
        ),
    );

    /**
     * Names of the views that this object will work on
     * 
     * @var array
     */
    protected $views = array(
        'portaledit'     => MB_PORTALEDITVIEW,
        'portaldetail'   => MB_PORTALDETAILVIEW,
        'wirelessedit'   => MB_WIRELESSEDITVIEW,
        'wirelessdetail' => MB_WIRELESSDETAILVIEW,
        'portallist'     => MB_PORTALLISTVIEW,
        'wirelesslist'   => MB_WIRELESSLISTVIEW,
        'portalsearch'   => MB_PORTALSEARCHVIEW,
        'wirelesssearch' => MB_WIRELESSBASICSEARCH,
    );
    
    /**
     * The indexes within the old viewdefs after the module name for each client
     * ex. $viewdefs['Bugs']['EditView']
     * 
     * @var array
     */
    protected $vardefIndexes = array(
        'portaledit'     => 'editview',
        'portaldetail'   => 'detailview',
        'wirelessedit'   => 'EditView',
        'portallist'     => 'listview',
        'wirelesslist'   => '',
        'wirelessdetail' => 'DetailView',
    );
    
    /**
     * The actual legacy defs converter. For search this will do nothing as search
     * is really just a file move and rename. 
     */
    abstract function convertLegacyViewDefsToSidecar();
    
    /**
     * Object constructor, simply sets required information into the object
     *
     * @param SidecarMetaDataUpgrader $upgrader The original entry into the upgrade process
     * @param array $file Information related to the file being worked on. There should be
     *                    an index for each property in this object.
     */
    public function __construct(SidecarMetaDataUpgrader $upgrader, Array $file) {
        $this->upgrader = $upgrader;
        
        foreach ($file as $prop => $val) {
            $this->$prop = $val;
        }
        
        if (!empty($this->fullpath)) {
            $this->filename = basename($this->fullpath);
        }
    }
    
    /**
     * Handles the actual upgrading for list metadata
     * 
     * THIS WILL BE REDEFINED FOR SEARCH
     * 
     * @return boolean
     */
    public function upgrade() {
        // Get our legacy view defs
        $this->setLegacyViewdefs();
        
        // Convert them
        $this->convertLegacyViewDefsToSidecar();
        
        // Save the new file and report it
        return $this->handleSave();
    }
    
    /**
     * Handles the actual setting of the new file name and the creating of the 
     * file contents
     */
    public function handleSave() {
        // Get what we need to make our new files
        $viewname = $this->views[$this->client . $this->viewtype];
        $newname = $this->getNewFileName($viewname);
        $content = $this->getNewFileContents();
        
        // Make the new file
        //return $this->save($newname, $content);
        return true;
    }
    
    /**
     * Sets the necessary legacy field defs for use in converting
     */
    public function setLegacyViewdefs() {
        // This check is probably not necessary, but seems like it is a good idea anyway
        if (file_exists($this->fullpath)) {
            require_once $this->fullpath;
            
            // There is an odd case where custom modules are pathed without the
            // package name prefix but still use it in the module name for the
            // viewdefs. This handles that case. Also sets a prop that lets the
            // rest of the process know that the module is named differently
            if (isset($module_name)) {
                $this->modulename = $module = $module_name;
            } else {
                $module = $this->module;
            }
            
            $var = $this->variableMap[$this->client][$this->viewtype];
            if (isset($$var)) {
                $defs = $$var;
                if (isset($this->vardefIndexes[$this->client.$this->viewtype])) {
                    $index = $this->vardefIndexes[$this->client.$this->viewtype];
                    $this->legacyViewdefs = empty($index) ? $defs[$module] : $defs[$module][$index];
                }
            }
        }
    }
    
    /**
     * Saves the contents of a file to the specified path
     * 
     * @param string $path The path to the file to save
     * @param string $content The content of the file to save
     * @return int The number of bytes written to the file or false on failure
     */
    public function save($path, $content) {
        return sugar_file_put_contents($path, $content);
    }
    
    /**
     * Gets the new path and filename for this view
     * 
     * @param string $view The view name to get the filename for
     * @return string
     */
    public function getNewFileName($view) {
        if ($this->deployed) {
            // Deployed will always use the full key_module name for custom modules 
            $module = $this->getNormalizedModuleName();
            $newname = MetaDataFiles::getDeployedFileName($view, $module, $this->type, $this->client);
        } else {
            $newname = MetaDataFiles::getUndeployedFileName($view, $this->module, $this->package, $this->type, $this->client);
        }
        
        // If this is a history file then add the timestamp back on
        if ($this->timestamp) {
            $newname .= '_' . $this->timestamp;
        }
        
        return $newname;
    }
    
    /**
     * Creates the stringified rendition of the vardefs that will be written to 
     * the new file. Works very similar to the write method in the metadata 
     * implementations, but without all the extra processing that goes in with
     * them. This is overwritten for search as search is just a content move.
     * 
     * @return string
     */
    public function getNewFileContents() {
        $module = $this->getNormalizedModuleName();
        $out  = "<?php\n\$viewdefs['{$module}'] = " . var_export($this->sidecarViewdefs[$module], true) . ";\n";
        return $out;
    }
    
    /**
     * For custom modules, gets the module name as it is represented to the app. 
     * Deployed module names will be PackageKey_ModuleName. Undeployed will just
     * be ModuleName.
     * 
     * @return string
     */
    public function getNormalizedModuleName() {
        return isset($this->modulename) ? $this->modulename : $this->module;
    }
}