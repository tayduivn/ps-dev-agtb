<?php

class SideBarLayout {

    protected $mainPane;
    protected $sidePane;
    protected $previewPane;
    protected $containers = array();
    protected $layout;
    protected $baseLayout;

    public function __construct() {
        $this->layout = MetaDataManager::getLayout('GenericLayout', array('sidebar', 'default'));
        $this->baseLayout = MetaDataManager::getLayout('GenericLayout', array('base'));;

        $this->mainPane = $this->containers['main'] = MetaDataManager::getLayout('GenericLayout', array('main-pane'));
        $this->sidePane = $this->containers['side'] = MetaDataManager::getLayout('GenericLayout', array('side-pane'));
        $this->previewPane = $this->containers['preview'] = MetaDataManager::getLayout('GenericLayout', array('preview-pane'));

        $this->mainPane->set("span", 8);
        $this->sidePane->set("span", 4);
    }

    public function push($section, $component) {
        if (isset($this->containers[$section])) {
            $this->containers[$section]->push($component);
        }
    }

    public function setSectionSpan($section, $span) {
        $this->containers[$section]->set("span", $span);
    }

    public function getLayout() {
        $this->push("preview", array("view" => "preview"));
        $this->layout->push(array("layout" => $this->mainPane->getLayout()));
        $this->layout->push(array("layout" => $this->sidePane->getLayout()));
        $this->layout->push(array("layout" => $this->previewPane->getLayout()));

        $this->baseLayout->push($this->layout->getLayout(true));
        return $this->baseLayout->getLayout();
    }
}
