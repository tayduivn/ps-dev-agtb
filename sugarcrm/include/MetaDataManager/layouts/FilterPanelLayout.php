<?php
/**
 * Creates a layout for views that include the filter panel.
 *
 * This view is used mostly on list views for items.
 */
class FilterPanelLayout
{
    protected $defaultToggles = array("activitystream", "subpanel");
    protected $layout;
    protected $baseLayout;
    protected $count = 0;

    /**
     * Constructor for FilterPanel Layout
     * @param array $opts Takes an array of options. Set the 'override' key to
     * whichever tab you want to focus on by default.
     */
    public function __construct($opts = array())
    {
        $this->layout = MetaDataManager::getLayout("GenericLayout", array("type" => "filterpanel"));
        $this->baseLayout = MetaDataManager::getLayout("GenericLayout", array("name" => "base"));

        // Add header view and subpanel layout
        $this->layout->push(array("view" => "filter"));
        $this->subpanels = MetaDataManager::getLayout("GenericLayout", array("name" => "subpanel", "type" => "subpanel"));

        $this->layout->set("toggles", $this->defaultToggles);
    }

    /**
     * Adds a new tab. The tab is wrapped in a filter panel function
     * @param $panel array("context" => array("link" => "Contacts"), "toggles" => array("activitystream", "list"), [OPTIONAL] "filter" => false)
     */
    public function push($panel)
    {
        $this->subpanels->push($panel);
    }


    /**
     * Returns metadata that renders the components for the tab layout.
     *
     * @return array
     */
    public function getLayout()
    {
        $this->layout->push($this->subpanels->getLayout(true));
        $this->baseLayout->push($this->layout->getLayout(true));
        return $this->baseLayout->getLayout();
    }
}
