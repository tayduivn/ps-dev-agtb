<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
*The contents of this file are subject to the SugarCRM Professional End User License Agreement
*("License") which can be viewed at http://www.sugarcrm.com/EULA.
*By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
*not use this file except in compliance with the License. Under the terms of the license, You
*shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
*otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
*service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
*of a third party.  Use of the Software may be subject to applicable fees and any use of the
*Software without first paying applicable fees is strictly prohibited.  You do not have the
*right to remove SugarCRM copyrights from the source code or user interface.
* All copies of the Covered Code must include on each user interface screen:
* (i) the "Powered by SugarCRM" logo and
* (ii) the SugarCRM copyright notice
* in the same form as they appear in the distribution.  See full license for requirements.
*Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
*to the License for the specific language governing these rights and limitations under the License.
*Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
********************************************************************************/

require_once("data/Relationships/M2MRelationship.php");

/**
 * Represents a one to many relationship that is table based.
 */
class One2MRelationship extends M2MRelationship
{

    public function __construct($def)
    {
        global $dictionary;

        $this->def = $def;
        $this->name = $def['name'];

        $this->selfReferencing = $def['lhs_module'] == $def['rhs_module'];
        $lhsModule = $def['lhs_module'];
        $rhsModule = $def['rhs_module'];

        if ($this->selfReferencing)
        {
            $links = VardefManager::getLinkFieldForRelationship(
                $lhsModule, BeanFactory::getBeanName($lhsModule), $this->name
            );
            if (empty($links))
            {
                $GLOBALS['log']->fatal("No Links found for relationship {$this->name}");
            }
            if (!isset($links[0])) //Only one link for a self referencing relationship, this is BAAAD
                $this->lhsLinkDef = $this->rhsLinkDef = $links;
            else
            {
                if ((!empty($links[0]['side']) && $links[0]['side'] == "right")
                        || (!empty($links[0]['link_type']) && $links[0]['link_type'] == "one"))
                {
                    //$links[0] is the RHS
                    $this->lhsLinkDef = $links[1];
                    $this->rhsLinkDef = $links[0];
                } else
                {
                    //$links[0] is the LHS
                    $this->lhsLinkDef = $links[0];
                    $this->rhsLinkDef = $links[1];
                }
            }
        } else
        {
            $this->lhsLinkDef = VardefManager::getLinkFieldForRelationship(
                $lhsModule, BeanFactory::getBeanName($lhsModule), $this->name
            );
            $this->rhsLinkDef = VardefManager::getLinkFieldForRelationship(
                $rhsModule, BeanFactory::getBeanName($rhsModule), $this->name
            );
            if (!isset($this->lhsLinkDef['name']) && isset($this->lhsLinkDef[0]))
            {
              $this->lhsLinkDef = $this->lhsLinkDef[0];
            }
            if (!isset($this->rhsLinkDef['name']) && isset($this->rhsLinkDef[0])) {
                $this->rhsLinkDef = $this->rhsLinkDef[0];
            }
        }
        $this->lhsLink = $this->lhsLinkDef['name'];
        $this->rhsLink = $this->rhsLinkDef['name'];
    }

    /**
     * @param  $lhs SugarBean left side bean to add to the relationship.
     * @param  $rhs SugarBean right side bean to add to the relationship.
     * @param  $additionalFields key=>value pairs of fields to save on the relationship
     * @return boolean true if successful
     */
    public function add($lhs, $rhs, $additionalFields = array())
    {
        $dataToInsert = $this->getRowToInsert($lhs, $rhs, $additionalFields);
        //If the current data matches the existing data, don't do anything
        if (!$this->checkExisting($dataToInsert))
        {
            $rhsLinkName = $this->rhsLink;
            //In a one to many, any existing links from the many (right) side must be removed first
            $rhs->load_relationship($rhsLinkName);
            $this->removeAll($rhs->$rhsLinkName);
            parent::add($lhs, $rhs, $additionalFields);
        }
    }
}