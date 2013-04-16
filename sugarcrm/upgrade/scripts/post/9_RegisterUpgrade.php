<?php
/**
 * Register upgrade with the system
 */
class SugarUpgradeRegisterUpgrade extends UpgradeScript
{
    public $order = 9900;
    public $type = self::UPGRADE_DB;

    public function run()
    {
	    // if error was encountered, script should have died before now
		$new_upgrade = new UpgradeHistory();
		$new_upgrade->filename = $this->context['zip'];
		$new_upgrade->md5sum = md5_file($this->context['zip']);
		$new_upgrade->name = pathinfo($this->context['zip'], PATHINFO_FILENAME);
		$new_upgrade->description = $this->manifest['description'];
		$new_upgrade->type = 'patch';
		$new_upgrade->version = $this->to_version;
		$new_upgrade->status = "installed";
		$new_upgrade->manifest = json_encode($this->manifest);
		$new_upgrade->save();
    }
}
