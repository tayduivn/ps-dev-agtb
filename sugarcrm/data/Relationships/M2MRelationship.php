<?php

require_once("data/Relationships/SugarRelationship.php");

/**
 * Represents a many to many relationship that is table based.
 */
class M2MRelationship extends SugarRelationship
{
    var $type = "many-to-many";

    public function __construct($def)
    {
        global $dictionary;

        $this->def = $def;
        $this->name = $def['name'];

        $lhsModule = $def['lhs_module'];
        $this->lhsLinkDef = VardefManager::getLinkFieldForRelationship(
            $lhsModule, BeanFactory::getBeanName($lhsModule), $this->name
        );
        $this->lhsLink = $this->lhsLinkDef['name'];

        $rhsModule = $def['rhs_module'];
        $this->rhsLinkDef = VardefManager::getLinkFieldForRelationship(
            $rhsModule, BeanFactory::getBeanName($rhsModule), $this->name
        );
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
        $lhsLinkName = $this->lhsLink;
        $rhsLinkName = $this->rhsLink;
        $lhsClass = get_class($lhs);
        $rhsClass = get_class($rhs);
        if (empty($lhs->$lhsLinkName) && !$lhs->load_relationship($lhsLinkName))
        {
            $GLOBALS['log']->fatal("could not load LHS $lhsLinkName in $lhsClass");
            return false;
        }
        if (empty($rhs->$rhsLinkName) && !$rhs->load_relationship($rhsLinkName))
        {
            $GLOBALS['log']->fatal("could not load RHS $rhsLinkName in $rhsClass");
            return false;
        }

        //Many to many has no additional logic, so just add a new row to the table and notify the beans.
        $dataToInsert = array(
            "id" => create_guid(),
            $this->def['join_key_lhs'] => $lhs->id,
            $this->def['join_key_rhs'] => $rhs->id,
            'date_modified' => TimeDate::getInstance()->getNow()->asDb(),
            'deleted' => 0,
        );
        $dataToInsert = array_merge($dataToInsert, $additionalFields);

        $this->addRow($dataToInsert);

        $lhs->$lhsLinkName->load();
        $rhs->$rhsLinkName->load();

        $this->callAfterAdd($lhs, $rhs);
        $this->callAfterAdd($rhs, $lhs);
    }


    public function remove($lhs, $rhs)
    {
        $lhsLinkName = $this->lhsLink;
        $rhsLinkName = $this->rhsLink;

        if (empty($lhs->$lhsLinkName) && !$lhs->load_relationship($lhsLinkName))
        {
            $GLOBALS['log']->fatal("could not load LHS $lhsLinkName");
            return false;
        }
        if (empty($rhs->$rhsLinkName) && !$rhs->load_relationship($rhsLinkName))
        {
            $GLOBALS['log']->fatal("could not load RHS $rhsLinkName");
            return false;
        }

        $dataToRemove = array(
            $this->def['join_key_lhs'] => $lhs->id,
            $this->def['join_key_rhs'] => $rhs->id
        );

        $this->removeRow($dataToRemove);

        $lhs->$lhsLinkName->load();
        $rhs->$rhsLinkName->load();

        $this->callAfterDelete($lhs, $rhs);
        $this->callAfterDelete($rhs, $lhs);
    }

    /**
     * @param  $link Link2 loads the relationship for this link.
     * @return void
     */
    public function load($link)
    {
        $db = DBManagerFactory::getInstance();
        $query = $this->getQuery($link);
        $result = $db->query($query);
        $beans = Array();
        $rows = Array();
        $relatedModule = $link->getSide() == REL_LHS ? $this->def['rhs_module'] : $this->def['lhs_module'];
        $idField = $link->getSide() == REL_LHS ? $this->def['join_key_rhs'] : $this->def['join_key_lhs'];
        while ($row = $db->fetchByAssoc($result))
        {
            $id = $row[$idField];
            $beans[$id] = BeanFactory::getBean($relatedModule, $id);
            $rows[$id] = $row;
        }
        return array("beans" => $beans, "rows" => $rows);
    }

    public function getQuery($link, $params = array())
    {
        if ($link->getSide() == REL_LHS) {
            $knownKey = $this->def['join_key_lhs'];
            $targetKey = $this->def['join_key_rhs'];
        }
        else
        {
            $knownKey = $this->def['join_key_rhs'];
            $targetKey = $this->def['join_key_lhs'];
        }

        if (empty($params['return_as_array'])) {
            return "SELECT $targetKey FROM {$this->getRelationshipTable()} WHERE $knownKey = '{$link->getFocus()->id}' AND deleted=0";
        }
        else
        {
            return array(
                'select' => "SELECT $targetKey id",
                'from' => "FROM {$this->getRelationshipTable()}",
                'where' => "WHERE $knownKey = '{$link->getFocus()->id}' AND deleted=0",
            );
        }
    }

    public function getJoin($link, $params = array(), $return_array = false)
    {
        $linkIsLHS = $link->getSide() == REL_LHS;
        $startingTable = $link->getFocus()->table_name;
        $startingKey = $linkIsLHS ? $this->def['lhs_key'] : $this->def['rhs_key'];
        $startingJoinKey = $linkIsLHS ? $this->def['join_key_lhs'] : $this->def['join_key_rhs'];
        $joinTable = $this->getRelationshipTable();
        $joinTableWithAlias = $joinTable;
        $joinKey = $linkIsLHS ? $this->def['join_key_rhs'] : $this->def['join_key_lhs'];
        $targetTable = $linkIsLHS ? $this->def['rhs_table'] : $this->def['lhs_table'];
        $targetTableWithAlias = $targetTable;
        $targetKey = $linkIsLHS ? $this->def['rhs_key'] : $this->def['lhs_key'];
        $join_type= isset($params['join_type']) ? $params['join_type'] : ' INNER JOIN ';

        $join = '';

        //Set up any table aliases required
        if (!empty($params['join_table_link_alias']))
        {
            $joinTableWithAlias = $joinTable . " ". $params['join_table_link_alias'];
            $joinTable = $params['join_table_link_alias'];
        }
        if ( ! empty($params['join_table_alias']))
        {
            $targetTableWithAlias = $targetTable . " ". $params['join_table_alias'];
            $targetTable = $params['join_table_alias'];
        }

        //First join the relationship table
        $join .= "$join_type $joinTableWithAlias ON $startingTable.$startingKey=$joinTable.$startingJoinKey AND $joinTable.deleted=0\n"
        //Next add any role filters
               . $this->getRoleFilterForJoin() . "\n"
        //Then finally join the related module's table
               . "$join_type $targetTableWithAlias ON $targetTable.$targetKey=$joinTable.$joinKey AND $targetTable.deleted=0\n";

		if($return_array){
			return array(
                'join' => $join,
                'type' => $this->type,
                'rel_key' => $joinKey,
                'join_tables' => array($joinTable, $targetTable),
                'where' => "",
                'select' => "$targetTable.id",
            );
		}
		return $join;
    }

    /**
     * Similar to getQuery or Get join, except this time we are starting from the related table and
     * searching for items with id's matching the $link->focus->id
     * @param  $link
     * @param array $params
     * @param bool $return_array
     * @return void
     */
    public function getSubpanelQuery($link, $params = array(), $return_array = false)
    {
        $targetIsLHS = $link->getSide() == REL_RHS;
        $startingTable = $targetIsLHS ? $this->def['lhs_table'] : $this->def['rhs_table'];;
        $startingKey = $targetIsLHS ? $this->def['lhs_key'] : $this->def['rhs_key'];
        $startingJoinKey = $targetIsLHS ? $this->def['join_key_lhs'] : $this->def['join_key_rhs'];
        $joinTable = $this->getRelationshipTable();
        $joinTableWithAlias = $joinTable;
        $joinKey = $targetIsLHS ? $this->def['join_key_rhs'] : $this->def['join_key_lhs'];
        $targetKey = $targetIsLHS ? $this->def['rhs_key'] : $this->def['lhs_key'];
        $join_type= isset($params['join_type']) ? $params['join_type'] : ' INNER JOIN ';

        $query = '';

        //Set up any table aliases required
        if (!empty($params['join_table_link_alias']))
        {
            $joinTableWithAlias = $joinTable . " ". $params['join_table_link_alias'];
            $joinTable = $params['join_table_link_alias'];
        }

        //First join the relationship table
        $query .= "$join_type $joinTableWithAlias ON $startingTable.$startingKey=$joinTable.$startingJoinKey "
                . "AND $joinTable.$joinKey='{$link->getFocus()->$targetKey}' AND $joinTable.deleted=0\n"
        //Next add any role filters
               . $this->getRoleFilterForJoin() . "\n";

		if($return_array){
			return array(
                'join' => $query,
                'type' => $this->type,
                'rel_key' => $joinKey,
                'join_tables' => array($joinTable),
                'where' => "",
                'select' => " ",
            );
		}
		return $query;

    }

    protected function getRoleFilterForJoin()
    {
        $ret = "";
        if (!empty($this->relationship_role_column) && !$this->ignore_role_filter)
        {
            $ret .= " AND ".$this->getRelationshipTable().'.'.$this->relationship_role_column;
            //role column value.
            if (empty($this->relationship_role_column_value))
            {
                $ret.=' IS NULL';
            } else {
                $ret.= "='".$this->relationship_role_column_value."'";
            }
            $ret.= "\n";
        }
        return $ret;
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
        if (!empty($this->def['table']))
            return $this->def['table'];
        else if(!empty($this->def['join_table']))
            return $this->def['join_table'];
        else
           echo "WTF? " . print_r($this->def, true) . "\n";

        return false;
    }
}