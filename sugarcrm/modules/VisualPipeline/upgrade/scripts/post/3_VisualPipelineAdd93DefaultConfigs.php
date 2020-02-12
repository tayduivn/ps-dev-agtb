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

class SugarUpgradeVisualPipelineAdd93DefaultConfigs extends UpgradeScript
{
    public $order = 3100;
    public $version = '9.3.0';
    public $type = self::UPGRADE_CUSTOM;

    public function run()
    {
        if ($this->shouldInstallPipeline93Defaults()) {
            VisualPipelineDefaults::setupPipeline93Settings(true);
        }
    }

    public function shouldInstallPipeline93Defaults()
    {
        $isConversion = !$this->fromFlavor('ent') && $this->toFlavor('ent');
        $isBelowOrAt93Ent = $this->toFlavor('ent') && version_compare($this->from_version, '9.3.0', '<=');
        return $isConversion || $isBelowOrAt93Ent;
    }
}
