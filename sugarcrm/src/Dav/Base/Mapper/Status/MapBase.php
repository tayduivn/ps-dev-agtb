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

namespace Sugarcrm\Sugarcrm\Dav\Base\Mapper\Status;

abstract class MapBase
{
    /**
     * Status map
     * @var array
     */
    protected $map = array();

    /**
     * Default sugar value
     *
     * @var mixed
     */
    protected $defaultSugarValue = 'none';

    /**
     * Default CalDav value
     *
     * @var mixed
     */
    protected $defaultCalDavValue = null;

    /**
     * Return Bean status by CalDav.
     *
     * @param string $calDavStatus
     * @param string|null $beanStatus
     * @return mixed
     */
    public function getSugarValue($calDavStatus, $beanStatus = null)
    {
        if ($calDavStatus == $this->defaultCalDavValue && $beanStatus == $this->defaultSugarValue) {
            return $this->defaultSugarValue;
        }

        $find = array();
        if (isset($this->map[$calDavStatus])) {
            if ($beanStatus !== null && in_array($beanStatus, $this->map[$calDavStatus])) {
                return $beanStatus;
            } else {
                $find = $this->map[$calDavStatus];
            }
        }
        return $find ? $find[0] : $this->defaultSugarValue;
    }

    /**
     * Return CalDav status by Bean.
     *
     * @param string $beanStatus
     * @param string|null $calDavStatus
     * @return mixed
     */
    public function getCalDavValue($beanStatus, $calDavStatus = null)
    {
        if ($beanStatus == $this->defaultSugarValue && $calDavStatus == $this->defaultCalDavValue) {
            return $this->defaultCalDavValue;
        }

        $find = array();
        foreach ($this->map as $key => $value) {
            if (in_array($beanStatus, $value)) {
                if ($calDavStatus !== null && $key === $calDavStatus) {
                    return $key;
                }
                $find[] = $key;
            }
        }
        return $find ? $find[0] : $this->defaultCalDavValue;
    }
}
