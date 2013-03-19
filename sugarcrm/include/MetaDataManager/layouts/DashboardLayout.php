<?php
/**
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

/**
 * Creates a layout for views that include the filter panel.
 *
 * This view is used mostly on list views for items.
 */
class DashboardLayout
{
    public $layout = array(
        "metadata" => array(
            "components" => array()
        )
    );

    /**
     * Constructs a generic layout which can be used to group views together.
     *
     * @param array $params
     */
    public function __construct($params)
    {
        $defaults = array("name" => "", "columns" => 1);
        $args = array_merge($defaults, $params);

        $this->layout["name"] = $args['name'];

        if (isset($args["columns"])) {
            if (is_array($args["columns"])) {
                foreach ($args["columns"] as $column) {
                    array_push(
                        $this->layout["metadata"]["components"],
                        array(
                            "rows" => array(),
                            "width" => isset($column["width"]) ? $column["width"] : 12 / count(
                                $args["columns"]
                            ),
                        )
                    );
                }
            } else {
                for ($i = 0; $i < $args["columns"]; $i++) {
                    array_push(
                        $this->layout["metadata"]["components"],
                        array(
                            "rows" => array(),
                            "width" => 12 / $args["columns"],
                        )
                    );
                }
            }
        }
    }

    public function push($column, $components)
    {
        if (!isset($this->layout["metadata"]["components"][$column])) {
            return;
        }
        $row = count($this->layout["metadata"]["components"][$column]["rows"]);
        $this->layout["metadata"]["components"][$column]["rows"][$row] = array();
        foreach ($components as $component) {
            $component["width"] = 12 / count($components);
            array_push(
                $this->layout["metadata"]["components"][$column]["rows"][$row],
                $component
            );
        }
    }

    /**
     * Returns metadata that renders the components for the tab layout.
     *
     * @return array
     */
    public function getLayout()
    {
        return $this->layout;
    }
}
