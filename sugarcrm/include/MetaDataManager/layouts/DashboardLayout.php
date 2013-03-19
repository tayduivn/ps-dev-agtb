<?php
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
     * @param array $params
     */
    public function __construct($params)
    {
        $defaults = array("name" => "", "columns" => 1);
        $args = array_merge($defaults, $params);

        $this->layout["name"] = $args['name'];

        if(isset($args["columns"])) {
            if(is_array($args["columns"])) {
                foreach($args["columns"] as $column) {
                    array_push($this->layout["metadata"]["components"], array(
                        "rows" => array(),
                        "width" => isset($column["width"]) ? $column["width"] : 12 / count($args["columns"])
                    ));
                }
            } else {
                for($i = 0; $i < $args["columns"]; $i++) {
                    array_push($this->layout["metadata"]["components"], array(
                        "rows" => array(),
                        "width" => 12 / $args["columns"]
                    ));
                }
            }
        }
    }

    public function push($column, $components) {
        if(!isset($this->layout["metadata"]["components"][$column])) {
            return;
        }
        $row = count($this->layout["metadata"]["components"][$column]["rows"]);
        $this->layout["metadata"]["components"][$column]["rows"][$row] = array();
        foreach($components as $index => $component) {
            $component["width"] = 12 / count($components);
            array_push($this->layout["metadata"]["components"][$column]["rows"][$row], $component);
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
