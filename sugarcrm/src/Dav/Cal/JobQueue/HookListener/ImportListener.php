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

namespace Sugarcrm\Sugarcrm\Dav\Cal\JobQueue\HookListener;

use Sugarcrm\Sugarcrm\Dav\Cal\Hook\Notifier\ListenerInterface;

/**
 * Class ImportListener
 * @package Sugarcrm\Sugarcrm\Dav\Cal\JobQueue\HookListener
 */
class ImportListener implements ListenerInterface
{
    /**
     * EventCollection object.
     *
     * @var \CalDavEventCollection
     */
    protected $bean;

    /**
     * Set of import data.
     *
     * @var array
     */
    protected $dataSet = array();

    /**
     * @param \CalDavEventCollection $bean
     */
    public function __construct(\CalDavEventCollection $bean)
    {
        $this->bean = $bean;
    }

    /**
     * @inheritdoc
     */
    public function update($beanModule, $beanId, $data)
    {
        if ($this->bean->module_name == $beanModule && $this->bean->id == $beanId) {
            $this->dataSet[] = $data;
            return false;
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getDataSet()
    {
        return $this->dataSet;
    }
}
