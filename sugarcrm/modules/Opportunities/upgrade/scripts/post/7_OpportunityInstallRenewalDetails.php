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

use Sugarcrm\Sugarcrm\AccessControl\AdminWork;

/**
 * Install Renewal Details dashboard.
 */
class SugarUpgradeOpportunityInstallRenewalDetails extends UpgradeScript
{
    public $order = 7552;
    public $type = self::UPGRADE_DB;

    /**
     * {@inheritDoc}
     */
    public function run()
    {
        if ($this->shouldInstallDashboard()) {
            $this->installDashboard();
        } else {
            $this->log('Not installing Renewal Details dashboard');
        }
    }

    /**
     * Determine if we should install the Renewal Details dashboard.
     *
     * @return bool true if we should install the Renewal Details dashboard.
     */
    public function shouldInstallDashboard(): bool
    {
        $isFlavorConversion = !$this->fromFlavor('ent') && $this->toFlavor('ent');
        $isBelow930Ent = $this->toFlavor('ent') && version_compare($this->from_version, '9.3.0', '<');
        return $isFlavorConversion || $isBelow930Ent;
    }

    /**
     * Install the specified dashboard and log a message if not installed.
     */
    public function installDashboard()
    {
        $this->log('Temporarily enabling admin work for Portal Home Dashboard installation');
        $adminWork = new AdminWork();
        $adminWork->startAdminWork();

        $this->log('Installing Renewal Details dashboard and dependencies');

        require_once 'modules/dashboards/DefaultDashboardInstaller.php';
        $this->defaultDashboardInstaller = new DefaultDashboardInstaller();

        $dashboardFile = 'modules/Opportunities/dashboards/multi-line/multi-line.php';

        $result = $this->defaultDashboardInstaller->buildDashboardFromFile($dashboardFile, 'Opportunities', 'multi-line');
        if (!$result) {
            $this->log('Did not install Renewal Details dashboard: ' . $dashboardFile);
        }
    }
}
