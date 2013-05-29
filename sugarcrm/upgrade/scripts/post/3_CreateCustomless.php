<?php
/**
 * Create empty file custom.less
 */
class SugarUpgradeCreateCustomless extends UpgradeScript
{
    public $order = 3000;
    public $type = self::UPGRADE_CORE;

    public function run()
    {
        if (!version_compare($this->from_version, '6.7.0', '<'))
        {
            return;
        }

        if(!file_exists('styleguide/less/clients/base/custom.less')) {
            $this->createFile('styleguide/less/clients/base/custom.less');
        }
    }
}
