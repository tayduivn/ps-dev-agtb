<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

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

require_once 'modules/ModuleBuilder/parsers/MetaDataFileInterface.php';

/**
 * Deployed metadata file
 */
class MetaDataFileDeployed implements MetaDataFileInterface
{
    /**
     * @var MetaDataFile
     */
    protected $file;

    /**
     * @var string
     */
    protected $location;

    /**
     * Constructor
     *
     * @param MetaDataFileInterface $file
     * @param string $location
     */
    public function __construct(MetaDataFileInterface $file, $location)
    {
        $this->file = $file;
        $this->location = strtolower($location);
    }

    /** {@inheritDoc} */
    public function getPath()
    {
        $paths = MetaDataFiles::getPaths();
        if (!isset($paths[$this->location])) {
            throw new Exception("Deployed metadata location $this->location is not recognized");
        }

        $path = $this->file->getPath();
        if ($paths[$this->location]) {
            array_unshift($path, $paths[$this->location]);
        }

        return $path;
    }
}
