<?php

class SubPanelLayout {
    protected $layout;
    protected $baseLayout;

    public function __construct() {
        $this->layout = MetaDataManager::getLayout("GenericLayout", array("type" => "subpanel"));
        $this->baseLayout = MetaDataManager::getLayout("GenericLayout", array("name" => "base"));
    }

    public function push($subpanel) {
        $panel = MetaDataManager::getLayout("GenericLayout", array("type" => "panel"));
    }
}