<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class sendUpdateEmail  {
    function send(&$bean, $event, $arguments) {
        global $timedate, $app_list_strings, $sugar_config, $current_user;
        if(isset($bean->id)){
            $fields_to_track = array('assigned_user_id', 'priority', 'status', 'resolution',);
            $fields_labels = array(
                'assigned_user_id' => 'Assigned To',
                'priority' => 'Priority',
                'status' => 'Status',
                'resolution' => 'Work Log',
            );

            $oldFocus = new ITRequest();
            $oldFocus->retrieve($bean->id);
            
            if(!isset($oldFocus->id)) {
                return false;
            }
            if(!in_array($oldFocus->status, $app_list_strings['itrequest_closed_statuses_dom']) && in_array($bean->status, $app_list_strings['itrequest_closed_statuses_dom'])){
                // SUGARINTERNAL CUSTOMIZATION - ITR: 16710 - jwhitcraft
                // SET THE FORMAT TO GET DB_DATE_TIME FORMAT.
                $bean->date_resolved = gmdate($GLOBALS['timedate']->get_db_date_time_format());
                // END SUGARINTERNAL CUSTOMIZATION
            }

            foreach ($fields_to_track as $field) {
                if ($bean->$field != $bean->fetched_row[$field]) {
                    $changed_fields[] = $field;
                }
            }

                // SEND STATUS NOTIFICATION IF THE STATUS CHANGES
            if(!empty($changed_fields)){
                require_once("modules/Administration/Administration.php");
                require_once("modules/Users/User.php");
                require_once("include/SugarPHPMailer.php");

                $related_users = $bean->get_linked_beans('users', 'User');

                if($GLOBALS['current_user']->id != $bean->created_by){
                    $created_user = new User();
                    $created_user->retrieve($bean->created_by);
                    $related_users[] = $created_user;
                }

                if(isset($GLOBALS['current_user']) && !empty($bean->assigned_user_id) && $GLOBALS['current_user']->id != $bean->assigned_user_id){
                    $assigned_user = new User();
                    $assigned_user->retrieve($bean->assigned_user_id);
                    $related_users[] = $assigned_user;
                }

                $admin = new Administration();
                $admin->retrieveSettings();

                foreach($related_users as $notify_user){
                    $notify_mail = new SugarPHPMailer();

                    $body = "IT Request {$bean->itrequest_number} has been updated by {$GLOBALS['current_user']->user_name} (* denotes an updated field).\n\n";
                    $body .= "Subject: {$bean->name}\n";
                    $body .= "Created By: ".get_assigned_user_name($bean->created_by, '')."\n";
                    if(!empty($bean->target_date)){
                        $body .= "Target Date: {$bean->target_date}\n";
                    }
                    foreach($fields_to_track as $field_name){
                        if(in_array($field_name, $changed_fields)){
                            $body .= "(*) ";
                        }
                        $body .= "{$fields_labels[$field_name]}: ";

                        $field_value = $bean->$field_name;
                        if($field_name == 'assigned_user_id' || $field_name == 'created_by'){
                            $field_value = get_assigned_user_name($field_value, '');
                        }
                        else if(isset($bean->field_name_map[$field_name]['type']) && $bean->field_name_map[$field_name]['type'] == 'enum'
                                && isset($bean->field_name_map[$field_name]['options'])){
                            if(isset($app_list_strings[$bean->field_name_map[$field_name]['options']][$field_value])){
                                $field_value = $app_list_strings[$bean->field_name_map[$field_name]['options']][$field_value];
                            }
                        }
                        $body .= $field_value."\n";
                    }
                    $body .= "\nClick on the link below to view the record.\n";
                    $body .= $sugar_config['site_url']."/index.php?module=ITRequests&action=DetailView&record={$bean->id}\n";

                    $body = from_html($body);
                    $notify_mail->Body = $body;

                    $subject = "Updated: IT Request {$bean->itrequest_number} - {$bean->name}";
                    $subject = from_html($subject);
                    $notify_mail->Subject = $subject;

                    $notify_address = (empty($notify_user->email1)) ? from_html($notify_user->email2) : from_html($notify_user->email1);
                    $notify_name = (empty($notify_user->first_name)) ? from_html($notify_user->user_name) : from_html($notify_user->first_name . " " . $notify_user->last_name);

                    $notify_mail->AddAddress($notify_address, $notify_name);

                    if ($admin->settings['mail_sendtype'] == "SMTP")
                    {
                        $notify_mail->Mailer = "smtp";
                        $notify_mail->Host = $admin->settings['mail_smtpserver'];
                        $notify_mail->Port = $admin->settings['mail_smtpport'];
                        if ($admin->settings['mail_smtpauth_req'])
                        {
                            $notify_mail->SMTPAuth = TRUE;
                            $notify_mail->Username = $admin->settings['mail_smtpuser'];
                            $notify_mail->Password = $admin->settings['mail_smtppass'];
                        }
                    }

                    if (empty($admin->settings['notify_send_from_assigning_user']))
                    {
                        $notify_mail->From = $admin->settings['notify_fromaddress'];
                        $notify_mail->FromName = (empty($admin->settings['notify_fromname'])) ? "" : $admin->settings['notify_fromname'];
                    }
                    else
                    {
                        // Send notifications from the current user's e-mail (if set)
                        $from_address = !empty($current_user->email1) ? $current_user->email1 : $admin->settings['notify_fromaddress'];
                        $notify_mail->From = $from_address;
                        $from_name = !empty($admin->settings['notify_fromname']) ? $admin->settings['notify_fromname'] : "";
                        if($current_user->getPreference('mail_fromname') != '')
                        {
                            $from_name = $current_user->getPreference('mail_fromname');
                        }
                        $notify_mail->FromName = $from_name;
                    }

                    if(!$notify_mail->Send())
                    {
                        $GLOBALS['log']->fatal("ITRequest Status Change Notification: error sending e-mail (method: {$notify_mail->Mailer}), (error: {$notify_mail->ErrorInfo})");
                    }
                    else
                    {
                        $GLOBALS['log']->info("ITRequest Status Change Notification: e-mail successfully sent");
                    }
                }
            }
        }
    }
}
