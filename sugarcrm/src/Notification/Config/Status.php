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

namespace Sugarcrm\Sugarcrm\Notification\Config;

use Sugarcrm\Sugarcrm\Notification\CarrierRegistry;
use Administration;

/**
 * Class Status
 * @package Notification
 */
class Status
{
    const CONFIG_CATEGORY = 'notification_carrier';

    /**
     * Returns object of Status, customized if it's present
     *
     * @return Status
     */
    public static function getInstance()
    {
        $class = \SugarAutoLoader::customClass('Sugarcrm\Sugarcrm\Notification\Config\Status');
        return new $class();
    }

    /**
     * Function return carrier status
     *
     * @param string $carrierName
     * @return bool
     */
    public function getCarrierStatus($carrierName)
    {
        $this->verifyModule($carrierName);
        /** @var Administration $config */
        $config = \BeanFactory::getBean('Administration');
        $config = $config->getSettings(static::CONFIG_CATEGORY);
        $key = static::CONFIG_CATEGORY . '_' . $carrierName;
        return !empty($config->settings[$key]);
    }

    /**
     * Saving status carrier
     *
     * @param string $carrierName
     * @param bool $status
     * @return bool
     */
    public function setCarrierStatus($carrierName, $status)
    {
        $this->verifyModule($carrierName);
        /** @var \Administration $config */
        $config = \BeanFactory::getBean('Administration');
        $config->saveSetting(static::CONFIG_CATEGORY, $carrierName, $status);
        return !empty($status);
    }

    /**
     * Verifies existing carrier module
     *
     * @param string $module
     * @throws \SugarApiExceptionNotFound
     */
    protected function verifyModule($module)
    {
        if (!in_array($module, $this->getCarrierRegistry()->getCarriers())) {
            throw new \LogicException('Not found carrier module: ' . $module);
        }
    }

    /**
     * Factory method
     *
     * @return CarrierRegistry
     */
    protected function getCarrierRegistry()
    {
        return CarrierRegistry::getInstance();
    }
}
