<?php
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

namespace Sugarcrm\Sugarcrm\Notification\Config;

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
        $config = \BeanFactory::getBean('Administration');
        $config = $config->getSettings(self::CONFIG_CATEGORY);
        $key = self::CONFIG_CATEGORY . '_' . $carrierName;
        if (isset($config->settings[$key])) {
            return $config->settings[$key];
        } else {
            return false;
        }
    }

    /**
     * Saving status carrier
     *
     * @param string $carrierName
     * @param boolean $status
     */
    public function setCarrierStatus($carrierName, $status)
    {
        $config = \BeanFactory::getBean('Administration');
        $config->saveSetting(self::CONFIG_CATEGORY, $carrierName, $status);
    }

}
