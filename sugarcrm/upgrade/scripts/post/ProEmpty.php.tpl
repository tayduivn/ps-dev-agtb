<?php
class SugarUpgrade extends UpgradeScript
{
    public $order = 3000;

    public function run()
    {
        if(!$this->toFlavor('pro')) return;
    }
}
