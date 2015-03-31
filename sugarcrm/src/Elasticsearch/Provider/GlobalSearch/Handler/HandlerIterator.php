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

namespace Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler;

/**
 *
 * Handler filter iterator
 *
 */
class HandlerIterator extends \FilterIterator
{
    /**
     * @var string
     */
    protected $interface;

    /**
     * Ctor
     * @param HandlerCollection $collection
     * @param string $interface
     */
    public function __construct(\Iterator $collection, $interface = null)
    {
        $this->setInterface($interface);
        parent::__construct($collection);
    }

    /**
     * {@inheritdoc}
     */
    public function accept()
    {
        if (!empty($this->interface)) {
            return in_array($this->interface, class_implements(parent::current()));
        }

        return true;
    }

    /**
     * Set interface to filter by
     * @param string $interface
     */
    public function setInterface($interface = null)
    {
        if (empty($interface)) {
            $this->interface = null;
        } else {
            $this->interface = sprintf(
                'Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\%sHandlerInterface',
                $interface
            );
        }
    }
}
