<?php

/**
 * Remove old connectors in 7.0.0 and above
 */
class SugarUpgradeRemoveOldCustomConnectors extends UpgradeScript
{
    /**
     * @var int
     */
    public $order = 7000;

    /**
     * @var int
     */
    public $type = self::UPGRADE_CUSTOM;

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        // according to INT-20 - old core connectors was deleted in 7.0.0
        if (!version_compare($this->to_version, '7.0.0', '>=')) {
            return;
        }

        $this->fileToDelete(array(
            'custom/modules/Connectors/connectors/sources/ext/rest/zoominfocompany',
            'custom/modules/Connectors/connectors/sources/ext/rest/zoominfoperson',
            'custom/modules/Connectors/connectors/sources/ext/rest/linkedin',
            'custom/modules/Connectors/connectors/sources/ext/rest/insideview',
            'custom/modules/Connectors/connectors/sources/ext/eapm/facebook',
            'custom/modules/Connectors/connectors/sources/ext/soap/hoovers',
        ));
    }
}
