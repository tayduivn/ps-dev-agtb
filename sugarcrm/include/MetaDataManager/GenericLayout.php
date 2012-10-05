<?php

class GenericLayout {
    public $layout = array("components" => array());

    public function __construct($name = null, $type = "simple") {
        $this->layout["type"] = $type;
        if ($name) {
            $this->layout["name"] = $name;
        }

        $this->set("span", 12);
    }

    public function set($property, $value) {
        $this->layout[$property] = $value;
    }

    public function push($component) {
            $this->layout["components"][] = $component;
    }

    public function getLayout($wrap = false) {
        return ($wrap) ? array("layout" => $this->layout) : $this->layout;
    }
}