<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) decodesublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/


require_once 'include/OutboundEmail/OutboundEmail.php';

class MailConfiguration {
    var $user;

    function __construct(User $user)
    {
        $this->user = $user;
    }


    /**
     * @return list of valid Email Accounts (From Name, From Email Address, Outbound Mail configuration id)
     */
    public function getFromAccounts($systemOnly=false,$expand=false) {
        $fromAccounts = array();
        $oe = new OutboundEmail();
        $system = $oe->getSystemMailerSettings();
        $ret = $this->user->getUsersNameAndEmail();
        if (empty($ret['email'])) {
            $systemReturn = $this->user->getSystemDefaultNameAndEmail();
            $ret['email'] = $systemReturn['email'];
            $ret['name'] = from_html($systemReturn['name']);
        } else {
            $ret['name'] = from_html($ret['name']);
        }

        if (!$systemOnly) {
            $ie = new InboundEmail();
            $ieAccounts = $ie->retrieveAllByGroupIdWithGroupAccounts($this->user->id);
            foreach($ieAccounts as $k => $v) {
                $name = $v->get_stored_options('from_name');
                $addr = $v->get_stored_options('from_addr');
                $storedOptions = unserialize(base64_decode($v->stored_options));
                // var_dump($storedOptions);
                if ($name != null && $addr != null) {
                    $name = from_html($name);
                    $fromAccount = array (
                        "id"     => $storedOptions["outbound_email"],
                        "type"   => "user",
                        "name"   => "{$name}",
                        "email"  => "{$addr}",
                        "text"   => "{$name} ({$addr})",
                        "personal" => (bool) ($v->is_personal),
                    );

                    if ($expand) {
                        $oe = new OutboundEmail();
                        $oe->retrieve($storedOptions["outbound_email"]);
                        $config = $this->toArray($oe);
                        unset($config['id']);
                        $fromAccount["config"] = $config;
                    }
                    $fromAccounts[] = $fromAccount;
                } // if
            } // foreach
        }

        //Substitute in the users system override if its available.
        $userSystemOverride = $oe->getUsersMailerForSystemOverride($this->user->id);
        $personal = false;
        if($userSystemOverride != null) {
            $system = $userSystemOverride;
            $personal = true;
        }
        if (!empty($system->mail_smtpserver)) {
            $fromAccount = array (
                "id"     => $system->id,
                "type"   => "system",
                "name"   => "{$ret['name']}",
                "email"  => "{$ret['email']}",
                "text"   => "{$ret['name']} ({$ret['email']})",
                "personal" => $personal,
            );

            if ($expand) {
                $oe = new OutboundEmail();
                $oe->retrieve($system->id);
                $config = $this->toArray($oe);
                unset($config['id']);
                $fromAccount["config"] = $config;
            }
            $fromAccounts[] = $fromAccount;
        }

        return $fromAccounts;
    }


    private function toArray($obj, $scalarOnly=true)
    {
        $fields = get_object_vars($obj);
        $arr = array();

        foreach($fields as $name => $type) {
            if (isset($obj->$name)) {
                if ((!$scalarOnly) || ( !is_array($obj->$name) && !is_object($obj->$name)) )
                    $arr[$name] = $obj->$name;
            } else {
                $arr[$name] = '';
            }
        }
        return $arr;
    }
}