<?php
/**
 * Creates a layout for views that include the filter panel.
 *
 * This view is used mostly on list views for items.
 */
class FilterPanelLayout
{
    protected $defaultToggles = array("activitystream", "subpanel");
    protected $initialToggle = "activitystream";
    protected $layout;
    protected $count = 0;

    /**
     * Constructor for FilterPanel Layout
     * @param array $opts Takes an array of options. Set the 'override' key to
     * whichever tab you want to focus on by default.
     */
    public function __construct($opts = array())
    {
        $this->layout = MetaDataManager::getLayout("GenericLayout", array("type" => "filterpanel"));

        // Add header view and subpanel layout
        $this->layout->push(array("layout" => "filter"));
        $this->layout->push(array("view" => "filter-create"));

        $this->layout->set("toggles", $this->defaultToggles);

        $this->layout->push(
            array(
                'layout' => 'activitystream',
                'context' => array('module' => 'Activities'),
            )
        );

        // Set default toggle
        $this->layout->set("default", (isset($opts["default"]) ? $opts["default"] : $this->initialToggle));
    }

    /**
     * Adds a new tab. The tab is wrapped in a filter panel function
     * @param $panel array("context" => array("link" => "Contacts"), "toggles" => array("activitystream", "list"), [OPTIONAL] "filter" => false)
     */
    public function push($panel)
    {
        $this->layout->push($panel);
    }


    /**
     * Returns metadata that renders the components for the tab layout.
     *
     * @return array
     */
    public function getLayout()
    {
        return $this->layout->getLayout();
    }
}
