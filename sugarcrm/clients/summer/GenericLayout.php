<?php
class GenericLayout {
    public $layout = array("components" => array());

    public function __construct($type = "simple", $name = null) {
        $this->layout["type"] = $type;
        if ($name) {
            $this->layout["name"] = $name;
        }
    }

    public function set($property, $value) {
        $this->layout[$property] = $value;
    }

    public function insertComponents($components) {
        $this->layout["components"][] = $components;
    }

    public function push($component) {
        $this->insertComponents($component);
    }

    public function getLayout() {
        return $this->layout;
    }
}