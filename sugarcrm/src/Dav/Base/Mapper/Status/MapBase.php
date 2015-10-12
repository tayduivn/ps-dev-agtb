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

namespace Sugarcrm\Sugarcrm\Dav\Base\Mapper\Status;

abstract class MapBase
{
    /**
     * Status map
     * @var array
     */
    protected $statusMap = array();

    /**
     * Field name from module defs
     * @var string
     */
    protected $statusField = 'status';

    /**
     * Retrieve logger instance
     * @return \LoggerManager
     */
    protected function getLogger()
    {
        return $GLOBALS['log'];
    }

    /**
     * Get global application strings
     * @return array
     */
    protected function getAppListStrings()
    {
        global $app_list_strings;

        return $app_list_strings;
    }

    /**
     * Filter statusMaps for valid mappings key and return it
     * If mapping not found empty array should be returned to allow CalDav or SugarCRM module select the default value
     * @param \CalDavEvent $event
     * @return array
     */
    public function getMapping(\CalDavEvent $event)
    {
        $appStrings = $this->getAppListStrings();
        $relatedModule = $event->getBean();
        if (empty($relatedModule)) {
            $this->getLogger()->error('CalDavEvent does not have related bean');
            return array();
        }
        if (!isset($relatedModule->field_defs[$this->statusField]['options'])) {
            $this->getLogger()->error('CalDavEvent can\'t retrieve ' . $this->statusField . ' options for module ' .
                $relatedModule->module_name);

            return array();
        }

        $optionsKey = $relatedModule->field_defs[$this->statusField]['options'];

        if (!isset($appStrings[$optionsKey])) {
            $this->getLogger()->error('CalDavEvent can\'t retrieve ' . $this->statusField . ' options for module ' .
                $relatedModule->module_name);

            return array();
        }

        $result = array();
        foreach ($this->statusMap as $davKey => $sugarKey) {
            if (isset($appStrings[$optionsKey][$sugarKey])) {
                $result[$davKey] = $sugarKey;
            }
        }

        return $result;
    }
}
