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
 * @api
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
                $lhsModule, BeanFactory::getObjectName($lhsModule), $this->name
            );
            if (empty($links))
            {
                $GLOBALS['log']->fatal("No Links found for relationship {$this->name}");
            }
            else {
                if (!is_array($links)) //Only one link for a self referencing relationship, this is very bad.
                {
                    $this->lhsLinkDef = $this->rhsLinkDef = $links;
                }
                else if (!empty($links[0]) && !empty($links[1]))
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
            }
        } else
        {
            $this->lhsLinkDef = VardefManager::getLinkFieldForRelationship(
                $lhsModule, BeanFactory::getObjectName($lhsModule), $this->name
            );
            $this->rhsLinkDef = VardefManager::getLinkFieldForRelationship(
                $rhsModule, BeanFactory::getObjectName($rhsModule), $this->name
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

    protected function linkIsLHS($link) {
        return ($link->getSide() == REL_LHS && !$this->selfReferencing) ||
               ($link->getSide() == REL_RHS && $this->selfReferencing);
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
			// Pre-load the RHS relationship, which is used later in the add() function and expects a Bean
			// and we also use it for clearing relationships in case of non self-referencing O2M relations
			// (should be preloaded because when using the relate_to field for updating/saving relationships,
			// only the bean id is loaded into $rhs->$rhsLinkName)
			$rhsLinkName = $this->rhsLink;
			$rhs->load_relationship($rhsLinkName);
        	
			// If it's a One2Many self-referencing relationship
        	// the positions of the default One (LHS) and Many (RHS) are swaped
        	// so we should clear the links from the many (left) side
        	if ($this->selfReferencing) {
        		// Load right hand side relationship name
	            $linkName = $this->rhsLink;
	            // Load the relationship into the left hand side bean
	            $lhs->load_relationship($linkName);
	            
	            // Pick the loaded link
	            $link = $lhs->$linkName;
	            // Get many (LHS) side bean
	            $focus = $link->getFocus();
	            // Get relations
	        	$related = $link->getBeans();
	        	
        		// Clear the relations from many side bean
	        	foreach($related as $relBean) {
	        		$this->remove($focus, $relBean);
	        	}
            } else { // For non self-referencing, remove all the relationships from the many (RHS) side
            	$this->removeAll($rhs->$rhsLinkName);
            }
            
            // Add relationship
            parent::add($lhs, $rhs, $additionalFields);
        }
    }

    /**
     * Just overriding the function from M2M to prevent it from occuring
     * 
     * The logic for dealing with adding self-referencing one-to-many relations is in the add() method
     */
    protected function addSelfReferencing($lhs, $rhs, $additionalFields = array())
    {
        //No-op on One2M.
    }

    /**
     * Just overriding the function from M2M to prevent it from occuring
     */
    protected function removeSelfReferencing($lhs, $rhs, $additionalFields = array())
    {
        //No-op on One2M.
    }
}
