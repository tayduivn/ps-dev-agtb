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

namespace Sugarcrm\Sugarcrm\JobQueue\Workload;

/**
 * Class OD
 * @package JobQueue
 */
class OD extends Workload
{
    /**
     * Add the instance key.
     * {@inheritdoc}
     */
    public function __construct($route, $data, array $attributes = array())
    {
        parent::__construct($route, $data, $attributes);
        $urlData = parse_url(\SugarConfig::getInstance()->get('site_url'));

        $this->setInstanceKey($urlData['host']);
    }

    /**
     * Set instance specific part.
     * @param string $instanceKey
     */
    public function setInstanceKey($instanceKey)
    {
        $this->attributes['instanceKey'] = $instanceKey;
    }

    /**
     * Return instance specific part.
     * @return string
     */
    public function getInstanceKey()
    {
        return $this->attributes['instanceKey'];
    }
}
