<?php

class GenericLayout
{
    public $layout = array("components" => array());

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

    public function set($property, $value)
    {
        $this->layout[$property] = $value;
    }

    public function push($component)
    {
        $this->layout["components"][] = $component;
    }

    public function getLayout($wrap = false)
    {
        return ($wrap) ? array("layout" => $this->layout) : $this->layout;
    }
}
