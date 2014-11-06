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
 * Undeployed metadata file
 */
class MetaDataFileUndeployed implements MetaDataFileInterface
{
    /**
     * @var MetaDataFile
     */
    protected $file;

    /**
     * @var string
     */
    protected $package;

    /**
     * @var string
     */
    protected $type;

    /**
     * Constructor
     *
     * @param MetaDataFileInterface $file
     * @param string $package
     */
    public function __construct(MetaDataFileInterface $file, $package, $type)
    {
        $this->file = $file;
        $this->package = $package;
        $type = strtolower($type);

        switch ($type) {
            case MB_BASEMETADATALOCATION:
            case MB_HISTORYMETADATALOCATION:
            case MB_WORKINGMETADATALOCATION:
                break;
            default:
                // just warn rather than die
                $GLOBALS['log']->warn(
                    "UndeployedMetaDataImplementation->getFileName(): view type $type is not recognized"
                );
                break;
        }

        $this->type = $type;
    }

    /** {@inheritDoc} */
    public function getPath()
    {
        $path = $this->file->getPath();

        switch ($this->type) {
            case MB_HISTORYMETADATALOCATION:
                array_unshift(
                    $path,
                    MetaDataFiles::$paths[MB_WORKINGMETADATALOCATION],
                    'modulebuilder',
                    'packages',
                    $this->package
                );
                break;
            default:
                // get the module again, all so we can call this method statically without relying
                // on the module stored in the class variables
                require_once 'modules/ModuleBuilder/MB/ModuleBuilder.php';
                $mb = new ModuleBuilder();
                array_shift($path);
                $module = array_shift($path);
                array_unshift($path, $mb->getPackageModule($this->package, $module)->getModuleDir());
        }

        return $path;
    }
}
