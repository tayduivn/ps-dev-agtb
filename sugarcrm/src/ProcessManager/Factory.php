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

/**
 * Declare the namespace for this class
 */
namespace Sugarcrm\Sugarcrm\ProcessManager;

/**
 * Load up the relevant Process Manager namespaces needed for this class
 */
use Sugarcrm\Sugarcrm\ProcessManager\Exception as PME;

/**
 * Factory class for handling creation of objects for Process Management
 * @package ProcessManager
 */
class Factory
{
    /**
     * Local stack of cached objects
     * @var array
     */
    protected static $cache = [];

    /**
     * Mapping of field types to evaluator objects types
     * @var array
     */
    protected static $fieldEvaluatorTypeMap = [
        'date' => 'Datetime',
        'time' => 'Datetime',
        'datetimecombo' => 'Datetime',
        'datetime' => 'Datetime',
    ];

    /**
     * Gets the correct field evaluator type for building an field evaluator
     * object.
     * @param array $def Field def for this field
     * @return string
     */
    protected static function getFieldEvaluatorType($def)
    {
        // Get the proper type for this field from the vardef
        $type = isset($def['custom_type']) ? $def['custom_type'] : $def['type'];
        if (isset(static::$fieldEvaluatorTypeMap[$type])) {
            return static::$fieldEvaluatorTypeMap[$type];
        }

        return ucfirst(strtolower($type));
    }

    /**
     * Gets a ProcessManager\EvaluatorInterface object
     * @param array $def Field def for this field
     * @param boolean $new Flag that tells this method whether to get a new object
     * @return ProcessManager\EvaluatorInterface
     */
    public static function getFieldEvaluator(array $def, $new = false)
    {
        // Get the proper type for this field from the vardef
        $type = static::getFieldEvaluatorType($def);

        if (!isset(static::$cache['evaluators'][$type]) || $new === true) {
            // Get the field evaluator namespace root
            $nsRoot = 'Sugarcrm\\Sugarcrm\\ProcessManager\\Field\\Evaluator\\';

            // Get the class name for this field type, getting the custom class if
            // found
            $class = \SugarAutoLoader::customClass($nsRoot . $type);

            // Set the base class name, getting the custom class if found
            $base = \SugarAutoLoader::customClass($nsRoot . 'Base');

            // Set the class name to load based on availability of the class. If
            // the type class exists, use it, otherwise fallback to the failsafe
            // base class name.
            $load = class_exists($class) ? $class : $base;

            // Load what we have now
            static::$cache['evaluators'][$type] = new $load;
        }

        return static::$cache['evaluators'][$type];
    }

    /**
     * Gets a Process Element object. This expects a mapping of file basename to
     * class name. This method allows for extending a PMSEElement class using
     * the 'Custom' prefix on a classname OR overriding a PMSEElement class
     * completely by reusing the name of the class/file. Priority is given to
     * Custom classes before overrides.
     *
     * Eventually this will return a ProcessManager\Element object, but until
     * all PMSE classes are moved out to ProcessManager classes, this will have
     * to do.
     *
     * @todo  Create a PMSERunnable and have all PMSEElement object
     * implement it.
     * @param string $name Name of the element to get the object for
     * @return PMSERunnable
     */
    public static function getElement($name = '')
    {
        // Start with the path
        $path = 'modules/pmse_Inbox/engine/PMSEElements/';

        // Default element class name
        if (empty($name)) {
            $name = 'PMSEElement';
        }

        // Default return value
        $return = null;

        // Create a Custom class name
        $custom = 'Custom' . $name;

        // This checks for Custom classes that will likely extends a base class
        if (\SugarAutoLoader::requireWithCustom("custom/$path{$custom}.php") !== false) {
            $return = new $custom;
        } elseif (\SugarAutoLoader::requireWithCustom("$path{$name}.php") !== false) {
            // This checks for custom classes that override a base class
            $return = new $name;
        }

        // Before returning, we should validate that the object is an instance
        // of PMSEElementInterface
        if ($return instanceof \PMSERunnable) {
            return $return;
        }

        $msg = "Could not instantiate a Process Manager $name Element object.";
        throw new PME\RuntimeException($msg);
    }
}
