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

require_once 'data/Link2.php';

/**
 * Function-driven link class
 */
class FunctionLink extends Link2
{
    public function __construct($linkName, $bean, $linkDef = false)
    {
        $this->focus = $bean;
        $this->name = $linkName;
        $this->db = DBManagerFactory::getInstance();
        if (empty($linkDef)) {
            $this->def = $bean->field_defs[$linkName];
        } else {
            $this->def = $linkDef;
        }
        if (empty($this->def['link_function'])) {
            $GLOBALS['log']->fatal("Bad link: no function definition for $linkName");
            $this->def['link_function'] = '';
            return;
        }
        if (!empty($this->def['relationship'])) {
            $this->relationship = SugarRelationshipFactory::getInstance()->getRelationship($this->def['relationship']);
            $this->relationship_fields = (!empty($this->def['rel_fields']))?$this->def['rel_fields']: array();
        }
    }

    /**
     * @see Link2::getSide()
     */
    public function getSide()
    {
        return REL_LHS;
    }

    /**
     * @see Link2::isParentRelationship()
     */
    public function isParentRelationship()
    {
        return false;
    }

    /**
     * Returns false if no relationship was found for this link
     * @return bool
     */
    public function loadedSuccesfully()
    {
        if (empty($this->def['relationship'])) {
            // if we didn't ask for any rel, then we're ok here
            return true;
        }
        return parent::loadedSuccesfully();
    }

    /**
     * @see Link2::getRelatedModuleName()
     */
    public function getRelatedModuleName()
    {
        if (!empty($this->def['module'])) {
            // allow prescribed module if we have no associated relationship
            return $this->def['module'];
        }
        return parent::getRelatedModuleName();
    }

    /**
     * (non-PHPdoc)
     * @see Link2::getQuery()
     */
    public function getQuery($params = array())
    {
        $params['return_as_array'] = true;
        $result = $this->getSubpanelQuery($params, true);
        if (empty($result)) {
            return $result;
        }
        $result['select'] = "SELECT {$this->getRelatedTableName()}.id ";
        if (empty($params['return_as_array'])) {
            $sql = $result['select'].$result['from'];
            if (!empty($result['join'])) {
                $sql .= $result['join'];
            }
            if (!empty($result['where'])) {
                $sql .= $result['where'];
            }
            return $sql;
        }
        return $result;
    }

    /**
     * (non-PHPdoc)
     * @see Link2::getSubpanelQuery()
     */
    public function getSubpanelQuery($params = array(), $return_array = false)
    {
        $select_array = !empty($this->def['generate_select']);
        if (!empty($params['return_as_array'])) {
            $return_array = true;
        }
        if (!$select_array && $return_array) {
            $GLOBALS['log']->fatal("Inconsistent getSubpanelQuery call for {$this->name}: return is $return_array, select is $select_array");
            return false;
        }
        $result = $this->callLinkFunction();
        if (!$return_array && $select_array) {
            $sql = $result['select'].$result['from'];
            if (!empty($result['join'])) {
                $sql .= $result['join'];
            }
            if (!empty($result['where'])) {
                $sql .= $result['where'];
            }
            return $sql;
        }
        return $result;
    }

    /**
     * (non-PHPdoc)
     * @see Link2::buildJoinSugarQuery()
     * @param SugarQuery $sugar_query
     */
    public function buildJoinSugarQuery($sugar_query, $options = array())
    {
        $query = $this->getSubpanelQuery();
        $joinType = !empty($options['joinType'])?$options['joinType']:"INNER";

        $sql = "$joinType JOIN ($query) {$options['joinTableAlias']}
            ON {$options['joinTableAlias']}.id = {$this->getRelatedTableName()}.id";

        return $sugar_query->joinRaw($sql, array('alias' => $options['joinTableAlias'], 'joinType' => $options['joinType']));
    }

    /**
     * Get all beans from link
     * @see Link2::query()
     */
    public function query($params)
    {
        // Not implemented currently
        return false;
    }

    /**
     * Return query that always fails
     * @return string
     */
    protected function failQuery($parameters)
    {
        if (!empty($parameters['generate_select'])) {
            return array(
                'select' => 'SELECT NULL id',
                'from' => " FROM ".$this->getRelatedTableName(),
                'where' => ' WHERE 0=1',
                'join' => ''
            );
        } else {
            return "SELECT NULL id FROM {$this->getRelatedTableName()} WHERE 0=1";
        }
    }

    /**
     * Call a user function
     * @param callable $function_name
     * @param array $parameters
     * @return mixed
     */
    protected function callUserFunction($function_name, $parameters)
    {
        if (!is_callable($function_name, false, $callable_name)) {
            $GLOBALS['log']->fatal("Can not call function $callable_name for link {$this->name}");
            return $this->failQuery($parameters);
        }
        //call function from required file
        return call_user_func($function_name, $parameters);
    }

    /**
     * Call the function associated with the link
     */
    protected function callLinkFunction()
    {
        if (!empty($this->def['function_parameters'])) {
            $parameters = $this->def['function_parameters'];
        } else {
            $parameters = array();
        }
        if (empty($this->def['link_function'])) {
            return $this->failQuery($parameters);
        }
        $function_name = $this->def['link_function'];
        $parameters['bean'] = $this->focus;
        if (!empty($parameters) && is_array($parameters) && isset($parameters['import_function_file'])) {
            //if the import file function is set, then import the file to call the custom function from
            if (!is_callable($function_name)) {
            //this call may happen multiple times, so only require if function does not exist
                require_once $parameters['import_function_file'];
            }

            return $this->callUserFunction($function_name, $parameters);
        }

        return $this->callUserFunction(array($this->focus, $function_name), $parameters);
    }
}
