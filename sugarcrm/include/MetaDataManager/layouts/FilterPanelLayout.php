<?php
/**
 * Creates a layout for views that include the filter panel.
 *
 * This view is used mostly on list views for items.
 */
class FilterPanelLayout
{
    protected $defaultToggles = array("activitystream", "subpanels");
    protected $initialToggle = "subpanels";
    protected $layout;
    protected $count = 0;

    protected $availableToggles = array(
        'activitystream' => array(
            'icon' => 'icon-th-list',
            'label' => 'LBL_ACTIVITY_STREAM',
        ),
        'subpanel' => array(
            'icon' => 'icon-table',
            'label' => 'LBL_DATA_VIEW',
        ),
        'list' => array(
            'icon' => 'icon-table',
            'label' => 'LBL_LISTVIEW',
        ),
    );

    /**
     * Constructor for FilterPanel Layout
     * @param array $opts Takes an array of options. Set the 'override' key to
     * whichever tab you want to focus on by default.
     */
    public function __construct($opts = array())
    {
        $this->layout = MetaDataManager::getLayout("GenericLayout", array("type" => "filterpanel"));

        // Set default toggle
        $toggles = isset($opts["toggles"]) ? $opts["toggles"] : $this->defaultToggles;
        $this->layout->set("default", (isset($opts["default"]) ? $opts["default"] : $this->initialToggle));

        // Add header view and subpanel layout
        $filterLayout = array(
            'layout' => 'filter',
            'targetEl' => '.filter',
            'position' => 'prepend'
        );
        if (isset($opts['layoutName'])) {
            $filterLayout['context']['layoutName'] = $opts['layoutName'];
        }
        $this->layout->push($filterLayout);
        $this->layout->push(array(
                "view" => "filter-actions",
                "targetEl" => '.filter-options'
            ));
        $this->layout->push(array(
                "view" => "filter-rows",
                "targetEl" => '.filter-options'
            ));

        if (in_array('activitystream', $toggles)) {
            $this->layout->push(
                array(
                    'layout' => 'activitystream',
                    'context' => array('module' => 'Activities'),
                )
            );
        }

        $this->layout->set("toggles", $toggles);
        $this->layout->set('availableToggles', $this->availableToggles);
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
