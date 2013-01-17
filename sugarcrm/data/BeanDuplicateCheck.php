<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

/**
 * Bean duplicate check manager
 * @api
 */
class BeanDuplicateCheck
{
    /**
     * Strategy for performing dupe check for this bean
     * @var DuplicateCheckStrategy
     */
    protected $strategy = false;

    /**
     * @param SugarBean $bean
     * @param array $metadata
     */
    public function __construct($bean, $metadata)
    {
        $dupeCheckClass = $this->determineStrategy($metadata, $bean->module_name);
        $this->setStrategy($dupeCheckClass, $bean, $metadata);
    }

    /**
     * Ask the strategy for the possible duplicates
     *
     * @return array
     */
    public function findDuplicates()
    {
        if ($this->strategy) {
            return $this->strategy->findDuplicates();
        } else {
            return null;
        }
    }

    /**
     * Determine the name of the strategy to construct
     *
     * @param array  $metadata
     * @param string $moduleName
     * @return bool|string  false=Not a valid strategy; string=Class name of the strategy
     */
    protected function determineStrategy($metadata, $moduleName)
    {
        $dupeCheckClass = false;
        $metadataCount  = count($metadata);

        if ($metadataCount === 0) {
            $GLOBALS["log"]->info("No DuplicateCheckStrategy exists for the {$moduleName} module");
        } elseif ($metadataCount !== 1) {
            //force only one strategy
            $GLOBALS["log"]->warn("More than one DuplicateCheckStrategy exists for the {$moduleName} module");
        } else {
            reset($metadata);
            $dupeCheckClass = key($metadata);
        }

        return $dupeCheckClass;
    }

    /**
     * Set the strategy to an instance of the strategy class, but only if it's valid
     *
     * @param bool|string $strategyName
     * @param SugarBean   $bean
     * @param array $metadata
     */
    protected function setStrategy($strategyName, $bean, $metadata)
    {
        if (!empty($strategyName)) {
            if (!class_exists($strategyName)) {
                $GLOBALS["log"]->warn("The DuplicateCheckStrategy named '{$strategyName}' does not exist");
            } else {
                $this->strategy = new $strategyName($bean, $metadata[$strategyName]);
            }
        }
    }

    /**
     * Return the selected strategy. This is specifically used for unit tests.
     *
     * @return bool|DuplicateCheckStrategy  false=No strategy; object=Instance of the strategy
     */
    public function getStrategy()
    {
        return $this->strategy;
    }
}
