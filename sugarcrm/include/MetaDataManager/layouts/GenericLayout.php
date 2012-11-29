<?php

class GenericLayout
{
    public $layout = array("components" => array());

    /**
     * Constructs a generic layout which can be used to group views together.
     * @param array $params
     */
    public function __construct($params)
    {
        $defaults = array("name" => null, "type" => "simple");
        $args = array_merge($defaults, $params);
        $this->layout["type"] = $args['type'];
        if ($args['name']) {
            $this->layout["name"] = $args['name'];
        }

        $this->set("span", 12);
    }

    /**
     * A generic property setter for layouts.
     * @param string $property
     * @param mixed  $value
     */
    public function set($property, $value)
    {
        $this->layout[$property] = $value;
    }

    /**
     * Add a component to the layout.
     * @param  array $component
     * @return void
     */
    public function push($component)
    {
        $this->layout["components"][] = $component;
    }

    /**
     * Return the metadata representation of the layout
     * @param boolean $wrap Determines whether to wrap the layout within
     * another layout
     * @return array
     */
    public function getLayout($wrap = false)
    {
        return ($wrap) ? array("layout" => $this->layout) : $this->layout;
    }
}
