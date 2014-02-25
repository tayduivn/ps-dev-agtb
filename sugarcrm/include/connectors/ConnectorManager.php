<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc. All rights reserved.
 */
/**
 * Connector Manager
 * Manages connector caching
 */
class ConnectorManager
{
    /**
     * gets list of all connectors
     * @return mixed
     */
    protected function getConnectorList()
    {
        return ConnectorUtils::getConnectors();
    }

    /**
     * gets connectors meta
     * @return array
     */
    public function buildConnectorsMeta()
    {
        require_once('include/connectors/sources/SourceFactory.php');
        require_once('include/connectors/utils/ConnectorUtils.php');

        $allConnectors = $this->getConnectorList();
        $connectors = array();
        // get general connector info
        foreach ($allConnectors as $name => $connector) {
            $instance = ConnectorFactory::getInstance($connector['id']);
            $connectorInfo = array(
                'testing_enabled' => false,
                'test_passed' => false,
                'eapm_bean' => false,
                'field_mapping' => array(),
                'id' => $connector['id']
            );

            if (!empty($connector['name'])) {
                $connectorInfo['name'] = $connector['name'];
            }

            $source = $this->getSourceForConnector($connector);
            if (isset($source)) {
                if ($source->hasTestingEnabled()) {
                    $connectorInfo['testing_enabled'] = true;
                    if ($source->test()) {
                        $connectorInfo['test_passed'] = true;
                    }
                }
            }

            if (method_exists($instance, 'getMapping')) {
                $connectorInfo['field_mapping'] = $instance->getMapping();
            }

            $connectors[$name] = $connectorInfo;
        }

        $connectorsHash = $this->hash($connectors);
        $connectors['_hash'] = $connectorsHash;

        $this->putConnectorCache($connectors);

        return $connectors;
    }

    /**
     * returns md5 of serialized input
     * @param mixed $in
     * @return string
     */
    public function hash($in)
    {
        return md5(serialize($in));
    }

    /**
     * gets connector meta and mixes in user auth info
     * @param array $connectors
     * @return array
     */
    public function getUserConnectors()
    {
        // Handle the cache file
        $cacheFile = sugar_cached('api/metadata/connectors.php');
        if (file_exists($cacheFile)) {
            require $cacheFile;
        } else {
            $connectors = $this->buildConnectorsMeta();
        }

        // mix in user specific data
        foreach ($connectors as $name => $connector) {
            if (is_array($connectors[$name])) {
                $eapmBean = $this->getEAPMForConnector($connector);
                $connectors[$name]['eapm_bean'] = !empty($eapmBean->id);
            }
        }

        $hash = $this->hash($connectors);

        return array(
            'connectors' => $connectors,
            '_hash' => $hash,
        );
    }

    /**
     * puts connector data in cache
     * @param array $data
     */
    public function putConnectorCache($data)
    {
        // Create the cache directory if need be
        // fix for the cache/api/metadata problem
        $cacheDir = 'api/metadata';

        mkdir_recursive(sugar_cached($cacheDir));

        // Handle the cache file
        $cacheFile = sugar_cached('api/metadata/connectors.php');
        $write = "<?php\n" .
            '// created: ' . date('Y-m-d H:i:s') . "\n" .
            '$connectors = ' .
            var_export_helper($data) . ';';

        // Write with atomic writing to prevent issues with simultaneous requests
        // for this file
        sugar_file_put_contents_atomic($cacheFile, $write);

        if (!empty($data['_hash'])) {
            $this->addToHash('connectors', $data['_hash']);
        }

    }

    /**
     * adds current connector hash to cache
     * @param string $key
     * @param string $hash
     */
    protected function addToHash($key, $hash)
    {
        $hashes = array();
        $path = sugar_cached("api/metadata/connectorHashes.php");
        @include($path);
        $hashes[$key] = $hash;
        write_array_to_file("hashes", $hashes, $path);
        SugarAutoLoader::addToMap($path);
    }

    /**
     * gets current connectors hash from cache
     * @param string $key key of hash to retrieve
     * @return bool
     */
    protected function getFromHashCache($key)
    {
        $hashes = array();
        $path = sugar_cached("api/metadata/connectorHashes.php");
        @include($path);

        return !empty($hashes[$key]) ? $hashes[$key] : false;
    }

    /**
     * gets source for connector
     * @param string $connector connector name
     * @return null|source
     */
    public function getSourceForConnector($connector)
    {
        if (isset($connector['id'])) {
            return SourceFactory::getSource($connector['id']);
        } else {
            return null;
        }
    }

    /**
     * checks if hash is valid
     * @param string $hash
     * @return bool
     */
    public function isHashValid($hash)
    {
        $userConnectors = $this->getUserConnectors();
        return $hash === $userConnectors['_hash'];
    }

    /**
     * gets EAPM bean for connector per current user
     * @param array $connector
     * @return null|object|SugarBean
     */
    public function getEAPMForConnector($connector)
    {
        if (isset($connector['name'])) {
            //Take the substring up to '&' because the name field always ends in '&#169'
            return EAPM::getLoginInfo(substr($connector['name'], 0, strpos($connector['name'], '&')));
        } else {
            return null;
        }
    }
}
