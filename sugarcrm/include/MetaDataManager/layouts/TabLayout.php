<?php

class TabLayout
{
    protected $layout,
        $baseLayout,
        $count = 0;

    public function __construct()
    {
        $this->layout = MetaDataManager::getLayout("GenericLayout", array("name" => "tabbed-layout", "type" => "tabbed-layout"));
        $this->baseLayout = MetaDataManager::getLayout("GenericLayout", array("name" => "base"));
    }

    /**
     * Adds a new tab.
     *
     * @param $tab array("name" => "Tab #1", "view" => "interactions")
     */
    public function push($tab)
    {
        $layout = MetaDataManager::getLayout("GenericLayout");

        if (isset($tab["name"])) {
            $layout->set("name", $tab["name"]);
        }

        if (isset($tab["view"])) {
            $layout->push(array("view" => $tab["view"]));
        }

        $this->layout->push($layout->getLayout(true));
        $this->count++;
    }

    /**
     * Returns metadata that renders the components for the tab layout.
     *
     * @return array
     */
    public function getLayout()
    {
        if ($this->count == 1) {
            $this->layout->set("onetab", true);
        }

        $this->baseLayout->push($this->layout->getLayout(true));

        return $this->baseLayout->getLayout();
    }
}
