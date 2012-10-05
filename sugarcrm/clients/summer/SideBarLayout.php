<?php

require_once('clients/summer/GenericLayout.php');

class SideBarLayout {

    protected $mainPane;
    protected $sidePane;
    protected $previewPane;
    protected $containers = array();
    protected $layout;
    protected $baseLayout;

    public function __construct() {
        $this->layout = new GenericLayout("sidebar", "default");
        $this->baseLayout = new GenericLayout("base");

        $this->mainPane = $this->containers['main'] = new GenericLayout("main-pane");
        $this->sidePane = $this->containers['side'] = new GenericLayout("side-pane");
        $this->previewPane = $this->containers['preview'] = new GenericLayout("preview-pane");

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
