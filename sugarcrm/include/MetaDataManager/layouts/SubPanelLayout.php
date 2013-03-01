<?php

class SubPanelLayout {
    protected $layout;
    protected $baseLayout;

    public function __construct(array $subpanels = array())
    {
        $this->layout = MetaDataManager::getLayout("GenericLayout", array("type" => "subpanel"));
        $this->baseLayout = MetaDataManager::getLayout("GenericLayout", array("name" => "subpanel"));
        $this->buildPanels($subpanels);
        $this->layout->set("subpanelList", $subpanels);
    }

    public function push($subpanel)
    {
        $panel = MetaDataManager::getLayout("GenericLayout", array("type" => "panel", "name" => $subpanel["name"]));
        $panel->push(array("view" => "panel-top"));
        $panel->push(array("view" => "massupdate"));
        $panel->push(array("view" => "panel-list"));
        $panel->push(array("view" => "list-bottom"));
        $this->layout->push(array_merge(array("layout" => $panel->getLayout()), $subpanel));
    }

    public function getLayout()
    {
        $this->baseLayout->push($this->layout->getLayout(true));
        return $this->layout->getLayout();
    }

    protected function buildPanels($subpanels)
    {
        foreach ($subpanels as $lang_key => $link) {
            $this->push(array("name" => $lang_key, "context" => array("link" => $link)));
        }
    }
}
