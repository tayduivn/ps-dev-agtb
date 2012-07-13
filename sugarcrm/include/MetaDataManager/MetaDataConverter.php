<?php
/**
 * Assists in backporting 6.6 Metadata formats to legacy style in order to
 * maintain backward compatibility with old clients consuming the V3 and V4 apis.
 */
class MetaDataConverter {
    /**
     * An instantiated object of MetaDataConverter type
     * 
     * @var MetaDataConverter 
     */
    protected static $converter = null;
    
    /**
     * Static entry point, will instantiate an object of itself to run the process.
     * Will convert $defs to legacy format $viewtype if there is a converter for 
     * it, otherwise will return the defs as-is with no modification.
     * 
     * @static
     * @param string $viewtype One of list|edit|detail
     * @param array $defs The defs to convert 
     * @return array Converted defs if there is a converter, else the passed in defs
     */
    public static function toLegacy($viewtype, $defs) {
        if (null === self::$converter) {
            self::$converter = new self;
        }
        
        $method = 'toLegacy' . ucfirst(strtolower($viewtype));
        if (method_exists(self::$converter, $method)) {
            return self::$converter->$method($defs);
        }
        
        return $defs;
    }
    
    /**
     * Takes in a 6.6+ version of mobile|portal|sidecar list view metadata and
     * converts it to pre-6.6 format for legacy clients. The formats of the defs
     * are pretty dissimilar so the steps are going to be:
     *  - Take in all defs
     *  - Clip everything but the fields portion of the panels section of the defs
     *  - Modify the fields array to be keyed on UPPERCASE field name
     * 
     * @param array $defs Field defs to convert
     * @return array
     */
    public function toLegacyList(array $defs) {
        $return = array();
        
        // Check our panels first
        if (isset($defs['panels']) && is_array($defs['panels'])) {
            foreach ($defs['panels'] as $panels) {
                // Handle fields if there are any (there should be)
                if (isset($panels['fields']) && is_array($panels['fields'])) {
                    // Logic here is simple... pull the name index value out, UPPERCASE it and
                    // set that as the new index name
                    foreach ($panels['fields'] as $field) {
                        if (isset($field['name'])) {
                            $name = strtoupper($field['name']);
                            unset($field['name']);
                            $return[$name] = $field;
                        }
                    }
                }
            }
        }
        
        
        return $return;
    }
    
    /**
     * Simple accessor into the grid legacy converter
     * 
     * @param array $defs Field defs to convert
     * @return array
     */
    public function toLegacyEdit(array $defs) {
        return $this->toLegacyGrid($defs);
    }
    
    /**
     * Simple accessor into the grid legacy converter
     * 
     * @param array $defs Field defs to convert
     * @return array
     */
    public function toLegacyDetail(array $defs) {
        return $this->toLegacyGrid($defs);
    }
    
    /**
     * Takes in a 6.6+ version of mobile|portal|sidecar edit|detail view metadata and
     * converts it to pre-6.6 format for legacy clients.
     * 
     * NOTE: This will only work for layouts that have only one field per row. For
     * the 6.6 upgrade that is sufficient since we were only converting portal
     * and mobile viewdefs. As is, this method will NOT convert grid layout view
     * defs that have more than one field per row.
     * 
     * @param array $defs
     * @return array
     */
    protected function toLegacyGrid(array $defs) {
        // Check our panels first
        if (isset($defs['panels']) && is_array($defs['panels'])) {
            // For our new panels
            $newpanels = array();
            foreach ($defs['panels'] as $panels) {
                // Handle fields if there are any (there should be)
                if (isset($panels['fields']) && is_array($panels['fields'])) {
                    // Logic is fairly straight forward... take each member of 
                    // the fields array and make it an array of its own
                    foreach ($panels['fields'] as $field) {
                        $newpanels[] = array($field);
                    }
                }
            }
            
            unset($defs['panels']);
            $defs['panels'] = $newpanels;
        }
        
        return $defs;
    }
}