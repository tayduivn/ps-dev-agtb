<?php
class SugarUpgrade extends UpgradeScript
{
    public $order = 5000;

    public function run()
    {
        if(!($this->from_flavor == 'ce' && $this->toFlavor('pro'))) return;
    }
}
