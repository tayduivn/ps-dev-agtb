<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

use Sugarcrm\Sugarcrm\PackageManager\PackageManager as UpgradedPackageManager;
use Sugarcrm\Sugarcrm\PackageManager\Entity\PackageManifest;

require_once 'include/utils.php';

class PackageManager
{
    /**
     * @var UpgradedPackageManager
     */
    private $upgradedPackageManager;

    /**
     * Constructor: In this method we will initialize the nusoap client to point to the hearbeat server
     */
    public function __construct()
    {
        $this->upgradedPackageManager = new UpgradedPackageManager();
    }

    public static function fromNameValueList($nvl)
    {
        $array = array();
        foreach ($nvl as $list) {
            $array[$list['name']] = $list['value'];
        }

        return $array;
    }

    /**
     * @param $type
     * @return string
     */
    protected function getPackageTypeUIText(string $type): string
    {
        switch ($type) {
            case PackageManifest::PACKAGE_TYPE_FULL:
                return translate('LBL_UW_TYPE_FULL', 'Administration');
            case PackageManifest::PACKAGE_TYPE_LANGPACK:
                return translate('LBL_UW_TYPE_LANGPACK', 'Administration');
                break;
            case PackageManifest::PACKAGE_TYPE_MODULE:
                return translate('LBL_UW_TYPE_MODULE', 'Administration');
                break;
            case PackageManifest::PACKAGE_TYPE_PATCH:
                return translate('LBL_UW_TYPE_PATCH', 'Administration');
                break;
            case PackageManifest::PACKAGE_TYPE_THEME:
                return translate('LBL_UW_TYPE_THEME', 'Administration');
                break;
        }
    }

    /**
     * return array of staging packages
     * @return array
     * @throws SugarQueryException
     */
    public function getPackagesInStaging(): array
    {
        $packages = $this->upgradedPackageManager->getStagedPackages();

        return array_map(
            function (UpgradeHistory $history) {
                $data = $history->getData();

                // Convert to old format because field order is critical for old YAHOO UI.
                return [
                    'name' => $data['name'],
                    'version' => $data['version'],
                    'published_date' => $data['published_data'],
                    'description' => $data['description'],
                    'uninstallable' => $history->isPackageUninstallable() ? 'Yes' : 'No',
                    'type' => $this->getPackageTypeUIText($data['type']),
                    'file' => $data['id'],
                    'file_install' => $data['id'],
                    'unFile' => $data['id'],
                ];
            },
            array_values($packages)
        );
    }

    public function getinstalledPackages()
    {
        $packages = $this->upgradedPackageManager->getInstalledPackages();

        return array_map(
            function (UpgradeHistory $history) {
                $data = $history->getData();

                // Convert to old format because field order is critical for old YAHOO UI.
                return [
                    'name' => $data['name'],
                    'version' => $data['version'],
                    'type' => $this->getPackageTypeUIText($data['type']),
                    'published_date' => $data['published_data'],
                    'description' => $data['description'],
                    'uninstallable' => $history->isPackageUninstallable() ? 'Yes' : 'No',
                    'file_install' => $data['id'],
                    'file' => $data['id'],
                    'enabled' => $history->isPackageEnabled() ? 'ENABLED' : 'DISABLED',
                    'id' => $history->id,
                ];
            },
            array_values($packages)
        );
    }
}
