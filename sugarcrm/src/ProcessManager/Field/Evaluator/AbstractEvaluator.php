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

namespace Sugarcrm\Sugarcrm\ProcessManager\Field\Evaluator;

/**
 * Field evaluator abstract class
 * @package ProcessManager
 */
abstract class AbstractEvaluator
{
    /**
     * SugarBean object
     * @var SugarBean
     */
    protected $bean;

    /**
     * The name of the field being evaluated
     * @var string
     */
    protected $name;

    /**
     * Data array used for various functions
     * @var array
     */
    protected $data;

    /**
     * Sets properties onto the evaluator object
     * @param SugarBean $bean The bean being used for evaluation
     * @param string $name Name of the field being evaluated
     * @param array $data Data used in various functions
     */
    public function init(\SugarBean $bean, $name, array $data)
    {
        $this->bean = $bean;
        $this->name = $name;
        $this->data = $data;
    }

    /**
     * Gets the current user object
     * @return User
     */
    protected function getCurrentUser()
    {
        global $current_user;
        return $current_user;
    }

    /**
     * Determines whether an object has the necessary setup to be checked
     * @return boolean
     */
    protected function isCheckable()
    {
        return isset($this->data[$this->name]) && isset($this->bean->{$this->name});
    }

    /**
     * Checks if a field exists on the bean to be checked
     * @return boolean
     */
    protected function hasProperty()
    {
        return property_exists($this->bean, $this->name);
    }
}
