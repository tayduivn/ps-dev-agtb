<?php

class FilterPanelLayout {
    protected $defaultTab = array("name" => "Activity Stream", "toggles" => array("activitystream", "timeline", "calendar"));

    protected $tabMeta = array();
    protected $toggleMeta = array(); // Toggle buttons
    protected $layout;
    protected $baseLayout;

    public function __construct($opts) {
        $this->layout = MetaDataManager::getLayout('GenericLayout', array('name' => 'filterpanel', 'type' => 'filterpanel'));
        $this->baseLayout = MetaDataManager::getLayout('GenericLayout', array('name' => 'base'));

        if (!isset($opts["override"])) {
            $this->setTab($this->defaultTab);
            $this->setDefaultTab($this->defaultTab);
        }
    }

    public function setDefaultTab($tabName, $toggleName = null) {
        $this->layout->set("defaultTab", $tabName);

        if ($toggleName) {
            $this->layout->set("defaultToggle", $toggleName);
        }
    }

    /**
     * @param {Array} $tab ['name' => 'TAB_NAME', 'toggles' => array('activitystream', 'list')]
     */
    public function setTab($tab) {
        $this->tabMeta[] = $tab;
    }

    protected function extractToggles() {
        foreach ($this->tabMeta as $tab) {
            foreach ($tab["toggles"] as $toggle) {
                if (!in_array($this->toggleMeta, $tab['toggles'])) {
                    $this->toggleMeta[] = $toggle;
                }
            }
        }
    }

    public function getLayout() {
        $this->extractToggles();

        // Load all the views for the toggles
        foreach ($this->toggleMeta as $toggle) {
            $this->layout->push(array("view" => $toggle));
        }

        $this->layout->set("tabs", $this->tabMeta);

        $this->baseLayout->push($this->layout->getLayout(true));
        return $this->baseLayout->getLayout();
    }
}
