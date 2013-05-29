<?php
/**
 * Update config entries for CE->PRO
 */
class SugarUpgradeConfigUpgrade extends UpgradeScript
{
    public $order = 4000;
    public $type = self::UPGRADE_CUSTOM;

    public function run()
    {
        // only do it when going from ce to non-ce
        if(!($this->from_flavor == 'ce' && $this->to_flavor != 'ce')) return;

        if(isset($this->upgrader->config['sugarbeet']))
        {
            unset($this->upgrader->config['sugarbeet']);
        }

        if(isset($this->upgrader->config['disable_team_access_check']))
        {
            unset($this->upgrader->config['disable_team_access_check']);
        }

        $passwordsetting_defaults = array(
                'minpwdlength' => '',
                'maxpwdlength' => '',
                'oneupper' => '',
                'onelower' => '',
                'onenumber' => '',
                'onespecial' => '',
                'SystemGeneratedPasswordON' => '',
                'generatepasswordtmpl' => '',
                'lostpasswordtmpl' => '',
                'customregex' => '',
                'regexcomment' => '',
                'forgotpasswordON' => false,
                'linkexpiration' => '1',
                'linkexpirationtime' => '30',
                'linkexpirationtype' => '1',
                'userexpiration' => '0',
                'userexpirationtime' => '',
                'userexpirationtype' => '1',
                'userexpirationlogin' => '',
                'systexpiration' => '0',
                'systexpirationtime' => '',
                'systexpirationtype' => '0',
                'systexpirationlogin' => '',
                'lockoutexpiration' => '0',
                'lockoutexpirationtime' => '',
                'lockoutexpirationtype' => '1',
                'lockoutexpirationlogin' => ''
         );

        if(!isset($this->upgrader->config['passwordsetting'])) {
            $this->upgrader->config['passwordsetting'] = $passwordsetting_defaults;
        } else {
            $this->upgrader->config['passwordsetting'] = array_merge($passwordsetting_defaults, $this->upgrader->config['passwordsetting']);
        }

    }
}
