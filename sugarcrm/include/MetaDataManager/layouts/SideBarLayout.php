<?php

class SideBarLayout
{
    protected $containers = array();
    protected $layout;
    protected $baseLayout;

    /**
     * Constructs a two-pane layout (typically one with content, and one with
     * widgets).
     */
    public function __construct()
    {
        $this->layout = MetaDataManager::getLayout('GenericLayout', array('name' => 'sidebar', 'type' => 'default'));
        $this->baseLayout = MetaDataManager::getLayout('GenericLayout', array('name' => 'base'));;

        $this->containers['main'] = MetaDataManager::getLayout('GenericLayout', array('name' => 'main-pane'));
        $this->containers['side'] = MetaDataManager::getLayout('GenericLayout', array('name' => 'side-pane'));
        $this->containers['dashboard'] = MetaDataManager::getLayout('GenericLayout', array('name' => 'dashboard-pane'));
        $this->containers['preview'] = MetaDataManager::getLayout('GenericLayout', array('name' => 'preview-pane'));

        $this->setSectionSpan('main', 8);
        $this->setSectionSpan('side', 4);
        $this->setSectionSpan('dashboard', 4);
        $this->setSectionSpan('preview', 8);
    }

    public function push($section, $component)
    {
        if (isset($this->containers[$section])) {
            $this->containers[$section]->push($component);
        }
    }

    public function setSectionSpan($section, $span)
    {
        $this->containers[$section]->set("span", $span);
    }

    public function getLayout()
    {
        foreach ($this->containers as $container) {
            $this->layout->push(array("layout" => $container->getLayout()));
        }

        $this->baseLayout->push($this->layout->getLayout(true));

        return $this->baseLayout->getLayout();
    }
}
