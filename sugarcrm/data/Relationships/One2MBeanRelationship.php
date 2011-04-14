<?php

require_once("data/Relationships/One2MRelationship.php");

/**
 * Represents a one to many relationship that is table based.
 */
class One2MBeanRelationship extends One2MRelationship
{
    //Type is read in sugarbean to determine query construction
    var $type = "one-to-many";

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
        $rhsLinkName = $this->rhsLink;

        //Since this is bean based, we know updating the RHS's field will overwrite any old value,
        //But we need to use delete to make sure custom logic is called correctly
        if ($rhs->load_relationship($rhsLinkName))
        {
            $oldLink = $rhs->$rhsLinkName;
            $prevRelated = $oldLink->getBeans(null);
            foreach($prevRelated as $oldLHS)
            {
                $this->remove($oldLHS, $rhs, false);
            }
        } else
        {
            echo ("Unable to load RHS module {$rhs->module_name} link $rhsLinkName\n");
        }

        //Now update the RHS bean's ID field
        $rhsID = $this->def['rhs_key'];
        $rhs->$rhsID = $lhs->id;
        foreach($additionalFields as $field => $val)
        {
            $rhs->$field = $val;
        }
        $rhs->save();

        $lhs->$lhsLinkName->beans[$rhs->id] = $rhs;
        $rhs->$rhsLinkName->beans = array($lhs->id => $lhs);

        $this->callAfterAdd($lhs, $rhs);
        $this->callAfterAdd($rhs, $lhs);
    }


    public function remove($lhs, $rhs, $save = true)
    {
        $rhsID = $this->def['rhs_key'];
        $rhs->$rhsID = '';

        if ($save)
            $rhs->save();

        $this->callAfterDelete($lhs, $rhs);
        $this->callAfterDelete($rhs, $lhs);
    }

    /**
     * @param  $link Link2 loads the relationship for this link.
     * @return void
     */
    public function load($link)
    {
        $relatedModule = $link->getSide() == REL_LHS ? $this->def['rhs_module'] : $this->def['lhs_module'];
        $rhsLinkName = $this->rhsLink;
        $beans = array();
        $rows = array();
        //The related bean ID is stored on the RHS table.
        //If the link is RHS, just grab it from the focus.
        if ($link->getSide() == REL_RHS)
        {
            $rhsID = $this->def['rhs_key'];
            $id = $link->getFocus()->$rhsID;
            if (!empty($id))
            {
                $beans[$id] = BeanFactory::getBean($relatedModule, $id);
            }
        }
        else //If the link is LHS, we need to query to get the full list and load all the beans.
        {
            $db = DBManagerFactory::getInstance();
            $query = $this->getQuery($link);
            if (empty($query))
            {
                echo ("query for {$this->name} was empty when loading from {$this->lhsLink}\n");
            }
            $result = $db->query($query);
            while ($row = $db->fetchByAssoc($result))
            {
                $id = $row['id'];
                $beans[$id] = BeanFactory::getBean($relatedModule, $id);
                $rows[$id] = $row;
            }
        }

        return array("beans" => $beans, "rows" => $rows);
    }

    public function getQuery($link, $return_as_array = false)
    {

        if ($link->getSide() == REL_RHS) {
            return false;
        }
        else
        {
            $lhsKey = $this->def['lhs_key'];
            if (!$return_as_array) {
                return "SELECT id FROM {$this->def['rhs_table']} WHERE {$this->def['rhs_key']} = '{$link->getFocus()->$lhsKey}' AND deleted=0";
            }
            else
            {
                return array(
                    'select' => "SELECT {$this->def['rhs_table']}.id",
                    'from' => "FROM {$this->def['rhs_table']}",
                    'where' => "WHERE {$this->def['rhs_table']}.{$this->def['rhs_key']} = '{$link->getFocus()->$lhsKey}' AND {$this->def['rhs_table']}.deleted=0",
                );
            }
        }
    }

    public function getJoin($link, $params = array(), $return_array = false)
    {
        $linkIsLHS = $link->getSide() == REL_LHS;
        $startingTable = (empty($params['left_join_table_alias']) ? $this->def['lhs_table'] : $params['left_join_table_alias']);
        if (!$linkIsLHS)
            $startingTable = (empty($params['right_join_table_alias']) ? $this->def['rhs_table'] : $params['right_join_table_alias']);
        $startingKey = $linkIsLHS ? $this->def['lhs_key'] : $this->def['rhs_key'];
        $targetTable = $linkIsLHS ? $this->def['rhs_table'] : $this->def['lhs_table'];
        $targetTableWithAlias = $targetTable;
        $targetKey = $linkIsLHS ? $this->def['rhs_key'] : $this->def['lhs_key'];
        $join_type= isset($params['join_type']) ? $params['join_type'] : ' INNER JOIN ';
        $join = '';

        //Set up any table aliases required
        if ( ! empty($params['join_table_alias']))
        {
            $targetTableWithAlias = $targetTable. " ".$params['join_table_alias'];
            $targetTable = $params['join_table_alias'];
        }

        //First join the relationship table
        $join .= "$join_type $targetTableWithAlias ON $startingTable.$startingKey=$targetTable.$targetKey AND $targetTable.deleted=0\n"
        //Next add any role filters
               . $this->getRoleFilterForJoin() . "\n";

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

    public function getSubpanelQuery($link, $params = array(), $return_array = false)
    {

        $linkIsLHS = $link->getSide() == REL_RHS;
        $startingTable = (empty($params['left_join_table_alias']) ? $this->def['lhs_table'] : $params['left_join_table_alias']);
        if (!$linkIsLHS)
            $startingTable = (empty($params['right_join_table_alias']) ? $this->def['rhs_table'] : $params['right_join_table_alias']);
        $startingKey = $linkIsLHS ? $this->def['lhs_key'] : $this->def['rhs_key'];
        $targetTable = $linkIsLHS ? $this->def['rhs_table'] : $this->def['lhs_table'];
        $targetKey = $linkIsLHS ? $this->def['rhs_key'] : $this->def['lhs_key'];
        $join_type= isset($params['join_type']) ? $params['join_type'] : ' INNER JOIN ';
        $query = '';

        $alias = empty($params['join_table_alias']) ? "{$link->name}_rel": $params['join_table_alias'];
        //Set up any table aliases required
        $targetTableWithAlias = "$targetTable $alias";
        $targetTable = $alias;

        $query .= "$join_type $targetTableWithAlias ON $startingTable.$startingKey=$targetTable.$targetKey AND $targetTable.deleted=0\n"
        //Next add any role filters
               . $this->getRoleFilterForJoin() . "\n";

		if($return_array){
			return array(
                'join' => $query,
                'type' => $this->type,
                'rel_key' => $targetKey,
                'join_tables' => array($targetTable),
                'where' => "WHERE $startingTable.$startingKey='{$link->focus->id}'",
                'select' => " ",
            );
		}
		return $query;

    }

    /**
     * @param  $lhs
     * @param  $rhs
     * @return bool
     */
    public function relationship_exists($lhs, $rhs)
    {

        return false;
    }

    public function getRelationshipTable()
    {
        return $this->def['table'];
    }
}