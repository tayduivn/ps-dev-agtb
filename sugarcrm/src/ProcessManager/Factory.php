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
     * Root of all files that shipped with PMSE
     * @var string
     */
    protected static $pmseBasePath = 'modules/';

    /**
     * PMSE Paths off of the pmseBasePath where files live
     * @var array
     */
    protected static $pmsePaths = [
        'pmse_Business_Rules/',
        'pmse_Business_Rules/clients/base/api/',
        'pmse_Emails_Templates/',
        'pmse_Emails_Templates/clients/base/api/',
        'pmse_Inbox/clients/base/api/',
        'pmse_Inbox/engine/',
        'pmse_Inbox/engine/parser/',
        'pmse_Inbox/engine/PMSEElements/',
        'pmse_Inbox/engine/PMSEHandlers/',
        'pmse_Inbox/engine/PMSEPreProcessor/',
        'pmse_Inbox/engine/wrappers/',
        'pmse_Project/clients/base/api/',
        'pmse_Project/clients/base/api/wrappers/',
        'pmse_Project/clients/base/api/wrappers/PMSEObservers/'
    ];

    /**
     * Gets an array of assembled paths for include.
     * @return array
     */
    protected static function getPMSEPaths()
    {
        // Set a default return
        $paths = [];

        // Loop and set now
        foreach (self::$pmsePaths as $path) {
            // Assumption here: basePaths are properly suffixed with /
            $paths[] = static::$pmseBasePath . $path;
        }

        return $paths;
    }


    /**
     * Gets a Process Author object. This expects a mapping of file basename to
     * class name. This method allows for extending a Process Author class using
     * the 'Custom' prefix on a classname OR overriding a Process Author class
     * completely by reusing the name of the class/file. Priority is given to
     * Custom classes before overrides.
     *
     * @param string $name Name of the element to get the object for
     * @return PMSE* Object
     */
    public static function getPMSEObject($name)
    {
        // Default variable for our classname
        $class = '';

        // Handle verification of the name being requested
        if (empty($name)) {
            $msg = 'Cannot load an unnamed PMSE Object';
            $exception = static::getException('Runtime', $msg);
            throw $exception;
        }

        // Get the paths to traverse
        $paths = self::getPMSEPaths();

        // First check for Custom classes of the type Custom$name
        foreach ($paths as $path) {
            $custom = 'Custom' . $name;
            if (\SugarAutoLoader::requireWithCustom("custom/$path{$custom}.php") !== false) {
                // Set our class name and move on
                $class = $custom;

                // Stop looking when we find something
                break;
            }
        }

        // Next check for PMSE standard / overridden classes
        if (empty($class)) {
            foreach ($paths as $path) {
                if (\SugarAutoLoader::requireWithCustom("$path{$name}.php") !== false) {
                    // Set it and forget it
                    $class = $name;

                    // Again, stop searching if we find something
                    break;
                }
            }
        }

        // Validate our return before sending anything back
        if (empty($class)) {
            $msg = "Unable to find/load a PMSE class named $name";
            $exception = static::getException('Runtime', $msg);
            throw $exception;
        }

        // Get new object. Argument passing will take place in other methods.
        return new $class;
    }

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

        // Handle the exception for this case
        $msg = "Could not instantiate a Process Manager $name Element object.";
        $exception = static::getException('Runtime', $msg);
        throw $exception;
    }

    /**
     * Gets a Process Manager exception object
     * @param string $type The type of object to get
     * @param string $message The exception message to throw and log
     * @return ExceptionInterface
     */
    public static function getException($type = '', $message = '')
    {
        // Since we need to log our exceptions, let's get the logger
        require_once 'modules/pmse_Inbox/engine/PMSELogger.php';

        // Type will determine what class to load
        if ($type === '') {
            $class = '\\Exception';
        } else {
            $class = 'Sugarcrm\\Sugarcrm\\ProcessManager\\Exception\\' . ucfirst(strtolower($type)) . 'Exception';
        }

        // Handle the message now
        if (empty($message)) {
            $message = 'An unknown Process Manager exception had occurred';
        }

        // Create the exception object
        $obj = new $class($message);

        // Log it
        \PMSELogger::getInstance()->alert($message);

        // Return it
        return $obj;
    }
}
