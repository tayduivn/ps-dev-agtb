<?php
/**
 * Creates a layout for views that include the filter panel.
 *
 * This view is used mostly on list views for items.
 */
class FilterPanelLayout
{
    protected $defaultTab = array("name" => "Activity Stream", "toggles" => array("activitystream", "timeline", "calendar"));
    protected $layout;
    protected $baseLayout;

    /**
     * Constructor for FilterPanel Layout
     * @param array $opts Takes an array of options. Set the 'override' key to
     * whichever tab you want to focus on by default.
     */
    public function __construct($opts = array())
    {
        $this->layout = MetaDataManager::getLayout('GenericLayout', array('name' => 'tabbed-layout', 'type' => 'tabbed-layout'));
        $this->baseLayout = MetaDataManager::getLayout('GenericLayout', array('name' => 'base'));;

        if (!isset($opts["override"])) {
            $this->push($this->defaultTab);
        }
    }

    /**
     * Adds a new tab. The tab is wrapped in a filter panel function
     * @param $tab array("context" => array("link" => "Contacts"), "toggles" => array("activitystream", "list"), [OPTIONAL] "filter" => false)
     */
    public function push($tab)
    {
        if (isset($tab["filter"]) && $tab["filter"] === false) {
            $filteredLayout = MetaDataManager::getLayout("GenericLayout");
            $filteredLayout->push($tab);
        } else {
            $filteredLayout = MetaDataManager::getLayout("GenericLayout", array("type" => "filterpanel"));

            foreach ($tab["toggles"] as $toggle) {
                $component = array("view" => $toggle);

                if ($tab["context"]) {
                    $component["context"] = $tab["context"];
                }

                $filteredLayout->push($component);
            }
        }

        $this->layout->push($filteredLayout->getLayout(true));
    }

    /**
     * Returns metadata that renders the componennts for the Filter layout.
     * @return array
     */
    public function getLayout()
    {
        $this->baseLayout->push($this->layout->getLayout(true));

        return $this->baseLayout->getLayout();
    }
}
