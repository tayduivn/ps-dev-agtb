<?php
/**
 * Creates a layout for views that include the filter panel.
 *
 * This view is used mostly on list views for items.
 */
class FilterPanelLayout
{
    protected $defaultTab = array("name" => "Activity Stream", "toggles" => array("activitystream", "timeline", "calendar"));
    protected $defaultToggle = "activitystream";

    protected $tabMeta = array();
    protected $toggleMeta = array(); // Toggle buttons
    protected $layout;
    protected $baseLayout;

    /**
     * Constructor for FilterPanel Layout
     * @param array $opts Takes an array of options. Set the 'override' key to
     * whichever tab you want to focus on by default.
     */
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

    /**
     * Returns metadata that renders the componennts for the Filter layout.
     * @return array
     */
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
