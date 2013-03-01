<?php

class SubPanelLayout {
    protected $layout;
    protected $baseLayout;

    public function __construct() {
        $this->layout = MetaDataManager::getLayout("GenericLayout", array("type" => "subpanel"));
        $this->baseLayout = MetaDataManager::getLayout("GenericLayout", array("name" => "subpanel"));
    }

    public function push($subpanel) {
        $panel = MetaDataManager::getLayout("GenericLayout", array("type" => "panel", "name" => $subpanel["name"]));
        $panel->push(array("view" => "panel-top"));
        $panel->push(array("view" => "massupdate"));
        $panel->push(array("view" => "panel-list"));
        $panel->push(array("view" => "list-bottom"));
        $this->layout->push(array_merge(array("layout" => $panel->getLayout()), $subpanel));
    }

    public function getLayout() {
        $this->baseLayout->push($this->layout->getLayout(true));
        return $this->baseLayout->getLayout();
    }
}
