<?php
require_once('clients/summer/SideBarLayout.php');

class TabbedLayout extends SideBarLayout {
    public function TabbedLayout() {
        $this->baseLayout = new GenericLayout("base");
        $this->layout = new GenericLayout("record-bottom", "record-bottom");
    }

    public function getLayout() {
        $this->baseLayout->push($this->layout->getLayout(true));
        return $this->baseLayout->getLayout();
    }


}
