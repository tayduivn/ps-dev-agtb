<?php

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
        $rhsLinkName = $this->rhsLink;
        //In a one to many, any existing links from the many (right) side must be removed first
        $rhs->load_relationship($rhsLinkName);
        $this->removeAll($rhs->$rhsLinkName);

        parent::add($lhs, $rhs, $additionalFields);
    }
}