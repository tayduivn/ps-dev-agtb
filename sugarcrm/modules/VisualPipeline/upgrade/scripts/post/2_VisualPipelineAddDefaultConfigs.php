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

class SugarUpgradeVisualPipelineAddDefaultConfigs extends UpgradeScript
{
    public $order = 2100;
    public $version = '9.1.0';
    public $type = self::UPGRADE_CUSTOM;

    public function run()
    {
        if ($this->shouldInstallPipelineDefaults()) {
            VisualPipelineDefaults::setupPipelineSettings();
        }
    }

    public function shouldInstallPipelineDefaults()
    {
        $isConversion = !$this->fromFlavor('ent') && $this->toFlavor('ent');
        $isBelowOrAt91Ent = $this->toFlavor('ent') && version_compare($this->from_version, '9.1.0', '<=');
        return $isConversion || $isBelowOrAt91Ent;
    }
}
