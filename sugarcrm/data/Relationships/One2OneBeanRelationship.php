<?php

require_once("data/Relationships/One2MBeanRelationship.php");

class One2OneBeanRelationship extends One2MBeanRelationship
{

    public function __construct($def)
    {
        parent::__construct($def);
    }
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

    protected function updateLinks($lhs, $lhsLinkName, $rhs, $rhsLinkName)
    {
        //RHS and LHS only ever have one bean
        if (isset($lhs->$lhsLinkName))
            $lhs->$lhsLinkName->beans = array($rhs->id => $rhs);

        if (isset($rhs->$rhsLinkName))
            $rhs->$rhsLinkName->beans = array($lhs->id => $lhs);
    }

    public function getJoin($link, $params = array(), $return_array = false)
    {
        $linkIsLHS = $link->getSide() == REL_LHS;
        $startingTable = $link->getFocus()->table_name;
        $startingKey = $linkIsLHS ? $this->def['lhs_key'] : $this->def['rhs_key'];
        $targetTable = $linkIsLHS ? $this->def['rhs_table'] : $this->def['lhs_table'];
        $targetTableWithAlias = $targetTable;
        $targetKey = $linkIsLHS ? $this->def['rhs_key'] : $this->def['lhs_key'];
        $join_type= isset($params['join_type']) ? $params['join_type'] : ' INNER JOIN ';

        $join = '';

        //Set up any table aliases required
        if ( ! empty($params['join_table_alias']))
        {
            $targetTableWithAlias = $targetTable . " ". $params['join_table_alias'];
            $targetTable = $params['join_table_alias'];
        }

        //join the related module's table
        $join .= "$join_type $targetTableWithAlias ON $targetTable.$targetKey=$startingTable.$startingKey AND $targetTable.deleted=0\n"
        //Next add any role filters
               . $this->getRoleFilterForJoin();

		if($return_array){
			return array(
                'join' => $join,
                'type' => $this->type,
                'rel_key' => $targetKey,
                'join_tables' => array($targetTable),
                'where' => "",
                'select' => "$targetTable.id",
            );
		}
		return $join;
    }
}