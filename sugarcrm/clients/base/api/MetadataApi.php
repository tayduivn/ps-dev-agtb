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

require_once('include/MetaDataManager/MetaDataManager.php');
require_once('include/api/SugarApi.php');

// An API to let the user in to the metadata
class MetadataApi extends SugarApi {
    public function registerApiRest() {
        return array(
            'getAllMetadata' => array(
                'reqType' => 'GET',
                'path' => array('metadata'),
                'pathVars' => array(''),
                'method' => 'getAllMetadata',
                'shortHelp' => 'This method will return all metadata for the system',
                'longHelp' => 'include/api/html/metadata_all_help.html',
            ),
            'getAllMetadataPost' => array(
                'reqType' => 'POST',
                'path' => array('metadata'),
                'pathVars' => array(''),
                'method' => 'getAllMetadata',
                'shortHelp' => 'This method will return all metadata for the system, filtered by the array of hashes sent to the server',
                'longHelp' => 'include/api/html/metadata_all_help.html',
            ),
            'getAllMetadataHashes' => array(
                'reqType' => 'GET',
                'path' => array('metadata','_hash'),
                'pathVars' => array(''),
                'method' => 'getAllMetadataHash',
                'shortHelp' => 'This method will return the hash of all metadata for the system',
                'longHelp' => 'include/api/html/metadata_all_help.html',
            ),
            'getPublicMetadata' =>  array(
                'reqType' => 'GET',
                'path' => array('metadata','public'),
                'pathVars'=> array(''),
                'method' => 'getPublicMetadata',
                'shortHelp' => 'This method will return the metadata needed when not logged in',
                'longHelp' => 'include/api/html/metadata_all_help.html',
                'noLoginRequired' => true,
            ),
        );
    }

    /**
     * Gets the type filter for this request
     * 
     * @param array $args
     * @param array $default
     * @return array
     */
    protected function getTypeFilter($args, $default) {
        $typeFilter = $default;
        if (!empty($args['type_filter'])) {
            // Explode is fine here, we control the list of types
            $types = explode(",", $args['type_filter']);
            if ($types != false) {
                $typeFilter = $types;
            }
        }
        
        return $typeFilter;
    }

    /**
     * Gets the module filter for this request
     * 
     * @param array $args
     * @param array $default
     * @return array
     */
    protected function getModuleFilter($args, $default) {
        $moduleFilter = $default;
        if (!empty($args['module_filter'])) {
            if (function_exists('str_getcsv')) {
                // Use str_getcsv here so that commas can be escaped, I pity the fool that has commas in his module names.
                $modules = str_getcsv($args['module_filter'],',','');
            } else {
                $modules = explode(",", $args['module_filter']);
            }
            
            if ( $modules != false ) {
                $moduleFilter = $modules;
            }
        }
        
        return $moduleFilter;
    }

    /**
     * Determines whether the request is a hash only metadata request
     * 
     * @param array $args
     * @return bool
     */
    protected function isOnlyHash($args) {
        return !empty($args['only_hash']) && ($args['only_hash'] == 'true' || $args['only_hash'] == '1');
    }

    /**
     * Authenticated metadata request endpoint
     * 
     * @param ServiceBase $api
     * @param array $args
     * @return array
     */
    public function getAllMetadata(ServiceBase $api, array $args) {
        // Get the metadata manager we need first
        $mm = MetaDataManager::getManager($api->platform);
        
        // These are the base metadata sections in private metadata
        $sections = $mm->getSections();
        
        // Default the type filter to everything, but filter them if requested
        $typeFilter = $this->getTypeFilter($args, $sections);
        
        // Same with module filtering
        $moduleFilter = $this->getModuleFilter($args, array());
        
        // Is this a hash only request?
        $onlyHash = $this->isOnlyHash($args);
        
        // Get our metadata now
        $data = $mm->getMetadata();

        // ETag that bad boy
        generateETagHeader($data['_hash']);
        
        // Handle chunking
        $key = array_search('modules', $sections);
        if ($key !== false) {
            unset($sections[$key]);
        }

        $baseChunks = $sections;
        $perModuleChunks = array('modules');
        
        return $this->filterResults($args, $data, $typeFilter, $onlyHash, $baseChunks, $perModuleChunks, $moduleFilter);
    }

    /**
     * Public metadata request endpoint
     * 
     * @param $api
     * @param $args
     * @return array
     */
    public function getPublicMetadata($api, $args) {
        // Get the metadata manager we need for this request
        $mm = MetaDataManager::getManager($api->platform, true);
        
        // Public metadata sections, no module info at this time
        $baseChunks = $mm->getSections();
        
        // Set the type filter from the sections
        $typeFilter = $this->getTypeFilter($args, $baseChunks);
        
        // See if this is a hash only request
        $onlyHash = $this->isOnlyHash($args);
        
        // Get the metadata now
        $data = $mm->getMetadata();
        generateETagHeader($data['_hash']);

        return $this->filterResults($args, $data, $typeFilter, $onlyHash, $baseChunks);
    }
    
    /*
     * Filters the results for Public and Private Metadata
     * @param array $args the Arguments from the Rest Request
     * @param array $data the data to be filtered
     * @param array $typeFilter the specific sections of metadata we want
     * @param bool $onlyHash check to return only hashes
     * @param array $baseChunks the chunks we want filtered
     * @param array $perModuleChunks the module chunks we want filtered
     * @param array $moduleFilter the specific modules we want
     */
    protected function filterResults($args, $data, $typeFilter, $onlyHash = false, $baseChunks = array(), $perModuleChunks = array(), $moduleFilter = array()) {
        if ( $onlyHash ) {
            // The client only wants hashes
            $hashesOnly = array();
            $hashesOnly['_hash'] = $data['_hash'];
            foreach ( $baseChunks as $chunk ) {
                if (in_array($chunk,$typeFilter) ) {
                    $hashesOnly[$chunk]['_hash'] = $data['_hash'];
                }
            }

            foreach ( $perModuleChunks as $chunk ) {
                if (in_array($chunk, $typeFilter)) {
                    // We want modules, let's filter by the requested modules and by which hashes match.
                    foreach($data[$chunk] as $modName => &$modData) {
                        if (empty($moduleFilter) || in_array($modName,$moduleFilter)) {
                            $hashesOnly[$chunk][$modName]['_hash'] = $data[$chunk][$modName]['_hash'];
                        }
                    }
                }
            }

            $data = $hashesOnly;

        } else {
            // The client is being bossy and wants some data as well.
            foreach ( $baseChunks as $chunk ) {
                if (!in_array($chunk,$typeFilter)
                    || (isset($args[$chunk]) && $args[$chunk] == $data[$chunk]['_hash'])) {
                    unset($data[$chunk]);
                }
            }

            // Relationships are special, they are a baseChunk but also need to pay attention to modules
            if (!empty($moduleFilter) && isset($data['relationships']) ) {
                // We only want some modules, but we want the relationships
                foreach ($data['relationships'] as $relName => $relData ) {
                    if ( $relName == '_hash' ) {
                        continue;
                    }
                    if (!in_array($relData['rhs_module'],$moduleFilter)
                        && !in_array($relData['lhs_module'],$moduleFilter)) {
                        unset($data['relationships'][$relName]);
                    }
                    else { $data['relationships'][$relName]['checked'] = 1; }
                }
            }

            foreach ( $perModuleChunks as $chunk ) {
                if (!in_array($chunk, $typeFilter)) {
                    unset($data[$chunk]);
                } else {
                    // We want modules, let's filter by the requested modules and by which hashes match.
                    foreach($data[$chunk] as $modName => &$modData) {
                        if ((!empty($moduleFilter) && !in_array($modName,$moduleFilter))
                            || (isset($args[$chunk][$modName]) && $args[$chunk][$modName] == $modData['_hash'])) {
                            unset($data[$chunk][$modName]);
                            continue;
                        }
                    }
                }
            }
        }

        return $data;
    }
}
