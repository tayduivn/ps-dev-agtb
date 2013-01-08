<?php
/**
 * Creates a layout for views that include the filter panel.
 *
 * This view is used mostly on list views for items.
 */
class FilterPanelLayout
{
    protected $defaultTab = array("name" => "Activity Stream", "toggles" => array("activitystream", "timeline"));
    protected $layout;
    protected $baseLayout;
    protected $count = 0;

    /**
     * Constructor for FilterPanel Layout
     * @param array $opts Takes an array of options. Set the 'override' key to
     *  - override
     *  - notabs
     * whichever tab you want to focus on by default.
     */
    public function __construct($opts = array())
    {
        $this->layout = MetaDataManager::getLayout('GenericLayout', array('name' => 'tabbed-layout', 'type' => 'tabbed-layout'));
        $this->baseLayout = MetaDataManager::getLayout('GenericLayout', array('name' => 'base'));

        if (!isset($opts["override"])) {
            $this->push($this->defaultTab);
        }

        foreach ($opts as $name => $option) {
            $this->layout->set($name, $option);
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

            // Add a filter view
            $filteredLayout->push(array("view" => "filter"));

            if (isset($tab["name"])) {
                $filteredLayout->set("name", $tab["name"]);
            }

            foreach ($tab["toggles"] as $type => $toggle) {
                if(is_string($type)) {
                    $filteredLayout->push(array($type => $toggle));
                } else {
                    $component = array("view" => $toggle);

                    if (isset($tab["context"])) {
                        $component["context"] = $tab["context"];
                    }

                    $filteredLayout->push($component);
                }
            }

            // Add the filter create view.
            $filteredLayout->push(array("view" => "filter-create"));
        }

        $this->layout->push($filteredLayout->getLayout(true));

        $this->count++;
    }

    /**
     * Returns metadata that renders the components for the Filter layout.
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
