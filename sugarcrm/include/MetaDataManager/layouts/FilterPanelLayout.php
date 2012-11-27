<?php

class FilterPanelLayout
{
    protected $defaultTab = array("name" => "Activity Stream", "toggles" => array("activitystream", "timeline", "calendar"));
    protected $defaultToggle = "activitystream";

    protected $tabMeta = array();
    protected $toggleMeta = array(); // Toggle buttons
    protected $layout;
    protected $baseLayout;

    public function __construct($opts = array())
    {
        $this->layout = MetaDataManager::getLayout('GenericLayout', array('name' => 'filterpanel', 'type' => 'filterpanel'));
        $this->baseLayout = MetaDataManager::getLayout('GenericLayout', array('name' => 'base'));

        // Add Activity Stream as default tab is not overridden.
        if (!isset($opts["override"])) {
            $this->setTab($this->defaultTab);
        }
    }

    public function setDefaultTab($tabName, $toggleName = null)
    {
        $this->layout->set("defaultTab", $tabName);

        if ($toggleName) {
            $this->setDefaultToggle($toggleName);
        }
    }

    public function setDefaultToggle($toggle)
    {
        $this->layout->set("defaultToggle", $toggle);
    }

    /**
     * @param {Array} $tab ['name' => 'TAB_NAME', 'toggles' => array('activitystream', 'list')]
     */
    public function setTab($tab)
    {
        if (!isset($tab["toggles"])) {
            $tab["toggles"] = $this->defaultToggle;
        }

        $this->tabMeta[] = $tab;
    }

    protected function extractToggles()
    {
        foreach ($this->tabMeta as $tab) {
            foreach ($tab["toggles"] as $toggle) {
                if (!in_array($this->toggleMeta, $tab['toggles'])) {
                    $this->toggleMeta[] = $toggle;
                }
            }
        }
    }

    public function getLayout()
    {
        $this->extractToggles();
        $this->setDefaultTab($this->defaultTab);

        // Load all the views for the toggles
        foreach ($this->toggleMeta as $toggle) {
            $this->layout->push(array("view" => $toggle));
        }

        $this->layout->set("tabs", $this->tabMeta);

        $this->baseLayout->push($this->layout->getLayout(true));

        return $this->baseLayout->getLayout();
    }
}
