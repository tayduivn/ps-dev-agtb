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

namespace Sugarcrm\Sugarcrm\Dav\Cal;

use Sugarcrm\Sugarcrm\JobQueue\Manager\Manager as JQManager;
use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory as CalDavAdapaterFactory;

/**
 * Class Handler
 * Export/import bean
 * @package Sugarcrm\Sugarcrm\Dav\Cal
 */
class Handler
{
    /**
     * Run export action
     * @param \SugarBean $bean
     */
    public function export(\SugarBean $bean)
    {
        $adapterFactory = $this->getAdapterFactory();
        if ($adapter = $adapterFactory->getAdapter($bean->module_name)) {
            $calDavEvent = $this->getCalDavEvent();
            if ($this->isBeanChild($bean)) {
                $bean = $this->getParentBean($bean);
            }
            $relatedCalDavBean = $calDavEvent->findByBean($bean);
            if ($relatedCalDavBean !== null) {
                $calDavBean = $relatedCalDavBean;
            } else {
                $calDavBean = $calDavEvent;
                $calDavBean->setBean($bean);
            }

            if ($adapter->export($bean, $calDavBean)) {
                $calDavBean->save();
            }
        }
    }

    /**
     * Get CalDav bean object
     * @return \CalDavEvent
     */

    public function getDavBean()
    {

    }

    /**
     * Get Sugar Bean object, except CalDav
     * @return \SugarBean
     */
    public function getSugarBean()
    {

    }

    /**
     * Run import action
     * @param \CalDavEvent $calDavBean
     */

    public function import(\CalDavEvent $calDavBean)
    {
        $bean = $calDavBean->getBean();
        $adapterFactory = $this->getAdapterFactory();
        if ($adapter = $adapterFactory->getAdapter($bean->module_name)) {
            if ($adapter->import($bean, $calDavBean)) {
                $bean->save();
            }
            if (!$calDavBean->parent_id) {
                $calDavBean->setBean($bean);
                $calDavBean->save();
            }
        }
    }

    /**
     * function return manager object for handler processing
     * @return \Sugarcrm\Sugarcrm\JobQueue\Manager\Manager
     */
    protected function getManager()
    {
        return new JQManager();
    }

    /**
     * @return \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory
     */
    protected function getAdapterFactory()
    {
        return CalDavAdapaterFactory::getInstance();
    }

    /**
     * @param \SugarBean $bean
     * @return null|\SugarBean
     */
    protected function getParentBean($bean)
    {
        return \BeanFactory::getBean($bean->module_name, $bean->repeat_parent_id, array('use_cache' => false));
    }

    /**
     * Check if bean is child using repeat_parent_id
     * @param \SugarBean $bean
     * @return bool
     */
    protected function isBeanChild($bean)
    {
        return $bean->repeat_parent_id ? true : false;
    }

    /**
     * return CalDavEvent bean
     * @return null|\SugarBean
     */
    protected function getCalDavEvent()
    {
        return \BeanFactory::getBean('CalDavEvents');
    }
}
