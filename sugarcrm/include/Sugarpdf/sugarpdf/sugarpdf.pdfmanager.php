<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

//FILE SUGARCRM flav=pro ONLY

require_once 'include/Sugarpdf/sugarpdf/sugarpdf.smarty.php';

class SugarpdfPdfmanager extends SugarpdfSmarty
{
    protected $pdfFilename;

    public function preDisplay()
    {
        parent::preDisplay();

        if (!empty($_REQUEST['pdf_template_id'])) {

            $pdfTemplate = BeanFactory::newBean('PdfManager');
            if ($pdfTemplate->retrieve($_REQUEST['pdf_template_id']) !== null) {

                $previewMode = FALSE;
                if (!empty($_REQUEST['pdf_preview']) && $_REQUEST['pdf_preview'] == 1) {
                    $previewMode = true;
                    $this->bean = BeanFactory::newBean($pdfTemplate->base_module);
                }

                $this->SetCreator(PDF_CREATOR);
                $this->SetAuthor($pdfTemplate->author);
                $this->SetTitle($pdfTemplate->title);
                $this->SetSubject($pdfTemplate->subject);
                $this->SetKeywords($pdfTemplate->keywords);
                $this->templateLocation = $this->buildTemplateFile($pdfTemplate, $previewMode);

                $filenameParts = array();
                if (!empty($this->bean) && !empty($this->bean->name)) {
                    $filenameParts[] = $this->bean->name;
                }
                if (!empty($pdfTemplate->name)) {
                    $filenameParts[] = $pdfTemplate->name;
                }

                $cr = array(' ',"\r", "\n","/");
                $this->pdfFilename = str_replace($cr, '_', implode("_", $filenameParts ).".pdf");
            }
        }


        if ($previewMode === FALSE) {
            require_once 'modules/PdfManager/PdfManagerHelper.php';
            $fields = PdfManagerHelper::parseBeanFields($this->bean, true);
        } else {
            $fields = array();
        }

        if ($this->module == 'Quotes' && $previewMode === FALSE) {
            global $locale;
            require_once 'modules/Quotes/Quote.php';
            require 'modules/Quotes/config.php';
            require_once 'modules/Currencies/Currency.php';
            $currency = new Currency();
            ////    settings
            $format_number_array = array(
                'currency_symbol' => true,
                'type' => 'sugarpdf',
                'currency_id' => $this->bean->currency_id,
                'charset_convert' => true, /* UTF-8 uses different bytes for Euro and Pounds */
            );
            $currency->retrieve($this->bean->currency_id);
            $fields['currency_iso'] = $currency->iso4217;
            $fields['subtotal'] = format_number_sugarpdf($this->bean->subtotal, $locale->getPrecision(), $locale->getPrecision(), $format_number_array);
            $fields['total'] = format_number_sugarpdf($this->bean->total, $locale->getPrecision(), $locale->getPrecision(), $format_number_array);

            $this->bean->load_relationship('product_bundles');
            $product_bundle_list = $this->bean->get_linked_beans('product_bundles','ProductBundle');
            if (is_array($product_bundle_list)) {

              $ordered_bundle_list = array();
              for ($cnt = 0; $cnt < count($product_bundle_list); $cnt++) {
                $index = $product_bundle_list[$cnt]->get_index($this->bean->id);
                $ordered_bundle_list[(int) $index[0]['bundle_index']] = $product_bundle_list[$cnt];
              } //for
              ksort($ordered_bundle_list);
            } //if

            $ordered_bundle_list_data = array();
            $bundles = array();
            $count = 0;
            foreach ($ordered_bundle_list as $ordered_bundle) {

                $bundleFields = PdfManagerHelper::parseBeanFields($ordered_bundle, true);
                $product_bundle_line_items = $ordered_bundle->get_product_bundle_line_items();
                foreach ($product_bundle_line_items as $product_bundle_line_item) {

                    $bundleFields['products'][$count] = PdfManagerHelper::parseBeanFields($product_bundle_line_item, true);

                    if ($product_bundle_line_item->object_name == "ProductBundleNote") {
                        $bundleFields['products'][$count]['name'] = $bundleFields['products'][$count]['description'];
                    }
                    $count++;
                }
                $bundles[] = $bundleFields;
            }

            $this->ss->assign('product_bundles', $bundles);
        }

         $this->ss->assign('fields', $fields);
    }

    /**
     * Build the Email with the attachement
     *
     * @param $file_name
     * @param $focus
     * @return $email_id
     */
    private function buildEmail ($file_name, $focus) {
        
        global $mod_strings;
        global $current_user;

        //First Create e-mail draft
        $email_object = BeanFactory::newBean("Emails");
        // set the id for relationships
        $email_object->id = create_guid();
        $email_object->new_with_id = true;

        //subject
        $email_object->name = $focus->name;
        //body
        $email_object->description_html = sprintf(translate('LBL_EMAIL_PDF_DEFAULT_DESCRIPTION', "PdfManager"), $file_name);
        $email_object->description = html_entity_decode($email_object->description_html,ENT_COMPAT,'UTF-8');

        //parent type, id
        $email_object->parent_type = $focus->module_name;
        $email_object->parent_id = $focus->id;
        //type is draft
        $email_object->type = "draft";
        $email_object->status = "draft";

        $email_object->to_addrs_ids = $focus->id;
        $email_object->to_addrs_names = $focus->name.";";

        if (isset($focus->emailAddress)) {
            $to_addrs = $focus->emailAddress->getPrimaryAddress($focus);
            $email_object->to_addrs_emails = $to_addrs.";";
            $email_object->to_addrs = $focus->name." <".$to_addrs.">";
        }
        elseif( $focus->module_name == "Quotes" ) {
            // link the sent pdf to the relevant account
            if(isset($focus->billing_account_id) && !empty($focus->billing_account_id)) {
                $email_object->load_relationship('accounts');
                $email_object->accounts->add($focus->billing_account_id);
            }

            //check to see if there is a billing contact associated with this quote
            if(!empty($focus->billing_contact_id) && $focus->billing_contact_id!="") {
                $contact = BeanFactory::newBean("Contacts");
                $contact->retrieve($focus->billing_contact_id);

                if(!empty($contact->email1) || !empty($contact->email2)) {
                    //contact email is set
                    $email_object->to_addrs_ids = $focus->billing_contact_id;
                    $email_object->to_addrs_names = $focus->billing_contact_name.";";

                    if(!empty($contact->email1)){
                        $email_object->to_addrs_emails = $contact->email1.";";
                        $email_object->to_addrs = $focus->billing_contact_name." <".$contact->email1.">";
                    } elseif(!empty($contact->email2)){
                        $email_object->to_addrs_emails = $contact->email2.";";
                        $email_object->to_addrs = $focus->billing_contact_name." <".$contact->email2.">";
                    }

                    // create relationship b/t the email(w/pdf) and the contact
                    $contact->load_relationship('emails');
                    $contact->emails->add($email_object->id);
                }//end if contact name is set
            } elseif(isset($focus->billing_account_id) && !empty($focus->billing_account_id)) {
                $acct = BeanFactory::newBean("Accounts");
                $acct->retrieve($focus->billing_account_id);

                if(!empty($acct->email1) || !empty($acct->email2)) {
                    //acct email is set
                    $email_object->to_addrs_ids = $focus->billing_account_id;
                    $email_object->to_addrs_names = $focus->billing_account_name.";";

                    if(!empty($acct->email1)){
                        $email_object->to_addrs_emails = $acct->email1.";";
                        $email_object->to_addrs = $focus->billing_account_name." <".$acct->email1.">";
                    } elseif(!empty($acct->email2)){
                        $email_object->to_addrs_emails = $acct->email2.";";
                        $email_object->to_addrs = $focus->billing_account_name." <".$acct->email2.">";
                    }

                    // create relationship b/t the email(w/pdf) and the acct
                    $acct->load_relationship('emails');
                    $acct->emails->add($email_object->id);
                }//end if acct name is set
            }
        }

        if (isset($email_object->team_id)) {
            $email_object->team_id  = $current_user->getPrivateTeamID();
        }
        if (isset($email_object->team_set_id)) {
            $teamSet = new TeamSet();
            $teamIdsArray = array($current_user->getPrivateTeamID());
            $email_object->team_set_id = $teamSet->addTeams($teamIdsArray);
        }

        $email_object->assigned_user_id = $current_user->id;

        //Save the email object
        global $timedate;
        $email_object->date_start = $timedate->to_display_date_time(gmdate($GLOBALS['timedate']->get_db_date_time_format()));
        $email_object->save(FALSE);
        $email_id = $email_object->id;
        
        $email_object->save(FALSE);
        $email_id = $email_object->id;

        //Handle PDF Attachment
        $note = BeanFactory::newBean("Notes");
        $note->filename = $file_name;
        $note->file_mime_type = $email_object->email2GetMime($GLOBALS['sugar_config']['upload_dir'].$file_name);
        $note->name = translate('LBL_EMAIL_ATTACHMENT', "Quotes").$file_name;

        $note->parent_id = $email_object->id;
        $note->parent_type = $email_object->module_name;
        
        //teams
        $note->team_id = $current_user->getPrivateTeamID();
        $noteTeamSet = new TeamSet();
        $noteteamIdsArray = array($current_user->getPrivateTeamID());
        $note->team_set_id = $noteTeamSet->addTeams($noteteamIdsArray);
        
        $note->save();
        $note_id = $note->id;

	    $source = $GLOBALS['sugar_config']['upload_dir'].$file_name;
	    $destination = $GLOBALS['sugar_config']['upload_dir'].$note_id;
        
        if (!copy($source, $destination)){
            $msg = str_replace('$destination', $destination, translate('LBL_RENAME_ERROR', "Quotes"));
            die($msg);
        }

        @unlink($source);

        //return the email id
        return $email_id;
    }
    
    /**
     * Build the template file for smarty to parse
     *
     * @param $pdfTemplate
     * @param $previewMode
     * @return $tpl_filename
     */
    private function buildTemplateFile($pdfTemplate, $previewMode = FALSE)
    {
        if (!empty($pdfTemplate)) {

            if ( ! file_exists($GLOBALS['sugar_config']['cache_dir'] . 'modules/PdfManager/tpls') ) {
                mkdir_recursive($GLOBALS['sugar_config']['cache_dir'] . 'modules/PdfManager/tpls');
            }
            $tpl_filename = $GLOBALS['sugar_config']['cache_dir'] . 'modules/PdfManager/tpls/' . $pdfTemplate->id . '.tpl';

            $pdfTemplate->body_html = from_html($pdfTemplate->body_html);

            if ($previewMode !== FALSE) {
                $tpl_filename = $GLOBALS['sugar_config']['cache_dir'] . 'modules/PdfManager/tpls/' . $pdfTemplate->id . '_preview.tpl';
                $pdfTemplate->body_html = str_replace(array('{', '}'), array('&#123;', '&#125;'), $pdfTemplate->body_html);
            }

            if ($pdfTemplate->base_module == 'Quotes') {

                $pdfTemplate->body_html = str_replace(
                    '$fields.product_bundles',
                    '$bundle',
                    $pdfTemplate->body_html
                );

                $pdfTemplate->body_html = str_replace(
                    '$fields.products',
                    '$product',
                    $pdfTemplate->body_html
                );
                
                $pdfTemplate->body_html = str_replace(
                    '<!--{START_BUNDLE_LOOP}-->',
                    '{foreach from=$product_bundles item="bundle"}',
                    $pdfTemplate->body_html
                );
                
                $pdfTemplate->body_html = str_replace(
                    '<!--{START_PRODUCT_LOOP}-->',
                    '{foreach from=$bundle.products item="product"}',
                    $pdfTemplate->body_html
                );
                
                $pdfTemplate->body_html = str_replace(
                    array("<!--{END_PRODUCT_LOOP}-->", "<!--{END_BUNDLE_LOOP}-->"),
                    '{/foreach}',
                    $pdfTemplate->body_html
                );
                
            }

            sugar_file_put_contents($tpl_filename, $pdfTemplate->body_html);

            return $tpl_filename;
        }

        return '';
    }

    /**
     * Set the file name and manage the email attachement output
     *
     * @see TCPDF::Output()
     */
    public function Output($name="doc.pdf", $dest='I')
    {
        if (!empty($this->pdfFilename)) {
            $name = $this->pdfFilename;
        }

        // This case is for "email as PDF"
        if (isset($_REQUEST['to_email']) && $_REQUEST['to_email']=="1") {
            // After the output the object is destroy
            
            $bean = $this->bean;

            $tmp = parent::Output('','S');
            $badoutput = ob_get_contents();
            if(strlen($badoutput) > 0) {
                ob_end_clean();
            }
            file_put_contents($GLOBALS['sugar_config']['upload_dir'].$name, ltrim($tmp));

            $email_id = $this->buildEmail($name, $bean);

            //redirect
            if($email_id=="") {
                //Redirect to quote, since something went wrong
                echo "There was an error with your request";
                exit; //end if email id is blank
            } else {
                SugarApplication::redirect("index.php?module=Emails&action=Compose&record=".$email_id."&replyForward=true&reply=");
            }

            parent::Output($name,'D');
        }

        parent::Output($name, 'D');
    }
}
