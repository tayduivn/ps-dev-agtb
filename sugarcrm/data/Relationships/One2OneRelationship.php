<?php

require_once("data/Relationships/SugarRelationship.php");

class One2OneRelationship extends One2MRelationship
{

    /**
     * @param  $lhs SugarBean left side bean to add to the relationship.
     * @param  $rhs SugarBean right side bean to add to the relationship.
     * @param  $additionalFields key=>value pairs of fields to save on the relationship
     * @return boolean true if successful
     */
    public function add($lhs, $rhs, $additionalFields = array())
    {
        $lhsLinkName = $this->lhsLink;
        //In a one to one, any existing links from boths sides must be removed first.
        //one2Many will take care of the right side, so we'll do the left.
        $lhs->load_relationship($lhsLinkName);
        $this->removeAll($lhs->$lhsLinkName);

        parent::add($lhs, $rhs, $additionalFields);
    }
}