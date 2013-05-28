<?php
/**
 * Class SubPanelLayout
 * Custom layout for Subpanels
 */
class SubPanelLayout {
    protected $layout;
    protected $baseLayout;

    /**
     * Create layout for each item in $subpanels
     * @param array $subpanels Array of subpanel definitions
     */
    public function __construct(array $subpanels = array())
    {
        $this->layout = MetaDataManager::getLayout("GenericLayout", array("type" => "subpanel"));
        $this->baseLayout = MetaDataManager::getLayout("GenericLayout", array("name" => "subpanel"));
        $this->buildPanels($subpanels);
        $this->layout->set("subpanelList", $subpanels);
    }
    /**
     * Construct a subpanel layout
     * @param $subpanel Subpanel definition
     */
    public function push($subpanel)
    {
        $panel = MetaDataManager::getLayout("GenericLayout", array("type" => "panel", "name" => $subpanel["name"]));
        $panel->push(array("view" => "panel-top")); // Subpanel header
        $panel->push(array("view" => "massupdate")); // Needed for mass update
        $panel->push(array("view" => "panel-list")); // Custom subpanel recordlistview
        $panel->push(array("view" => "list-bottom")); // Standard list bottom for pagination
        $this->layout->push(array_merge(array("layout" => $panel->getLayout()), $subpanel));
    }

    public function getLayout()
    {
        $this->baseLayout->push($this->layout->getLayout(true));
        return $this->layout->getLayout();
    }

    protected function buildPanels($subpanels)
    {
        foreach ($subpanels as $lang_key => $link) {
            $this->push(array("name" => $lang_key, "context" => array("link" => $link)));
        }
    }
}
